<?php

namespace App\Http\Controllers;

use App\Services\GroupBalancePaymentProcessor;
use App\Services\LeadStatisticsService;
use App\Services\ReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public $service;

    protected $leadStatsService;

    public function __construct(LeadStatisticsService $leadStatsService)
    {
        $this->middleware('permission:report-index')->only(['index', 'processGroupPayments']);
        $this->middleware('permission:report-educational')->only('educational');
        $this->middleware('permission:report-leads')->only('leads');
        $this->middleware('permission:report-debts')->only('debts');
        $this->service = new ReportService();
        $this->leadStatsService = $leadStatsService;
    }

    public function index(Request $request)
    {
        $params = $this->service->getRequestParams($request->all());
        $chartData = $this->service->getChartData($params);
        $cardsData = $this->service->getCardsData($params);
        $paymentTypes = []; //$this->service->getPaymentTypes($params);
        $paymentsByCreators = $this->service->getPaymentsByCreators($params);

        return view('admin.reports.index', [
            'chartData' => $chartData,
            'cardsData' => $cardsData,
            'paymentTypes' => $paymentTypes,
            'paymentsByCreators' => $paymentsByCreators,
            'params' => $params,
        ]);
    }

    public function educational()
    {
        return view('admin.reports.educational');
    }

    public function leads(Request $request)
    {
        // Validate and sanitize date inputs
        $startDate = $request->input('from') ? date('Y-m-d', strtotime($request->input('from'))) : null;
        $endDate = $request->input('to') ? date('Y-m-d', strtotime($request->input('to'))) : null;

        // Get comprehensive lead statistics
        $leadStats = $this->leadStatsService->getLeadStatistics($startDate, $endDate);

        return view('admin.reports.leads', [
            'leadStats' => $leadStats,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function debts()
    {
        return view('admin.reports.debts');
    }

    public function products()
    {
        return view('admin.reports.products');
    }

    public function expenses()
    {
        return view('admin.reports.expenses');
    }

    /**
     * Process group payments command
     */
    public function processGroupPayments(Request $request): RedirectResponse
    {
        try {
            $summary = (new GroupBalancePaymentProcessor)->process((int) auth()->id());

            Log::info('Group payments processing completed', [
                'processed_count' => $summary['processed_count'],
                'skipped_count' => $summary['skipped_count'],
                'total_amount' => $summary['total_amount'],
                'total_groups' => $summary['groups_count'],
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()->with('success', __('translation.group_payments_processed_successfully'));
        } catch (\Exception $e) {
            Log::error('Error processing group payments', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()->with('error', __('translation.error_occurred'));
        }
    }
}
