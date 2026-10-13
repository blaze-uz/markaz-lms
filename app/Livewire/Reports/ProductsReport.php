<?php

namespace App\Livewire\Reports;

use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\ProductOrderItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ProductsReport extends Component
{
    public $startDate = null;
    public $endDate = null;
    public $chartData = [];
    public $totalStats = [];
    public $topProducts = [];
    public $monthlyTrend = [];
    public $inventoryData = []; // Add this property for inventory chart

    public function mount()
    {
        // Default to current month
        $this->startDate = now()->subMonths(3)->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
        $this->loadReportData();
    }

    public function render()
    {
        return view('livewire.reports.products-report');
    }

    public function loadReportData()
    {
        // Calculate date range for charts
        $startDate = Carbon::parse($this->startDate)->startOfDay();
        $endDate = Carbon::parse($this->endDate)->endOfDay();

        // Get top products by sales
        $topProductsQuery = ProductOrderItem::query()
            ->join('product_orders', 'product_orders.id', '=', 'product_order_items.product_order_id')
            ->join('products', 'products.id', '=', 'product_order_items.product_id')
            ->select(
                'product_order_items.product_id',
                'products.name as product_name',
                DB::raw('SUM(product_order_items.quantity) as total_quantity'),
                DB::raw('SUM(product_order_items.price * product_order_items.quantity) as total_sales')
            )
            ->whereNull('product_orders.deleted_at')
            ->whereBetween('product_orders.created_at', [$startDate, $endDate])
            ->groupBy('product_order_items.product_id', 'products.name');

        $this->topProducts = $topProductsQuery->orderBy('total_sales', 'desc')
            ->limit(10)
            ->get();

        // Prepare chart data for top products
        $productLabels = [];
        $productQuantities = [];
        $productSales = [];

        foreach ($this->topProducts as $product) {
            $productLabels[] = $product->product_name;
            $productQuantities[] = $product->total_quantity;
            $productSales[] = $product->total_sales;
        }

        $this->chartData = [
            'productLabels' => $productLabels,
            'productQuantities' => $productQuantities,
            'productSales' => $productSales,
        ];

        // Calculate monthly trends
        $monthlyTrendQuery = ProductOrderItem::query()
            ->join('product_orders', 'product_orders.id', '=', 'product_order_items.product_order_id')
            ->select(
                DB::raw('TO_CHAR(product_orders.created_at, \'YYYY-MM\') as month'),
                DB::raw('SUM(product_order_items.quantity) as total_quantity'),
                DB::raw('SUM(product_order_items.price * product_order_items.quantity) as total_sales')
            )
            ->whereNull('product_orders.deleted_at')
            ->whereBetween('product_orders.created_at', [
                $startDate->copy()->subYear(), // Show trends for the past year
                $endDate
            ])
            ->groupBy(DB::raw('TO_CHAR(product_orders.created_at, \'YYYY-MM\')'))
            ->orderBy('month');

        $monthlyData = $monthlyTrendQuery->get();

        $monthLabels = [];
        $monthlySales = [];
        $monthlyQuantities = [];

        foreach ($monthlyData as $data) {
            $formattedMonth = Carbon::createFromFormat('Y-m', $data->month)->format('M Y');
            $monthLabels[] = $formattedMonth;
            $monthlySales[] = $data->total_sales;
            $monthlyQuantities[] = $data->total_quantity;
        }

        $this->monthlyTrend = [
            'labels' => $monthLabels,
            'sales' => $monthlySales,
            'quantities' => $monthlyQuantities,
        ];

        // Get inventory status data for pie chart
        $this->loadInventoryData();

        // Calculate total stats
        $this->totalStats = [
            'total_quantity' => $this->topProducts->sum('total_quantity'),
            'total_sales' => $this->topProducts->sum('total_sales'),
            'total_products' => $this->topProducts->count(),
        ];
    }

    /**
     * Load inventory status data for the pie chart with a cleaner approach
     */
    protected function loadInventoryData()
    {
        // Use a subquery approach which is more PostgreSQL-friendly
        $inventoryQuery = DB::table(function ($query) {
            $query->select(
                DB::raw("CASE 
                    WHEN stock = 0 THEN 'Out of Stock' 
                    WHEN stock BETWEEN 1 AND 5 THEN 'Low Stock'
                    WHEN stock BETWEEN 6 AND 20 THEN 'Medium Stock'
                    ELSE 'Well Stocked' 
                END as stock_status"),
                DB::raw('COUNT(*) as product_count')
            )
                ->from('products')
                ->groupBy('stock_status');
        }, 'inventory')
            ->select('stock_status', 'product_count')
            ->orderBy(DB::raw("CASE 
            WHEN stock_status = 'Out of Stock' THEN 1
            WHEN stock_status = 'Low Stock' THEN 2
            WHEN stock_status = 'Medium Stock' THEN 3
            ELSE 4
        END"));

        $inventoryData = $inventoryQuery->get();

        // Rest of the method stays the same
        $stockLabels = [];
        $stockCounts = [];
        $stockColors = [
            'Out of Stock' => '#f46a6a', // Danger
            'Low Stock' => '#f1b44c',    // Warning
            'Medium Stock' => '#50a5f1', // Info
            'Well Stocked' => '#34c38f'  // Success
        ];
        $backgroundColors = [];

        foreach ($inventoryData as $item) {
            $stockLabels[] = $item->stock_status;
            $stockCounts[] = $item->product_count;
            $backgroundColors[] = $stockColors[$item->stock_status] ?? '#74788d'; // Default color
        }

        $this->inventoryData = [
            'labels' => $stockLabels,
            'counts' => $stockCounts,
            'colors' => $backgroundColors
        ];
    }

    public function updatedStartDate()
    {
        $this->loadReportData();
    }

    public function updatedEndDate()
    {
        $this->loadReportData();
    }

    public function resetFilters()
    {
        $this->startDate = now()->subMonths(3)->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
        $this->loadReportData();
    }

    public function exportReport()
    {
        $this->dispatch('notify', [
            'type' => 'info',
            'message' => __('translation.export_feature_coming_soon')
        ]);
    }
}
