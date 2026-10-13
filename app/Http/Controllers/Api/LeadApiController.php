<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreLeadApiRequest;
use App\Services\LeadApiService;
use App\Services\LeadService;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeadApiController extends Controller
{
    protected $service;
    protected $leadService;

    public function __construct()
    {
        $this->service = new LeadApiService();
        $this->leadService = new LeadService();
    }

    public function getBoards()
    {
        $boards = $this->service->getBoards();
        return response()->json([
            'success' => true,
            'boards' => $boards,
        ]);
    }

    /**
     * Store a new lead via API
     *
     * @param StoreLeadApiRequest $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {

        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'phone' => ['required', 'string', 'regex:/^\+998\(\d{2}\)\d{3}-\d{2}-\d{2}$/'],
            'board_id' => 'required|exists:kanban_boards,id',
            'course_id' => 'nullable|exists:courses,id',
            'chosen_days' => 'nullable|in:odd,even',
            'chosen_time' => 'nullable|date_format:H:i:s',
            'comment' => 'nullable|string|max:500',
            'teacher_ids' => 'nullable|array',
            'teacher_ids.*' => 'nullable|exists:users,id',
            'api_key' => 'required|string'
        ], [
            'phone.regex' => 'Phone number must be in the format +998(XX)XXX-XX-XX',
            'chosen_days.in' => 'Chosen days must be either odd or even',
        ]);

        // Validate API key
        if (!$this->validateApiKey($validated['api_key'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key',
            ], 401);
        }

        // Remove API key from data
        unset($validated['api_key']);

        // Store the lead
        $response = $this->service->storeLead($validated);

        // Return success response
        return response()->json([
            'success' => $response['success'],
            'message' => $response['message'],
            'lead' => $response['lead'] ?? null,
        ], $response['success'] ? 201 : 400);
    }

    /**
     * Update lead order and collection
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'collection_id' => 'required|exists:lead_collections,id',
            'order' => 'required|integer',
            'reorder_items' => 'required|array',
            'reorder_items.*.id' => 'required|exists:leads,id',
            'reorder_items.*.order' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();

            // Update the moved lead
            $lead = Lead::findOrFail($validated['lead_id']);
            $oldCollectionId = $lead->collection_id;

            $lead->update([
                'collection_id' => $validated['collection_id'],
                'order' => $validated['order']
            ]);

            // Update order of all affected leads
            foreach ($validated['reorder_items'] as $item) {
                Lead::where('id', $item['id'])->update(['order' => $item['order']]);
            }

            // Log the changes for debugging
            Log::info('Lead moved successfully', [
                'lead_id' => $validated['lead_id'],
                'from_collection' => $oldCollectionId,
                'to_collection' => $validated['collection_id'],
                'new_order' => $validated['order'],
                'reorder_items_count' => count($validated['reorder_items'])
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Lead order updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to update lead order', [
                'error' => $e->getMessage(),
                'lead_id' => $validated['lead_id'] ?? null,
                'collection_id' => $validated['collection_id'] ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update lead order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Load more leads for infinite scroll
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function loadMore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'collection_id' => 'required|exists:lead_collections,id',
            'offset' => 'required|integer|min:0',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $limit = $validated['limit'] ?? 50;
        $offset = $validated['offset'];
        $collectionId = $validated['collection_id'];

        try {
            $leads = $this->leadService->loadMoreLeads($collectionId, $offset, $limit);

            // Check if there are more records beyond current offset + limit
            $totalCount = $this->leadService->getCollectionLeadCount($collectionId);
            $hasMore = ($offset + $leads->count()) < $totalCount;

            return response()->json([
                'success' => true,
                'leads' => $leads,
                'hasMore' => $hasMore
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load more leads: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate the API key
     *
     * @param string $apiKey
     * @return bool
     */
    private function validateApiKey(string $apiKey): bool
    {
        // Get the valid API key from environment variables
        $validApiKey = settings('leads_api_key');

        // Return true if the API key matches
        return $apiKey === $validApiKey;
    }

    /**
     * Get lead details with optimized performance
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $lead = Lead::with([
                'collection:id,name',
                'causer:id,name'
            ])
                ->select([
                    'id',
                    'name',
                    'phone',
                    'status',
                    'comment',
                    'collection_id',
                    'causer_id',
                    'created_at',
                    'updated_at'
                ])
                ->findOrFail($id);

            return response()->json($lead);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch lead details: ' . $e->getMessage()
            ], 500);
        }
    }
}
