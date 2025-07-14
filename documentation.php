<?php 

    // view admin profile details
    require_once ("db_connection/conn.php");

    if (!admin_is_logged_in()) {
        admin_login_redirect();
    }

    include ("includes/header.inc.php");
    include ("includes/nav.inc.php");

?>

    <main>
        <section class="bg-body-secondary">
            <div class="p-2">
                <div class="gradient-bottom-right start-indigo middle-yellow end-purple position-relative py-24 rounded-3">
                    <div class="container mw-screen-xl">
                        <div class="row align-items-center">
                            <div class="col-md-8 col-xl-7">
                                <h1 class="display-4 font-display fw-bolder mb-5">Documentation</h1>
                                <p class="lead pe-lg-24">This is where you find everything you need to know about Giltmarket company Limited and how the system work and how to use the system itself.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="container mw-screen-xl py-10">
            <div class="mb-10 pb-10 border-bottom">
                <div class="row g-3 align-items-center">
                    <div class="col">
                        <h1 class="h2 ls-tight">What to know</h1>
                    </div>
                    <div class="col-auto">
                        <div class="hstack gap-2 justify-content-md-end">
                            <a href="<?= PROOT; ?>" class="btn d-inline-flex btn-sm btn-neutral">
                                <span>Back Home</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3">
                    <ul class="nav flex-column mt-lg-6 position-lg-sticky top-lg-6">
                        <li class="nav-item">
                            <a class="nav-link px-0" href="#item-1">Introduction</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-0" href="#item-2">Trades</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-0" href="#item-3">Authentications</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-0" href="#item-4">Logs</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-0" href="#item-5">Admins</a>
                        </li>
                    </ul>
                </div>
                <div class="col-lg">
                    <article class="article">
                        <h2 id="item-1">Introduction</h2>
                        <p>Giltmarket Company LTD is a trading system where customers come to make a trade by purchasing gold</p>
                        <p></p>
                        <p>Minors or people below 18 years old are not allowed to use this Website.</p>
                        <h2 id="item-2">Trades</h2>
                        <p>Other than the content you own, under these Terms, Company Name and/or its licensors own all the intellectual property rights and materials contained in this Website.</p>
                        <p>You are granted limited license only for purposes of viewing the material contained on this Website.</p>
                        <h2 id="item-3">Authentications</h2>
                        <p>Other than the content you own, under these Terms, Company Name and/or its licensors own all the intellectual property rights and materials contained in this Website.</p>
                        <p>You are granted limited license only for purposes of viewing the material contained on this Website.</p>
                        <h2 id="item-4">Logs</h2>
                        <p>Other than the content you own, under these Terms, Company Name and/or its licensors own all the intellectual property rights and materials contained in this Website.</p>
                        <p>You are granted limited license only for purposes of viewing the material contained on this Website.</p>
                        <h2 id="item-5">Admins</h2>
                        <p>Other than the content you own, under these Terms, Company Name and/or its licensors own all the intellectual property rights and materials contained in this Website.</p>
                        <p>You are granted limited license only for purposes of viewing the material contained on this Website.</p>
                    </article>
                </div>
            </div>
        </section>

<?php include ("includes/footer.inc.php"); ?>
