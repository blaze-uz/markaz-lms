<?php

namespace App\Livewire\Student;

use App\Enums\TransactionTypeEnum;
use App\Models\PaymentType;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\ProductOrderItem;
use App\Models\ProductOrderPayment;
use App\Models\Student;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class StudentProductOrdersTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public Student $student;

    public $perPage = 10;

    public $search = '';

    public $sortField = 'created_at';

    public $sortDirection = 'desc';

    public $filters = [
        'status' => '',
        'date_from' => null,
        'date_to' => null,
    ];

    // Properties for creating a new order
    public $orderItems = [];

    public $notes = '';

    public $orderTotal = 0;

    public $availableProducts;

    // Property for delete confirmation
    public $orderToDelete = null;

    // Payment related properties
    public $orderForPayment = null;

    public $paymentAmount = null;

    public $paymentMethod = '';

    public $paymentNotes = '';

    // Add a new property to store payment methods
    public $paymentMethods = [];

    // Add these properties
    public $orderForHistory = null;

    public $paymentHistory = [];

    protected $listeners = ['refreshOrders' => '$refresh'];

    public function mount(Student $student)
    {
        $this->student = $student;
        $this->loadAvailableProducts();
        $this->loadPaymentMethods();
        $this->addProductLine();
    }

    public function loadAvailableProducts()
    {
        // Load active products with stock > 0
        $this->availableProducts = Product::where('status', 'active')
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get();

        // Ensure it's never null
        if (! $this->availableProducts) {
            $this->availableProducts = collect();
        }
    }

    // Add a method to load payment methods
    protected function loadPaymentMethods()
    {
        // You can fetch this from a settings table or use a config value
        $this->paymentMethods = PaymentType::orderBy('position')->get();
        $this->paymentMethod = $this->paymentMethods->first()->id ?? '';
    }

    public function addProductLine()
    {
        $this->orderItems[] = [
            'product_id' => '',
            'quantity' => 1,
            'price' => 0,
            'subtotal' => 0,
        ];
    }

    public function removeProductLine($index)
    {
        if (isset($this->orderItems[$index])) {
            unset($this->orderItems[$index]);
            $this->orderItems = array_values($this->orderItems);
            $this->calculateOrderTotal();
        }
    }

    public function updatedOrderItems($value, $key)
    {
        // When a product is selected or quantity changed
        if (
            preg_match('/^orderItems\.(\d+)\.product_id$/', $key, $matches) ||
            preg_match('/^orderItems\.(\d+)\.quantity$/', $key, $matches)
        ) {

            $index = $matches[1];
            if (! empty($this->orderItems[$index]['product_id'])) {
                $product = $this->availableProducts->find($this->orderItems[$index]['product_id']);
                if ($product) {
                    $quantity = max(1, intval($this->orderItems[$index]['quantity'] ?? 1));
                    $this->orderItems[$index]['price'] = $product->price;
                    $this->orderItems[$index]['quantity'] = $quantity;
                    $this->orderItems[$index]['subtotal'] = $product->price * $quantity;
                }
            }

            $this->calculateOrderTotal();
        }
    }

    public function calculateOrderTotal()
    {
        $this->orderTotal = collect($this->orderItems)->sum('subtotal');
    }

    public function saveOrder()
    {
        abort_unless(auth()->user()?->can('product-order-store'), 403);

        // Validate the form data
        $this->validate([
            'orderItems.*.product_id' => 'required|exists:products,id',
            'orderItems.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        if (empty($this->orderItems)) {
            session()->flash('error', __('translation.no_products_selected'));

            return;
        }

        // Recalculate total price explicitly before saving
        $totalPrice = 0;
        foreach ($this->orderItems as $item) {
            $product = Product::find($item['product_id']);
            if ($product) {
                $totalPrice += $product->price * $item['quantity'];
            }
        }

        // Create the order with the calculated total
        $order = ProductOrder::create([
            'student_id' => $this->student->id,
            'total_price' => $totalPrice,  // Use the freshly calculated total
            'left_amount' => $totalPrice,  // Use the freshly calculated total
            'status' => 'pending',
            'notes' => $this->notes,
            'created_by' => auth()->id(),
        ]);

        // Create order items and update stock
        foreach ($this->orderItems as $item) {
            $product = Product::find($item['product_id']);
            $itemTotal = $product->price * $item['quantity'];

            ProductOrderItem::create([
                'product_order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $product->price,
                'total' => $itemTotal,  // Calculate this directly instead of using subtotal
            ]);

            // Reduce stock
            $product->stock -= $item['quantity'];
            $product->save();
        }

        // Reset form and close modal
        $this->reset(['orderItems', 'notes', 'orderTotal']);
        $this->addProductLine();

        // Dispatch events and show notification
        $this->dispatch('orderCreated');
        session()->flash('success', __('translation.order_created_successfully'));
    }

    public function confirmOrderDeletion($orderId)
    {
        $this->orderToDelete = $orderId;
        $this->dispatch('openDeleteModal');
    }

    public function deleteOrder()
    {
        abort_unless(auth()->user()?->can('product-order-delete'), 403);

        if (! $this->orderToDelete) {
            return;
        }

        $order = ProductOrder::findOrFail($this->orderToDelete);

        // Make sure the order belongs to this student
        if ($order->student_id !== $this->student->id) {
            session()->flash('error', __('translation.unauthorized_action'));

            return;
        }

        // Delete the order
        $order->delete();

        $this->orderToDelete = null;
        $this->dispatch('orderDeleted');
        session()->flash('success', __('translation.order_deleted_successfully'));
    }

    public function showPaymentModal($orderId)
    {
        try {
            $this->orderForPayment = ProductOrder::findOrFail($orderId);

            // Reset payment form
            $this->paymentAmount = null;
            // Set the payment method to the first available method
            $this->paymentMethod = $this->paymentMethods->first()->id ?? '';

            $this->dispatch('openPaymentModal');
        } catch (\Exception $e) {
            logger('Error showing payment modal: '.$e->getMessage());
            session()->flash('error', 'Failed to load payment form.');
        }
    }

    public function setFullPayment()
    {
        if ($this->orderForPayment) {
            $this->paymentAmount = $this->orderForPayment->left_amount;
        }
    }

    public function savePayment()
    {
        abort_unless(auth()->user()?->can('student-payment'), 403);

        if (! $this->orderForPayment) {
            session()->flash('error', __('translation.no_order_selected'));

            return;
        }

        // Validate payment information
        $this->validate([
            'paymentAmount' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    if ($value > $this->orderForPayment->left_amount) {
                        $fail(__('translation.payment_amount_exceeds_left_amount'));
                    }
                },
            ],
            'paymentMethod' => 'required',
        ]);

        try {
            DB::beginTransaction();
            // Create payment record
            $productPayment = ProductOrderPayment::create([
                'product_order_id' => $this->orderForPayment?->id ?? 0,
                'student_id' => $this->student?->id ?? 0,
                'amount' => $this->paymentAmount,
                'payment_type_id' => $this->paymentMethod,
                'payment_date' => now(),
                'notes' => $this->paymentNotes,
                'created_by' => auth()->id(),
            ]);

            $this->createTransaction($productPayment);

            // Update order's left amount
            $this->orderForPayment->left_amount -= $this->paymentAmount;

            // If fully paid, update status
            if ($this->orderForPayment->left_amount <= 0) {
                $this->orderForPayment->left_amount = 0; // Ensure no negative amounts
            }

            $this->orderForPayment->save();

            DB::commit();

            // Reset and close modal
            $this->orderForPayment = null;
            $this->paymentAmount = null;
            $this->paymentMethod = '';
            $this->paymentNotes = '';

            $this->dispatch('paymentSaved');
            session()->flash('success', __('translation.payment_saved_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            logger('Error saving payment: '.$e->getMessage());
            session()->flash('error', __('translation.error_saving_payment'));
        }
    }

    public function showPaymentHistory($orderId)
    {
        try {
            $this->orderForHistory = ProductOrder::with(['productOrderItems.product'])->findOrFail($orderId);

            // Load payment history with creator information
            $this->paymentHistory = ProductOrderPayment::with(['paymentType', 'creator'])
                ->where('product_order_id', $orderId)
                ->orderBy('payment_date', 'desc')
                ->get();

            $this->dispatch('openPaymentHistoryModal');
        } catch (\Exception $e) {
            logger('Error showing payment history: '.$e->getMessage());
            session()->flash('error', __('translation.error_loading_payment_history'));
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
    }

    public function resetFilters()
    {
        $this->reset('filters');
        $this->search = '';
    }

    public function render()
    {
        $orders = ProductOrder::query()
            ->with(['productOrderItems.product'])
            ->where('student_id', $this->student->id)
            ->when($this->search, function (Builder $query) {
                $query->whereHas('productOrderItems.product', function ($q) {
                    $q->where('name', 'ilike', '%'.$this->search.'%');
                });
            })
            ->when($this->filters['date_from'], function (Builder $query) {
                $query->whereDate('created_at', '>=', $this->filters['date_from']);
            })
            ->when($this->filters['date_to'], function (Builder $query) {
                $query->whereDate('created_at', '<=', $this->filters['date_to']);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.student.student-product-orders-table', [
            'orders' => $orders,
            'orderItems' => $this->orderItems,
            'availableProducts' => $this->availableProducts,
        ]);
    }

    protected function createTransaction($productPayment)
    {
        $student = Student::find($productPayment->student_id);
        if (! $student) {
            throw new \Exception(__('translation.student_not_found'));
        }

        $student_branch = $student->branches()->first();
        $main_account = $student_branch->account_id;
        if (! $main_account) {
            throw new \Exception(__('translation.no_data'));
        }

        $student_account_id = $student->account_id;
        if (! $student_account_id) {
            throw new \Exception(__('translation.no_data'));
        }

        //income transaction
        Transaction::create([
            'type' => TransactionTypeEnum::INCOME->value,
            'to_account_id' => $student_account_id,
            'amount' => $productPayment->amount,
            'created_by' => auth()->id() ?? 1,
        ]);

        //expense transaction to lc account
        Transaction::create([
            'type' => TransactionTypeEnum::EXPENSE->value,
            'from_account_id' => $student_account_id,
            'to_account_id' => $main_account,
            'amount' => $productPayment->amount,
            'created_by' => auth()->id() ?? 1,
        ]);
    }
}
