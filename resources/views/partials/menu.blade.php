<aside class="left-sidebar">
    <!-- Sidebar scroll-->
    <div class="scroll-sidebar">
        <!-- Sidebar navigation-->
        <nav class="sidebar-nav">
            <ul id="sidebarnav">
                <!-- User Profile-->
                <li>
                    <!-- User profile -->
                    <div class="user-profile text-center position-relative pt-4 mt-1">
                        <!-- User profile image -->
                        <div class="profile-img m-auto">
                            @if(auth()->user()->foto)
                                <img src="{{ asset('assets/images/users/'.auth()->user()->foto) }}" alt="user" class="w-100 rounded-circle">
                            @else
                                <img src="{{ asset('assets/images/users/usuario.png') }}" alt="user" class="w-100 rounded-circle">
                            @endif
                        </div>
                        <!-- User profile text-->
                        <div class="profile-text py-1 text-white">
                            {{ auth()->user()->name }}
                        </div>
                    </div>
                    <!-- End User profile text-->
                </li>
                <!-- User Profile-->
                <li class="nav-small-cap"><i class="mdi mdi-dots-horizontal"></i> <span class="hide-menu">ADMINISTRACION</span></li>
                <li class="sidebar-item">
                    <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false">
                        <i data-feather="home" class="feather-icon"></i><span class="hide-menu"> Administracion</span>
                    </a>
                    <ul aria-expanded="false" class="collapse  first-level">
                        <li class="sidebar-item">
                            <a href='{{ route('rol.listado') }}' class="sidebar-link">
                                <i data-feather="home" class="feather-icon"></i><span class="hide-menu"> Rol </span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-devider"></li>
            </ul>
        </nav>
        <!-- End Sidebar navigation -->
    </div>
    <!-- End Sidebar scroll-->
</aside>
