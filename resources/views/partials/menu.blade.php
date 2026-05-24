<aside class="left-sidebar">
    <!-- Sidebar scroll-->
    <div class="scroll-sidebar">
        <!-- Sidebar navigation-->
        <nav class="sidebar-nav">
            @php
                $user = Auth::user();
                $gruposTotales = \App\Models\Grupo::all();
                if($user->rol_id == 1){
                    $misSucursales = \App\Models\Sucursal::all();
                }else{
                    $misSucursales = \App\Models\Sucursal::where('id', $user->puntoVenta->sucursal_id)->limit(1)->get();
                }
            @endphp
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
                    <a class="sidebar-link has-arrow waves-effect waves-dark" aria-expanded="false">
                        <i data-feather="home" class="feather-icon"></i><span class="hide-menu"> Administracion</span>
                    </a>
                    <ul aria-expanded="false" class="collapse  first-level">
                        <li class="sidebar-item">
                            <a href='{{ route('rol.listado') }}' class="sidebar-link">
                                <i data-feather="home" class="feather-icon"></i><span class="hide-menu"> Rol </span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href='{{ route('sucursal.listado') }}' class="sidebar-link">
                                <i data-feather="home" class="feather-icon"></i><span class="hide-menu"> Sucursal </span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href='{{ route('usuario.listado') }}' class="sidebar-link">
                                <i data-feather="home" class="feather-icon"></i><span class="hide-menu"> Usuario </span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href='{{ route('marca.listado') }}' class="sidebar-link">
                                <i data-feather="home" class="feather-icon"></i><span class="hide-menu"> Marca </span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href='{{ route('cliente.listado') }}' class="sidebar-link">
                                <i data-feather="home" class="feather-icon"></i><span class="hide-menu"> Cliente </span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href='{{ route('auto.listado') }}' class="sidebar-link">
                                <i data-feather="home" class="feather-icon"></i><span class="hide-menu"> Automovil </span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href='{{ route('grupo.listado') }}' class="sidebar-link">
                                <i data-feather="home" class="feather-icon"></i><span class="hide-menu"> Grupos </span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href='{{ route('consulta.listado') }}' class="sidebar-link">
                                <i data-feather="home" class="feather-icon"></i><span class="hide-menu"> Consultas </span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href='{{ route('producto.listado') }}' class="sidebar-link">
                                <i data-feather="home" class="feather-icon"></i><span class="hide-menu"> Productos </span>
                            </a>
                        </li>
                    </ul>
                </li>
                <!-- Pagos-->
                <li class="nav-small-cap"><i class="mdi mdi-dots-horizontal"></i> <span class="hide-menu">PAGOS</span></li>
                <li class="sidebar-item">
                    <a class="sidebar-link has-arrow waves-effect waves-dark" aria-expanded="false">
                        <i data-feather="home" class="feather-icon"></i><span class="hide-menu"> Pagos</span>
                    </a>
                    <ul aria-expanded="false" class="collapse  first-level">
                        <li class="sidebar-item">
                            <a href='{{ route('caja.listado') }}' class="sidebar-link">
                                <i data-feather="home" class="feather-icon"></i><span class="hide-menu"> Cajas </span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href='{{ route('pago.listado') }}' class="sidebar-link">
                                <i data-feather="home" class="feather-icon"></i><span class="hide-menu"> Listado Pagos </span>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- TALLERES --}}
                <li class="nav-small-cap"><i class="mdi mdi-dots-horizontal"></i> <span class="hide-menu">TALLERES</span></li>
                @foreach ($misSucursales as $ms)
                    <li class="sidebar-item">
                        <a class="sidebar-link has-arrow waves-effect waves-dark" aria-expanded="false">
                            <i data-feather="home" class="feather-icon"></i><span class="hide-menu"> {{ $ms->nombre }}</span>
                        </a>
                        <ul aria-expanded="false" class="collapse  first-level">
                            <li class="sidebar-item">
                                <a href="{{ route('clienteSucursal.listado', ['sucursal_id' => $ms->id]) }}" class="sidebar-link">
                                    <i data-feather="home" class="feather-icon"></i><span class="hide-menu"> Clientes </span>
                                </a>
                            </li>
                            <li class="sidebar-item">
                                <a href="{{ route('autoSucursal.listado', ['sucursal_id' => $ms->id]) }}" class="sidebar-link">
                                    <i data-feather="home" class="feather-icon"></i><span class="hide-menu"> Automoviles </span>
                                </a>
                            </li>
                            @foreach ( $gruposTotales as $grupo)
                                <li class="sidebar-item">
                                    <a class="sidebar-link has-arrow waves-effect waves-dark" aria-expanded="false">
                                        <i data-feather="home" class="feather-icon"></i><span class="hide-menu"> {{ $grupo->nombre }}</span>
                                    </a>
                                    <ul aria-expanded="false" class="collapse  first-level">
                                        <li class="sidebar-item">
                                            <a href="{{ route('grupoCliente.listado', ['sucursal_id' => $ms->id, 'grupo_id' => $grupo->id]) }}" class="sidebar-link">
                                                <i data-feather="home" class="feather-icon"></i><span class="hide-menu"> Clientes </span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach
                <li class="nav-devider"></li>
            </ul>
        </nav>
        <!-- End Sidebar navigation -->
    </div>
    <!-- End Sidebar scroll-->
</aside>
