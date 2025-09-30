    <!-- Sidenav -->
    <!-- Sidenav (toolbar) -->
    <aside class="aside aside-sm sidenav-toolbar d-none d-xl-flex">
        <nav class="navbar navbar-expand-xl navbar-vertical">
            <div class="container-fluid">
                <!-- Nav -->
                <nav class="navbar-nav nav-pills h-100">
                    <?php if (admin_has_permission()): ?>
                    <div class="nav-item" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Make new withdrawal">
                        <a class="nav-link" href="#withdrawalModal" type="button" data-bs-toggle="modal" data-bs-target="#withdrawalModal">
                            <span class="material-symbols-outlined">payment_arrow_down</span>
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="nav-item" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Add new deposit">
                        <a class="nav-link" href="#transactionModal" type="button" data-bs-toggle="modal" data-bs-target="#transactionModal">
                            <span class="material-symbols-outlined">send_money</span>
                        </a>
                    </div>
                    <?php endif; ?>
                    <div class="nav-item" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Contact us">
                        <a class="nav-link" href="mailto:yevgenysim+simpleqode@gmail.com">
                            <span class="material-symbols-outlined">support</span>
                        </a>
                    </div>
                    <div class="nav-item dropend mt-auto">
                        <a href="#" role="button" data-bs-toggle="dropdown" data-bs-settings-switcher aria-expanded="false">
                            <div class="nav-link">
                                <span class="material-symbols-outlined">settings</span>
                            </div>
                        </a>
                        <div class="dropdown-menu top-auto bottom-0 ms-xl-3">
                            <!-- Color mode -->
                            <h6 class="dropdown-header">Color mode</h6>
                            <a class="dropdown-item d-flex" data-bs-theme-value="light" href="#" role="button"> <span class="material-symbols-outlined me-2">light_mode</span> Light </a>
                            <a class="dropdown-item d-flex" data-bs-theme-value="dark" href="#" role="button"> <span class="material-symbols-outlined me-2">dark_mode</span> Dark </a>
                            <a class="dropdown-item d-flex" data-bs-theme-value="auto" href="#" role="button"> <span class="material-symbols-outlined me-2">contrast</span> Auto </a>
                    
                            <!-- Navigation position -->
                            <hr class="dropdown-divider" />
                            <h6 class="dropdown-header">Navigation position</h6>
                            <a class="dropdown-item d-flex" data-bs-navigation-position-value="sidenav" href="#" role="button">
                                <span class="material-symbols-outlined me-2">keyboard_tab_rtl</span> Sidenav
                            </a>
                            <a class="dropdown-item d-flex" data-bs-navigation-position-value="topnav" href="#" role="button">
                                <span class="material-symbols-outlined me-2">vertical_align_top</span> Topnav
                            </a>
                        
                            <!-- Sidenav sizing -->
                            <div class="sidenav-sizing">
                                <hr class="dropdown-divider" />
                                <h6 class="dropdown-header">Sidenav sizing</h6>
                                <a class="dropdown-item d-flex" data-bs-sidenav-sizing-value="base" href="#" role="button">
                                    <span class="material-symbols-outlined me-2">density_large</span> Base
                                </a>
                                <a class="dropdown-item d-flex" data-bs-sidenav-sizing-value="md" href="#" role="button">
                                    <span class="material-symbols-outlined me-2">density_medium</span> Medium
                                </a>
                                <a class="dropdown-item d-flex" data-bs-sidenav-sizing-value="sm" href="#" role="button">
                                    <span class="material-symbols-outlined me-2">density_small</span> Small
                                </a>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>
        </nav>
    </aside>
    
    <!-- Sidenav (sm) -->
    <aside class="aside aside-sm sidenav-sm">
        <nav class="navbar navbar-expand-xl navbar-vertical">
            <div class="container-lg">
                <!-- Brand -->
                <a class="navbar-brand fs-5 fw-bold text-xl-center mb-xl-4" href="<?= PROOT; ?>index">
                    <i class="fs-4 text-secondary" data-duoicon="box-2"></i> <span class="d-xl-none ms-1">
                        Admin<?= get_person_role(); ?>
                    </span>
                </a>
            
                <!-- User -->
                <div class="d-flex ms-auto d-xl-none">
                    <div class="dropdown my-n2">
                        <a class="btn btn-link d-inline-flex align-items-center dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="avatar avatar-sm avatar-status avatar-status-success me-3">
                                <img class="avatar-img" src="<?= (($admin_data['admin_profile'] != NULL) ? $admin_data['admin_profile'] : PROOT . 'assets/media/avatar.png'); ?>" alt="..." />
                            </span>
                            <span class="d-none d-xl-block"><?= ((admin_is_logged_in()) ? ucwords($admin_data['admin_name']): ucwords($collector_data['collector_name'])); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= PROOT; ?>account">Account</a></li>
                            <li><a class="dropdown-item" href="<?= PROOT; ?>auth/password-reset" target="_blank">Change password</a></li>
                            <li><hr class="dropdown-divider" /></li>
                            <li><a class="dropdown-item" href="<?= PROOT; ?>auth/sign-out">Sign out</a></li>
                        </ul>
                    </div>
    
                    <!-- Divider -->
                    <div class="vr align-self-center bg-dark mx-2"></div>
            
                    <!-- Notifications -->
                    <div class="dropdown ">
                        <button class="btn btn-link" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                            <span class="material-symbols-outlined scale-125">notifications</span>
                            <span class="position-absolute top-0 end-0 m-3 p-1 bg-warning rounded-circle">
                                <span class="visually-hidden">New notifications</span>
                            </span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" style="width: 350px">
                            <!-- Header -->
                            <div class="row">
                                <div class="col">
                                    <h6 class="dropdown-header me-auto">Notifications</h6>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-sm btn-link" type="button"><span class="material-symbols-outlined me-1">done_all</span> Mark all as read</button>
                                    <button class="btn btn-sm btn-link" type="button"><span class="material-symbols-outlined">settings</span></button>
                                </div>
                            </div>
                
                            <!-- Items -->
                            <!-- <div class="list-group list-group-flush px-4">
                                <div class="list-group-item border-style-dashed px-0">
                                    <div class="row gx-3">
                                        <div class="col-auto">
                                            <div class="avatar avatar-sm">
                                                <img class="avatar-img" src="./assets/img/photos/photo-1.jpg" alt="..." />
                                            </div>
                                        </div>
                                        <div class="col">
                                            <p class="text-body mb-2">
                                                <span class="fw-semibold">Emily T.</span> commented on your post <br /><small class="text-body-secondary">5 minutes ago</small>
                                            </p>
                                            <div class="card">
                                                <div class="card-body p-3">Love the new dashboard layout! Super clean and easy to navigate 🔥</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="list-group-item border-style-dashed px-0">
                                    <div class="row gx-3">
                                        <div class="col-auto">
                                            <div class="avatar avatar-sm">
                                                <img class="avatar-img" src="./assets/img/photos/photo-2.jpg" alt="..." />
                                            </div>
                                        </div>
                                        <div class="col">
                                            <p class="text-body mb-2">
                                                <span class="fw-semibold">Michael J.</span> requested changes on your post <br />
                                                <small class="text-body-secondary">10 minutes ago</small>
                                            </p>
                                            <div class="card">
                                                <div class="card-body p-3">
                                                    <p class="mb-2">Could you update the revenue chart with the latest data? Thanks!</p>
                                                    <p class="mb-0">
                                                    <button class="btn btn-sm btn-light" type="button">Update now</button>
                                                    <button class="btn btn-sm btn-link">Dismiss</button>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="list-group-item border-style-dashed px-0">
                                    <div class="row gx-3 align-items-center">
                                        <div class="col-auto">
                                            <div class="avatar">
                                                <span class="material-symbols-outlined">error</span>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <p class="text-body mb-0">
                                                <span class="fw-semibold">System alert</span> - Build failed <br />
                                                <small class="text-body-secondary">1 hour ago</small>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                        </div>
                    </div>
                </div>
        
                <!-- Toggler -->
                <button
                class="navbar-toggler ms-3"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#sidenavSmallCollapse"
                aria-controls="sidenavSmallCollapse"
                aria-expanded="false"
                aria-label="Toggle navigation"
                >
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <!-- Collapse -->
                <div class="collapse navbar-collapse" id="sidenavSmallCollapse">
                    <!-- Search -->
                    <div class="input-group d-xl-none my-4 my-xl-0">
                        <input class="form-control" type="search" placeholder="Search" aria-label="Search" aria-describedby="sidenavSmallSearchMobile" />
                        <span class="input-group-text" id="sidenavSmallSearchMobile">
                            <span class="material-symbols-outlined">search</span>
                        </span>
                    </div>
            
                    <!-- Nav -->
                    <nav class="navbar-nav nav-pills">
                        <div class="nav-item dropend">
                            <a class="nav-link active" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="material-symbols-outlined">space_dashboard</span>
                                <span class="ms-3 d-xl-none">Home</span>
                            </a>
                            <div class="dropdown-menu ms-xl-3">
                                <h6 class="dropdown-header d-none d-xl-block">Dashboards</h6>
                                <a class="dropdown-item active" href="<?= PROOT; ?>index">Home</a>
                                <a class="dropdown-item " href="javasdceipt:;">Summary</a>
                                <a class="dropdown-item " href="javascript:;">Note</a>
                            </div>
                        </div>
                        <div class="nav-item dropend">
                            <a
                            class="nav-link "
                            href="#"
                            role="button"
                            data-bs-toggle="dropdown"
                            data-bs-auto-close="outside"
                            aria-expanded="false"
                            >
                                <span class="material-symbols-outlined">auto_stories</span>
                                <span class="ms-3 d-xl-none">Pages</span>
                            </a>
                            <ul class="dropdown-menu ms-xl-3">
                                <li>
                                    <h6 class="dropdown-header d-none d-xl-block">Pages</h6>
                                </li>
                                <li class="dropend">
                                    <a
                                    class="dropdown-item d-flex "
                                    href="#"
                                    role="button"
                                    data-bs-toggle="dropdown"
                                    data-bs-auto-close="outside"
                                    aria-expanded="false"
                                    >
                                        Customers <span class="material-symbols-outlined ms-auto">chevron_right</span>
                                    </a>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item " href="./customers/customers.html">Customers</a>
                                        <a class="dropdown-item " href="./customers/customer.html">Archive customers</a>
                                        <a class="dropdown-item " href="./customers/customer-new.html">New customer</a>
                                    </div>
                                </li>
                                <li class="dropend">
                                    <a
                                    class="dropdown-item d-flex "
                                    href="#"
                                    role="button"
                                    data-bs-toggle="dropdown"
                                    data-bs-auto-close="outside"
                                    aria-expanded="false"
                                    >
                                        Customers <span class="material-symbols-outlined ms-auto">chevron_right</span>
                                    </a>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item " href="./customers/customers.html">Customers</a>
                                        <a class="dropdown-item " href="./customers/customer.html">Archive customers</a>
                                        <a class="dropdown-item " href="./customers/customer-new.html">New customer</a>
                                    </div>
                                </li>
                                <li class="dropend">
                                    <a
                                    class="dropdown-item d-flex "
                                    href="#"
                                    role="button"
                                    data-bs-toggle="dropdown"
                                    data-bs-auto-close="outside"
                                    aria-expanded="false"
                                    >
                                        Projects <span class="material-symbols-outlined ms-auto">chevron_right</span>
                                    </a>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item " href="./projects/projects.html">Projects</a>
                                        <a class="dropdown-item " href="./projects/project.html">Project overview</a>
                                        <a class="dropdown-item " href="./projects/project-new.html">New project</a>
                                    </div>
                                </li>
                             </ul>
                        </div>
                    </nav>
                
                    <!-- Divider -->
                    <hr class="my-4" />
                    
                    <!-- Nav -->
                    <nav class="navbar-nav nav-pills">
                        <div class="nav-item dropend">
                            <a class="nav-link " href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="material-symbols-outlined">code</span>
                                <span class="ms-3 d-xl-none">Docs</span>
                            </a>
                            <div class="dropdown-menu ms-xl-3">
                                <h6 class="dropdown-header d-none d-xl-block">Documentation</h6>
                                <!-- <a class="dropdown-item " href="./docs/getting-started.html">Getting started</a>
                                <a class="dropdown-item " href="./docs/components.html">Components</a> -->
                                <a class="dropdown-item d-flex " href="javascript:;"
                                    >Version <span class="badge text-bg-primary ms-auto">0.0.1</span>
                                    </a>
                            </div>
                        </div>
                    </nav>
    
                    <!-- Divider -->
                    <hr class="my-4 d-xl-none" />
        
                    <!-- Nav -->
                    <nav class="navbar-nav nav-pills mt-auto">
                        <div class="nav-item">
                            <?php if (admin_has_permission()): ?>
                            <a
                                class="nav-link"
                                href="#withdrawalModal" data-bs-toggle="modal" data-bs-target="#withdrawalModal"
                                data-bs-toggle="tooltip"
                                data-bs-placement="right"
                                data-bs-title="Make new withdrawal"
                            >
                                <span class="material-symbols-outlined">payment_arrow_down</span> <span class="d-xl-none ms-3">Make new withdrawal</span>
                            </a>
                            <?php ese: ?>
                            <a
                                class="nav-link"
                                href="#transactionModal" data-bs-toggle="modal" data-bs-target="#transactionModal"
                                data-bs-toggle="tooltip"
                                data-bs-placement="right"
                                data-bs-title="Add new deposit"
                            >
                                <span class="material-symbols-outlined">send_money</span> <span class="d-xl-none ms-3">Add new deposit</span>
                            </a>
                            <?php endif; ?>
                        </div>
                        <div class="nav-item">
                            <a class="nav-link" href="mailto:info@namibra.io" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Contact us">
                                <span class="material-symbols-outlined">alternate_email</span> <span class="d-xl-none ms-3">Contact us</span>
                            </a>
                        </div>
                        <div class="nav-item dropend">
                            <a class="nav-link" href="#" role="button" data-bs-toggle="dropdown" data-bs-settings-switcher aria-expanded="false">
                                <span class="material-symbols-outlined">settings</span> <span class="d-xl-none ms-3">Settings</span>
                            </a>
                            <div class="dropdown-menu top-auto bottom-0 ms-xl-3">
                                <!-- Color mode -->
                                <h6 class="dropdown-header">Color mode</h6>
                                <a class="dropdown-item d-flex" data-bs-theme-value="light" href="#" role="button"> <span class="material-symbols-outlined me-2">light_mode</span> Light </a>
                                <a class="dropdown-item d-flex" data-bs-theme-value="dark" href="#" role="button"> <span class="material-symbols-outlined me-2">dark_mode</span> Dark </a>
                                <a class="dropdown-item d-flex" data-bs-theme-value="auto" href="#" role="button"> <span class="material-symbols-outlined me-2">contrast</span> Auto </a>
                                
                                <!-- Navigation position -->
                                <hr class="dropdown-divider" />
                                <h6 class="dropdown-header">Navigation position</h6>
                                <a class="dropdown-item d-flex" data-bs-navigation-position-value="sidenav" href="#" role="button">
                                    <span class="material-symbols-outlined me-2">keyboard_tab_rtl</span> Sidenav
                                </a>
                                <a class="dropdown-item d-flex" data-bs-navigation-position-value="topnav" href="#" role="button">
                                    <span class="material-symbols-outlined me-2">vertical_align_top</span> Topnav
                                </a>
                
                                <!-- Sidenav sizing -->
                                <div class="sidenav-sizing">
                                    <hr class="dropdown-divider" />
                                    <h6 class="dropdown-header">Sidenav sizing</h6>
                                    <a class="dropdown-item d-flex" data-bs-sidenav-sizing-value="base" href="#" role="button">
                                        <span class="material-symbols-outlined me-2">density_large</span> Base
                                    </a>
                                    <a class="dropdown-item d-flex" data-bs-sidenav-sizing-value="md" href="#" role="button">
                                        <span class="material-symbols-outlined me-2">density_medium</span> Medium
                                    </a>
                                    <a class="dropdown-item d-flex" data-bs-sidenav-sizing-value="sm" href="#" role="button">
                                        <span class="material-symbols-outlined me-2">density_small</span> Small
                                    </a>
                                </div>
                            </div>
                        </div>
                    </nav>
                </div>
            </div>
        </nav>
    </aside>
    
    <!-- Sidenav (md) -->
    <aside class="aside aside-md sidenav-md">
        <nav class="navbar navbar-expand-xl navbar-vertical">
            <div class="container-lg">
                <!-- Brand -->
                <a class="navbar-brand fs-5 fw-bold text-xl-center mb-xl-4" href="<?= PROOT; ?>index">
                    <i class="fs-4 text-secondary" data-duoicon="box-2"></i> <span class="d-xl-none ms-1">
                        Admin<?= get_person_role(); ?>
                    </span>
                </a>
            
                <!-- User -->
                <div class="d-flex ms-auto d-xl-none">
                    <div class="dropdown my-n2">
                        <a class="btn btn-link d-inline-flex align-items-center dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="avatar avatar-sm avatar-status avatar-status-success me-3">
                                <img class="avatar-img" src="<?= (($admin_data['admin_profile'] != NULL) ? $admin_data['admin_profile'] : PROOT . 'assets/media/avatar.png'); ?>" alt="..." />
                            </span>
                            <span class="d-none d-xl-block"><?= ((admin_is_logged_in()) ? ucwords($admin_data['admin_name']): ucwords($collector_data['collector_name'])); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= PROOT; ?>account">Account</a></li>
                            <li><a class="dropdown-item" href="<?= PROOT; ?>auth/password-reset" target="_blank">Change password</a></li>
                            <li><hr class="dropdown-divider" /></li>
                            <li><a class="dropdown-item" href="<?= PROOT; ?>auth/sign-out">Sign out</a></li>
                        </ul>
                    </div>
        
                    <!-- Divider -->
                    <div class="vr align-self-center bg-dark mx-2"></div>
            
                    <!-- Notifications -->
                    <div class="dropdown ">
                        <button class="btn btn-link" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                            <span class="material-symbols-outlined scale-125">notifications</span>
                            <span class="position-absolute top-0 end-0 m-3 p-1 bg-warning rounded-circle">
                                <span class="visually-hidden">New notifications</span>
                            </span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" style="width: 350px">
                            <!-- Header -->
                            <div class="row">
                                <div class="col">
                                    <h6 class="dropdown-header me-auto">Notifications</h6>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-sm btn-link" type="button"><span class="material-symbols-outlined me-1">done_all</span> Mark all as read</button>
                                    <button class="btn btn-sm btn-link" type="button"><span class="material-symbols-outlined">settings</span></button>
                                </div>
                            </div>
                    
                            <!-- Items -->
                            <!-- <div class="list-group list-group-flush px-4">
                                <div class="list-group-item border-style-dashed px-0">
                                    <div class="row gx-3">
                                        <div class="col-auto">
                                            <div class="avatar avatar-sm">
                                                <img class="avatar-img" src="./assets/img/photos/photo-1.jpg" alt="..." />
                                            </div>
                                        </div>
                                        <div class="col">
                                            <p class="text-body mb-2">
                                                <span class="fw-semibold">Emily T.</span> commented on your post <br /><small class="text-body-secondary">5 minutes ago</small>
                                            </p>
                                            <div class="card">
                                                <div class="card-body p-3">Love the new dashboard layout! Super clean and easy to navigate 🔥</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="list-group-item border-style-dashed px-0">
                                    <div class="row gx-3">
                                        <div class="col-auto">
                                            <div class="avatar avatar-sm">
                                                <img class="avatar-img" src="./assets/img/photos/photo-2.jpg" alt="..." />
                                            </div>
                                        </div>
                                        <div class="col">
                                            <p class="text-body mb-2">
                                                <span class="fw-semibold">Michael J.</span> requested changes on your post <br />
                                                <small class="text-body-secondary">10 minutes ago</small>
                                            </p>
                                            <div class="card">
                                                <div class="card-body p-3">
                                                    <p class="mb-2">Could you update the revenue chart with the latest data? Thanks!</p>
                                                    <p class="mb-0">
                                                        <button class="btn btn-sm btn-light" type="button">Update now</button>
                                                        <button class="btn btn-sm btn-link">Dismiss</button>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="list-group-item border-style-dashed px-0">
                                    <div class="row gx-3 align-items-center">
                                        <div class="col-auto">
                                            <div class="avatar">
                                                <span class="material-symbols-outlined">error</span>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <p class="text-body mb-0">
                                                <span class="fw-semibold">System alert</span> - Build failed <br />
                                                <small class="text-body-secondary">1 hour ago</small>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                        </div>
                    </div>
                </div>
        
                <!-- Toggler -->
                <button
                    class="navbar-toggler ms-3"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#sidenavMediumCollapse"
                    aria-controls="sidenavMediumCollapse"
                    aria-expanded="false"
                    aria-label="Toggle navigation"
                >
                    <span class="navbar-toggler-icon"></span>
                </button>
            
                <!-- Collapse -->
                <div class="collapse navbar-collapse" id="sidenavMediumCollapse">
                    <!-- Search -->
                    <div class="input-group d-xl-none my-4 my-xl-0">
                        <input class="form-control" type="search" placeholder="Search" aria-label="Search" aria-describedby="sidenavMediumSearchMobile" />
                        <span class="input-group-text" id="sidenavMediumSearchMobile">
                            <span class="material-symbols-outlined">search</span>
                        </span>
                    </div>
            
                    <!-- Nav -->
                    <nav class="navbar-nav nav-pills">
                        <div class="nav-item dropend">
                            <a
                            class="nav-link flex-xl-column active"
                            href="#"
                            role="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            >
                                <span class="material-symbols-outlined">space_dashboard</span>
                                <span class="ms-3 ms-xl-0 mt-xl-1 d-xl-block align-self-stretch fs-xl-sm text-xl-center text-truncate">Home</span>
                            </a>
                            <div class="dropdown-menu ms-xl-3">
                                <a class="dropdown-item active" href="./index.html">Default</a>
                                <a class="dropdown-item " href="./dashboards/crypto.html">Crypto</a>
                                <a class="dropdown-item " href="./dashboards/saas.html">SaaS</a>
                            </div>
                        </div>
                        <div class="nav-item dropend">
                            <a
                            class="nav-link flex-xl-column "
                            href="#"
                            role="button"
                            data-bs-toggle="dropdown"
                            data-bs-auto-close="outside"
                            aria-expanded="false"
                            >
                                <span class="material-symbols-outlined">auto_stories</span>
                                <span class="ms-3 ms-xl-0 mt-xl-1 d-xl-block align-self-stretch fs-xl-sm text-xl-center text-truncate">Pages</span>
                            </a>
                            <ul class="dropdown-menu ms-xl-3">
                                <li class="dropend">
                                    <a
                                    class="dropdown-item d-flex "
                                    href="#"
                                    role="button"
                                    data-bs-toggle="dropdown"
                                    data-bs-auto-close="outside"
                                    aria-expanded="false"
                                    >
                                        Customers <span class="material-symbols-outlined ms-auto">chevron_right</span>
                                    </a>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item " href="<?= PROOT; ?>customers">Customers</a>
                                        <a class="dropdown-item " href="<?= PROOT; ?>archive-customer">Archived customers</a>
                                        <a class="dropdown-item " href="<?= PROOT; ?>customer-new">New customer</a>
                                    </div>
                                </li>
                                <li class="dropend">
                                    <a
                                    class="dropdown-item d-flex "
                                    href="#"
                                    role="button"
                                    data-bs-toggle="dropdown"
                                    data-bs-auto-close="outside"
                                    aria-expanded="false"
                                    >
                                        Customers <span class="material-symbols-outlined ms-auto">chevron_right</span>
                                    </a>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item " href="<?= PROOT; ?>customers">Customers</a>
                                        <a class="dropdown-item " href="<?= PROOT; ?>archive-customer">Archived customers</a>
                                        <a class="dropdown-item " href="<?= PROOT; ?>customer-new">New customer</a>
                                    </div>
                                </li>
                                
                            </ul>
                        </div>
                    </nav>
    
                    <!-- Divider -->
                    <hr class="my-4" />
            
                    <!-- Nav -->
                    <nav class="navbar-nav nav-pills">
                        <div class="nav-item dropend">
                            <a
                            class="nav-link flex-xl-column "
                            href="#"
                            role="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            >
                                <span class="material-symbols-outlined">code</span>
                                <span class="ms-3 ms-xl-0 mt-xl-1 d-xl-block align-self-stretch fs-xl-sm text-xl-center text-truncate">Docs</span>
                            </a>
                            <div class="dropdown-menu ms-xl-3">
                                <h6 class="dropdown-header d-none d-xl-block">Documentation</h6>
                                <!-- <a class="dropdown-item " href="./docs/getting-started.html">Getting started</a>
                                <a class="dropdown-item " href="./docs/components.html">Components</a> -->
                                <a class="dropdown-item d-flex " href="javascript:;"
                                    >Version <span class="badge text-bg-primary ms-auto">0.0.1</span></a
                                >
                            </div>
                        </div>
                    </nav>
    
                    <!-- Divider -->
                    <hr class="my-4 d-xl-none" />
            
                    <!-- Nav -->
                    <nav class="navbar-nav nav-pills mt-auto">
                        <div class="nav-item">
                            <?php if (admin_has_permission()): ?>
                            <a
                                class="nav-link flex-xl-column"
                                href="#withdrawalModal" data-bs-toggle="modal" data-bs-target="#withdrawalModal"
                                data-bs-toggle="tooltip"
                                data-bs-placement="right"
                                data-bs-title="Make new withdrawal"
                            >
                                <span class="material-symbols-outlined">payment_arrow_down</span> <span class="d-xl-none ms-3">Make new withdrawal</span>
                            </a>
                            <?php else: ?>
                            <a
                                class="nav-link flex-xl-column"
                                href="#transactionModal" data-bs-toggle="modal" data-bs-target="#transactionModal"
                                data-bs-toggle="tooltip"
                                data-bs-placement="right"
                                data-bs-title="Add new deposit"
                            >
                                <span class="material-symbols-outlined">local_mall</span> <span class="d-xl-none ms-3">Add new deposit</span>
                            </a>
                            <?php endif; ?>
                        </div>
                        <div class="nav-item">
                            <a
                            class="nav-link flex-xl-column"
                            href="mailto:info@namibra.com"
                            data-bs-toggle="tooltip"
                            data-bs-placement="right"
                            data-bs-title="Contact us"
                            >
                                <span class="material-symbols-outlined">alternate_email</span> <span class="d-xl-none ms-3">Contact us</span>
                            </a>
                        </div>
                        <div class="nav-item dropend">
                            <a class="nav-link flex-xl-column" href="#" role="button" data-bs-toggle="dropdown" data-bs-settings-switcher aria-expanded="false">
                                <span class="material-symbols-outlined">settings</span> <span class="d-xl-none ms-3">Settings</span>
                            </a>
                            <div class="dropdown-menu top-auto bottom-0 ms-xl-3">
                                <!-- Color mode -->
                                <h6 class="dropdown-header">Color mode</h6>
                                <a class="dropdown-item d-flex" data-bs-theme-value="light" href="#" role="button"> <span class="material-symbols-outlined me-2">light_mode</span> Light </a>
                                <a class="dropdown-item d-flex" data-bs-theme-value="dark" href="#" role="button"> <span class="material-symbols-outlined me-2">dark_mode</span> Dark </a>
                                <a class="dropdown-item d-flex" data-bs-theme-value="auto" href="#" role="button"> <span class="material-symbols-outlined me-2">contrast</span> Auto </a>
                                
                                <!-- Navigation position -->
                                <hr class="dropdown-divider" />
                                <h6 class="dropdown-header">Navigation position</h6>
                                <a class="dropdown-item d-flex" data-bs-navigation-position-value="sidenav" href="#" role="button">
                                    <span class="material-symbols-outlined me-2">keyboard_tab_rtl</span> Sidenav
                                </a>
                                <a class="dropdown-item d-flex" data-bs-navigation-position-value="topnav" href="#" role="button">
                                    <span class="material-symbols-outlined me-2">vertical_align_top</span> Topnav
                                </a>
                            
                                <!-- Sidenav sizing -->
                                <div class="sidenav-sizing">
                                    <hr class="dropdown-divider" />
                                    <h6 class="dropdown-header">Sidenav sizing</h6>
                                    <a class="dropdown-item d-flex" data-bs-sidenav-sizing-value="base" href="#" role="button">
                                        <span class="material-symbols-outlined me-2">density_large</span> Base
                                    </a>
                                    <a class="dropdown-item d-flex" data-bs-sidenav-sizing-value="md" href="#" role="button">
                                        <span class="material-symbols-outlined me-2">density_medium</span> Medium
                                    </a>
                                    <a class="dropdown-item d-flex" data-bs-sidenav-sizing-value="sm" href="#" role="button">
                                        <span class="material-symbols-outlined me-2">density_small</span> Small
                                    </a>
                                </div>
                            </div>
                        </div>
                    </nav>
                </div>
            </div>
        </nav>
    </aside>
    
    <!-- Sidenav (base) -->
    <aside class="aside aside-base sidenav-base">
        <nav class="navbar navbar-expand-xl navbar-vertical">
            <div class="container-lg">
            <!-- Brand -->
                <a class="navbar-brand d-flex align-items-center fs-5 fw-bold px-xl-3 mb-xl-4" href="<?= PROOT; ?>index">
                    <i class="fs-4 text-secondary me-2" data-duoicon="box-2"></i> 
                    Admin<?= get_person_role(); ?>
                </a>
            
                <!-- User -->
                <div class="d-flex ms-auto d-xl-none">
                    <div class="dropdown my-n2">
                        <a class="btn btn-link d-inline-flex align-items-center dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="avatar avatar-sm avatar-status avatar-status-success me-3">
                            <img class="avatar-img" src="<?= (($admin_data['admin_profile'] != NULL) ? $admin_data['admin_profile'] : PROOT . 'assets/media/avatar.png'); ?>" alt="..." />
                            </span>
                            <span class="d-none d-xl-block"><?= ucwords($admin_data['admin_name']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= PROOT; ?>account">Account</a></li>
                            <li><a class="dropdown-item" href="<?= PROOT; ?>auth/password-reset" target="_blank">Change password</a></li>
                            <li><hr class="dropdown-divider" /></li>
                            <li><a class="dropdown-item" href="<?= PROOT; ?>auth/sign-out">Sign out</a></li>
                        </ul>
                    </div>
            
                    <!-- Divider -->
                    <div class="vr align-self-center bg-dark mx-2"></div>
            
                    <!-- Notifications -->
                    <div class="dropdown ">
                        <button class="btn btn-link" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                            <span class="material-symbols-outlined scale-125">notifications</span>
                            <span class="position-absolute top-0 end-0 m-3 p-1 bg-warning rounded-circle">
                                <span class="visually-hidden">New notifications</span>
                            </span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" style="width: 350px">
                            <!-- Header -->
                            <div class="row">
                                <div class="col">
                                    <h6 class="dropdown-header me-auto">Notifications</h6>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-sm btn-link" type="button"><span class="material-symbols-outlined me-1">done_all</span> Mark all as read</button>
                                    <button class="btn btn-sm btn-link" type="button"><span class="material-symbols-outlined">settings</span></button>
                                </div>
                            </div>
                            
                            <!-- Items -->
                            <!-- <div class="list-group list-group-flush px-4">
                                <div class="list-group-item border-style-dashed px-0">
                                    <div class="row gx-3">
                                        <div class="col-auto">
                                            <div class="avatar avatar-sm">
                                                <img class="avatar-img" src="./assets/img/photos/photo-1.jpg" alt="..." />
                                            </div>
                                        </div>
                                        <div class="col">
                                            <p class="text-body mb-2">
                                                <span class="fw-semibold">Emily T.</span> commented on your post <br /><small class="text-body-secondary">5 minutes ago</small>
                                            </p>
                                            <div class="card">
                                                <div class="card-body p-3">Love the new dashboard layout! Super clean and easy to navigate 🔥</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="list-group-item border-style-dashed px-0">
                                    <div class="row gx-3">
                                        <div class="col-auto">
                                            <div class="avatar avatar-sm">
                                                <img class="avatar-img" src="./assets/img/photos/photo-2.jpg" alt="..." />
                                            </div>
                                        </div>
                                        <div class="col">
                                            <p class="text-body mb-2">
                                                <span class="fw-semibold">Michael J.</span> requested changes on your post <br />
                                                <small class="text-body-secondary">10 minutes ago</small>
                                            </p>
                                            <div class="card">
                                                <div class="card-body p-3">
                                                    <p class="mb-2">Could you update the revenue chart with the latest data? Thanks!</p>
                                                    <p class="mb-0">
                                                        <button class="btn btn-sm btn-light" type="button">Update now</button>
                                                        <button class="btn btn-sm btn-link">Dismiss</button>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="list-group-item border-style-dashed px-0">
                                    <div class="row gx-3 align-items-center">
                                        <div class="col-auto">
                                            <div class="avatar">
                                                <span class="material-symbols-outlined">error</span>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <p class="text-body mb-0">
                                                <span class="fw-semibold">System alert</span> - Build failed <br />
                                                <small class="text-body-secondary">1 hour ago</small>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                        </div>
                    </div>
                </div>
                
                <!-- Toggler -->
                <button
                    class="navbar-toggler ms-3"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#sidenavBaseCollapse"
                    aria-controls="sidenavBaseCollapse"
                    aria-expanded="false"
                    aria-label="Toggle navigation"
                >
                    <span class="navbar-toggler-icon"></span>
                </button>
    
                <!-- Collapse -->
                <div class="collapse navbar-collapse" id="sidenavBaseCollapse">
                    <!-- Search -->
                    <div class="input-group d-xl-none my-4 my-xl-0">
                        <input class="form-control" type="search" placeholder="Search" aria-label="Search" aria-describedby="sidenavBaseSearchMobile" />
                        <span class="input-group-text" id="sidenavBaseSearchMobile">
                            <span class="material-symbols-outlined">search</span>
                        </span>
                    </div>
            
                    <!-- Nav -->
                    <nav class="navbar-nav nav-pills mb-7">
                        <div class="nav-item">
                            <a
                            class="nav-link nav-dashboards"
                            href="#"
                            data-bs-toggle="collapse"
                            data-bs-target="#dashboards"
                            role="button"
                            aria-expanded="false"
                            aria-controls="dashboards"
                            >
                                <span class="material-symbols-outlined me-3">space_dashboard</span> Dashboards
                            </a>
                            <div class="collapse" id="dashboards">
                                <nav class="nav nav-pills">
                                    <a class="nav-link active" href="<?= PROOT; ?>index">Home</a>
                                    <a class="nav-link " href="<?= PROOT; ?>live">Live</a>
                                    <a class="nav-link " href="<?= PROOT; ?>summary">Summary</a>
                                </nav>
                            </div>
                        </div>
                        <?php if ((admin_is_logged_in() && admin_has_permission('approver'))): ?>
                        <div class="nav-item">
                            <a
                                class="nav-link nav-collectors"
                                href="#"
                                data-bs-toggle="collapse"
                                data-bs-target="#collectors"
                                role="button"
                                aria-expanded="false"
                                aria-controls="collectors"
                            >
                                <span class="material-symbols-outlined me-3">reduce_capacity</span> Collectors
                            </a>
                            <div class="collapse " id="collectors">
                                <nav class="nav nav-pills">
                                    <a class="nav-link sub-nav-collectors" href="<?= PROOT; ?>app/collectors">Collectors</a>
                                    <a class="nav-link sub-nav-archived-collectors" href="<?= PROOT; ?>app/archived-collectors">Archived collectors</a>
                                    <a class="nav-link sub-nav-new-collectors" href="<?= PROOT; ?>app/collector-new">New collector</a>
                                </nav>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="nav-item">
                            <a
                            class="nav-link nav-customers"
                            href="#"
                            data-bs-toggle="collapse"
                            data-bs-target="#customers"
                            role="button"
                            aria-expanded="false"
                            aria-controls="customers"
                            >
                                <span class="material-symbols-outlined me-3">group</span> Customers
                            </a>
                            <div class="collapse " id="customers">
                                <nav class="nav nav-pills">
                                    <a class="nav-link sub-nav-customers" href="<?= PROOT; ?>app/customers">Customers</a>
                                    <?php if ((admin_is_logged_in() && admin_has_permission('approver'))): ?>
                                    <a class="nav-link sub-nav-archived-customers" href="<?= PROOT; ?>app/archived-customers">Archived customers</a>
                                    <?php endif; ?>
                                    <a class="nav-link sub-nav-new-customers" href="<?= PROOT; ?>app/customer-new">New customer</a>
                                </nav>
                            </div>
                        </div>
                        <div class="nav-item">
                            <a
                            class="nav-link nav-transactions"
                            href="#"
                            data-bs-toggle="collapse"
                            data-bs-target="#transactions"
                            role="button"
                            aria-expanded="false"
                            aria-controls="transactions"
                            >
                                <span class="material-symbols-outlined me-3">receipt</span> Transactions
                            </a>
                            <div class="collapse " id="transactions">
                                <nav class="nav nav-pills">
                                    <a class="nav-link sub-nav-transactions" href="<?= PROOT; ?>app/transactions">Transactions</a>
                                    <?php if (admin_has_permission()): ?>
                                    <a class="nav-link" href="#withdrawalModal" data-bs-toggle="modal" data-bs-target="#withdrawalModal">Make new withdrawal</a>
                                    <?php else: ?>
                                    <a class="nav-link" href="#transactionModal" data-bs-toggle="modal" data-bs-target="#transactionModal">Add new deposit</a>
                                    <?php endif; ?>
                                    <a class="nav-link sub-nav-approved-transactions" href="<?= PROOT; ?>app/transactions-approved">Approved transactions </a>
                                    <a class="nav-link sub-nav-not-approved-transactions" href="<?= PROOT; ?>app/transactions-not-approved">Not approved transactions </a>
                                    <a class="nav-link sub-nav-archived-transactions" href="<?= PROOT; ?>app/transactions-archive">Archive transactions</a>
                                </nav>
                            </div>
                        </div>
                        <div class="nav-item">
                            <a
                            class="nav-link nav-collections"
                            href="#"
                            data-bs-toggle="collapse"
                            data-bs-target="#collections"
                            role="button"
                            aria-expanded="false"
                            aria-controls="collections"
                            >
                                <span class="material-symbols-outlined me-3">collections_bookmark</span> Collections
                            </a>
                            <div class="collapse " id="collections">
                                <nav class="nav nav-pills">
                                    <a class="nav-link sub-nav-collections" href="<?= PROOT; ?>app/collections">Collections </a>
                                    <a class="nav-link sub-nav-archived-collections" href="<?= PROOT; ?>app/collections-archive">Archive collections</a>
                                </nav>
                            </div>
                        </div>
                        <?php if ((admin_is_logged_in() && admin_has_permission('approver'))): ?>
                        <div class="nav-item">
                            <a
                            class="nav-link nav-admins"
                            href="#"
                            data-bs-toggle="collapse"
                            data-bs-target="#admins"
                            role="button"
                            aria-expanded="false"
                            aria-controls="admins"
                            >
                                <span class="material-symbols-outlined me-3">shield_person</span> Admins
                            </a>
                            <div class="collapse " id="admins">
                                <nav class="nav nav-pills">
                                    <a class="nav-link " href="<?= PROOT; ?>app/admins">Admins</a>
                                    <a class="nav-link " href="<?= PROOT; ?>app/archived-admins">Archive admins</a>
                                    <a class="nav-link " href="<?= PROOT; ?>app/admin-new">New admin</a>
                                </nav>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="nav-item">
                            <a
                            class="nav-link nav-logs"
                            href="#"
                            data-bs-toggle="collapse"
                            data-bs-target="#logs"
                            role="button"
                            aria-expanded="false"
                            aria-controls="logs"
                            >
                                <span class="material-symbols-outlined me-3">list_alt</span> Logs
                            </a>
                            <div class="collapse " id="logs">
                                <nav class="nav nav-pills">
                                    <a class="nav-link sub-nav-logs" href="<?= PROOT; ?>app/logs">Logs </a>
                                </nav>
                            </div>
                        </div>
                    </nav>
            
                    <!-- Heading -->
                    <h3 class="fs-base px-3 mb-4">Documentation</h3>
            
                    <!-- Nav -->
                    <nav class="navbar-nav nav-pills mb-xl-7">
                        <!-- <div class="nav-item">
                            <a class="nav-link " href="<?= PROOT; ?>documentation">
                            <span class="material-symbols-outlined me-3">sticky_note_2</span> Getting started
                            </a>
                        </div>
                        <div class="nav-item">
                            <a class="nav-link " href="javascript:;">
                            <span class="material-symbols-outlined me-3">deployed_code</span> Components
                            </a>
                        </div> -->
                        <div class="nav-item">
                            <a class="nav-link " href="javascript:;">
                            <span class="material-symbols-outlined me-3">list_alt</span> Version
                            <span class="badge text-bg-primary ms-auto">0.0.1</span>
                            </a>
                        </div>
                    </nav>
    
                    <!-- Divider -->
                    <hr class="my-4 d-xl-none" />
            
                    <!-- Nav -->
                    <nav class="navbar-nav nav-pills d-xl-none mb-7">
                        <div class="nav-item">
                            <?php if (admin_has_permission()): ?>
                            <a class="nav-link" href="#withdrawalModal" type="button" data-bs-toggle="modal" data-bs-target="#withdrawalModal">
                                <span class="material-symbols-outlined me-3">payment_arrow_down</span> Make new withdrawal
                            </a>
                            <?php else: ?>
                            <a class="nav-link" href="#transactionModal" type="button" data-bs-toggle="modal" data-bs-target="#transactionModal">
                                <span class="material-symbols-outlined me-3">send_money</span> Add new deposit
                            </a>
                            <?php endif; ?>
                        </div>
                        <div class="nav-item">
                            <a class="nav-link" href="mailto:info@namibra.io">
                                <span class="material-symbols-outlined me-3">alternate_email</span> Contact us
                            </a>
                        </div>
                        <div class="nav-item dropdown">
                            <a class="nav-link" href="#" role="button" data-bs-toggle="dropdown" data-bs-settings-switcher aria-expanded="false">
                                <span class="material-symbols-outlined me-3"> settings </span> Settings
                            </a>
                            <div class="dropdown-menu ">
                                <!-- Color mode -->
                                <h6 class="dropdown-header">Color mode</h6>
                                <a class="dropdown-item d-flex" data-bs-theme-value="light" href="#" role="button"> <span class="material-symbols-outlined me-2">light_mode</span> Light </a>
                                <a class="dropdown-item d-flex" data-bs-theme-value="dark" href="#" role="button"> <span class="material-symbols-outlined me-2">dark_mode</span> Dark </a>
                                <a class="dropdown-item d-flex" data-bs-theme-value="auto" href="#" role="button"> <span class="material-symbols-outlined me-2">contrast</span> Auto </a>
                                
                                <!-- Navigation position -->
                                <hr class="dropdown-divider" />
                                <h6 class="dropdown-header">Navigation position</h6>
                                <a class="dropdown-item d-flex" data-bs-navigation-position-value="sidenav" href="#" role="button">
                                    <span class="material-symbols-outlined me-2">keyboard_tab_rtl</span> Sidenav
                                </a>
                                <a class="dropdown-item d-flex" data-bs-navigation-position-value="topnav" href="#" role="button">
                                    <span class="material-symbols-outlined me-2">vertical_align_top</span> Topnav
                                </a>
                                
                                <!-- Sidenav sizing -->
                                <div class="sidenav-sizing">
                                    <hr class="dropdown-divider" />
                                    <h6 class="dropdown-header">Sidenav sizing</h6>
                                    <a class="dropdown-item d-flex" data-bs-sidenav-sizing-value="base" href="#" role="button">
                                        <span class="material-symbols-outlined me-2">density_large</span> Base
                                    </a>
                                    <a class="dropdown-item d-flex" data-bs-sidenav-sizing-value="md" href="#" role="button">
                                        <span class="material-symbols-outlined me-2">density_medium</span> Medium
                                    </a>
                                    <a class="dropdown-item d-flex" data-bs-sidenav-sizing-value="sm" href="#" role="button">
                                        <span class="material-symbols-outlined me-2">density_small</span> Small
                                    </a>
                                </div>
                            </div>
                        </div>
                    </nav>
    
                    <!-- Card -->
                    <div class="card mt-auto">
                        <div class="card-body">
                            <!-- Heading -->
                            <h6>Need help?</h6>
                
                            <!-- Text -->
                            <p class="text-body-secondary mb-0">Feel free to reach out to us should you have any questions or suggestions.</p>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </aside>
