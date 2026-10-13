<!-- Create Product Modal -->
<div class="modal fade" id="createProductModal" tabindex="-1" aria-labelledby="createProductModalLabel"
    aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createProductModalLabel">{{ __("translation.add_product") }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label for="name" class="form-label">{{ __("translation.name") }}</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                            wire:model="name">
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="price">{{ __("translation.price") }}</label>
                        <input type="tel" id="price" name="price"
                            class="form-control money @error('price') is-invalid @enderror"
                            value="{{($price) ?? ''}}" wire:model="price">
                        @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="stock" class="form-label">{{ __("translation.stock") }}</label>
                        <input type="number" class="form-control @error('stock') is-invalid @enderror" id="stock"
                            wire:model="stock">
                        @error('stock')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="min_stock" class="form-label">{{ __("translation.min_stock") }}</label>
                        <input type="number" class="form-control @error('min_stock') is-invalid @enderror" id="min_stock"
                            wire:model="min_stock">
                        @error('min_stock')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">{{ __("translation.min_stock_help") }}</small>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">{{ __("translation.description") }}</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description"
                            wire:model="description" rows="3"></textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ __("translation.cancel") }}
                </button>
                <button type="button" class="btn btn-primary" wire:click="saveProduct">
                    {{ __("translation.save") }}
                </button>
            </div>
        </div>
    </div>
</div>