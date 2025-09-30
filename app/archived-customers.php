<?php 
    require ('../system/DatabaseConnector.php');
    
	// Check if the user is logged in
	if (!admin_is_logged_in()) {
		admin_login_redirect();
	}

    $title = 'Archive Collectors | ';
    $body_class = '';
    include ('../system/inc/head.php');
    include ('../system/inc/modals.php');
    include ('../system/inc/sidebar.php');
    include ('../system/inc/topnav-base.php');
    include ('../system/inc/topnav.php');

    // fetch all inactive collectors
    $sql = "
        SELECT * FROM customers 
        WHERE customer_status = ? 
        ORDER BY customer_name ASC
    ";
    $statement = $dbConnection->prepare($sql);
    $statement->execute(['inactive']);
    $rows = $statement->fetchAll();
    $count_rows = $statement->rowCount();

    //
    // inactivate collector
    if (isset($_GET['restore']) && !empty($_GET['restore'])) {
        $restore_id = sanitize($_GET['restore']);

        $sql = $dbConnection->query("UPDATE customers SET customer_status = 'active' WHERE customer_id = '" . $restore_id . "'")->execute();
        if ($sql) {
            $log_message =  'Admin [' . $admin_id . '] has set customer [' . $restore_id . '] status to Active!';
            add_to_log($log_message, $admin_id, 'admin');
            $_SESSION['flash_success'] = $log_message;
            redirect(PROOT . 'app/customers');
        } else {
            $_SESSION['flash_success'] = 'Could\'nt update customer status to Active!';
            redirect(PROOT . 'app/archived-customers');
        }
    }
?>

    <!-- Main -->
    <main class="main px-lg-6">
        <!-- Content -->
        <div class="container-lg">
            <!-- Page header -->
            <div class="row align-items-center mb-7">
                <div class="col-auto">
                    <!-- Avatar -->
                    <div class="avatar avatar-xl rounded text-primary">
                        <i class="fs-2" data-duoicon="user"></i>
                    </div>
                </div>
                <div class="col">
                    <!-- Breadcrumb -->
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item"><a class="text-body-secondary" href="#">Collectors</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Archive</li>
                        </ol>
                    </nav>

                    <!-- Heading -->
                    <h1 class="fs-4 mb-0">Collectors</h1>
                </div>
                    <div class="col-12 col-sm-auto mt-4 mt-sm-0">
                        <div class="row gx-2">
                            <div class="col-6 col-sm-auto">
                                <!-- Action -->
                                <a class="btn btn-secondary d-block" href="<?= PROOT; ?>app/collector-new">
                                    <span class="material-symbols-outlined me-1">add</span> New collector
                                </a>
                            </div>
                            <div class="col-6 col-sm-auto">
                                <a class="btn btn-light d-block" href="<?= goBack(); ?>"><span class="material-symbols-outlined me-1">arrow_back_ios</span> Go back </a>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- Page content -->
            <div class="row">
                <div class="col-12">
                    <!-- Filters -->
                    <div class="card card-line bg-body-tertiary border-transparent mb-7">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-12 col-lg-auto mb-3 mb-lg-0">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <div class="text-body-secondary">No customers selected</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg">
                                    <div class="row gx-3  ">
                                        <div class="col col-lg-auto ms-auto">
                                            <div class="input-group bg-body">
                                                <input type="text" class="form-control" placeholder="Search" aria-label="Search" aria-describedby="search" id="search" />
                                                <span class="input-group-text">
                                                    <span class="material-symbols-outlined">search</span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive mb-7">
                        <table class="table table-hover table-select table-round align-middle mb-0">
                            <thead>
                                <th style="width: 0px">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="tableCheckAll" />
                                        <label class="form-check-label" for="tableCheckAll"></label>
                                    </div>
                                </th>
                                <th>Account number</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Default amount</th>
                                <th>Address</th>
                                <th>Start date</th>
                                <th>Joined date</th>
                                <th colspan="2">Total saved</th>
                            </thead>
                            <tbody>
                                <?php if ($count_rows > 0): ?>
                                <?php 
                                    $i = 1;
                                    foreach($rows as $row): 
                                        $total_saved = sum_customer_saves($row["customer_id"], $status = 'Approved');

                                ?>
                                <tr class="align-middle">
                                    <td style="width: 0px"><?= $i; ?></td>
                                    <td class="text-body-secondary"><?= $row["customer_account_number"]; ?></td>
                                    <td><?= ucwords($row["customer_name"]); ?></td>
                                    <td><?= $row['customer_phone']; ?></td>
                                    <td><?= money($row['customer_default_daily_amount']); ?></td>
                                    <td><?= $row['customer_address']; ?></td>
                                    <td><?= (($row['customer_start_date'] == '0000-00-00') ? 'N/A' : pretty_date_notime($row['customer_start_date']));?></td>
                                    <td><?= pretty_date_notime($row['created_at']); ?></td>
                                    <td><?= money($total_saved); ?></td>
                                    <td style="width: 0px">
                                        <a href="<?= PROOT . 'app/archived-customers?restore=' . $row["customer_id"]; ?>" class="btn btn-secondary w-100 mt-4">Restore</a>
                                    </td>
                                </tr>

                                <!-- DELETE Expenditure -->
                                <div class="modal fade" id="restoreModal_<?= $i; ?>" tabindex="-1" aria-labelledby="restoreModalLabel_<?= $i; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header border-bottom-0 pb-0">
                                                <h1 class="modal-title fs-5" id="restoreModalLabel_'<?= $i; ?>">Restore collector</h1>
                                                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>
                                                    Collector with name <?= strtoupper($row['admin_name']); ?> will be Restored.
                                                    <br>Are you sure you want to proceed to this action.
                                                </p>
                                                <a href="<?= PROOT . 'app/archived-collectors?restore=' . $row["admin_id"]; ?>" class="btn btn-warning w-100 mt-4">Restore</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php $i++; endforeach; ?>
                                <?php else: ?>
                                    <tr class="text-warning">
                                        <td colspan="9"> 
                                            <div class="alert alert-info">No data found!</div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

<?php include ('../system/inc/footer.php'); ?>