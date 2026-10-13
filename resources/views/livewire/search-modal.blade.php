<div>
    <div wire:ignore.self class="modal fade search-modal" id="searchModal" tabindex="-1"
        aria-labelledby="searchModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="border-0 modal-content">
                <div class="p-3 modal-header bg-soft-info">
                    <div class="gap-2 d-flex align-items-center w-100">
                        <button type="button" class="btn-close d-sm-none me-2" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                        <div class="search-box position-relative flex-grow-1">
                            <input type="text" wire:model.live.debounce.300ms="search"
                                class="border form-control form-control-lg search" id="search"
                                placeholder="{{ __('translation.search_for_students') }}..." autocomplete="off">
                            <i class="ri-search-line search-icon"></i>

                            <div wire:loading class="position-absolute end-0 top-50 translate-middle-y me-3">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-close d-none d-sm-inline-block" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                </div>

                <div class="border-bottom bg-light">
                    <div class="p-2">
                        <div class="row g-2">
                            <div class="col-4">
                                <button type="button" wire:click="setFilter('active')"
                                    class="btn filter-btn w-100 {{ $filter === 'active' ? 'btn-primary' : 'btn-light' }}">
                                    <i class="ri-user-line me-1"></i> {{ __('translation.active_students') }}
                                </button>
                            </div>
                            <div class="col-4">
                                <button type="button" wire:click="setFilter('deleted')"
                                    class="btn filter-btn w-100 {{ $filter === 'deleted' ? 'btn-primary' : 'btn-light' }}">
                                    <i class="ri-user-unfollow-line me-1"></i> {{ __('translation.deleted_students') }}
                                </button>
                            </div>
                            <div class="col-4">
                                <button type="button" wire:click="setFilter('all')"
                                    class="btn filter-btn w-100 {{ $filter === 'all' ? 'btn-primary' : 'btn-light' }}">
                                    <i class="ri-group-line me-1"></i> {{ __('translation.all_students') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-0 modal-body" style="height: 300px; overflow-y: auto;">
                    @if(empty($search))
                    @if(count($recentStudents) > 0)
                    <div class="p-3">
                        <div class="mb-2 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 text-muted">{{ __('translation.recent_students') }}</h6>
                            <button wire:click="clearRecentStudents" class="p-1 btn btn-link btn-sm text-danger">
                                {{ __('translation.clear_all') }}
                            </button>
                        </div>
                        <div class="list-group list-group-flush">
                            @foreach($recentStudents as $student)
                            <div class="px-0 list-group-item">
                                <div class="d-flex align-items-center justify-content-between">
                                    <a href="{{ route('admin.students.show', $student) }}"
                                        class="py-2 text-body text-decoration-none flex-grow-1">
                                        <div class="d-flex align-items-center">
                                            <i class="ri-history-line me-2"></i>
                                            <div>
                                                <div class="fw-medium">{{ $student->name }} {{ $student->surname }}
                                                </div>
                                                <div class="text-muted small">
                                                    {{ $student->phone }}
                                                    @if($student->groups->isNotEmpty())
                                                    • {{ $student->groups->first()->name }}
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    <button wire:click="removeRecentStudent({{ $student->id }})"
                                        class="p-2 btn btn-link text-danger">
                                        <i class="ri-close-line"></i>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div class="p-4 text-center">
                        <div class="mx-auto avatar-md">
                            <div class="avatar-title bg-light text-primary display-6 rounded-circle">
                                <i class="ri-user-search-line"></i>
                            </div>
                        </div>
                        <h5>{{ __('translation.search_students') }}</h5>
                        <p class="mb-0 text-muted">
                            {{ __('translation.search_by_name_surname_or_phone_number') }}
                        </p>
                    </div>
                    @endif
                    @endif

                    @if($showResults && count($students) > 0)
                    <div class="list-group list-group-flush">
                        @foreach($students as $student)
                        <div class="px-0 list-group-item">
                            <button wire:click="redirectToStudent({{ $student->id }})"
                                class="d-block text-body text-decoration-none w-100 btn btn-link text-start">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <div class="fw-medium">{{ $student->name }} {{ $student->surname }}</div>
                                        <div class="text-muted small">
                                            {{ $student->phone }}
                                            @if($student->groups->isNotEmpty())
                                            • {{ $student->groups->first()->name }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </button>
                        </div>
                        @endforeach
                    </div>
                    @elseif($showResults)
                    <div class="p-3 text-center">
                        <div class="mx-auto avatar-lg">
                            <div class="avatar-title bg-light text-primary display-5 rounded-circle">
                                <i class="ri-search-2-line"></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <h4>{{ __('translation.No results found') }}</h4>
                            <p class="mb-0 text-muted">
                                {{ __('translation.We couldn\'t find any student matching') }}
                                <strong>"{{ $search }}"</strong>
                            </p>
                            <p class="text-muted">
                                {{ __('translation.Try adjusting your search to find what you\'re looking for.') }}
                            </p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            const searchModal = document.getElementById('searchModal');
            searchModal.addEventListener('shown.bs.modal', () => {
                const searchInput = document.getElementById('search');
                searchInput.focus();
                searchInput.select(); // This will select all text

                // For mobile devices, this ensures the text is selected
                searchInput.setSelectionRange(0, searchInput.value.length);
            });
        });
    </script>
    @endpush

    <style>
        @media (max-width: 576px) {
            .search-modal .modal-dialog {
                margin: 0;
            }

            .search-modal .modal-content {
                min-height: 100vh;
                border-radius: 0;
            }

            .search-modal .modal-body {
                height: calc(100vh - 130px) !important;
            }

            .search-modal .form-control {
                font-size: 16px;
            }

            .search-modal .list-group-item {
                padding-top: 12px;
                padding-bottom: 12px;
            }

            .search-modal .filter-btn {
                font-size: 11px;
                padding: 6px 4px;
            }

            .search-modal .filter-btn i {
                font-size: 14px;
            }
        }

        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .filter-btn {
            padding: 8px 16px;
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
        }

        .btn-link.text-danger {
            transition: background-color 0.2s ease;
            padding: 8px;
            border-radius: 6px;
        }

        .btn-link.text-danger:hover {
            background-color: var(--vz-danger-bg-subtle);
        }

        .btn-link.text-danger:focus {
            box-shadow: none;
        }

        .search-modal .search-icon {
            top: 50%;
            transform: translateY(-50%);
        }
    </style>
</div>
