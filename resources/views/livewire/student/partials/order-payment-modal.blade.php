<div>
    <div class="modal fade" id="orderPaymentModal" tabindex="-1" aria-labelledby="paymentModalLabel"
        aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">
                        {{ __('translation.make_payment') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($orderForPayment)
                    <div class="alert alert-info">
                        <div class="d-flex justify-content-between">
                            <span>{{ __('translation.order_total') }}:</span>
                            <strong>{{ money($orderForPayment->total_price) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>{{ __('translation.paid_amount') }}:</span>
                            <strong>{{ money($orderForPayment->total_price - $orderForPayment->left_amount) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>{{ __('translation.left_amount') }}:</span>
                            <strong class="text-danger">{{ money($orderForPayment->left_amount) }}</strong>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="paymentAmount" class="form-label">{{ __('translation.payment_amount') }}</label>
                        <input 
                            type="tel" 
                            id="paymentAmount" 
                            name="paymentAmount"
                            class="form-control money" 
                            wire:model="paymentAmount"
                            placeholder="{{ __('translation.payment_amount') }}">
                        @error('paymentAmount')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label d-block">{{ __('translation.payment_method') }}</label>
                        <div class="payment-method-buttons">
                            <div class="btn-group w-100" role="group" aria-label="Payment methods">
                                @forelse($paymentMethods as $method)
                                    <input type="radio" class="btn-check" name="paymentMethod" id="payment_{{ $method->id }}" 
                                        value="{{ $method->id }}" wire:model="paymentMethod" autocomplete="off">
                                    <label class="btn btn-outline-primary" for="payment_{{ $method->id }}">
                                        @if($method->icon)
                                            <i class="mdi {{ $method->icon }} me-1"></i>
                                        @endif
                                        {{ $method->name }}
                                    </label>
                                @empty
                                    <div class="alert alert-warning w-100 mb-0">
                                        {{ __('translation.no_payment_methods_available') }}
                                    </div>
                                @endforelse
                            </div>
                        </div>
                        @error('paymentMethod')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-0">
                        <label for="payment_notes" class="form-label">{{ __('translation.notes') }} ({{ __('translation.optional') }})</label>
                        <textarea id="payment_notes" class="form-control" wire:model="paymentNotes" rows="2"></textarea>
                    </div>
                    
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" id="fullPaymentCheckbox"
                            wire:click="setFullPayment">
                        <label class="form-check-label" for="fullPaymentCheckbox">
                            {{ __('translation.pay_full_amount') }}
                        </label>
                    </div>
                    @else
                    <div class="alert alert-warning">
                        {{ __('translation.no_order_selected') }}
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __('translation.cancel') }}
                    </button>
                    <button type="button" class="btn btn-primary" 
                        wire:click="savePayment" 
                        wire:loading.attr="disabled"
                        @if(!$orderForPayment) disabled @endif>
                        {{ __('translation.make_payment') }}
                    </button>
                </div>
            </div>
        </div>      
    </div>
    @script
    <script>
        $wire.on('openPaymentModal', () => {
            // Use Bootstrap's native method to open the modal
            const modalElement = document.getElementById('orderPaymentModal');
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        });
        
        $wire.on('paymentSaved', () => {
            // Use Bootstrap's native method to close the modal
            const modalElement = document.getElementById('orderPaymentModal');
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
        });
        
        // Handle full payment checkbox
        document.addEventListener('click', function(event) {
            if (event.target && event.target.id === 'fullPaymentCheckbox') {
                // The wire:click will handle setting the amount, but we need to mask it after
                setTimeout(() => {
                    initMoneyMask();
                }, 300);
            }
        });
    </script>
    @endscript
</div>