<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel"
    aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProductModalLabel">{{ __("translation.edit_product") }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($editingProduct)
                <form>
                    <input type="hidden" wire:model="editingProductId">
                    
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">{{ __("translation.name") }}</label>
                        <input type="text" class="form-control @error('editingProduct.name') is-invalid @enderror" 
                               id="edit_name" wire:model="editingProduct.name">
                        @error('editingProduct.name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="edit_price">{{ __("translation.price") }}</label>
                        <input type="tel" id="edit_price" name="edit_price"
                            class="form-control money @error('editingProduct.price') is-invalid @enderror"
                            wire:model="editingProduct.price">
                        @error('editingProduct.price')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="edit_min_stock" class="form-label">{{ __("translation.min_stock") }}</label>
                        <input type="number" class="form-control @error('editingProduct.min_stock') is-invalid @enderror" 
                               id="edit_min_stock" wire:model="editingProduct.min_stock">
                        @error('editingProduct.min_stock')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">{{ __("translation.min_stock_help") }}</small>
                    </div>

                    <div class="mb-3">
                        <label for="edit_status" class="form-label">{{ __("translation.status") }}</label>
                        <select class="form-select @error('editingProduct.status') is-invalid @enderror" 
                                id="edit_status" wire:model="editingProduct.status">
                            <option value="active">{{ __("translation.active") }}</option>
                            <option value="inactive">{{ __("translation.inactive") }}</option>
                        </select>
                        @error('editingProduct.status')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="edit_description" class="form-label">{{ __("translation.description") }}</label>
                        <textarea class="form-control @error('editingProduct.description') is-invalid @enderror" 
                                  id="edit_description" wire:model="editingProduct.description" rows="3"></textarea>
                        @error('editingProduct.description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </form>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ __("translation.cancel") }}
                </button>
                <button type="button" class="btn btn-primary" wire:click="updateProduct">
                    {{ __("translation.save") }}
                </button>
            </div>
        </div>
    </div>
</div>