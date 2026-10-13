<?php

namespace App\Http\Controllers;

use App\Http\Requests\Lead\LeadFormRequest;
use App\Http\Requests\Lead\StoreStudentRequest;
use App\Models\Lead;
use App\Models\LeadCall;
use App\Models\LeadCollection;
use App\Models\LeadComment;
use App\Models\LeadReminder;
use App\Models\User;
use App\Services\LeadDynamicFieldService;
use App\Services\LeadService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

class LeadController extends Controller
{
    public $service;

    protected LeadDynamicFieldService $leadDynamicFieldService;

    public function __construct()
    {
        $this->service = new LeadService();
        $this->leadDynamicFieldService = app(LeadDynamicFieldService::class);
        $this->middleware('permission:lead-index')->only(['index', 'leadsBoard']);
    }

    public function index()
    {
        $leads_page = $this->service->getLeadsPage();
        if ($leads_page == 'table') {
            return redirect()->route('admin.leads.table');
        } else {
            return view('admin.leads.index');
        }
    }

    public function leadsBoard()
    {
        return view('admin.leads.index');
    }

    public function create()
    {
        $teachers = $this->service->getTeachers();
        $boards = $this->service->getBoards();
        $courses = $this->service->getCourses();

        $selectedBoard = request()->get('board');

        return view('admin.leads.create', [
            'teachers' => $teachers,
            'boards' => $boards,
            'courses' => $courses,
            'selectedBoard' => $selectedBoard,
        ]);
    }

    public function edit($id)
    {
        $item = $this->service->getLead($id);
        $teachers = $this->service->getTeachers();
        $boards = $this->service->getBoards();
        $courses = $this->service->getCourses();

        return view('admin.leads.edit', [
            'item' => $item,
            'teachers' => $teachers,
            'boards' => $boards,
            'courses' => $courses,
        ]);
    }

    public function storeLead(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|max:200',
            'phone' => 'required',
            'phone2' => 'nullable',
            'address' => 'nullable',
            'chosen_days' => 'sometimes',
            'chosen_time' => 'sometimes',
            'teachers' => 'sometimes',
            'board' => 'sometimes',
            'course' => 'sometimes',
            'comment' => 'sometimes',
            'reminder_date' => 'sometimes',
            'reminder_time' => 'sometimes',
            'reminder_note' => 'sometimes',
        ]);

        $this->service->storeLead($data);
        session()->flash('success_alert', __('translation.alert_messages.created'));

        return redirect()->route('admin.leads.table');
    }

    public function updateLead(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|max:200',
            'phone' => 'required',
            'phone2' => 'nullable',
            'address' => 'nullable',
            'chosen_days' => 'sometimes',
            'chosen_time' => 'sometimes',
            'teachers' => 'sometimes',
            'board' => 'sometimes',
            'course' => 'sometimes',
            'comment' => 'sometimes',
        ]);

        $this->service->updateLead($data, $id);
        session()->flash('success_alert', __('translation.alert_messages.updated'));

        return redirect()->route('admin.leads.table');
    }

    public function settings()
    {
        return view('admin.leads.settings.index');
    }

    public function createCollection()
    {
        return view('admin.leads.settings.create-collection');
    }

    public function storeCollection(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
        ]);

        $this->service->storeCollection($data);
        session()->flash('success_alert', __('translation.alert_messages.created'));

        return redirect()->route('admin.leads.settings');
    }

    public function editCollection($collection_id)
    {
        $item = $this->service->getCollection($collection_id);

        return view('admin.leads.settings.edit-collection', compact('item'));
    }

    public function updateCollection(Request $request, $collection_id)
    {
        $data = $request->validate([
            'name' => 'required',
        ]);

        $this->service->updateCollection($data, $collection_id);
        session()->flash('success_alert', __('translation.alert_messages.updated'));

        return redirect()->route('admin.leads.settings');
    }

    public function deleteCollection($id)
    {
        $this->service->deleteCollection($id);
        session()->flash('success_alert', __('translation.alert_messages.deleted'));

        return redirect()->route('admin.leads.settings');
    }

    public function createBoard()
    {
        $collections = $this->service->getCollections();
        $groups = $this->service->getGroups();

        return view('admin.leads.settings.create-board', [
            'collections' => $collections,
            'groups' => $groups,
        ]);
    }

    public function storeBoard(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'collection_id' => 'required',
            'group_id' => 'sometimes',
            'success_message' => 'sometimes',
        ]);

        $this->service->storeBoard($data);
        session()->flash('success_alert', __('translation.alert_messages.created'));

        return redirect()->route('admin.leads.settings');
    }

    public function editBoard($board_id)
    {
        $item = $this->service->getBoard($board_id);
        $collections = $this->service->getCollections();
        $groups = $this->service->getGroups();

        return view('admin.leads.settings.edit-board', [
            'item' => $item,
            'collections' => $collections,
            'groups' => $groups,
        ]);
    }

    public function updateBoard(Request $request, $board_id)
    {
        $data = $request->validate([
            'name' => 'required',
            'collection_id' => 'required',
            'group_id' => 'sometimes',
            'success_message' => 'sometimes',
            'form_comment_state' => 'sometimes',
        ]);

        $this->service->updateBoard($data, $board_id);
        session()->flash('success_alert', __('translation.alert_messages.updated'));

        return redirect()->route('admin.leads.settings');
    }

    public function deleteBoard($board_id)
    {
        $this->service->deleteBoard($board_id);
        session()->flash('success_alert', __('translation.alert_messages.deleted'));

        return redirect()->route('admin.leads.settings');
    }

    public function storeStudent(StoreStudentRequest $request)
    {
        $this->service->storeStudent($request->validated());
        session()->flash('success_alert', __('translation.alert_messages.lead_created'));

        return redirect()->route('admin.leads.index');
    }

    public function convertToStudent(StoreStudentRequest $request)
    {
        try {
            $this->service->storeStudent($request->validated());

            return response()->json([
                'success' => true,
                'message' => __('translation.alert_messages.created'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function leadForm($board_url)
    {
        $exists = $this->service->checkLeadExists($board_url);
        if ($exists) {
            return redirect()->route('thank-you');
        }
        $board = $this->service->getBoardWithUrl($board_url);
        $logo = $this->service->getLogo();
        $courses = [];
        if ($board->form_course_state) {
            $courses = $this->service->getCourses($this->service->getBoardBranchIds($board));
        }

        $leadDynamicFields = $this->leadDynamicFieldService->definitions();

        return view('frontend.leads.form', compact('board', 'logo', 'courses', 'leadDynamicFields'));
    }

    /**
     * @throws \Exception
     */
    public function leadStore(LeadFormRequest $request, $board)
    {
        $this->service->leadStore($request->validated(), $board);

        return to_route('thank-you');
    }

    public function thankYou()
    {
        return view('frontend.leads.thank-you', ['success_message' => null]);
    }

    public function addComment(Request $request)
    {
        $data = $request->validate([
            'comment' => 'required',
            'task_id' => 'required',
        ]);

        $this->service->addComment($data);

        session()->flash('success_alert', __('translation.alert_messages.comment_added'));

        return redirect()->route('admin.leads.index');
    }

    public function leadsTable()
    {
        return view('admin.leads.leads-table');
    }

    public function tablePage(Request $request)
    {
        // Use the same leads layout that extends master
        Inertia::setRootView('layouts.leads');

        $search = $request->get('search', '');

        // Get the required data using the service
        $collections = $this->service->getLeadCollections();
        $teachers = $this->service->getTeachers();
        $boards = $this->service->getBoards();
        $courses = $this->service->getCourses();
        $causers = \App\Models\User::select(['id', 'name'])->orderBy('name')->get(); // Get users as causers

        return Inertia::render('Leads/Table', [
            'collections' => $collections,
            'teachers' => $teachers,
            'boards' => $boards,
            'courses' => $courses,
            'causers' => $causers,
            'search' => $search,
            'leadDynamicFields' => $this->leadDynamicFieldService->definitions(),
        ]);
    }

    public function getTableData(Request $request)
    {
        $query = $this->leadTableQuery($request);
        $selectableLeadCount = (clone $query)->count('leads.id');

        $leads = $query
            ->with([
                'reminders:id,lead_id,reminder_date,reminder_status',
            ])
            ->leftJoin('lead_reminders', 'leads.id', '=', 'lead_reminders.lead_id')
            ->select('leads.*')
            ->orderByRaw('CASE WHEN lead_reminders.reminder_date IS NULL THEN 1 ELSE 0 END, CASE WHEN lead_reminders.reminder_status = \'pending\' THEN 0 ELSE 1 END, lead_reminders.reminder_date ASC')
            ->orderBy('leads.created_at', 'desc')
            ->paginate(20);

        return response()->json(array_merge($leads->toArray(), [
            'selectable_total' => $selectableLeadCount,
        ]));
    }

    public function prepareSmsSelection(Request $request): JsonResponse
    {
        $leadIds = $this->leadTableQuery($request)
            ->orderBy('leads.created_at', 'desc')
            ->pluck('leads.id')
            ->unique()
            ->values();

        if ($leadIds->isEmpty()) {
            return response()->json([
                'message' => __('translation.sms.no_receivers'),
            ], 422);
        }

        $token = (string) Str::uuid();
        session()->put("lead_sms_selection.{$token}", $leadIds->all());

        return response()->json([
            'count' => $leadIds->count(),
            'url' => route('admin.sms.create', [
                'sendSmsType' => 'leads',
                'sendSmsSelection' => $token,
            ]),
        ]);
    }

    private function leadTableQuery(Request $request): Builder
    {
        $search = $request->get('search', '');
        $selectedChosenDays = $request->get('selectedChosenDays', '');
        $selectedTeacher = $request->get('selectedTeacher');
        $selectedBoard = $request->get('selectedBoard');
        $selectedCourse = $request->get('selectedCourse');
        $selectedCauser = $request->get('selectedCauser');
        $selectedAttend = $request->get('selectedAttend');
        $includeDeleted = $request->boolean('includeDeleted');
        $customAttributeFilters = $request->input('customAttributeFilters', []);

        if (! is_array($customAttributeFilters)) {
            $customAttributeFilters = [];
        }

        [$startDate, $endDate] = $this->leadTableDateRange($request);

        $relations = [
            'teacher:id,name',
            'collection:id,name',
            'board:id,name',
            'course:id,name',
            'causer:id,name',
        ];

        return Lead::with($relations)
            ->when($includeDeleted, function ($query): void {
                $query->withTrashed();
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search): void {
                    $query->where('leads.name', 'ilike', '%'.$search.'%')
                        ->orWhere(DB::raw("replace(replace(replace(leads.phone,'(',''),')',''),'-','')"), 'ilike', '%'.$search.'%');
                });
            })
            ->when($selectedChosenDays && $selectedChosenDays !== '', function ($query) use ($selectedChosenDays) {
                $query->where('leads.chosen_days', $selectedChosenDays);
            })
            ->when($selectedTeacher, function ($query) use ($selectedTeacher) {
                $query->where('leads.teacher_id', $selectedTeacher);
            })
            ->when($selectedCauser, function ($query) use ($selectedCauser) {
                $query->where('leads.causer_id', $selectedCauser);
            })
            ->when($selectedBoard, function ($query) use ($selectedBoard) {
                $query->where('leads.board_id', $selectedBoard);
            })
            ->when($selectedCourse, function ($query) use ($selectedCourse) {
                $query->where('leads.course_id', $selectedCourse);
            })
            ->when($selectedAttend !== null && $selectedAttend !== '', function ($query) use ($selectedAttend) {
                $query->where('leads.attend', (bool) $selectedAttend);
            })
            ->tap(function ($query) use ($customAttributeFilters): void {
                $this->leadDynamicFieldService->applyTableCustomAttributeFilters($query, $customAttributeFilters);
            })
            ->whereDate('leads.created_at', '>=', $startDate)
            ->whereDate('leads.created_at', '<=', $endDate);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function leadTableDateRange(Request $request): array
    {
        $date = $request->get('date', now()->format('Y.m.d').' - '.now()->format('Y.m.d'));
        $dates = explode(' - ', $date);
        $start = str_replace('.', '-', $dates[0]);
        $end = str_replace('.', '-', $dates[1] ?? $dates[0]);

        return [
            Carbon::parse($start)->startOfDay(),
            Carbon::parse($end)->endOfDay(),
        ];
    }

    public function export(Request $request)
    {
        $query = $this->leadTableQuery($request)
            ->orderBy('leads.created_at', 'desc');

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\LeadsExport($query), 'leads.xlsx');
    }

    public function toggleAttend(Request $request, $leadId)
    {
        $lead = \App\Models\Lead::findOrFail($leadId);
        $lead->update([
            'attend' => ! $lead->attend,
        ]);

        return response()->json([
            'success' => true,
            'attend' => $lead->attend,
        ]);
    }

    public function storeCall($id, Request $request)
    {
        $data = $request->validate([
            'status' => 'required',
            'comment' => 'sometimes',
        ]);

        $this->service->storeCall($id, $data);
        session()->flash('success_alert', __('translation.alert_messages.created'));

        return back();
    }

    public function import()
    {
        $boards = $this->service->getBoards();
        $courses = $this->service->getCourses();

        return view('admin.leads.import', [
            'boards' => $boards,
            'courses' => $courses,
        ]);
    }

    public function importStore(Request $request)
    {
        $data = $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048',
            'board' => 'required',
            'course' => 'sometimes',
        ]);

        try {
            $this->service->importStore($data);
            session()->flash('success_alert', __('translation.alert_messages.imported'));

            return redirect()->route('admin.leads.table');
        } catch (\Exception $e) {
            session()->flash('error_alert', $e->getMessage());

            return redirect()->back();
        }
    }

    public function boardShow($board_id)
    {
        if (! is_numeric($board_id)) {
            abort(404);
        }

        return view('admin.leads.partials.show-board', [
            'item' => $this->service->getBoard($board_id),
        ]);
    }

    public function transferToGroupView($board_id)
    {
        $item = $this->service->getBoard($board_id);
        $groups = $this->service->getGroups();
        $leads = $this->service->getLeads($board_id);

        return view('admin.leads.partials.transfer-to-group', [
            'item' => $item,
            'groups' => $groups,
            'board_id' => $board_id,
            'leads' => $leads,
        ]);
    }

    public function transferToGroup(Request $request, $board_id)
    {
        $data = $request->validate([
            'leads.*.name' => 'required',
            'leads.*.surname' => 'required',
            'leads.*.phone' => 'required',
            'leads.*.gender' => 'required',
        ]);
        $this->service->transferToGroup($data, $board_id);
        session()->flash('success_alert', __('translation.alert_messages.transferred'));

        return redirect()->route('admin.leads.table');
    }

    public function setUrl($board_id)
    {
        $this->service->setUrl($board_id);
        session()->flash('success_alert', __('translation.alert_messages.created'));

        return redirect()->back();
    }

    public function callsIndex()
    {
        return view('admin.leads.calls.index');
    }

    public function getReminders(Lead $lead)
    {
        $reminders = $lead->reminders()
            ->with('causer:id,name')
            ->orderBy('reminder_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($reminders);
    }

    public function getUserReminders()
    {
        $reminders = LeadReminder::with(['lead:id,name,phone', 'causer:id,name'])
            ->where('created_by', auth()->id())
            ->orderByRaw('CASE WHEN reminder_status = \'pending\' THEN 0 ELSE 1 END')
            ->orderBy('reminder_date', 'asc')
            ->orderBy('reminder_time', 'asc')
            ->get();

        return response()->json($reminders);
    }

    public function addReminder($lead_id, Request $request)
    {
        $data = $request->validate([
            'reminder_date' => 'required',
            'reminder_time' => 'required',
            'reminder_note' => 'sometimes',
        ]);

        $this->service->addReminder($lead_id, $data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('translation.alert_messages.reminder_added'),
            ]);
        }

        session()->flash('success_alert', __('translation.alert_messages.reminder_added'));

        return redirect()->back();
    }

    public function updateReminder(Request $request, $reminderId)
    {
        $data = $request->validate([
            'status' => 'required',
        ]);
        $this->service->updateReminder($data, $reminderId);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('translation.alert_messages.updated'),
            ]);
        }

        return redirect()->back()->with('success_alert', __('translation.alert_messages.updated'));
    }

    public function deleteReminder($reminderId)
    {
        $this->service->deleteReminder($reminderId);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('translation.alert_messages.deleted'),
            ]);
        }

        return redirect()->back()->with('success_alert', __('translation.alert_messages.deleted'));
    }

    public function reminders()
    {
        return view('admin.leads.reminders-list');
    }

    public function remindersPage()
    {
        // Use the leads layout that extends master
        Inertia::setRootView('layouts.leads');

        return Inertia::render('Leads/Reminders');
    }

    public function inertiaIndex(Request $request)
    {
        $leads_page = $this->service->getLeadsPage();
        if ($leads_page == 'table') {
            return redirect()->route('admin.leads.table-page');
        }

        // Use the leads layout that extends master
        Inertia::setRootView('layouts.leads');

        // Get search parameter
        $search = $request->get('search');

        // Get collections and leads using the service
        $collections = $this->service->getLeadCollections();
        $leads = $this->service->getLeadsGroupedByCollection($collections, 50, 0, $search);
        $leadCounts = $this->service->getLeadCounts($collections, $search);

        return Inertia::render('Leads/Index', [
            'collections' => $collections,
            'leads' => $leads,
            'leadCounts' => $leadCounts,
            'teachers' => $this->service->getTeachers(auth()->user()->current_branch_id),
            'boards' => $this->service->getBoards(),
            'courses' => $this->service->getCourses(),
            'groups' => $this->service->getGroups(),
            'search' => $search,
            'leadDynamicFields' => $this->leadDynamicFieldService->definitions(),
        ]);
    }

    /**
     * Store a new lead
     */
    public function store(Request $request)
    {
        $data = $request->validate(array_merge([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'phone2' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'collection_id' => 'required|exists:lead_collections,id',
            'board_id' => 'nullable|exists:lead_boards,id',
            'course_id' => 'nullable|exists:courses,id',
            'teacher_id' => 'nullable|exists:users,id',
            'chosen_days' => 'nullable|string|max:100',
            'chosen_time' => 'nullable|string|max:10',
            'comment' => 'nullable|string|max:1000',
            'order' => 'nullable|integer',
        ], $this->leadDynamicFieldService->customAttributeValidationRules()));

        $data['custom_attributes'] = $this->leadDynamicFieldService->normalizeValidated(
            $request->input('custom_attributes')
        );

        try {
            DB::beginTransaction();

            // Set order to 0 to place at top, then shift other leads down
            $data['order'] = 0;
            $data['status'] = 'new';
            $data['causer_id'] = auth()->id();

            // Shift all existing leads in this collection down by 1
            Lead::where('collection_id', $data['collection_id'])
                ->update(['order' => DB::raw('COALESCE("order", 0) + 1')]);

            // Create the new lead
            $lead = Lead::create($data);

            // Attach the lead to the current user's branch
            if (auth()->user()->current_branch_id) {
                $lead->branches()->attach(auth()->user()->current_branch_id);
            }

            // Load relationships for the response
            $lead->load(['collection:id,name', 'board:id,name', 'causer:id,name', 'course:id,name', 'teacher:id,name']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('translation.alert_messages.created'),
                'lead' => $lead,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => __('translation.alert_messages.error'),
                'errors' => ['general' => $e->getMessage()],
            ], 422);
        }
    }

    public function getLeadDetails(Lead $lead)
    {
        $lead->load([
            'collection:id,name',
            'board:id,name',
            'causer:id,name',
            'course:id,name',
            'teacher:id,name',
        ]);

        $payload = $lead->toArray();
        $payload['dynamic_field_definitions'] = $this->leadDynamicFieldService->definitions();

        return response()->json($payload);
    }

    /**
     * Update a lead using the new Lead model
     */
    public function update(Request $request, Lead $lead)
    {
        $data = $request->validate(array_merge([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'phone2' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'collection_id' => 'required|exists:lead_collections,id',
            'board_id' => 'nullable|exists:lead_boards,id',
            'course_id' => 'nullable|exists:courses,id',
            'teacher_id' => 'nullable|exists:users,id',
            'chosen_days' => 'nullable|string|max:100',
            'chosen_time' => 'nullable|string|max:10',
            'comment' => 'nullable|string|max:1000',
        ], $this->leadDynamicFieldService->customAttributeValidationRules()));

        $data['custom_attributes'] = $this->leadDynamicFieldService->normalizeValidated(
            $request->input('custom_attributes')
        );

        try {
            DB::beginTransaction();

            // Update the lead
            $lead->update($data);

            // Load relationships for the response
            $lead->load(['collection:id,name', 'board:id,name', 'causer:id,name', 'course:id,name', 'teacher:id,name']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('translation.alert_messages.updated'),
                'lead' => $lead,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => __('translation.alert_messages.error'),
                'errors' => ['general' => $e->getMessage()],
            ], 422);
        }
    }

    /**
     * Delete a lead
     */
    public function destroy(Lead $lead)
    {
        try {
            DB::beginTransaction();

            // Delete the lead
            $lead->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('translation.alert_messages.deleted'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => __('translation.alert_messages.error'),
                'errors' => ['general' => $e->getMessage()],
            ], 422);
        }
    }

    public function searchLeads(Request $request)
    {
        $search = $request->get('search');
        $limit = $request->get('limit', 50); // Default to 50
        $offset = $request->get('offset', 0); // Default to 0

        // Additional filters
        $filters = [
            'selectedChosenDays' => $request->get('selectedChosenDays'),
            'selectedTeacher' => $request->get('selectedTeacher'),
            'selectedBoard' => $request->get('selectedBoard'),
            'selectedCourse' => $request->get('selectedCourse'),
            'selectedAttend' => $request->get('selectedAttend'),
            'dateRange' => $request->get('dateRange'),
            'startDate' => $request->get('startDate'),
            'endDate' => $request->get('endDate'),
        ];

        // Debug logging
        Log::info('Lead search filters:', [
            'search' => $search,
            'filters' => $filters,
            'limit' => $limit,
            'offset' => $offset,
        ]);

        // Get collections and leads using the service
        $collections = $this->service->getLeadCollections();
        $leads = $this->service->getLeadsGroupedByCollection($collections, $limit, $offset, $search, $filters);
        $leadCounts = $this->service->getLeadCounts($collections, $search, $filters);

        Log::info('Lead search results:', [
            'collections_count' => $collections->count(),
            'leads_count' => collect($leads)->map(fn ($collectionLeads) => count($collectionLeads))->toArray(),
            'leadCounts' => $leadCounts,
        ]);

        return response()->json([
            'collections' => $collections,
            'leads' => $leads,
            'leadCounts' => $leadCounts,
        ]);
    }

    public function updateLeadOrder(Request $request)
    {
        $data = $request->validate([
            'lead_id' => 'required|integer',
            'collection_id' => 'required|integer',
            'order' => 'required|integer',
            'reorder_items' => 'required|array',
            'reorder_items.*.id' => 'required|integer',
            'reorder_items.*.order' => 'required|integer',
            'reorder_items.*.collection_id' => 'sometimes|integer',
        ]);

        try {
            DB::beginTransaction();

            // Update the main lead (the one being dragged) using Eloquent to trigger activity logging
            $mainLead = Lead::findOrFail($data['lead_id']);
            $mainLead->update([
                'collection_id' => $data['collection_id'],
                'order' => $data['order'],
            ]);

            // Update all reorder items with their new orders and collections
            foreach ($data['reorder_items'] as $item) {
                $lead = Lead::findOrFail($item['id']);
                $updateData = ['order' => $item['order']];

                // If collection_id is provided, update it (for cross-collection moves)
                if (isset($item['collection_id'])) {
                    $updateData['collection_id'] = $item['collection_id'];
                }

                $lead->update($updateData);
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lead order update failed: '.$e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function loadMoreLeads(Request $request)
    {
        $collectionId = $request->get('collection_id');
        $offset = $request->get('offset', 0);
        $limit = $request->get('limit', 50);

        try {
            $leads = $this->service->loadMoreLeads($collectionId, $offset, $limit);
            $totalCount = $this->service->getCollectionLeadCount($collectionId);
            $hasMore = ($offset + $limit) < $totalCount;

            return response()->json([
                'success' => true,
                'leads' => $leads,
                'hasMore' => $hasMore,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get comments for a lead
     */
    public function getComments(Lead $lead)
    {
        $comments = $lead->comments()
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'comments' => $comments,
        ]);
    }

    /**
     * Store a new comment for a lead
     */
    public function storeComment(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'comment' => 'required|string|min:1|max:1000',
        ]);

        $comment = $lead->comments()->create([
            'comment' => $data['comment'],
            'user_id' => auth()->id(),
        ]);

        $comment->load('user:id,name');

        return response()->json([
            'success' => true,
            'comment' => $comment,
        ]);
    }

    /**
     * Delete a comment
     */
    public function deleteComment(Lead $lead, $commentId)
    {
        $comment = $lead->comments()->findOrFail($commentId);

        // Only allow deletion by the comment author or admin
        if ($comment->user_id !== auth()->id() && ! auth()->user()->hasRole('admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $comment->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Get calls for a lead
     */
    public function getCalls(Lead $lead)
    {
        $calls = $lead->calls()
            ->with('causer:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'calls' => $calls,
        ]);
    }

    /**
     * Store a new call for a lead
     */
    public function storeLeadCall(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'status' => 'required|in:answered,not_answered,busy,declined',
            'comment' => 'nullable|string|max:1000',
            'duration' => 'nullable|integer|min:0',
        ]);

        $call = $lead->calls()->create([
            'status' => $data['status'],
            'comment' => $data['comment'],
            'duration' => $data['duration'] ?? null,
            'causer_id' => auth()->id(),
        ]);

        $call->load('causer:id,name');

        return response()->json([
            'success' => true,
            'call' => $call,
        ]);
    }

    /**
     * Delete a call
     */
    public function deleteCall(Lead $lead, $callId)
    {
        $call = $lead->calls()->findOrFail($callId);

        // Only allow deletion by the call author or admin
        if ($call->causer_id !== auth()->id() && ! auth()->user()->hasRole('admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $call->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Get activities for a lead
     */
    public function getActivities(Lead $lead)
    {
        try {
            // Get lead call IDs
            $leadCallIds = $lead->calls()->pluck('id')->toArray();

            // Get lead comment IDs
            $leadCommentIds = $lead->comments()->pluck('id')->toArray();

            $activities = \Spatie\Activitylog\Models\Activity::where(function ($query) use ($lead, $leadCallIds, $leadCommentIds) {
                // Direct lead activities
                $query->where(function ($q) use ($lead) {
                    $q->where('subject_type', Lead::class)
                        ->where('subject_id', $lead->id);
                });

                // Lead call activities
                if (! empty($leadCallIds)) {
                    $query->orWhere(function ($q) use ($leadCallIds) {
                        $q->where('subject_type', LeadCall::class)
                            ->whereIn('subject_id', $leadCallIds);
                    });
                }

                // Lead comment activities
                if (! empty($leadCommentIds)) {
                    $query->orWhere(function ($q) use ($leadCommentIds) {
                        $q->where('subject_type', LeadComment::class)
                            ->whereIn('subject_id', $leadCommentIds);
                    });
                }
            })
                ->with('causer:id,name')
                ->orderBy('created_at', 'desc')
                ->get();

            // Get all collection IDs from activities to fetch collection names
            $collectionIds = collect();
            foreach ($activities as $activity) {
                if ($activity->properties) {
                    $attributes = $activity->properties['attributes'] ?? [];
                    $old = $activity->properties['old'] ?? [];

                    if (isset($attributes['collection_id'])) {
                        $collectionIds->push($attributes['collection_id']);
                    }
                    if (isset($old['collection_id'])) {
                        $collectionIds->push($old['collection_id']);
                    }
                }
            }

            // Fetch collection names
            $collections = [];
            if ($collectionIds->isNotEmpty()) {
                $collections = LeadCollection::whereIn('id', $collectionIds->unique()->filter())
                    ->pluck('name', 'id')
                    ->toArray();
            }

            return response()->json([
                'success' => true,
                'activities' => $activities,
                'collections' => $collections,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching lead activities: '.$e->getMessage(), [
                'lead_id' => $lead->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching activities',
            ], 500);
        }
    }

    /**
     * Get archived (soft deleted) leads
     */
    public function getArchivedLeads(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 15);

        $query = Lead::onlyTrashed()
            ->with([
                'collection:id,name',
                'course:id,name',
                'teacher:id,name',
                'causer' => function ($query) {
                    $query->select('id', 'name')->with('media');
                },
            ]);

        // Add search functionality
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', '%'.$search.'%')
                    ->orWhere('phone', 'ILIKE', '%'.$search.'%')
                    ->orWhere('comment', 'ILIKE', '%'.$search.'%');
            });
        }

        // Add deleted_by information from activity log
        $query->addSelect([
            'deleted_by_id' => \Spatie\Activitylog\Models\Activity::select('causer_id')
                ->whereColumn('subject_id', 'leads.id')
                ->where('subject_type', Lead::class)
                ->where('description', 'deleted')
                ->orderBy('created_at', 'desc')
                ->limit(1),
        ]);

        $leads = $query->orderBy('deleted_at', 'desc')->paginate($perPage);

        // Load deleted_by user information
        foreach ($leads as $lead) {
            if ($lead->deleted_by_id) {
                $lead->deleted_by = User::select('id', 'name')
                    ->with('media')
                    ->find($lead->deleted_by_id);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $leads->items(),
            'current_page' => $leads->currentPage(),
            'last_page' => $leads->lastPage(),
            'per_page' => $leads->perPage(),
            'total' => $leads->total(),
            'from' => $leads->firstItem(),
            'to' => $leads->lastItem(),
        ]);
    }

    /**
     * Show archived leads page
     */
    public function archivedIndex()
    {
        // Use the same leads layout that provides translation functionality
        Inertia::setRootView('layouts.leads');

        return Inertia::render('Leads/Archived', [
            'locale' => app()->getLocale(),
        ]);
    }

    /**
     * Restore a soft deleted lead
     */
    public function restoreLead($id)
    {
        $lead = Lead::onlyTrashed()->findOrFail($id);

        $lead->restore();

        return response()->json([
            'success' => true,
            'message' => __('translation.alert_messages.restored'),
            'lead' => $lead->load(['collection', 'course', 'teacher', 'causer']),
        ]);
    }

    /**
     * Permanently delete a lead
     */
    public function forceDeleteLead($id)
    {
        $lead = Lead::onlyTrashed()->findOrFail($id);

        // Delete related records first
        $lead->calls()->delete();
        $lead->comments()->delete();
        $lead->reminders()->delete();

        // Force delete the lead
        $lead->forceDelete();

        return response()->json([
            'success' => true,
            'message' => __('translation.alert_messages.deleted'),
        ]);
    }

    /**
     * Get lead collections for settings
     */
    public function getLeadCollections()
    {
        $collections = $this->service->getLeadCollections();

        return response()->json([
            'success' => true,
            'collections' => $collections,
        ]);
    }

    /**
     * Store a new lead collection
     */
    public function storeLeadCollection(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $collection = $this->service->storeLeadCollection($data);

            return response()->json([
                'success' => true,
                'message' => __('translation.alert_messages.created'),
                'collection' => $collection,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('translation.alert_messages.error'),
            ], 422);
        }
    }

    /**
     * Update a lead collection
     */
    public function updateLeadCollection(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $collection = $this->service->updateLeadCollection($data, $id);

            return response()->json([
                'success' => true,
                'message' => __('translation.alert_messages.updated'),
                'collection' => $collection,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('translation.alert_messages.error'),
            ], 422);
        }
    }

    /**
     * Delete a lead collection
     */
    public function deleteLeadCollection($id)
    {
        try {
            $this->service->deleteLeadCollection($id);

            return response()->json([
                'success' => true,
                'message' => __('translation.alert_messages.deleted'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('translation.alert_messages.error'),
            ], 422);
        }
    }

    /**
     * Update lead collection order
     */
    public function updateLeadCollectionOrder(Request $request)
    {
        $data = $request->validate([
            'collections' => 'required|array',
            'collections.*.id' => 'required|integer|exists:lead_collections,id',
            'collections.*.order' => 'required|integer',
        ]);

        try {
            $this->service->updateLeadCollectionOrder($data['collections']);

            return response()->json([
                'success' => true,
                'message' => __('translation.alert_messages.updated'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('translation.alert_messages.error'),
            ], 422);
        }
    }

    /**
     * Generate lead collection link
     */
    public function generateLeadCollectionLink($id)
    {
        try {
            $collection = \App\Models\LeadCollection::findOrFail($id);

            // Generate a unique board URL if it doesn't exist
            $boardUrl = $collection->board_url;
            if (! $boardUrl) {
                // Generate unique URL without collection name - just ID and random string
                $boardUrl = $collection->id.'-'.Str::random(8);

                // Save the board URL to the collection
                $collection->update(['board_url' => $boardUrl]);
            }

            return response()->json([
                'success' => true,
                'board_url' => $boardUrl,
                'message' => __('translation.alert_messages.success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('translation.alert_messages.error'),
            ], 422);
        }
    }

    /**
     * Get lead collection link
     */
    public function getLeadCollectionLink($id)
    {
        try {
            $collection = \App\Models\LeadCollection::findOrFail($id);

            if (! $collection->board_url) {
                return response()->json([
                    'success' => false,
                    'message' => __('translation.leads.no_link_found'),
                ], 404);
            }

            return response()->json([
                'success' => true,
                'board_url' => $collection->board_url,
                'message' => __('translation.alert_messages.success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('translation.alert_messages.error'),
            ], 422);
        }
    }

    /**
     * Remove lead collection link
     */
    public function removeLeadCollectionLink($id)
    {
        try {
            $collection = \App\Models\LeadCollection::findOrFail($id);

            // Remove the board URL from the collection
            $collection->update(['board_url' => null]);

            return response()->json([
                'success' => true,
                'message' => __('translation.leads.link_removed_successfully'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('translation.alert_messages.error'),
            ], 422);
        }
    }

    /**
     * Get all lead boards for API
     */
    public function getLeadBoards()
    {
        $boards = $this->service->getLeadBoards();

        return response()->json([
            'success' => true,
            'boards' => $boards,
        ]);
    }

    /**
     * Store a new lead board
     */
    public function storeLeadBoard(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'lead_collection_id' => 'required|exists:lead_collections,id',
            'group_id' => 'nullable|exists:groups,id',
        ]);

        try {
            $board = $this->service->storeLeadBoard($data);

            return response()->json([
                'success' => true,
                'message' => __('translation.alert_messages.created'),
                'board' => $board,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('translation.alert_messages.error'),
            ], 422);
        }
    }

    /**
     * Update a lead board
     */
    public function updateLeadBoard(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'lead_collection_id' => 'required|exists:lead_collections,id',
            'group_id' => 'nullable|exists:groups,id',
        ]);

        try {
            $board = $this->service->updateLeadBoard($data, $id);

            return response()->json([
                'success' => true,
                'message' => __('translation.alert_messages.updated'),
                'board' => $board,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('translation.alert_messages.error'),
            ], 422);
        }
    }

    /**
     * Delete a lead board
     */
    public function deleteLeadBoard($id)
    {
        try {
            $this->service->deleteLeadBoard($id);

            return response()->json([
                'success' => true,
                'message' => __('translation.alert_messages.deleted'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('translation.alert_messages.error'),
            ], 422);
        }
    }

    /**
     * Generate lead board URL
     */
    public function generateLeadBoardUrl($id)
    {
        try {
            $board = \App\Models\LeadBoard::findOrFail($id);

            // Generate a unique URL if it doesn't exist
            $url = $board->url;
            if (! $url) {
                // Generate unique URL
                $url = $board->id.'-'.Str::random(8);

                // Save the URL to the board
                $board->update(['url' => $url]);
            }

            return response()->json([
                'success' => true,
                'url' => $url,
                'full_url' => route('lead.form', $url),
                'message' => __('translation.alert_messages.success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('translation.alert_messages.error'),
            ], 422);
        }
    }

    /**
     * Remove lead board URL
     */
    public function removeLeadBoardUrl($id)
    {
        try {
            $board = \App\Models\LeadBoard::findOrFail($id);

            // Remove the URL from the board
            $board->update(['url' => null]);

            return response()->json([
                'success' => true,
                'message' => __('translation.leads.link_removed_successfully'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('translation.alert_messages.error'),
            ], 422);
        }
    }

    /**
     * Send Telegram message to teacher about lead
     */
    public function sendTelegramToTeacher(Request $request, Lead $lead)
    {
        try {
            if (! $lead->teacher_id) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => __('translation.leads.no_teacher_assigned'),
                    ], 422);
                }

                return back()->with('error', __('translation.leads.no_teacher_assigned'));
            }

            $this->service->notifyTeacherAboutLead($lead);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('translation.alert_messages.sent'),
                ]);
            }

            return back()->with('success', __('translation.alert_messages.sent'));
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('translation.alert_messages.error'),
                ], 422);
            }

            return back()->with('error', __('translation.alert_messages.error'));
        }
    }
}
