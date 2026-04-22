<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/favicon.png') }}">
    @yield('metadatos')
    <title>@yield('title')</title>
    <link rel="canonical" href="https://www.wrappixel.com/templates/monsteradmin/" />
    <!-- Custom CSS -->
    @section('css')
    @show
    <link href="{{ asset('assets/libs/sweetalert2/dist/sweetalert2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/libs/select2/dist/css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('dist/css/style.min.css') }}" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body class="fix-header card-no-border fix-sidebar">
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div>
    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div id="main-wrapper">
        <!-- ============================================================== -->
        <!-- Topbar header - style you can find in pages.scss -->
        <!-- ============================================================== -->
        <header class="topbar">
                    <nav class="navbar top-navbar navbar-expand-md navbar-dark">
                        <div class="navbar-header">
                            <!-- This is for the sidebar toggle which is visible on mobile only -->
                            <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)"><i
                                    class="ti-menu ti-close"></i></a>
                            <!-- ============================================================== -->
                            <!-- Logo -->
                            <!-- ============================================================== -->
                            <a class="navbar-brand" href="{{ url('home') }}" style="background-color: #fff;">
                                <!-- Logo icon -->
                                <b class="logo-icon">
                                    <!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->
                                    <!-- Dark Logo icon -->
                                    {{--  <img src="{{ asset('assets/images/background/portal_dos.png') }}" alt="homepage" class="dark-logo" />  --}}
                                    <!-- Light Logo icon -->
                                    <img src="https://tse1.mm.bing.net/th/id/OIP.YBpXSFpgZNTEKo-yQ8_DNQHaEK?rs=1&pid=ImgDetMain&o=7&rm=3" alt="homepage" class="light-logo" style="max-width:50px; margin-top: -7px;"/>
                                </b>
                                <!--End Logo icon -->
                                <!-- Logo text -->
                                <span class="logo-text">
                                    <!-- dark Logo text -->
                                    <img src="https://tse1.mm.bing.net/th/id/OIP.YBpXSFpgZNTEKo-yQ8_DNQHaEK?rs=1&pid=ImgDetMain&o=7&rm=3" alt="homepage" class="dark-logo" />
                                    <!-- Light Logo text -->
                                    <img src="https://tse1.mm.bing.net/th/id/OIP.YBpXSFpgZNTEKo-yQ8_DNQHaEK?rs=1&pid=ImgDetMain&o=7&rm=3" class="light-logo" alt="homepage" style="max-width:150px; margin-top: -7px;"/>
                                </span>
                            </a>
                            <!-- ============================================================== -->
                            <!-- End Logo -->
                            <!-- ============================================================== -->
                            <!-- ============================================================== -->
                            <!-- Toggle which is visible on mobile only -->
                            <!-- ============================================================== -->
                            <a class="topbartoggler d-block d-md-none waves-effect waves-light" href="javascript:void(0)"
                                data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                                aria-expanded="false" aria-label="Toggle navigation"><i class="ti-more"></i></a>
                        </div>
                        <!-- ============================================================== -->
                        <!-- End Logo -->
                        <!-- ============================================================== -->
                        <div class="navbar-collapse collapse" id="navbarSupportedContent">
                            <!-- ============================================================== -->
                            <!-- toggle and nav items -->
                            <!-- ============================================================== -->
                            <ul class="navbar-nav float-left mr-auto">
                                <li class="nav-item d-none d-md-block">
                                    <a class="nav-link sidebartoggler waves-effect waves-light" href="javascript:void(0)" data-sidebartype="mini-sidebar">
                                        <i class="icon-arrow-left-circle"></i>
                                    </a>
                                </li>

                                <!-- <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle waves-effect waves-dark" href="" data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false"> <i class="mdi mdi-message"></i>
                                        <div class="notify"> <span class="heartbit"></span> <span class="point"></span> </div>
                                    </a>
                                    <div class="dropdown-menu mailbox animated bounceInDown">
                                        <ul class="list-style-none">
                                            <li>
                                                <div class="font-weight-medium border-bottom rounded-top py-3 px-4">
                                                    Notifications
                                                </div>
                                            </li>
                                            <li>
                                                <div class="message-center notifications position-relative" style="height:250px;">
                                                    <a href="javascript:void(0)"
                                                        class="message-item d-flex align-items-center border-bottom px-3 py-2">
                                                        <span class="btn btn-danger rounded-circle btn-circle"><i
                                                                class="fa fa-link"></i></span>
                                                        <div class="w-75 d-inline-block v-middle pl-2">
                                                            <h5 class="message-title mb-0 mt-1">Luanch Admin</h5> <span
                                                                class="font-12 text-nowrap d-block text-muted text-truncate">Just see
                                                                the my new admin!</span> <span
                                                                class="font-12 text-nowrap d-block text-muted">9:30 AM</span>
                                                        </div>
                                                    </a>
                                                    <a href="javascript:void(0)"
                                                        class="message-item d-flex align-items-center border-bottom px-3 py-2">
                                                        <span class="btn btn-success rounded-circle btn-circle"><i
                                                                class="ti-calendar"></i></span>
                                                        <div class="w-75 d-inline-block v-middle pl-2">
                                                            <h5 class="message-title mb-0 mt-1">Event today</h5> <span
                                                                class="font-12 text-nowrap d-block text-muted text-truncate">Just a
                                                                reminder that you have event</span> <span
                                                                class="font-12 text-nowrap d-block text-muted">9:10 AM</span>
                                                        </div>
                                                    </a>
                                                    <a href="javascript:void(0)"
                                                        class="message-item d-flex align-items-center border-bottom px-3 py-2">
                                                        <span class="btn btn-info rounded-circle btn-circle"><i
                                                                class="ti-settings"></i></span>
                                                        <div class="w-75 d-inline-block v-middle pl-2">
                                                            <h5 class="message-title mb-0 mt-1">Settings</h5> <span
                                                                class="font-12 text-nowrap d-block text-muted text-truncate">You can
                                                                customize this template as you want</span> <span
                                                                class="font-12 text-nowrap d-block text-muted">9:08 AM</span>
                                                        </div>
                                                    </a>
                                                    <a href="javascript:void(0)"
                                                        class="message-item d-flex align-items-center border-bottom px-3 py-2">
                                                        <span class="btn btn-primary rounded-circle btn-circle"><i
                                                                class="ti-user"></i></span>
                                                        <div class="w-75 d-inline-block v-middle pl-2">
                                                            <h5 class="message-title mb-0 mt-1">Pavan kumar</h5> <span
                                                                class="font-12 text-nowrap d-block text-muted text-truncate">Just see
                                                                the my admin!</span> <span
                                                                class="font-12 text-nowrap d-block text-muted">9:02 AM</span>
                                                        </div>
                                                    </a>
                                                </div>
                                            </li>
                                            <li>
                                                <a class="nav-link border-top text-center text-dark pt-3" href="javascript:void(0);">
                                                    <strong>Check all notifications</strong> <i class="fa fa-angle-right"></i> </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>

                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle waves-effect waves-dark" href="" id="2" data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false"> <i class="mdi mdi-email"></i>
                                        <div class="notify"> <span class="heartbit"></span> <span class="point"></span> </div>
                                    </a>
                                    <div class="dropdown-menu mailbox animated bounceInDown" aria-labelledby="2">
                                        <ul class="list-style-none">
                                            <li>
                                                <div class="font-weight-medium border-bottom rounded-top py-3 px-4">
                                                    Messages
                                                </div>
                                            </li>
                                            <li>
                                                <div class="message-center message-body position-relative" style="height:250px;">
                                                    <a href="javascript:void(0)"
                                                        class="message-item d-flex align-items-center border-bottom px-3 py-2">
                                                        <span class="user-img position-relative d-inline-block"> <img
                                                                src="{{ asset('assets/images/users/1.jpg') }}" alt="user"
                                                                class="rounded-circle w-100"> <span
                                                                class="profile-status rounded-circle online"></span> </span>
                                                        <div class="w-75 d-inline-block v-middle pl-2">
                                                            <h5 class="message-title mb-0 mt-1">Pavan kumar</h5> <span
                                                                class="font-12 text-nowrap d-block text-muted text-truncate">Just see
                                                                the my admin!</span> <span
                                                                class="font-12 text-nowrap d-block text-muted">9:30 AM</span>
                                                        </div>
                                                    </a>
                                                    <a href="javascript:void(0)"
                                                        class="message-item d-flex align-items-center border-bottom px-3 py-2">
                                                        <span class="user-img position-relative d-inline-block"> <img
                                                                src="{{ asset('assets/images/users/2.jpg') }}" alt="user"
                                                                class="rounded-circle w-100"> <span
                                                                class="profile-status rounded-circle busy"></span> </span>
                                                        <div class="w-75 d-inline-block v-middle pl-2">
                                                            <h5 class="message-title mb-0 mt-1">Sonu Nigam</h5> <span
                                                                class="font-12 text-nowrap d-block text-muted text-truncate">I've sung a
                                                                song! See you at</span> <span
                                                                class="font-12 text-nowrap d-block text-muted">9:10 AM</span>
                                                        </div>
                                                    </a>
                                                    <a href="javascript:void(0)"
                                                        class="message-item d-flex align-items-center border-bottom px-3 py-2">
                                                        <span class="user-img position-relative d-inline-block"> <img
                                                                src="{{ asset('assets/images/users/3.jpg') }}" alt="user"
                                                                class="rounded-circle w-100"> <span
                                                                class="profile-status rounded-circle away"></span> </span>
                                                        <div class="w-75 d-inline-block v-middle pl-2">
                                                            <h5 class="message-title mb-0 mt-1">Arijit Sinh</h5> <span
                                                                class="font-12 text-nowrap d-block text-muted text-truncate">I am a
                                                                singer!</span> <span class="font-12 text-nowrap d-block text-muted">9:08
                                                                AM</span>
                                                        </div>
                                                    </a>
                                                    <a href="javascript:void(0)"
                                                        class="message-item d-flex align-items-center border-bottom px-3 py-2">
                                                        <span class="user-img position-relative d-inline-block"> <img
                                                                src="{{ asset('assets/images/users/4.jpg') }}" alt="user"
                                                                class="rounded-circle w-100"> <span
                                                                class="profile-status rounded-circle offline"></span> </span>
                                                        <div class="w-75 d-inline-block v-middle pl-2">
                                                            <h5 class="message-title mb-0 mt-1">Pavan kumar</h5> <span
                                                                class="font-12 text-nowrap d-block text-muted text-truncate">Just see
                                                                the my admin!</span> <span
                                                                class="font-12 text-nowrap d-block text-muted">9:02 AM</span>
                                                        </div>
                                                    </a>
                                                </div>
                                            </li>
                                            <li>
                                                <a class="nav-link border-top text-center text-dark pt-3" href="javascript:void(0);">
                                                    <b>See all e-Mails</b> <i class="fa fa-angle-right"></i> </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>

                                <li class="nav-item dropdown mega-dropdown"><a class="nav-link dropdown-toggle waves-effect waves-dark"
                                        href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <span class=""><i class="mdi mdi-view-grid"></i></span>
                                    </a>
                                    <div class="dropdown-menu animated bounceInDown">
                                        <div class="mega-dropdown-menu row">
                                            <div class="col-lg-3 col-xlg-2 mb-4">
                                                <h4 class="mb-3">CAROUSEL</h4>
                                                <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
                                                    <div class="carousel-inner" role="listbox">
                                                        <div class="carousel-item active">
                                                            <div class="container p-0"> <img class="d-block img-fluid"
                                                                    src="{{ asset('assets/images/big/img1.jpg') }}" alt="First slide"></div>
                                                        </div>
                                                        <div class="carousel-item">
                                                            <div class="container p-0"><img class="d-block img-fluid"
                                                                    src="{{ asset('assets/images/big/img2.jpg') }}" alt="Second slide"></div>
                                                        </div>
                                                        <div class="carousel-item">
                                                            <div class="container p-0"><img class="d-block img-fluid"
                                                                    src="{{ asset('assets/images/big/img3.jpg') }}" alt="Third slide"></div>
                                                        </div>
                                                    </div>
                                                    <a class="carousel-control-prev" href="#carouselExampleControls" role="button"
                                                        data-slide="prev"> <span class="carousel-control-prev-icon"
                                                            aria-hidden="true"></span> <span class="sr-only">Previous</span> </a>
                                                    <a class="carousel-control-next" href="#carouselExampleControls" role="button"
                                                        data-slide="next"> <span class="carousel-control-next-icon"
                                                            aria-hidden="true"></span> <span class="sr-only">Next</span> </a>
                                                </div>
                                            </div>
                                            <div class="col-lg-3 mb-4">
                                                <h4 class="mb-3">ACCORDION</h4>
                                                <div id="accordion">
                                                    <div class="card mb-1">
                                                        <div class="card-header" id="headingOne">
                                                            <h5 class="mb-0">
                                                                <button class="btn btn-link" data-toggle="collapse"
                                                                    data-target="#collapseOne" aria-expanded="true"
                                                                    aria-controls="collapseOne">
                                                                    Collapsible Group Item #1
                                                                </button>
                                                            </h5>
                                                        </div>
                                                        <div id="collapseOne" class="collapse show" aria-labelledby="headingOne"
                                                            data-parent="#accordion">
                                                            <div class="card-body">
                                                                Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus
                                                                terry.
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card mb-1">
                                                        <div class="card-header" id="headingTwo">
                                                            <h5 class="mb-0">
                                                                <button class="btn btn-link collapsed" data-toggle="collapse"
                                                                    data-target="#collapseTwo" aria-expanded="false"
                                                                    aria-controls="collapseTwo">
                                                                    Collapsible Group Item #2
                                                                </button>
                                                            </h5>
                                                        </div>
                                                        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo"
                                                            data-parent="#accordion">
                                                            <div class="card-body">
                                                                Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus
                                                                terry.
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card mb-1">
                                                        <div class="card-header" id="headingThree">
                                                            <h5 class="mb-0">
                                                                <button class="btn btn-link collapsed" data-toggle="collapse"
                                                                    data-target="#collapseThree" aria-expanded="false"
                                                                    aria-controls="collapseThree">
                                                                    Collapsible Group Item #3
                                                                </button>
                                                            </h5>
                                                        </div>
                                                        <div id="collapseThree" class="collapse" aria-labelledby="headingThree"
                                                            data-parent="#accordion">
                                                            <div class="card-body">
                                                                Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus
                                                                terry.
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-3  mb-4">
                                                <h4 class="mb-3">CONTACT US</h4>
                                                <form>
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" id="exampleInputname1"
                                                            placeholder="Enter Name"> </div>
                                                    <div class="form-group">
                                                        <input type="email" class="form-control" placeholder="Enter email"> </div>
                                                    <div class="form-group">
                                                        <textarea class="form-control" id="exampleTextarea" rows="3"
                                                            placeholder="Message"></textarea>
                                                    </div>
                                                    <button type="submit" class="btn btn-info">Submit</button>
                                                </form>
                                            </div>
                                            <div class="col-lg-3 col-xlg-4 mb-4">
                                                <h4 class="mb-3">List style</h4>
                                                <ul class="list-style-none">
                                                    <li><a href="javascript:void(0)"><i class="fa fa-check text-success"></i> You can
                                                            give link</a></li>
                                                    <li><a href="javascript:void(0)"><i class="fa fa-check text-success"></i> Give
                                                            link</a></li>
                                                    <li><a href="javascript:void(0)"><i class="fa fa-check text-success"></i> Another
                                                            Give link</a></li>
                                                    <li><a href="javascript:void(0)"><i class="fa fa-check text-success"></i> Forth
                                                            link</a></li>
                                                    <li><a href="javascript:void(0)"><i class="fa fa-check text-success"></i> Another
                                                            fifth link</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </li> -->
                            </ul>
                            <!-- ============================================================== -->
                            <!-- Right side toggle and nav items -->
                            <!-- ============================================================== -->
                            <ul class="navbar-nav float-right">
                                <!-- ============================================================== -->
                                <!-- Search -->
                                <!-- ============================================================== -->
                                <!-- <li class="nav-item search-box d-none d-md-block">
                                    <form class="app-search mt-3 mr-2">
                                        <input type="text" class="form-control rounded-pill border-0" placeholder="Search for...">
                                        <a class="srh-btn"><i class="ti-search"></i></a>
                                    </form>
                                </li> -->
                                <!-- ============================================================== -->
                                <!-- User profile and search -->
                                <!-- ============================================================== -->
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark pro-pic" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        @if(auth()->user()->foto)
                                            <img src="{{ asset('assets/images/users/'.auth()->user()->foto) }}" class="rounded-circle" width="31">
                                        @else
                                            <img src="{{ asset('assets/images/users/usuario.png') }}" alt="user" class="rounded-circle" width="31"/>
                                        @endif
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right user-dd animated flipInY">
                                        <div class="d-flex no-block align-items-center p-3 mb-2 border-bottom">
                                            <div class="">
                                                @if(auth()->user()->foto)
                                                    <img src="{{ asset('assets/images/users/'.auth()->user()->foto) }}" alt="user" class="rounded" width="80">
                                                @else
                                                    <img src="{{ asset('assets/images/users/usuario.png') }}" alt="user" class="rounded" width="80">
                                                @endif
                                            </div>
                                            <div class="ml-2">
                                                <h4 class="mb-0">{{ auth()->user()->name }}</h4>
                                                <p class=" mb-0">{{ auth()->user()->email }}</p>
                                                <a href="{{ url('User/perfil') }}" class="btn btn-rounded btn-danger btn-sm">Ver Perfil</a>
                                            </div>
                                        </div>
                                        <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                            <i class="fa fa-power-off mr-1 ml-1"></i>
                                            {{ __('CERRAR SESION') }}
                                        </a>
                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                            @csrf
                                        </form>
                                    </div>
                                </li>
                                <!-- ============================================================== -->
                                <!-- User profile and search -->
                                <!-- ============================================================== -->
                                <!-- ============================================================== -->
                                <!-- create new -->
                                <!-- ============================================================== -->
                                <!-- <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown2" role="button"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="flag-icon flag-icon-us"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right  animated bounceInDown"
                                        aria-labelledby="navbarDropdown2">
                                        <a class="dropdown-item" href="#"><i class="flag-icon flag-icon-us"></i> English</a>
                                        <a class="dropdown-item" href="#"><i class="flag-icon flag-icon-fr"></i> French</a>
                                        <a class="dropdown-item" href="#"><i class="flag-icon flag-icon-es"></i> Spanish</a>
                                        <a class="dropdown-item" href="#"><i class="flag-icon flag-icon-de"></i> German</a>
                                    </div>
                                </li> -->
                            </ul>
                        </div>
                    </nav>
                </header>
        <!-- ============================================================== -->
        <!-- End Topbar header -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        @include('partials.menu')
        <!-- ============================================================== -->
        <!-- End Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- ============================================================== -->
        <div class="page-wrapper">
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid">
                <!-- ============================================================== -->
                <!-- Start Page Content -->
                <!-- ============================================================== -->
                @yield('content')
                <!-- ============================================================== -->
                <!-- End PAge Content -->
                <!-- ============================================================== -->
                <!-- ============================================================== -->
            </div>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- footer -->
            <!-- ============================================================== -->
            <footer class="footer">
                © {{ date('Y') }} gipet.net
            </footer>
            <!-- ============================================================== -->
            <!-- End footer -->
            <!-- ============================================================== -->
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->

    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
    <script src="{{ asset('assets/libs/jquery/dist/jquery.min.js') }}"></script>
    <!-- Bootstrap tether Core JavaScript -->
    <script src="{{ asset('assets/libs/popper.js/dist/umd/popper.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <!-- apps -->
    <script src="{{ asset('dist/js/app.min.js') }}"></script>
    <script src="{{ asset('dist/js/app.init.js') }}"></script>
    <script src="{{ asset('dist/js/app-style-switcher.js') }}"></script>
    <!-- slimscrollbar scrollbar JavaScript -->
    <script src="{{ asset('assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/jquery-sparkline/jquery.sparkline.min.js') }}"></script>
    <!--Wave Effects -->
    <script src="{{ asset('dist/js/waves.js') }}"></script>
    <!--Menu sidebar -->
    <script src="{{ asset('dist/js/sidebarmenu.js') }}"></script>
    <!--Custom JavaScript -->
    <script src="{{ asset('dist/js/feather.min.js') }}"></script>
    <script src="{{ asset('dist/js/custom.min.js') }}"></script>

    {{-- sweet alert --}}
    <script src="{{ asset('assets/libs/sweetalert2/dist/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('assets/extra-libs/sweetalert2/sweet-alert.init.js') }}"></script>

    {{-- select2 --}}
    <script src="{{ asset('assets/libs/select2/dist/js/select2.min.js') }}"></script>

    <script>
        // funcion para la validacion del formulario
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                // Fetch all the forms we want to apply custom Bootstrap validation styles to
                var forms = document.getElementsByClassName('needs-validation');
                // Loop over them and prevent submission
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
        // fin funcion para la validacion del formulario

         // script para que todos los formularios pasen con ENTER en vez de TAB
        jQuery(document).ready(function() {
            $('body').on('keydown', 'input, select', function(e) {
            if (e.key === "Enter") {
                var self = $(this), form = self.parents('form:eq(0)'), focusable, next;
                focusable = form.find('input,a,select,button,textarea').filter(':visible');
                next = focusable.eq(focusable.index(this)+1);
                if (next.length) {
                    next.focus();
                } else {
                    form.submit();
                }
                return false;
            }
            });
        });
    </script>

    @section('js')

    @show
</body>

</html>
