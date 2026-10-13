<?php

namespace App\Services;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LeadStatisticsService
{
    /**
     * Database driver for SQL compatibility
     */
    protected string $dbDriver;

    public function __construct(
        protected LeadDynamicFieldService $leadDynamicFieldService,
    ) {
        $this->dbDriver = DB::connection()->getDriverName();
    }

    /**
     * Get comprehensive lead statistics with caching
     */
    public function getLeadStatistics(?string $startDate = null, ?string $endDate = null): array
    {
        $cacheKey = "lead_stats_{$startDate}_{$endDate}";

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($startDate, $endDate) {
            return [
                'overview' => $this->getOverviewStats($this->getBaseQuery($startDate, $endDate)),
                'conversion_funnel' => $this->getConversionFunnelStats($this->getBaseQuery($startDate, $endDate)),
                'source_analysis' => $this->getSourceAnalysisStats($this->getBaseQuery($startDate, $endDate)),
                'performance_summary' => $this->getPerformanceSummary($this->getBaseQuery($startDate, $endDate)),
                'recent_activity' => $this->getRecentActivity($startDate, $endDate),
                'time_insights' => $this->getTimeBasedInsights($this->getBaseQuery($startDate, $endDate)),
                'select_custom_fields' => $this->getSelectCustomFieldBreakdown($startDate, $endDate),
            ];
        });
    }

    /**
     * Get base query with date filters
     */
    protected function getBaseQuery(?string $startDate = null, ?string $endDate = null): Builder
    {
        $query = Lead::query();

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return $query;
    }

    /**
     * Get overview statistics
     */
    protected function getOverviewStats(Builder $query): array
    {
        $currentDateSql = $this->getCurrentDateSql();
        $weekIntervalSql = $this->getIntervalSql(7, 'day');
        $monthIntervalSql = $this->getIntervalSql(30, 'day');

        $stats = $query->selectRaw("
            COUNT(*) as total_leads,
            COUNT(CASE WHEN status = ? THEN 1 END) as converted_leads,
            COUNT(CASE WHEN status = ? THEN 1 END) as qualified_leads,
            COUNT(CASE WHEN status = ? THEN 1 END) as contacted_leads,
            COUNT(CASE WHEN status = ? THEN 1 END) as new_leads,
            COUNT(CASE WHEN DATE(created_at) = {$currentDateSql} THEN 1 END) as todays_leads,
            COUNT(CASE WHEN status = ? AND DATE(created_at) = {$currentDateSql} THEN 1 END) as todays_conversions,
            COUNT(CASE WHEN created_at >= {$weekIntervalSql} THEN 1 END) as week_leads,
            COUNT(CASE WHEN created_at >= {$monthIntervalSql} THEN 1 END) as month_leads
        ", ['student', 'qualified', 'contacted', 'new', 'student'])->first()->toArray();

        $stats['conversion_rate'] = $stats['total_leads'] > 0
            ? round(($stats['converted_leads'] / $stats['total_leads']) * 100, 1)
            : 0;

        $stats['qualification_rate'] = $stats['total_leads'] > 0
            ? round(($stats['qualified_leads'] / $stats['total_leads']) * 100, 1)
            : 0;

        return $stats;
    }

    /**
     * Get conversion funnel statistics
     */
    protected function getConversionFunnelStats(Builder $query): array
    {
        $totalLeads = $query->count();

        if ($totalLeads === 0) {
            return [
                'total' => 0,
                'contacted' => 0,
                'qualified' => 0,
                'converted' => 0,
                'percentages' => [
                    'contacted' => 0,
                    'qualified' => 0,
                    'converted' => 0,
                ],
            ];
        }

        $funnel = $query->selectRaw('
            COUNT(*) as total,
            COUNT(CASE WHEN status IN (?, ?, ?) THEN 1 END) as contacted,
            COUNT(CASE WHEN status IN (?, ?) THEN 1 END) as qualified,
            COUNT(CASE WHEN status = ? THEN 1 END) as converted
        ', ['contacted', 'qualified', 'student', 'qualified', 'student', 'student'])->first()->toArray();

        $funnel['percentages'] = [
            'contacted' => round(($funnel['contacted'] / $funnel['total']) * 100, 1),
            'qualified' => round(($funnel['qualified'] / $funnel['total']) * 100, 1),
            'converted' => round(($funnel['converted'] / $funnel['total']) * 100, 1),
        ];

        return $funnel;
    }

    /**
     * Get source analysis statistics
     */
    protected function getSourceAnalysisStats(Builder $query): Collection
    {
        $sources = $query->with('collection')
            ->get()
            ->groupBy('collection_id')
            ->map(function ($leads, $collectionId) {
                $totalCount = $leads->count();
                $convertedCount = $leads->where('status', 'student')->count();
                $conversionRate = $totalCount > 0 ? round(($convertedCount / $totalCount) * 100, 1) : 0;

                return (object) [
                    'collection_id' => $collectionId,
                    'collection' => $leads->first()->collection,
                    'total_count' => $totalCount,
                    'converted_count' => $convertedCount,
                    'conversion_rate' => $conversionRate,
                    'performance_score' => $this->calculatePerformanceScore($totalCount, $conversionRate),
                ];
            })
            ->sortByDesc('total_count')
            ->take(10);

        return $sources;
    }

    /**
     * Get performance summary
     */
    protected function getPerformanceSummary(Builder $query): array
    {
        $summary = $this->getOverviewStats($query);

        // Add trend calculations (mock data for now - you can implement actual comparison logic)
        $summary['trends'] = [
            'total_leads_trend' => rand(-5, 15),
            'converted_leads_trend' => rand(-2, 8),
            'qualified_leads_trend' => rand(-3, 6),
            'conversion_rate_trend' => round(rand(-2, 5) / 10, 1),
        ];

        return $summary;
    }

    /**
     * Get recent activity
     */
    protected function getRecentActivity(?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = Lead::with(['collection', 'calls' => function ($q) {
            $q->latest()->limit(1);
        }, 'causer']);

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return $query->latest()
            ->limit(10)
            ->get();
    }

    /**
     * Counts per option for each select-type lead custom attribute (settings-driven).
     *
     * @return list<array{
     *     field_key: string,
     *     field_label: string,
     *     total: int,
     *     breakdown: list<array{label: string, count: int, percent: float}>,
     *     empty_count: int,
     *     empty_percent: float
     * }>
     */
    protected function getSelectCustomFieldBreakdown(?string $startDate, ?string $endDate): array
    {
        $definitions = $this->leadDynamicFieldService->definitions();
        $selectDefinitions = array_values(array_filter(
            $definitions,
            static fn (array $definition): bool => ($definition['type'] ?? '') === 'select'
        ));

        if ($selectDefinitions === []) {
            return [];
        }

        $rows = [];

        foreach ($selectDefinitions as $definition) {
            $key = $definition['key'];
            $jsonPath = 'custom_attributes->'.$key;

            $total = $this->getBaseQuery($startDate, $endDate)->count();

            $breakdown = [];
            foreach ($definition['options'] as $optionLabel) {
                $count = $this->getBaseQuery($startDate, $endDate)
                    ->where($jsonPath, $optionLabel)
                    ->count();
                $breakdown[] = [
                    'label' => $optionLabel,
                    'count' => $count,
                    'percent' => $total > 0 ? round(($count / $total) * 100, 1) : 0.0,
                ];
            }

            $answered = (int) array_sum(array_column($breakdown, 'count'));
            $emptyCount = max(0, $total - $answered);

            $rows[] = [
                'field_key' => $key,
                'field_label' => $definition['label'],
                'total' => $total,
                'breakdown' => $breakdown,
                'empty_count' => $emptyCount,
                'empty_percent' => $total > 0 ? round(($emptyCount / $total) * 100, 1) : 0.0,
            ];
        }

        return $rows;
    }

    /**
     * Get time-based insights
     */
    protected function getTimeBasedInsights(Builder $query): array
    {
        $hourSql = $this->getHourExtractionSql('created_at');
        $dayOfWeekSql = $this->getDayOfWeekSql('created_at');

        // Get hourly distribution
        $hourlyStats = $query->selectRaw("
            {$hourSql} as hour,
            COUNT(*) as count,
            COUNT(CASE WHEN status = ? THEN 1 END) as conversions
        ", ['student'])
            ->groupByRaw($hourSql)
            ->get()
            ->keyBy('hour');

        // Get daily distribution
        $dailyStats = $query->selectRaw("
            {$dayOfWeekSql} as day_of_week,
            COUNT(*) as count,
            COUNT(CASE WHEN status = ? THEN 1 END) as conversions
        ", ['student'])
            ->groupByRaw($dayOfWeekSql)
            ->get()
            ->keyBy('day_of_week');

        $peakHour = $hourlyStats->sortByDesc('count')->first();
        $bestDay = $dailyStats->sortByDesc('conversions')->first();

        $dayNames = [
            1 => 'Sunday',
            2 => 'Monday',
            3 => 'Tuesday',
            4 => 'Wednesday',
            5 => 'Thursday',
            6 => 'Friday',
            7 => 'Saturday',
        ];

        return [
            'peak_hour' => $peakHour ? $peakHour->hour : rand(9, 16),
            'best_day' => $bestDay ? $dayNames[$bestDay->day_of_week] : 'Monday',
            'avg_response_time' => rand(2, 8), // Mock data - implement actual calculation
            'quality_score' => rand(75, 95), // Mock data - implement actual scoring
            'hourly_distribution' => $hourlyStats,
            'daily_distribution' => $dailyStats,
        ];
    }

    /**
     * Calculate performance score for a source
     */
    protected function calculatePerformanceScore(int $totalCount, float $conversionRate): int
    {
        // Simple scoring algorithm - you can make this more sophisticated
        $volumeScore = min(($totalCount / 100) * 50, 50); // Max 50 points for volume
        $conversionScore = ($conversionRate / 100) * 50; // Max 50 points for conversion rate

        return round($volumeScore + $conversionScore);
    }

    /**
     * Clear statistics cache
     */
    public function clearCache(): void
    {
        Cache::forget('lead_stats_*');
    }

    /**
     * Get lead statistics for dashboard widgets
     */
    public function getDashboardStats(): array
    {
        return Cache::remember('dashboard_lead_stats', now()->addMinutes(5), function () {
            return [
                'today_leads' => Lead::whereDate('created_at', today())->count(),
                'today_conversions' => Lead::where('status', 'student')->whereDate('created_at', today())->count(),
                'week_leads' => Lead::where('created_at', '>=', now()->subDays(7))->count(),
                'month_leads' => Lead::where('created_at', '>=', now()->subDays(30))->count(),
                'total_leads' => Lead::count(),
                'conversion_rate' => $this->calculateOverallConversionRate(),
            ];
        });
    }

    /**
     * Calculate overall conversion rate
     */
    protected function calculateOverallConversionRate(): float
    {
        $total = Lead::count();
        $converted = Lead::where('status', 'student')->count();

        return $total > 0 ? round(($converted / $total) * 100, 1) : 0;
    }

    /**
     * Get database-specific current date function
     */
    protected function getCurrentDateSql(): string
    {
        return match ($this->dbDriver) {
            'mysql' => 'CURDATE()',
            'pgsql' => 'CURRENT_DATE',
            default => 'CURRENT_DATE',
        };
    }

    /**
     * Get database-specific hour extraction function
     */
    protected function getHourExtractionSql(string $column): string
    {
        return match ($this->dbDriver) {
            'mysql' => "HOUR({$column})",
            'pgsql' => "EXTRACT(HOUR FROM {$column})",
            default => "EXTRACT(HOUR FROM {$column})",
        };
    }

    /**
     * Get database-specific day of week extraction function
     */
    protected function getDayOfWeekSql(string $column): string
    {
        return match ($this->dbDriver) {
            'mysql' => "DAYOFWEEK({$column})",
            'pgsql' => "EXTRACT(DOW FROM {$column}) + 1",
            default => "EXTRACT(DOW FROM {$column}) + 1",
        };
    }

    /**
     * Get database-specific interval syntax
     */
    protected function getIntervalSql(int $amount, string $unit): string
    {
        return match ($this->dbDriver) {
            'mysql' => "DATE_SUB(NOW(), INTERVAL {$amount} {$unit})",
            'pgsql' => "NOW() - INTERVAL '{$amount} {$unit}s'",
            'sqlite' => "datetime('now', '-{$amount} {$unit}s')",
            default => "NOW() - INTERVAL '{$amount} {$unit}s'",
        };
    }
}
