<div>
    <ul id="sortable" class="list-group col-md-8 col-sm-12 mx-auto">
        @foreach($paymentTypes as $item)
            <li data-index="{{ $item->id }}" data-order="{{$item->order}}"
                class="list-group-item">{{ $item->name }}</li>
        @endforeach
    </ul>

    @push('custom-scripts')
        <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
        <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
        <script src="{{ asset('assets/js/jquery.ui.touch-punch.min.js') }}"></script>
        <script>
            $(() => {
                $('#sortable').children().each(function (index, element) {
                    element.addEventListener('mouseover', event => {
                        event.target.style.cursor = 'grab';
                    });
                });

                $('#sortable').sortable({
                    placeholder: "sortable-placeholder",
                    update: function (event, ui) {
                        $(this).children().each(function (index) {
                            if ($(this).attr('data-order') !== (index + 1)) {
                                $(this).attr('data-order', (index + 1)).addClass('updated')
                            }
                        });
                        var orders = [];
                        $('.updated').each(function () {
                            orders.push([$(this).attr('data-index'), $(this).attr('data-order')])
                            $(this).removeClass('updated');
                        });
                        updateOrderWithAjax(orders);
                    }
                });
            });

            function updateOrderWithAjax(orders) {
                // Get CSRF token from meta tag
                var csrfToken = $('meta[name="csrf-token"]').attr('content');

                $.ajax({
                    url: '{{ route('admin.settings.payment-types.update-order') }}',
                    type: 'POST',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    data: {orders: orders},
                    success: function (response) {
                        // Handle success response if needed
                        console.log("Order updated successfully:", response);
                    },
                    error: function (xhr, status, error) {
                        // Handle error response if needed
                        console.error("Error updating order:", error);
                    }
                });
            }
        </script>
    @endpush
</div>
