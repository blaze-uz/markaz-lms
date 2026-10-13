<div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div class="input-group">
                                <input wire:model.live.debounce.300ms="search" type="text" class="form-control"
                                    placeholder="{{ __('translation.search') }}...">
                                <button class="btn btn-primary" type="button">
                                    <i class="mdi mdi-magnify"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex justify-content-md-end">
                            <div class="dropdown me-2">
                                <button class="btn btn-secondary dropdown-toggle" type="button" id="filterDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="mdi mdi-filter-outline"></i> {{ __("translation.filter") }}
                                </button>
                                <div class="dropdown-menu p-3" style="width: 300px;" aria-labelledby="filterDropdown">
                                    <h6>{{__("translation.price_range")}}</h6>
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <input type="number" class="form-control"
                                                wire:model.live.debounce.500ms="filters.price_min" placeholder="Min">
                                        </div>
                                        <div class="col-6">
                                            <input type="number" class="form-control"
                                                wire:model.live.debounce.500ms="filters.price_max" placeholder="Max">
                                        </div>
                                    </div>
                                    <h6>{{ __("translation.status") }}</h6>
                                    <select class="form-select mb-3" wire:model.live="filters.status">
                                        <option value="">{{ __("translation.all_statuses") }}</option>
                                        <option value="active">{{ __("translation.active") }}</option>
                                        <option value="inactive">{{ __("translation.inactive") }}</option>
                                    </select>
                                    <div class="d-grid">
                                        <button class="btn btn-sm btn-light" wire:click="resetFilters">
                                            {{ __("translation.clear_filters") }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- Simple button to trigger Bootstrap modal -->
                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                data-bs-target="#createProductModal">
                                <i class="mdi mdi-plus"></i> {{ __("translation.add") }}
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th wire:click="sortBy('id')" class="cursor-pointer">
                                        ID
                                        @if($sortField === 'id')
                                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('name')" class="cursor-pointer">
                                        {{ __("translation.name") }}
                                        @if($sortField === 'name')
                                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('price')" class="cursor-pointer">
                                        {{ __("translation.price") }}
                                        @if($sortField === 'price')
                                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('stock')" class="cursor-pointer">
                                        {{ __("translation.stock") }}
                                        @if($sortField === 'stock')
                                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </th>
                                    <th wire:click="sortBy('status')" class="cursor-pointer">
                                        {{ __("translation.status") }}
                                        @if($sortField === 'status')
                                        <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </th>
                                    <th>
                                        {{ __("translation.actions") }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $product)
                                <tr>
                                    <td>{{ $product->id }}</td>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ money($product->price) }}</td>
                                    <td>
                                        {{ $product->stock }}
                                        @if($product->stock < $product->min_stock)
                                            <span class="badge bg-warning ms-1" data-bs-toggle="tooltip" title="{{ __('translation.low_stock_warning') }}">
                                                <i class="mdi mdi-alert-circle"></i>
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($product->status === 'active')
                                        <span class="badge bg-success">
                                            {{ __("translation.active") }}
                                        </span>
                                        @else
                                        <span class="badge bg-danger">
                                            {{ __("translation.inactive") }}
                                        </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#addStockModal" 
                                                wire:click="setSelectedProduct({{ $product->id }})">
                                                <i class="mdi mdi-plus-circle"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-info"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editProductModal" 
                                                wire:click="editProduct({{ $product->id }})">
                                                <i class="mdi mdi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteProductModal"
                                                data-product-id="{{ $product->id }}"
                                                data-product-name="{{ $product->name }}">
                                                <i class="mdi mdi-trash-can"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="mb-3">
                                            <i class="mdi mdi-database-search text-muted" style="font-size: 3rem;"></i>
                                        </div>
                                        <h5 class="text-muted">
                                            {{ __("translation.no_data") }}
                                        </h5>
                                        @if($search || $filters['status'] || $filters['price_min'] ||
                                        $filters['price_max'])
                                        <button class="btn btn-sm btn-outline-secondary mt-3" wire:click="resetFilters">
                                            {{ __("translation.clear_filters") }}
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            <select wire:model.live="perPage" class="form-select form-select-sm w-auto">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                        <div>
                            {{ $products->links() }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-preloader d-none" wire:loading.class.remove="d-none">
                <div class="card-status">
                    <div class="spinner-border text-success">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include modals as partials -->
    @include('livewire.product.partials._create-product-modal')
    @include('livewire.product.partials._add-stock-modal')
    @include('livewire.product.partials._edit-product-modal')
    @include('livewire.product.partials._delete-product-modal')

    @push('custom-scripts')
    <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.15/jquery.mask.js"></script>
    
    <script>
        // Wait for both jQuery and the mask plugin to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Check if mask plugin is available
            if (typeof $.fn.mask === 'function') {
                initMoneyMask();
            } else {
                console.error('jQuery Mask plugin not loaded');
            }
        });
        
        // Initialize money mask function
        function initMoneyMask() {
            try {
                $('.money').mask('000,000,000,000', {reverse: true});
                console.log('Money mask initialized');
            } catch (e) {
                console.error('Error initializing money mask:', e);
            }
        }
        
        // Re-initialize in modal show events
        $(document).on('shown.bs.modal', function() {
            setTimeout(function() {
                initMoneyMask();
            }, 100);
        });
    </script>
    @endpush

    @script
    <script>
        // Livewire event listeners using modern syntax
        $wire.on('productSaved', () => {
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('createProductModal')).hide();
            // Show notification
            window.notifyMessage('{{ __("translation.product_saved") }}', 'success');
        });

        $wire.on('productUpdated', () => {
            bootstrap.Modal.getInstance(document.getElementById('editProductModal')).hide();
            window.notifyMessage('{{ __("translation.product_updated") }}', 'success');
        });

        $wire.on('stockAdded', () => {
            bootstrap.Modal.getInstance(document.getElementById('addStockModal')).hide();
            window.notifyMessage('{{ __("translation.stock_added_successfully") }}', 'success');
        });

        // Re-initialize mask when any modal opens via Livewire
        $wire.on('open-modal', () => {
            setTimeout(initMoneyMask, 100);
        });

        // Delete modal setup
        const deleteModal = document.getElementById('deleteProductModal');
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', (event) => {
                const button = event.relatedTarget;
                const productId = button.getAttribute('data-product-id');
                const productName = button.getAttribute('data-product-name');
                
                // Set product name in modal
                deleteModal.querySelector('.product-name').textContent = productName;
                
                // Set form action
                const form = document.getElementById('deleteProductForm');
                form.action = `{{ route('admin.products.index') }}/${productId}`;
            });
        }
    </script>
    @endscript
</div>