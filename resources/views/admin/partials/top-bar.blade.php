<section class="top-bar">
    <div class="top-bar-inner">
        <div id="hamburger">
            <div class="svg">
                <img src="{{ asset('images/hamburger.svg') }}" alt="Hamburger Icon">
            </div>
        </div>

        <div class="pull-right">
            <div class="profile-dropdown">
                <div class="profile-head">
                    <div class="img-box">
                        <img src="{{ asset('images/profile.png') }}" alt="profile" />
                        <span class="dot"></span>
                    </div>
                </div>

                <div class="profile-dropdown-content">
                    <a class="link" href="{{ route('logout') }}"  onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="bi bi-power me-1"></i> Logout</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>