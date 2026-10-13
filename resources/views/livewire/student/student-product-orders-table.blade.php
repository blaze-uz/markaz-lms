<div>
    <style>
        /* Mobile responsive improvements */
        @media (max-width: 768px) {
            .table-responsive {
                border: none;
            }

            .table td {
                padding: 0.5rem 0.25rem;
                vertical-align: top;
            }

            .table th {
                padding: 0.5rem 0.25rem;
                font-size: 0.875rem;
            }

            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }

            .dropdown-menu {
                font-size: 0.875rem;
            }

            .input-group {
                width: 100% !important;
                max-width: none !important;
            }

            .text-truncate {
                max-width: 120px !important;
            }
        }

        @media (max-width: 576px) {
            .card-header .row>div {
                margin-bottom: 1rem;
            }

            .card-header .row>div:last-child {
                margin-bottom: 0;
            }

            .text-truncate {
                max-width: 100px !important;
            }
        }
    </style>

    <div class="card rounded-4 mt-4">
        <div class="card-header rounded-top-4">
            <div class="row align-items-center">
                <div class="col-12 col-md-6 mb-2 mb-md-0">
                    <h5 class="card-title mb-0">{{ __('translation.product_orders') }}</h5>
                </div>
                <div class="col-12 col-md-6">
                    <div class="d-flex flex-column flex-md-row gap-2 justify-content-md-end">
                        <!-- Create Order button -->
                        <button type="button" class="btn btn-sm btn-success order-1 order-md-1" data-bs-toggle="modal"
                            data-bs-target="#createOrderModal">
                            <i class="mdi mdi-plus"></i>
                        </button>

                        <!-- Search input -->
                        <div class="input-group order-3 order-md-2" style="max-width: 250px;">
                            <input wire:model.live.debounce.300ms="search" type="text"
                                class="form-control form-control-sm" placeholder="{{ __('translation.search') }}...">
                            <button class="btn btn-sm btn-primary" type="button">
                                <i class="mdi mdi-magnify"></i>
                            </button>
                        </div>

                        <!-- Filter dropdown -->
                        <div class="dropdown order-2 order-md-3">
                            <button class="btn btn-sm btn-secondary dropdown-toggle w-100 w-md-auto" type="button"
                                id="orderFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="mdi mdi-filter-outline me-1"></i>
                                <span class="d-none d-sm-inline">{{ __('translation.Filter') }}</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end p-3" style="width: 280px;"
                                aria-labelledby="orderFilterDropdown">
                                <h6>{{ __('translation.status') }}</h6>
                                <select class="form-select mb-3" wire:model.live="filters.status">
                                    <option value="">{{ __('translation.all_statuses') }}</option>
                                    <option value="pending">{{ __('translation.pending') }}</option>
                                    <option value="completed">{{ __('translation.completed') }}</option>
                                    <option value="cancelled">{{ __('translation.cancelled') }}</option>
                                </select>

                                <h6>{{ __('translation.date_range') }}</h6>
                                <div class="mb-2">
                                    <label class="form-label small">{{ __('translation.from') }}</label>
                                    <input type="date" class="form-control form-control-sm"
                                        wire:model.live="filters.date_from">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">{{ __('translation.to') }}</label>
                                    <input type="date" class="form-control form-control-sm"
                                        wire:model.live="filters.date_to">
                                </div>

                                <div class="d-grid">
                                    <button class="btn btn-light btn-sm" wire:click="resetFilters">
                                        {{ __('translation.clear_filters') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-centered table-nowrap mb-0">
                    <thead class="table-light">
                        <tr>
                            <th wire:click="sortBy('id')" class="cursor-pointer" style="width: 60px;">
                                <span class="d-none d-md-inline">ID</span>
                                <span class="d-inline d-md-none">#</span>
                                @if ($sortField === 'id')
                                    <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </th>
                            <th>{{ __('translation.products') }}</th>
                            <th wire:click="sortBy('total_price')" class="cursor-pointer text-nowrap">
                                <span class="d-none d-md-inline">{{ __('translation.amount') }}</span>
                                <span class="d-inline d-md-none">{{ __('translation.total') }}</span>
                                @if ($sortField === 'total_price')
                                    <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('left_amount')"
                                class="cursor-pointer text-nowrap d-none d-lg-table-cell">
                                {{ __('translation.left_amount') }}
                                @if ($sortField === 'left_amount')
                                    <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('created_at')"
                                class="cursor-pointer text-nowrap d-none d-md-table-cell">
                                {{ __('translation.order_date') }}
                                @if ($sortField === 'created_at')
                                    <i class="mdi mdi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </th>
                            <th class="text-center" style="width: 120px;">{{ __('translation.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td class="fw-bold">{{ $order->id }}</td>
                                <td>
                                    @foreach ($order->productOrderItems ?? [] as $item)
                                        <div class="d-flex align-items-center mb-1">
                                            <span class="badge bg-primary-subtle text-primary me-1 flex-shrink-0">
                                                ×{{ $item->quantity }}
                                            </span>
                                            <div class="flex-grow-1">
                                                <div class="text-truncate" style="max-width: 150px;"
                                                    title="{{ $item->product?->name ?? __('translation.unknown_product') }}">
                                                    {{ $item->product?->name ?? __('translation.unknown_product') }}
                                                </div>
                                                <small class="text-muted">({{ money($item->price) }})</small>
                                            </div>
                                        </div>
                                    @endforeach

                                    <!-- Mobile-only info -->
                                    <div class="d-md-none mt-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">{{ dateFormat($order->created_at) }}</small>
                                            @if ($order->left_amount > 0)
                                                <span
                                                    class="badge bg-danger-subtle text-danger">{{ money($order->left_amount) }}
                                                    {{ __('translation.left') }}</span>
                                            @else
                                                <span
                                                    class="badge bg-success-subtle text-success">{{ __('translation.paid') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-nowrap">
                                    <div class="fw-bold">{{ money($order->total_price) }}</div>
                                    <!-- Show left amount on mobile under total -->
                                    <div class="d-lg-none">
                                        @if ($order->left_amount > 0)
                                            <small class="text-danger">{{ money($order->left_amount) }}
                                                {{ __('translation.left') }}</small>
                                        @else
                                            <small class="text-success">{{ __('translation.paid') }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    @if ($order->left_amount > 0)
                                        <span class="text-danger fw-bold">{{ money($order->left_amount) }}</span>
                                    @else
                                        <span
                                            class="badge bg-success-subtle text-success">{{ __('translation.paid') }}</span>
                                    @endif
                                </td>
                                <td class="text-nowrap d-none d-md-table-cell">{{ dateFormat($order->created_at) }}
                                </td>
                                <td>
                                    <div class="d-flex flex-column flex-md-row gap-1 justify-content-center">
                                        <!-- Payment button -->
                                        <button type="button" class="btn btn-sm btn-primary"
                                            wire:click="showPaymentModal({{ $order->id }})"
                                            {{ $order->left_amount <= 0 ? 'disabled' : '' }}
                                            title="{{ __('translation.payment') }}">
                                            <i class="mdi mdi-cash"></i>
                                        </button>

                                        <!-- More actions dropdown for mobile -->
                                        <div class="dropdown d-md-none">
                                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="mdi mdi-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                @if ($order->status === 'pending')
                                                    <li>
                                                        <button class="dropdown-item"
                                                            wire:click="completeOrder({{ $order->id }})">
                                                            <i
                                                                class="mdi mdi-check me-2"></i>{{ __('translation.complete') }}
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button class="dropdown-item"
                                                            wire:click="cancelOrder({{ $order->id }})">
                                                            <i
                                                                class="mdi mdi-close me-2"></i>{{ __('translation.cancel') }}
                                                        </button>
                                                    </li>
                                                @endif
                                                <li>
                                                    <button class="dropdown-item"
                                                        wire:click="showPaymentHistory({{ $order->id }})">
                                                        <i
                                                            class="mdi mdi-history me-2"></i>{{ __('translation.history') }}
                                                    </button>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <button class="dropdown-item text-danger"
                                                        wire:click="confirmOrderDeletion({{ $order->id }})">
                                                        <i
                                                            class="mdi mdi-trash-can me-2"></i>{{ __('translation.delete') }}
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>

                                        <!-- Desktop action buttons -->
                                        <div class="d-none d-md-flex gap-1">
                                            @if ($order->status === 'pending')
                                                <button type="button" class="btn btn-sm btn-success"
                                                    wire:click="completeOrder({{ $order->id }})"
                                                    title="{{ __('translation.complete') }}">
                                                    <i class="mdi mdi-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-warning"
                                                    wire:click="cancelOrder({{ $order->id }})"
                                                    title="{{ __('translation.cancel') }}">
                                                    <i class="mdi mdi-close"></i>
                                                </button>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-info"
                                                wire:click="showPaymentHistory({{ $order->id }})"
                                                title="{{ __('translation.history') }}">
                                                <i class="mdi mdi-history"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                wire:click="confirmOrderDeletion({{ $order->id }})"
                                                title="{{ __('translation.delete') }}">
                                                <i class="mdi mdi-trash-can"></i>
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="mb-3">
                                        <i class="mdi mdi-shopping text-muted" style="font-size: 3rem;"></i>
                                    </div>
                                    <h5 class="text-muted">
                                        {{ __('translation.no_orders') }}
                                    </h5>
                                    @if ($search || $filters['status'] || $filters['date_from'] || $filters['date_to'])
                                        <button class="btn btn-sm btn-outline-secondary mt-3"
                                            wire:click="resetFilters">
                                            {{ __('translation.clear_filters') }}
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 gap-3">
                <div class="d-flex align-items-center gap-2">
                    <label class="form-label mb-0 text-nowrap">{{ __('translation.per_page') }}:</label>
                    <select wire:model.live="perPage" class="form-select form-select-sm" style="width: auto;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <div class="d-flex justify-content-center">
                    {{ $orders->links() }}
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

    <!-- Create Order Modal -->
    <div class="modal fade" id="createOrderModal" tabindex="-1" aria-labelledby="createOrderModalLabel"
        aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createOrderModalLabel">
                        {{ __('translation.create_order_for', ['name' => $student->name]) }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <!-- Product Selection Section -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-end align-items-center mb-3">
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                    wire:click="addProductLine">
                                    <i class="mdi mdi-plus me-1"></i>{{ __('translation.add_more') }}
                                </button>
                            </div>

                            @foreach ($orderItems ?? [] as $index => $item)
                                <div class="row g-2">
                                    <div class="col-md-7">
                                        <label class="form-label small">{{ __('translation.Product') }}</label>
                                        <select
                                            class="form-select product-select @error('orderItems.' . $index . '.product_id') is-invalid @enderror"
                                            wire:model="orderItems.{{ $index }}.product_id"
                                            data-index="{{ $index }}">
                                            <option value="">{{ __('translation.select_product') }}</option>
                                            @foreach ($availableProducts as $product)
                                                <option value="{{ $product->id }}"
                                                    data-stock="{{ $product->stock }}"
                                                    data-name="{{ $product->name }}">
                                                    {{ $product->name }} ({{ money($product->price) }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('orderItems.' . $index . '.product_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label small">{{ __('translation.quantity') }}</label>
                                        <div class="input-group">
                                            <input type="number" min="1"
                                                class="form-control quantity-input @error('orderItems.' . $index . '.quantity') is-invalid @enderror"
                                                wire:model="orderItems.{{ $index }}.quantity"
                                                data-index="{{ $index }}">
                                            <span class="input-group-text stock-display-{{ $index }}"></span>
                                        </div>
                                        @error('orderItems.' . $index . '.quantity')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-1 d-flex align-items-end">
                                        <button type="button" class="btn btn-outline-danger"
                                            wire:click="removeProductLine({{ $index }})">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach

                            @if (count($orderItems) === 0)
                                <div class="alert alert-info mb-0">
                                    {{ __('translation.no_products_added') }}
                                </div>
                            @endif
                        </div>

                        <!-- Notes -->
                        <div class="mb-0">
                            <label for="notes" class="form-label">{{ __('translation.notes') }}
                                ({{ __('translation.optional') }})</label>
                            <textarea id="notes" class="form-control" wire:model="notes" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __('translation.cancel') }}
                    </button>
                    <button type="button" class="btn btn-primary" wire:click="saveOrder"
                        wire:loading.attr="disabled">
                        {{ __('translation.create_order') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Order Confirmation Modal -->
    <div class="modal fade" id="deleteOrderModal" tabindex="-1" aria-labelledby="deleteOrderModalLabel"
        aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteOrderModalLabel">{{ __('translation.confirm_delete') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('translation.delete_order_confirmation') }}</p>
                    @if ($orderToDelete)
                        <div class="alert alert-warning">
                            <strong>{{ __('translation.order_id') }}:</strong> #{{ $orderToDelete }}
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __('translation.cancel') }}
                    </button>
                    <button type="button" class="btn btn-danger" wire:click="deleteOrder">
                        {{ __('translation.delete') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    @include('livewire.student.partials.order-payment-modal')
    <!-- Include the payment history modal -->
    @include('livewire.student.partials.order-payment-history-modal')
    @script
        <script>
            // Modal handling
            $wire.on('orderCreated', () => {
                bootstrap.Modal.getInstance(document.getElementById('createOrderModal')).hide();
                window.notifyMessage('{{ __('translation.order_created_successfully') }}', 'success');
            });

            $wire.on('orderDeleted', () => {
                bootstrap.Modal.getInstance(document.getElementById('deleteOrderModal')).hide();
                window.notifyMessage('{{ __('translation.order_deleted_successfully') }}', 'success');
            });

            $wire.on('openDeleteModal', () => {
                const modal = new bootstrap.Modal(document.getElementById('deleteOrderModal'));
                modal.show();
            });

            // Handle stock limits without Livewire updates
            document.addEventListener('DOMContentLoaded', function() {
                // Process initial product selections
                document.querySelectorAll('.product-select').forEach(function(select) {
                    updateStockInfo(select);
                });

                // Add event listeners for product selection changes
                document.addEventListener('change', function(event) {
                    if (event.target.classList.contains('product-select')) {
                        updateStockInfo(event.target);
                    }
                });

                // Add event listeners for quantity changes
                document.addEventListener('input', function(event) {
                    if (event.target.classList.contains('quantity-input')) {
                        validateQuantity(event.target);
                    }
                });

                function updateStockInfo(selectElement) {
                    const index = selectElement.dataset.index;
                    const selectedOption = selectElement.options[selectElement.selectedIndex];
                    const stockDisplay = document.querySelector('.stock-display-' + index);
                    const quantityInput = document.querySelector('.quantity-input[data-index="' + index + '"]');

                    if (selectedOption && selectedOption.value) {
                        const stock = parseInt(selectedOption.dataset.stock);
                        const productName = selectedOption.dataset.name;

                        // Update the stock display
                        stockDisplay.textContent = '{{ __('translation.available') }}: ' + stock;

                        // Mark as warning if low stock
                        if (stock < 5) {
                            stockDisplay.classList.add('text-warning');
                        } else {
                            stockDisplay.classList.remove('text-warning');
                        }

                        // Set max attribute and store data on the input
                        quantityInput.setAttribute('max', stock);
                        quantityInput.dataset.stock = stock;
                        quantityInput.dataset.productName = productName;

                        // Ensure quantity doesn't exceed stock
                        validateQuantity(quantityInput);
                    } else if (stockDisplay) {
                        // Clear if no product selected
                        stockDisplay.textContent = '';
                        if (quantityInput) {
                            quantityInput.removeAttribute('max');
                            delete quantityInput.dataset.stock;
                        }
                    }
                }

                function validateQuantity(inputElement) {
                    const stock = parseInt(inputElement.dataset.stock);
                    const productName = inputElement.dataset.productName;

                    if (stock && !isNaN(stock)) {
                        if (parseInt(inputElement.value) > stock) {
                            inputElement.value = stock;
                            window.notifyMessage('{{ __('translation.max_quantity_exceeded') }}: ' + productName,
                                'warning');
                        }
                    }
                }
            });

            // Reinitialize when modal opens
            document.getElementById('createOrderModal').addEventListener('shown.bs.modal', function() {
                document.querySelectorAll('.product-select').forEach(function(select) {
                    updateStockInfo(select);
                });
            });

            // // Payment modal handling
            // $wire.on('openPaymentModal', () => {
            //     // Direct DOM manipulation approach that works regardless of Bootstrap version
            //     const modalElement = document.getElementById('orderPaymentModal');
            //     modalElement.classList.add('show');
            //     modalElement.style.display = 'block';
            //     document.body.classList.add('modal-open');

            //     // Create backdrop if it doesn't exist
            //     let backdropElement = document.querySelector('.modal-backdrop');
            //     if (!backdropElement) {
            //         backdropElement = document.createElement('div');
            //         backdropElement.className = 'modal-backdrop fade show';
            //         document.body.appendChild(backdropElement);
            //     }

            //     console.log('Modal should be visible now');
            // });

            $wire.on('paymentSaved', () => {
                closePaymentModal();
                window.notifyMessage('{{ __('translation.payment_saved_successfully') }}', 'success');
            });

            // New function to close the modal
            function closePaymentModal() {
                const modalElement = document.getElementById('paymentModal');
                modalElement.classList.remove('show');
                modalElement.style.display = 'none';
                document.body.classList.remove('modal-open');

                // Remove backdrop
                const backdropElement = document.querySelector('.modal-backdrop');
                if (backdropElement) {
                    backdropElement.remove();
                }
            }

            // Add event listeners for close buttons
            document.addEventListener('DOMContentLoaded', function() {
                // Get all close buttons in the payment modal
                const closeButtons = document.querySelectorAll('#paymentModal [data-bs-dismiss="modal"]');
                closeButtons.forEach(button => {
                    button.addEventListener('click', closePaymentModal);
                });
            });

            // Add to your script section
            function testPaymentModal() {
                const modalElement = document.getElementById('paymentModal');
                modalElement.classList.add('show');
                modalElement.style.display = 'block';
                document.body.classList.add('modal-open');

                let backdropElement = document.querySelector('.modal-backdrop');
                if (!backdropElement) {
                    backdropElement = document.createElement('div');
                    backdropElement.className = 'modal-backdrop fade show';
                    document.body.appendChild(backdropElement);
                }
            }

            // Add to your existing script section
            $wire.on('openPaymentHistoryModal', () => {
                // Use Bootstrap's native method to open the modal
                const modalElement = document.getElementById('orderPaymentHistoryModal');
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            });
        </script>
    @endscript
</div>
