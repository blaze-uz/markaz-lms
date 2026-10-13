<div>
    @push('css')
    <link href="{{ asset('assets/libs/choices.js/choices.js.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}" rel="stylesheet" type="text/css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link rel="stylesheet" href="{{ asset('assets/libs/tom-select/tom-select.min.css') }}" />
    <style>
        .ts-dropdown {
            background-color: white;
        }
    </style>
    @endpush
    @include("livewire.expense.partials.cards")
    <div class="flex-wrap gap-2 mb-3 d-flex justify-content-between align-items-baseline">
        <div class="flex-wrap gap-2 d-flex align-items-center">
            <x-search parent-class="m-0" />
            <button type="button" class="bg-white border text-start btn" id="daterange-btn-debts" data-livewire="@this">
                <i class="far fa-calendar-alt"></i>
                <span>
                    {{ $date ? $date : "Select" }}
                </span>
                <i class="fas fa-caret-down"></i>
            </button>
        </div>
        <div class="flex-wrap gap-2 d-flex justify-content-end align-items-center">
            <button type="button" class="btn btn-info" data-bs-toggle="offcanvas" href="#filterCanvas">
                <i class="align-bottom ri-filter-3-line me-1"></i> @lang("translation.Filter")
            </button>
            <button class="btn btn-success t-excel-btn" wire:click="export">
                <i class="align-bottom ri-file-excel-2-line"></i>
            </button>
            <a href="{{ route('admin.expenses.settings.index') }}" class="btn btn-success t-settings-btn">
                <i class="fas fa-cog"></i>
            </a>
            <a href="{{ route('admin.expenses.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                @lang('translation.expenses.add')
            </a>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th>ID</th>
                        <th>@lang('translation.expenses.item_name')</th>
                        <th>@lang('translation.expenses.quantity')</th>
                        <th>@lang('translation.expenses.price')</th>
                        <th>@lang('attr.payment_type')</th>
                        <th>@lang('translation.expenses.place_name')</th>
                        <th>@lang('translation.expenses.expense_account')</th>
                        <th>@lang('translation.expenses.created_by')</th>
                        <th>@lang('attr.created_at')</th>
                        <th></th>
                    </tr>
                    @forelse($items as $item)
                    <tr>
                        <td>{{$item->id}}</td>
                        <td class="text-nowrap">{{$item->item?->name}}</td>
                        <td class="text-nowrap">{{$item->quantity}}</td>
                        <td class="text-nowrap text-danger">
                            <span class="fw-semibold">
                                {{money($item->price)}}
                            </span>
                        </td>
                        <td class="text-nowrap">
                            <span class="badge bg-success-subtle text-success">
                                {{$item->paymentType->name ?? '-'}}
                            </span>
                        </td>
                        <td>{{$item->place?->name}}</td>
                        <td class="text-nowrap">{{$item->account?->name}}</td>
                        <td class="text-nowrap">{{$item->createdBy?->name}}</td>
                        <td class="text-nowrap">{{dateFormat($item->date)}}</td>
                        <td>
                            <div class="gap-2 d-flex">
                                <a href="{{ route('admin.expenses.show', $item->id) }}"
                                    class="t-show-btn btn btn-sm btn-success">
                                    <i class="mdi mdi-eye"></i>
                                </a>
                                @can('expense-edit')
                                <a href="{{ route('admin.expenses.edit', $item->id) }}"
                                    class="t-edit-btn btn btn-sm btn-success">
                                    <i class="mdi mdi-pencil"></i>
                                </a>
                                @endcan
                                @can('expense-delete')
                                <form action="{{ route('admin.expenses.destroy', $item) }}" class="d-inline-block"
                                    method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="t-delete-btn btn btn-sm btn-danger"
                                        onclick="return confirm('Are you sure?')">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="100" class="text-center">
                            <x-no-result />
                        </td>
                    </tr>
                    @endforelse
                </table>
                {{ $items->links() }}
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
    <div class="offcanvas offcanvas-end" tabindex="-1" id="filterCanvas" aria-labelledby="filterCanvasLabel">
        <div class="offcanvas-header bg-light">
            <h5 class="offcanvas-title" id="filterCanvas">@lang("translation.Filter")</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <!--end offcanvas-header-->
        <form class="d-flex flex-column justify-content-end h-100" action="{{route('admin.expenses.index')}}">
            <div class="offcanvas-body">
                <div class="mb-3">
                    <label for="items">
                        @lang("translation.expenses.item_name")
                    </label>
                    <select class="tom-select form-control form-select" id="items" name="expense-item">
                        <option value="">@lang("translation.expenses.select")</option>
                        @foreach($expenseItems as $item)
                        <option value="{{$item->id}}" @if(request()->get('expense-item') == $item->id) selected @endif
                            >{{$item->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="places">
                        @lang("translation.expenses.place_name")
                    </label>
                    <select class="form-control form-select tom-select" id="places" name="expense-place">
                        <option value="">@lang("translation.expenses.select")</option>
                        @foreach($expensePlaces as $place)
                        <option value="{{$place->id}}" @if(request()->get('expense-place') == $place->id) selected
                            @endif
                            >{{$place->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="creators">
                        @lang("translation.expenses.creators")
                    </label>
                    <select class="form-control form-select tom-select" id="creators" name="creator">
                        <option value="">@lang("translation.expenses.creators")</option>
                        @foreach($creators as $creator)
                        <option value="{{$creator['id']}}" @if($creator==$creator['id']) selected @endif>
                            {{$creator['name']}}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <!--end offcanvas-body-->
            <div class="gap-2 p-3 text-center offcanvas-footer border-top hstack">
                <a class="btn btn-light w-100" href="{{ route('admin.expenses.index') }}">
                    @lang("translation.Clear Filter")</a>
                <button type="submit" class="btn btn-success w-100">@lang("translation.Filter")</button>
            </div>
            <!--end offcanvas-footer-->
        </form>
    </div>

    @push("custom-scripts")
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tom-select/2.3.1/js/tom-select.complete.js"
        integrity="sha512-96+GeOCMUo6K6W5zoFwGYN9dfyvJNorkKL4cv+hFVmLYx/JZS5vIxOk77GqiK0qYxnzBB+4LbWRVgu5XcIihAQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script>
        document.querySelectorAll('.tom-select').forEach((el) => {
            //add backgroud color to the dropdown
            let settings = {};
            new TomSelect(el, settings);
        });
        flatpickr("#range", {
            mode: 'range',
        });
        
        tippy('.t-settings-btn', {
            content: "{{__('translation.expenses.settings')}}",
        });
        tippy('.t-excel-btn', {
            content: "{{__('translation.export')}}",
        });
    </script>
    <script>
        //set locale moment
        moment.locale('{{app()->getLocale() == "ru" ? "ru" : "uz-latn"}}');
        //Date range as a button
        $('#daterange-btn-debts').daterangepicker({
                ranges: {
                    "{{__('attr.today')}}": [moment(), moment()],
                    "{{__('attr.yesterday')}}": [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    "{{__('attr.last7days')}}": [moment().subtract(6, 'days'), moment()],
                    "{{__('attr.last30days')}}": [moment().subtract(29, 'days'), moment()],
                    "{{__('attr.thisMonth')}}": [moment().startOf('month'), moment().endOf('month')],
                    "{{__('attr.lastMonth')}}": [
                        moment().subtract(1, 'months').startOf('month'),
                        moment().subtract(1, 'months').endOf('month')
                    ],
                    "{{__('attr.thisYear')}}": [moment().startOf('year'), moment().endOf('year')],
                    "{{__('attr.lastYear')}}": [
                        moment().subtract(1, 'years').startOf('year'),
                        moment().subtract(1, 'years').endOf('year')
                    ],
                },
                startDate: moment(),
                endDate: moment(),
                locale: {
                    "customRangeLabel": "{{__('attr.optionalDate')}}",
                    "applyLabel": "{{__('attr.save')}}",
                    "cancelLabel": "{{__('attr.cancel')}}",
                }
            },
            function (start, end) {
                let selected = start.format('YYYY.MM.DD') + ' - ' + end.format('YYYY.MM.DD');
                eval($('#daterange-btn-debts').attr('data-livewire')).set('date', selected);
                $('#daterange-btn-debts span').html(selected);
            }
        );
    </script>
    @endpush
</div>