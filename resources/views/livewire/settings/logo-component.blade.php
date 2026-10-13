<div class="navbar-brand-box horizontal-logo">
    <a href="{{ route('admin.dashboard') }}" class="logo logo-dark">
        <span class="logo-sm">
            <img src="{{ ($logo != '')? $logo : asset('images/markaz-lms.svg') }}" alt="" height="30">
        </span>
        <span class="logo-lg">
            <img src="{{ ($logo != '')? $logo : asset('images/markaz-lms.svg') }}" alt="" style="height: 30px">
        </span>
    </a>

    <a href="{{ route('admin.dashboard') }}" class="logo logo-light">
        <span class="logo-sm">
            <img src="{{ ($logo != '')? $logo : asset('images/markaz-lms.svg') }}" alt="" height="30">
        </span>
        <span class="logo-lg">
            <img src="{{ ($logo != '')? $logo : asset('images/markaz-lms.svg') }}" alt="" style="height: 30px">
        </span>
    </a>
</div>