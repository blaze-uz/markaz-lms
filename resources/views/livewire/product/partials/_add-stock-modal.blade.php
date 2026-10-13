<!-- Add Stock Modal -->
<div class="modal fade" id="addStockModal" tabindex="-1" aria-labelledby="addStockModalLabel"
    aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStockModalLabel">{{ __("translation.add_stock") }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($selectedProduct)
                <div class="text-center mb-3">
                    <h5>{{ $selectedProduct->name }}</h5>
                    <p class="mb-1">{{ __("translation.current_stock") }}: <span class="fw-bold">{{ $selectedProduct->stock }}</span></p>
                    @if($selectedProduct->min_stock > 0)
                    <p class="text-muted small">{{ __("translation.min_stock") }}: {{ $selectedProduct->min_stock }}</p>
                    @endif
                </div>
                @endif
                
                <div class="mb-3">
                    <label for="quantity_to_add" class="form-label">{{ __("translation.quantity_to_add") }}</label>
                    <input type="number" class="form-control @error('quantityToAdd') is-invalid @enderror" 
                        id="quantity_to_add" wire:model="quantityToAdd" min="1">
                    @error('quantityToAdd')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="stock_note" class="form-label">{{ __("translation.note") }} ({{ __("translation.optional") }})</label>
                    <textarea class="form-control @error('stockNote') is-invalid @enderror" 
                        id="stock_note" wire:model="stockNote" rows="2"></textarea>
                    @error('stockNote')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ __("translation.cancel") }}
                </button>
                <button type="button" class="btn btn-primary" wire:click="addStock">
                    {{ __("translation.add_stock") }}
                </button>
            </div>
        </div>
    </div>
</div>