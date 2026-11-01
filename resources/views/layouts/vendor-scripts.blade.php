{{-- jQuery before Livewire so stacks and third-party scripts always have $; avoid duplicate LW assets via config inject_assets=false --}}
<script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
@livewireScripts
@yield('script-top')
<script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
<script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
<script src="{{ asset('assets/libs/feather-icons/feather.min.js') }}"></script>
<script src="{{ asset('assets/js/lord-icon-2.1.0.js') }}"></script>
<script src="{{ asset('assets/js/toastify.min.js') }}"></script>
<script src="{{ asset('assets/libs/choices.js/choices.js.min.js') }}"></script>
<script src="{{ asset('assets/js/apex/apex.min.js') }}"></script>
<script src="{{ asset('assets/js/poppy.min.js') }}"></script>
<script src="{{ asset('assets/js/tippy.min.js') }}"></script>

{{-- Paid theme file, not in the repository (see README). --}}
@if (file_exists(public_path('assets/js/theme.js')))
    <script src="{{ asset('assets/js/theme.js') }}"></script>
@endif
<script>
    // Add an event listener for when the modal is shown
    document.getElementById('searchModal').addEventListener('shown.bs.modal', function () {
        // Focus the input field with id 'search' when the modal is shown
        document.getElementById('search').focus();
    });
    document.addEventListener('keydown', function (event) {
        // Check if the event target is an input field or textarea
        if (!(event.target.tagName.toLowerCase() === 'input' || event.target.tagName.toLowerCase() === 'textarea') && event.key === "/") {
            var modal = document.getElementById('searchModal');
            var modalToggle = new bootstrap.Modal(modal);
            modalToggle.show();
        }
    });
    window.notifyMessage = (message, classname = 'success') => {
        let notificationsConfigs = {
            gravity: 'top',
            position: 'center',
            duration: 10000,
            close: "close",
        }
        Toastify({
            newWindow: true,
            text: message,
            gravity: notificationsConfigs.gravity,
            position: notificationsConfigs.position,
            className: "bg-" + classname,
            stopOnFocus: true,
            offset: {
                x: notificationsConfigs.offset ? 50 : 0, // horizontal axis - can be a number or a string indicating unity. eg: '2em'
                y: notificationsConfigs.offset ? 10 : 0, // vertical axis - can be a number or a string indicating unity. eg: '2em'
            },
            duration: notificationsConfigs.duration,
            close: notificationsConfigs.close === "close",
            style: notificationsConfigs.style === "style" ? {
                background: "linear-gradient(to right, #0AB39C, #405189)"
            } : "",
        }).showToast();
    }

    document.addEventListener('DOMContentLoaded', function () {
        const inputFields = document.querySelectorAll('.firstCapitalInput');

        inputFields.forEach(inputField => {
            inputField.addEventListener('input', function () {
                let inputValue = inputField.value;

                if (inputValue.length > 0) {
                    inputField.value = inputValue.charAt(0).toUpperCase() + inputValue.slice(1);
                }
            });
        });

        tippy('.t-create-btn', {
            content: "{!!__('translation.btn.create')!!}"
        });
        tippy('.t-show-btn', {
            content: "{!!__('translation.btn.show')!!}"
        });
        tippy('.t-edit-btn', {
            content: "{!!__('attr.edit')!!}",
        });
        tippy('.t-delete-btn', {
            content: "{!!__('translation.btn.delete')!!}",
        });
        tippy('.quick-actions-dropdown', {
            content: "{!!__('translation.quick_actions')!!}",
        });
        tippy('.theme-btn', {
            content: "{!!__('translation.theme')!!}",
        });
        tippy('.search-btn', {
            content: "{!!__('translation.search')!!}",
        });
    });

    //get all collapse menu-dropdown classes and add show class if user with phone screen
    const menuDropdowns = document.querySelectorAll('.menu-dropdown');
    if (window.innerWidth < 768) {
        menuDropdowns.forEach(menuDropdown => {
            menuDropdown.classList.add('show');
        });
    }

</script>

@yield('script')

@stack("custom-scripts")
@stack("script-bottom")

@yield('script-bottom')
