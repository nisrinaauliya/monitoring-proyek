<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- FAVICON -->
    <link rel="icon" href="{{ asset('img/logo_wag.png') }}" type="image/png">
    <title>@yield('title', 'Sistem Helpdesk')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet">

<body class="bg-light d-flex min-vh-100">

    <!-- SIDEBAR -->
    <aside id="sidebar" class="d-flex flex-column border-end" 
           style="width: 250px; background-color: white; transition: width 0.4s ease;">

        <!-- Logo -->
        <div class="border-bottom d-flex align-items-center justify-content-center" style="height: 65px;">
            <a href="{{ route('dashboard') }}"> 
                <img src="{{ asset('img/logo_wag.png') }}" alt="Logo" style="max-height: 40px;">
            </a>
        </div>

        <!-- Menu -->
        <nav class="flex-grow-1 p-3 overflow-auto">
            <div class="nav flex-column">

                <a href="{{ route('dashboard') }}" 
                    class="nav-link text-dark py-2 rounded d-flex align-items-center sidebar-menu">
                    <i class="bi bi-grid fs-6"></i>
                    <span class="sidebar-text ms-3" style="transition: opacity 0.3s ease, max-width 0.4s ease; opacity: 1; max-width: 200px; overflow: hidden; white-space: nowrap; display: inline-block;">Dashboard</span>
                </a>
  
                <a href="{{ route('ppsmbbyuser') }}" 
                    class="nav-link text-dark py-2 rounded d-flex align-items-center sidebar-menu">
                    <i class="bi bi-file-earmark-arrow-down fs-6"></i>
                    <span class="sidebar-text ms-3" style="transition: opacity 0.3s ease, max-width 0.4s ease; opacity: 1; max-width: 200px; overflow: hidden; white-space: nowrap; display: inline-block;">PPSMB by User</span>
                </a>

               @if(Auth::user()->role === 'admin' || Auth::user()->dept === 'CMD')
                <a href="{{ route('ppsmbbycmd') }}" 
                    class="nav-link text-dark py-2 rounded d-flex align-items-center sidebar-menu">
                    <i class="bi bi-file-earmark-check fs-6"></i>
                    <span class="sidebar-text ms-3" style="transition: opacity 0.3s ease, max-width 0.4s ease; opacity: 1; max-width: 200px; overflow: hidden; white-space: nowrap; display: inline-block;">PPSMB by CMD</span>
                </a>
                @endif

                @if(Auth::user()->role === 'admin' || Auth::user()->dept === 'DINOV')
                <a href="{{ route('ppsmbbydinov') }}" 
                    class="nav-link text-dark py-2 rounded d-flex align-items-center sidebar-menu">
                    <i class="bi bi-file-earmark-check fs-6"></i>
                    <span class="sidebar-text ms-3" style="transition: opacity 0.3s ease, max-width 0.4s ease; opacity: 1; max-width: 200px; overflow: hidden; white-space: nowrap; display: inline-block;">PPSMB by DINOV</span>
                </a>
                @endif

                @if(Auth::user()->role === 'admin' || Auth::user()->dept === 'IT')
                <a href="{{ route('ppsmbbyit') }}" 
                    class="nav-link text-dark py-2 rounded d-flex align-items-center sidebar-menu">
                    <i class="bi bi-file-earmark-check fs-6"></i>
                    <span class="sidebar-text ms-3" style="transition: opacity 0.3s ease, max-width 0.4s ease; opacity: 1; max-width: 200px; overflow: hidden; white-space: nowrap; display: inline-block;">PPSMB by IT</span>
                </a>
                @endif

                @if(Auth::user()->role === 'admin' || in_array(Auth::user()->dept, ['CMD', 'DINOV', 'IT']))
                <a href="{{ route('report') }}" 
                    class="nav-link text-dark py-2 rounded d-flex align-items-center sidebar-menu">
                    <i class="bi bi-card-heading fs-6"></i>
                    <span class="sidebar-text ms-3" style="transition: opacity 0.3s ease, max-width 0.4s ease; opacity: 1; max-width: 200px; overflow: hidden; white-space: nowrap; display: inline-block;">Report</span>
                </a>
                
                <a href="{{ route('result') }}" 
                    class="nav-link text-dark py-2 rounded d-flex align-items-center sidebar-menu">
                    <i class="bi bi-printer fs-6"></i>
                    <span class="sidebar-text ms-3" style="transition: opacity 0.3s ease, max-width 0.4s ease; opacity: 1; max-width: 200px; overflow: hidden; white-space: nowrap; display: inline-block;">Result</span>
                </a>
                @endif
                                   
            </div>
        </nav>

        <!-- Logout -->
        <div class="nav flex-column p-3 border-top mt-auto" style="height: 65px;">
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                class="nav-link py-2 rounded d-flex align-items-center text-danger">
                <i class="bi bi-box-arrow-right fs-6"></i>
                <span class="sidebar-text ms-3" style="transition: opacity 0.3s ease, max-width 0.4s ease; opacity: 1; max-width: 200px; overflow: hidden; white-space: nowrap; display: inline-block;">Logout</span>
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="flex-grow-1 d-flex flex-column">
        <header class="navbar navbar-light bg-white border-bottom shadow-sm sticky-top" style="height: 65px;">
            <div class="container-fluid px-4">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div class="d-flex align-items-center gap-3">
                        <button class="btn btn-link text-dark p-0 fs-3" id="sidebarToggle">
                            <i class="bi bi-list"></i>
                        </button>
                        <h5 class="mb-0 fw-semibold">@yield('page_title')</h5>
                    </div>

                    <!-- USER DROPDOWN -->
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center gap-2 text-dark text-decoration-none" data-bs-toggle="dropdown">
                            <div class="rounded-circle bg-danger d-flex align-items-center justify-content-center text-white" style="width: 35px; height: 35px; font-size: 14px;">
                                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                            </div>
                            <i class="bi bi-chevron-down" style="font-size: 11px;"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end p-2" style="min-width: 200px;">
                            <!-- Info User -->
                            <li class="d-flex align-items-center gap-2 px-2 py-2">
                                <div class="rounded-circle bg-danger d-flex align-items-center justify-content-center text-white flex-shrink-0" style="width: 38px; height: 38px; font-size: 14px;">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold" style="font-size: 14px;">{{ Auth::user()->name }}</div>
                                    <div class="text-muted" style="font-size: 12px;">{{ Auth::user()->dept }}</div>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item rounded" href="#">
                                    <i class="bi bi-key me-2"></i>Change Password
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item rounded text-danger" href="#"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-grow-1 p-4 overflow-auto">
            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

 <script>
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');

    let isExpanded = sessionStorage.getItem('sidebarExpanded') !== 'false';

    function setCollapsed() {
        sidebar.style.width = '80px';
        document.querySelectorAll('.sidebar-text').forEach(el => {
            el.style.opacity = '0';
            el.style.maxWidth = '0';
        });
        document.querySelectorAll('.sidebar-menu').forEach(el => {
            el.style.width = '45px';
            el.style.margin = '0 auto';
        });
    }

    function setExpanded() {
        sidebar.style.width = '250px';
        document.querySelectorAll('.sidebar-text').forEach(el => {
            el.style.opacity = '1';
            el.style.maxWidth = '200px';
        });
        document.querySelectorAll('.sidebar-menu').forEach(el => {
            el.style.width = '';
            el.style.margin = '';
        });
    }

    // apply state saat load
    if (!isExpanded) {
        setCollapsed();
    }

    toggleBtn.addEventListener('click', () => {
        if (isExpanded) {
            setCollapsed();
        } else {
            setExpanded();
        }
        isExpanded = !isExpanded;
        sessionStorage.setItem('sidebarExpanded', isExpanded);
    });

    // ACTIVE MENU HIGHLIGHT
    const menuLinks = document.querySelectorAll('.sidebar-menu');
    const currentPath = window.location.pathname;

    menuLinks.forEach(link => {
        const linkPath = new URL(link.href).pathname;

        if (currentPath === linkPath || currentPath.startsWith(linkPath + '/')) {
            link.style.backgroundColor = '#af2027';
            link.classList.add('text-white');
            link.classList.remove('text-dark');
            link.querySelectorAll('i, span').forEach(el => el.classList.add('text-white'));
        }

        link.addEventListener('click', function () {
            menuLinks.forEach(l => {
                l.style.backgroundColor = '';
                l.classList.remove('text-white');
                l.classList.add('text-dark');
                l.querySelectorAll('i, span').forEach(el => el.classList.remove('text-white'));
            });

            this.style.backgroundColor = '#af2027';
            this.classList.add('text-white');
            this.classList.remove('text-dark');
            this.querySelectorAll('i, span').forEach(el => el.classList.add('text-white'));
        });
    });
</script>
</body>
</html>