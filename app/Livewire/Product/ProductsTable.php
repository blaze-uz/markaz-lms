<?php

namespace App\Livewire\Product;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class ProductsTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';

    public $sortField = 'id';

    public $sortDirection = 'desc';

    public $perPage = 10;

    public $filters = [
        'status' => '',
        'price_min' => null,
        'price_max' => null,
    ];

    // Form properties
    public $name = '';

    public $price = '';

    public $stock = '';

    public $min_stock = ''; // Added min_stock field

    public $status = 'active';

    public $description = '';

    public $is_active = true;

    // Add these properties
    public $selectedProduct = null;

    public $quantityToAdd = 1;

    public $stockNote = '';

    // Add these properties
    public $editingProduct = null;

    public $editingProductId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'id'],
        'sortDirection' => ['except' => 'desc'],
    ];

    protected $rules = [
        'name' => 'required|string|max:255',
        'price' => 'required|min:0',
        'stock' => 'required|integer|min:0',
        'min_stock' => 'required|integer|min:0', // Added validation rule
        'status' => 'required|in:active,inactive',
        'description' => 'nullable|string',
    ];

    // Add custom messages with translations
    protected function messages()
    {
        return [
            'name.required' => __('validation.required', ['attribute' => __('translation.name')]),
            'name.string' => __('validation.string', ['attribute' => __('translation.name')]),
            'name.max' => __('validation.max.string', ['attribute' => __('translation.name'), 'max' => 255]),

            'price.required' => __('validation.required', ['attribute' => __('translation.price')]),
            'price.min' => __('validation.min.numeric', ['attribute' => __('translation.price'), 'min' => 0]),

            'stock.required' => __('validation.required', ['attribute' => __('translation.stock')]),
            'stock.integer' => __('validation.integer', ['attribute' => __('translation.stock')]),
            'stock.min' => __('validation.min.numeric', ['attribute' => __('translation.stock'), 'min' => 0]),

            'min_stock.required' => __('validation.required', ['attribute' => __('translation.min_stock')]),
            'min_stock.integer' => __('validation.integer', ['attribute' => __('translation.min_stock')]),
            'min_stock.min' => __('validation.min.numeric', ['attribute' => __('translation.min_stock'), 'min' => 0]),

            'status.required' => __('validation.required', ['attribute' => __('translation.status')]),
            'status.in' => __('validation.in', ['attribute' => __('translation.status')]),

            'description.string' => __('validation.string', ['attribute' => __('translation.description')]),
        ];
    }

    #[On('product-created')]
    public function refreshProducts()
    {
        // Will refresh the component when product-created event is fired
    }

    public function openCreateModal()
    {
        $this->dispatch('open-modal', name: 'create-product');
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

    public function saveProduct()
    {
        abort_unless(auth()->user()?->can('product-store'), 403);

        // Remove commas from price before validation
        if (! empty($this->price)) {
            $this->price = removeComma($this->price);
        }

        $validated = $this->validate();

        Product::create($validated);

        $this->reset(['name', 'price', 'stock', 'min_stock', 'status', 'description']); // Added min_stock to reset

        // Dispatch an event to close the modal
        $this->dispatch('productSaved');

        // Show a success notification
        session()->flash('success_alert', __('translation.alert_messages.created'));
    }

    public function updatedIsActive($value)
    {
        $this->status = $value ? 'active' : 'inactive';
    }

    public function mount()
    {
        $this->is_active = $this->status === 'active';
    }

    public function render()
    {
        $products = Product::query()
            ->when($this->search, function (Builder $query) {
                $query->where(function (Builder $query) {
                    $query->where('name', 'ilike', '%'.$this->search.'%')
                        ->orWhere('description', 'ilike', '%'.$this->search.'%');
                });
            })
            ->when($this->filters['status'], function (Builder $query) {
                $query->where('status', $this->filters['status']);
            })
            ->when($this->filters['price_min'], function (Builder $query) {
                $query->where('price', '>=', $this->filters['price_min']);
            })
            ->when($this->filters['price_max'], function (Builder $query) {
                $query->where('price', '<=', $this->filters['price_max']);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.product.products-table', [
            'products' => $products,
        ]);
    }

    // Add these methods
    public function setSelectedProduct($productId)
    {
        $this->selectedProduct = Product::find($productId);
        $this->quantityToAdd = 1;
        $this->stockNote = '';
    }

    public function addStock()
    {
        abort_unless(auth()->user()?->can('product-update'), 403);

        $this->validate([
            'quantityToAdd' => 'required|integer|min:1',
            'stockNote' => 'nullable|string|max:255',
        ]);

        if (! $this->selectedProduct) {
            return;
        }

        // Update the stock
        $this->selectedProduct->stock += $this->quantityToAdd;
        $this->selectedProduct->save();

        // Optionally log the stock addition
        // StockLog::create([
        //     'product_id' => $this->selectedProduct->id,
        //     'quantity' => $this->quantityToAdd,
        //     'type' => 'add',
        //     'note' => $this->stockNote,
        //     'user_id' => auth()->id(),
        // ]);

        // Reset the form and close the modal
        $this->dispatch('stockAdded');

        // Show success message
        session()->flash('success_alert', __('translation.alert_messages.stock_added'));
    }

    // Add these methods for edit functionality
    public function editProduct($productId)
    {
        $this->editingProductId = $productId;
        $product = Product::find($productId);

        // Clone for editing (to avoid live binding issues)
        $this->editingProduct = [
            'name' => $product->name,
            'price' => $product->price,
            'min_stock' => $product->min_stock,
            'status' => $product->status,
            'description' => $product->description,
        ];
        $this->dispatch('open-modal');
    }

    public function updateProduct()
    {
        abort_unless(auth()->user()?->can('product-update'), 403);

        // Price formatting
        if (! empty($this->editingProduct['price'])) {
            $this->editingProduct['price'] = removeComma($this->editingProduct['price']);
        }

        // Validation
        $this->validate([
            'editingProduct.name' => 'required|string|max:255',
            'editingProduct.price' => 'required|numeric|min:0',
            'editingProduct.min_stock' => 'required|integer|min:0',
            'editingProduct.status' => 'required|in:active,inactive',
            'editingProduct.description' => 'nullable|string',
        ]);

        // Update product
        $product = Product::find($this->editingProductId);

        $product->update([
            'name' => $this->editingProduct['name'],
            'price' => $this->editingProduct['price'],
            'min_stock' => $this->editingProduct['min_stock'],
            'status' => $this->editingProduct['status'],
            'description' => $this->editingProduct['description'],
        ]);

        $this->dispatch('productUpdated');
        $this->reset(['editingProduct', 'editingProductId']);

        // Show success notification
        session()->flash('success_alert', __('translation.alert_messages.updated'));
    }
}
