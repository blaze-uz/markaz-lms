<?php

namespace App\Http\Controllers;

use App\Services\SmsTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SmsTemplateController extends Controller
{
    public function __construct(
        private SmsTemplateService $smsTemplateService
    ) {
    }

    /**
     * Display a listing of SMS templates
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'status', 'type']);
        $templates = $this->smsTemplateService->getAllTemplates($filters);
        $statuses = $this->smsTemplateService->getTemplateStatuses();
        $types = $this->smsTemplateService->getTemplateTypes();
        $statistics = $this->smsTemplateService->getTemplateStatistics();

        return view('admin.settings.sms.index', compact(
            'templates',
            'statuses',
            'types',
            'statistics',
            'filters'
        ));
    }

    /**
     * Show the form for creating a new SMS template
     */
    public function create(): View
    {
        $types = $this->smsTemplateService->getTemplateTypes();

        return view('admin.settings.sms.create', compact('types'));
    }

    /**
     * Store a newly created SMS template
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $template = $this->smsTemplateService->createTemplate($request->all());

            if ($request->input('action') === 'create_and_moderate') {
                $result = $this->smsTemplateService->sendToModeration($template->id);
                session()->flash('success_alert', $result['message']);
            } else {
                session()->flash('success_alert', __('translation.alert_messages.sms_template_created'));
            }

            return redirect()->route('admin.settings.sms');
        } catch (\Exception $e) {
            Log::error('SMS Template Creation Error', [
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);

            return back()
                ->withInput()
                ->with('error_alert', $e->getMessage());
        }
    }

    /**
     * Display the specified SMS template
     */
    public function show(int $id)
    {
        try {
            $template = $this->smsTemplateService->getTemplateById($id);

            return view('admin.settings.sms.show', compact('template'));
        } catch (\Exception $e) {
            Log::error('SMS Template Show Error', [
                'error' => $e->getMessage(),
                'template_id' => $id,
            ]);

            return redirect()
                ->route('admin.settings.sms')
                ->with('error_alert', 'Template not found');
        }
    }

    /**
     * Show the form for editing the specified SMS template
     */
    public function edit(int $id)
    {
        try {
            if (! $this->smsTemplateService->canEditTemplate($id)) {
                session()->flash('error_alert', __('translation.sms.cannot_edit_during_moderation'));

                return redirect()->route('admin.settings.sms.show', $id);
            }

            $template = $this->smsTemplateService->getTemplateById($id);
            $types = $this->smsTemplateService->getTemplateTypes();

            return view('admin.settings.sms.edit', compact('template', 'types'));
        } catch (\Exception $e) {
            Log::error('SMS Template Edit Error', [
                'error' => $e->getMessage(),
                'template_id' => $id,
            ]);

            return redirect()
                ->route('admin.settings.sms')
                ->with('error_alert', 'Template not found');
        }
    }

    /**
     * Update the specified SMS template
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        try {
            if (! $this->smsTemplateService->canEditTemplate($id)) {
                session()->flash('error_alert', __('translation.sms.cannot_edit_during_moderation'));

                return redirect()->route('admin.settings.sms.show', $id);
            }

            $template = $this->smsTemplateService->updateTemplate($id, $request->all());

            session()->flash('success_alert', __('translation.alert_messages.sms_template_updated'));

            return redirect()->route('admin.settings.sms');
        } catch (\Exception $e) {
            Log::error('SMS Template Update Error', [
                'error' => $e->getMessage(),
                'template_id' => $id,
                'data' => $request->all(),
            ]);

            return back()
                ->withInput()
                ->with('error_alert', $e->getMessage());
        }
    }

    /**
     * Remove the specified SMS template
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->smsTemplateService->deleteTemplate($id);

            session()->flash('success_alert', __('translation.alert_messages.sms_template_deleted'));

            return redirect()->route('admin.settings.sms');
        } catch (\Exception $e) {
            Log::error('SMS Template Delete Error', [
                'error' => $e->getMessage(),
                'template_id' => $id,
            ]);

            return back()->with('error_alert', $e->getMessage());
        }
    }

    /**
     * Send template to moderation
     */
    public function sendToModeration(int $id): RedirectResponse
    {
        try {
            $result = $this->smsTemplateService->sendToModeration($id);

            session()->flash('success_alert', $result['message']);

            return redirect()->route('admin.settings.sms');
        } catch (\Exception $e) {
            Log::error('SMS Template Send to Moderation Error', [
                'error' => $e->getMessage(),
                'template_id' => $id,
            ]);

            return back()->with('error_alert', $e->getMessage());
        }
    }

    /**
     * Get template statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $statistics = $this->smsTemplateService->getTemplateStatistics();

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            Log::error('SMS Template Statistics Error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics',
            ], 500);
        }
    }

    /**
     * Check template status from external API
     */
    public function checkStatus(int $id): RedirectResponse
    {
        try {
            $template = $this->smsTemplateService->getTemplateById($id);
            $updatedTemplate = $this->smsTemplateService->checkStatus($template);

            return redirect()
                ->route('admin.settings.sms.show', $updatedTemplate->id)
                ->with('success_alert', __('translation.alert_messages.sms_template_status_checked'));
        } catch (\Exception $e) {
            Log::error('SMS Template Check Status Error', [
                'error' => $e->getMessage(),
                'template_id' => $id,
            ]);

            return redirect()
                ->route('admin.settings.sms.show', $id)
                ->with('error_alert', __('translation.sms.status_check_failed').': '.$e->getMessage());
        }
    }
}
