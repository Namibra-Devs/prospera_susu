<?php
    require ('../../system/DatabaseConnector.php');

    // Get filters from GET parameters
    $limit = isset($_POST['limit']) ? sanitize($_POST['limit']) : '10';
    $page = 1;
    if (isset($_POST['page']) && $_POST['page'] > 1) {
        $start = (($_POST['page'] - 1) * $limit);
        $page = $_POST['page'];
    } else {
        $start = 0;
    }

    $collector_id = isset($_POST['collector_id']) ? sanitize($_POST['collector_id']) : '';
    $collector_id = ($collector_id == 'all' || $collector_id == '') ? null : $collector_id;
    $account_number = isset($_POST['account_number']) ? sanitize($_POST['account_number']) : '';
    $customer_name = isset($_POST['customer_name']) ? sanitize($_POST['customer_name']) : '';
    $note_keyword = isset($_POST['note_keyword']) ? sanitize($_POST['note_keyword']) : '';
    $sort_by = isset($_POST['sort_by']) ? sanitize($_POST['sort_by']) : '';
    $date_from = isset($_POST['filter_start_date']) ? sanitize($_POST['filter_start_date']) : '';
    $date_to = isset($_POST['filter_end_date']) ? sanitize($_POST['filter_end_date']) : '';
    $transaction_type = isset($_POST['transaction_type']) ? sanitize($_POST['transaction_type']) : '';
    $transaction_type = ($transaction_type == 'all' || $transaction_type == '') ? null : $transaction_type;
    $payment_mode_filter = isset($_POST['payment_mode_filter']) ? sanitize($_POST['payment_mode_filter']) : '';
    $payment_mode_filter = ($payment_mode_filter == 'all' || $payment_mode_filter == '') ? null : $payment_mode_filter;
    $min_amount = isset($_POST['min_amount']) ? sanitize($_POST['min_amount']) : '';
    $max_amount = isset($_POST['max_amount']) ? sanitize($_POST['max_amount']) : '';
    $results_html = '';
    $results_stats = '';

    // merge both savings and withdrawals into one query
    $query = "SELECT * FROM (
            SELECT 
                saving_id AS transaction_id, 
                saving_customer_id AS customer_id, 
                saving_customer_account_number AS account_number,
                saving_collector_id AS collector_id, 
                saving_amount AS amount, 
                saving_date_collected AS transaction_date, 
                saving_mode AS payment_mode, 
                saving_note AS note_content, 
                saving_status AS status, 
                'saving' AS type, 
                created_at FROM savings 
                UNION ALL 
                    SELECT 
                        withdrawal_id AS transaction_id, 
                        withdrawal_customer_id AS customer_id, 
                        withdrawal_customer_account_number AS account_number, 
                        withdrawal_approver_id AS collector_id, 
                        withdrawal_amount_requested AS amount, 
                        withdrawal_date_requested AS transaction_date, 
                        withdrawal_mode AS payment_mode, 
                        withdrawal_note AS note_content, 
                        withdrawal_status AS status, 
                        'withdrawal' AS type, 
                        created_at FROM withdrawals
            ) 
        AS transactions WHERE 1=1 
    ";
    // Apply filters
    if ($transaction_type && $transaction_type != 'all') {
        if ($transaction_type == 'deposit') {
            $query .= " AND type = 'saving' ";
        } elseif ($transaction_type == 'withdrawal') {
            $query .= " AND type = 'withdrawal' ";
        } else {
            // do nothing, show all
        }
    }

    if (!empty($collector_id)) {
        $query .= " AND collector_id = '$collector_id' AND collector_id IN (SELECT admin_id FROM susu_admins WHERE admin_id = '$collector_id') ";
    }
    if (!empty($account_number)) {
        $query .= " AND account_number LIKE '%$account_number%' ";
    }
    if (!empty($customer_name)) {
        // Join with customers table to filter by customer name
        $query .= " AND customer_id IN (SELECT customer_id FROM customers WHERE CONCAT(customer_first_name, ' ', customer_last_name) LIKE '%$customer_name%') ";
    }
    if (!empty($note_keyword)) {
        // Join with notes table to filter by note keyword
        $query .= " AND (note_content LIKE '%$note_keyword%') ";
    }
    if (!empty($date_from)) {
        $query .= " AND DATE(transaction_date) >= '$date_from' ";
    }
    if (!empty($date_to)) {
        $query .= " AND DATE(transaction_date) <= '$date_to' ";
    }
    if (!empty($min_amount)) {
        $query .= " AND amount >= '$min_amount' ";
    }
    if (!empty($max_amount)) {
        $query .= " AND amount <= '$max_amount' ";
    }
    if (!empty($payment_mode_filter)) {
        $query .= " AND payment_mode = '$payment_mode_filter' ";
    }
    // Apply sorting
    if (!empty($sort_by)) {
        switch ($sort_by) {
            case 'date_asc':
                $query .= " ORDER BY transaction_date ASC ";
                break;
            case 'date_desc':
                $query .= " ORDER BY transaction_date DESC ";
                break;
            case 'amount_asc':
                $query .= " ORDER BY amount ASC ";
                break;
            case 'amount_desc':
                $query .= " ORDER BY amount DESC ";
                break;
            default:
                $query .= " ORDER BY transaction_date DESC ";
        }
    } else {
        $query .= " ORDER BY transaction_date DESC ";
    }

    // Execute query
    $statement = $dbConnection->prepare($query);
    $statement->execute();
    $transaction_stats = $statement->fetchAll(PDO::FETCH_OBJ);
	$total_data = $statement->rowCount();

    // store transaction stats in session for export
    $_SESSION['transaction_stats'] = $transaction_stats;

    if ($total_data > 0) {
        // if transaction type is saving, sum amount as deposits, else sum as withdrawals
        $total_deposits = 0;
        $total_withdrawals = 0;
        foreach ($transaction_stats as $transaction_stat) {
            if ($transaction_stat->type === 'saving') {
                $total_deposits += $transaction_stat->amount;
            } else {
                $total_withdrawals += $transaction_stat->amount;
            }
        }
    }

    $filter_query = $query . ' LIMIT ' . $start . ', ' . $limit . '';
    $statement = $dbConnection->prepare($filter_query);
	$statement->execute();
    $transactions = $statement->fetchAll(PDO::FETCH_OBJ);
    $count_filter = $statement->rowCount();

    // search result data
    $filters = [
        'collector_id' => $collector_id,
        'account_number' => $account_number,
        'customer_name' => $customer_name,
        'note_keyword' => $note_keyword,
        'date_from' => $date_from,
        'date_to' => $date_to,
        'transaction_type' => $transaction_type,
        'payment_mode' => $payment_mode_filter,
        'min_amount' => $min_amount,
        'max_amount' => $max_amount,
        'sort_by' => $sort_by,
        'limit' => $limit,
        'page' => $page
    ];
    // make it readable for users
    // if filter keys value is empty dont show
    $search_query = '';
    foreach ($filters as $key => $value) {
        if (!empty($value)) {
            $search_query .= $key . '=' . $value . '&';
        }
    }
    $search_query = rtrim($search_query, '&');
    // add the number of seconds it took to display results
    $start_time = microtime(true);
    $end_time = microtime(true);
    $execution_time = $end_time - $start_time;
    $search_query .= ' (in ' . number_format($execution_time, 4) . ' seconds)';

    // Generate HTML for results and stats
    $results_html = ' 
        <div class="d-flex align-items-center justify-content-between mb-5">
            <h2 class="fs-5 mb-0">Search results</h2>
            <div class="d-flex">
                <div class="dropdown">
                    <button class="btn btn-light px-3" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                        <span class="material-symbols-outlined">filter_list</span>
                    </button>
                    <div class="dropdown-menu rounded-3 p-6">
                        <h4 class="fs-lg mb-4">Export/Download result</h4>
                        <form style="width: 350px" id="exportForm" action="' . PROOT . 'app/controller/export.transaction.collectors.filter.php">
                            <div class="row gx-3">
                                <div class="col-sm-12 mb-3">
                                    <div class="btn-group w-100" role="group" aria-label="Basic radio toggle button group">
                                        <input type="radio" class="btn-check" name="export-type" id="export_xlsx" autocomplete="off" checked value="xlsx" required />
                                        <label class="btn btn-light" for="export_xlsx" data-bs-toggle="tooltip" data-bs-title="XLSX">
                                        <img src="' . PROOT . 'assets/media/XLSX.png" class="w-rem-6 h-rem-6 rounded-circle" alt="...">
                                        </label>
                                        <input type="radio" class="btn-check" name="export-type" id="export_csv" autocomplete="off" value="csv" required />
                                        <label class="btn btn-light" for="export_csv" data-bs-toggle="tooltip" data-bs-title="CSV">
                                        <img src="' . PROOT . 'assets/media/CSV.png" class="w-rem-6 h-rem-6 rounded-circle" alt="...">
                                        </label>
                                        <input type="radio" class="btn-check" name="export-type" id="export_pdf" autocomplete="off" value="pdf" required />
                                        <label class="btn btn-light" for="export_pdf" data-bs-toggle="tooltip" data-bs-title="PDF">
                                        <img src="' . PROOT . 'assets/media/PDF.png" class="w-rem-6 rh-rem-6 ounded-circle" alt="...">
                                        </label>
                                        <input type="radio" class="btn-check" name="export-type" id="export_xls" autocomplete="off" value="xls" required />
                                        <label class="btn btn-light" for="export_xls" data-bs-toggle="tooltip" data-bs-title="XLS">
                                        <img src="' . PROOT . 'assets/media/XLS.png" class="w-rem-6 h-rem-6 rounded-circle" alt="...">
                                        </label>
                                    </div>
                                </div>
                                <button id="submit-export" type="button" class="btn btn-warning">Export</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>




        <div class="card mb-7 mb-xxl-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            <th style="width: 0px">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="select-all-transactions" />
                                    <label class="form-check-label" for="select-all"></label>
                                </div>
                            </th>
                            <th class="fs-sm"></th>
                            <th class="fs-sm">Client</th>
                            <th class="fs-sm">Amount</th>
                            <th class="fs-sm">Handler</th>
                            <th class="fs-sm">Type</th>
                            <th class="fs-sm">Status</th>
                            <th class="fs-sm">Date</th>
                            <!-- '. ((admin_has_permission('approver')) ? '<th class="fs-sm"></th>' : '') .' -->
                        </tr>
                    </thead>
                    <tbody>
    ';

    // Generate HTML for results
    if (count($transactions) > 0) {
        $i = 1;
        foreach ($transactions as $transaction) {
            // get customer name
            $client_name = findCustomerByAccountNumber($transaction->account_number)->customer_name;
            if (!$client_name) {
                $client_name = 'Unknown';
            }

            // get handler name
            $handler = findAdminById($transaction->collector_id)->admin_name;
            if (!$handler) {
                $handler = 'Admin';
            }

            $options = '';
            if ($transaction->type === 'saving') {
                $type = '<span class="fs-sm text-info">Deposit</span>';

                // check status of deposite transactions
                if ($transaction->status == 'Pending') {
                    $transaction->status = '<span class="badge bg-warning-subtle text-warning">Pending</span>';
                } elseif ($transaction->status == 'Approved') {
                    $transaction->status = '<span class="badge bg-success-subtle text-success">Approved</span>';
                } elseif ($transaction->status == 'Rejected') {
                    $transaction->status = '<span class="badge bg-danger-subtle text-danger">Rejected</span>';
                }

                // show approve button if status is pending
                if ($transaction->status == '<span class="badge bg-warning-subtle text-warning">Pending</span>') {
                    $options .= ' <a href="' . PROOT . 'app/transactions?d=1&approved=' . $transaction->transaction_id . '" class="btn btn-sm btn-light" onclick="return confirm(\'Are you sure you want to APPROVE this Deposit Transaction?\');">Approve</a>';
                    $options .= ' <a href="' . PROOT . 'app/transactions?d=1&reject=' . $transaction->transaction_id . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to REJECT this Deposit Transaction?\');">Reject</a>';
                }
            } else {
                $type = '<span class="fs-sm text-warning">Withdrawal</span>';

                // check status of withdrawal transactions
                if ($transaction->status == 'Pending') {
                    $transaction->status = '<span class="badge bg-warning-subtle text-warning">Pending</span>';
                } elseif ($transaction->status == 'Approved') {
                    $transaction->status = '<span class="badge bg-success-subtle text-success">Approved</span>';
                } elseif ($transaction->status == 'Paid') {
                    $transaction->status = '<span class="badge bg-primary-subtle text-primary">Paid</span>';
                } elseif ($transaction->status == 'Rejected') {
                    $transaction->status = '<span class="badge bg-danger-subtle text-danger">Rejected</span>';
                }

                // show approve button if status is pending
                if ($transaction->status == '<span class="badge bg-warning-subtle text-warning">Pending</span>') {
                    $options .= ' <a href="' . PROOT . 'app/transactions?w=1&approved=' . $transaction->transaction_id . '" class="btn btn-sm btn-warning" onclick="return confirm(\'Are you sure you want to APPROVE this Withdrawal Transaction?\');">Approve</a>';
                    $options .= ' <a href="' . PROOT . 'app/transactions?w=1&reject=' . $transaction->transaction_id . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to REJECT this Withdrawal Transaction?\');">Reject</a>';
                }

                // show paid button if status is approved
                if ($transaction->status == '<span class="badge bg-success-subtle text-success">Approved</span>') {
                    $options .= ' <a href="'.PROOT.'app/transactions?w=1&paid='.$transaction->transaction_id.'" class="btn btn-sm btn-success" onclick="return confirm(\'Are you sure you want to set this Withdrawal Transaction as PAID?\');">Mark as Paid</a>';
                }
            }

             // set background color for all today transactions
            if (date('Y-m-d', strtotime($transaction->transaction_date)) == date('Y-m-d')) {
                $results_html .= '<tr class="table-success" data-id="' . (($transaction->type == 'saving') ? 'save_' : 'withdraw_') . $transaction->transaction_id . '">';
            } else {
                $results_html .= '<tr data-id="' . (($transaction->type == 'saving') ? 'save_' : 'withdraw_') . $transaction->transaction_id . '">';
            }
            
            $results_html .= '
                    <td style="width: 0px">
                        <div class="form-check">
                            <input class="form-check-input transaction-check-tt" type="checkbox" name="transaction_ids[]" id="tableCheckOne" value="' . (($transaction->type == 'saving') ? 'save_' : 'withdraw_') . $transaction->transaction_id . '" />
                            <label class="form-check-label" for="tableCheckOne"></label>
                        </div>
                        <small class="status"></small>
                    </td>
                    <td>' . $i . '</td>
                    <td>' . ucwords($client_name) . ' (' . $transaction->account_number . ')</td>
                    <td>' . money($transaction->amount) . '</td>
                    <td>' .  ucwords($handler) . '</td>
                    <td>' . $type . '</td>
                    <td>' . $transaction->status . '</td>
                    <td>' . pretty_date_notime($transaction->transaction_date) . '</td>
                    <!-- '. ((admin_has_permission('approver')) ? '<td class="status">' . $options . '</td>' : '') .' -->
                </tr>
            ';
            $i++;
        }
        if (admin_has_permission('approver')) {
            $results_html .= '
                <tr>
                    <td colspan="9">
                        <div class="d-flex justify-content-between align-items-center">
                            <div id="message" class="text-primary mb-0"></div>
                            <div>
                                <button class="btn btn-success btn-sm" id="approve-btn">Approve Selected</button>
                                <button class="btn btn-danger btn-sm" id="reject-btn">Reject Selected</button>
                            </div>
                            <div id="loading" style="display: none;">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                <span>Processing...</span>
                            </div>
                        </div>
                    </td>
                </tr>
            ';
        }

        $results_stats = '
            <hr class="my-5" />
            <div class="alert alert-info small">Search results for: <i>' . $search_query . '</i></div>
            <div class="row mb-8">
                <div class="col-12 col-md-6 col-xxl-4 mb-4 mb-xxl-0">
                    <div class="card bg-body-tertiary border-transparent">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <!-- Heading -->
                                    <h4 class="fs-sm fw-normal text-body-secondary mb-1">Deposits</h4>

                                    <!-- Text -->
                                    <div class="fs-4 fw-semibold" id="filter-deposits">' . money($total_deposits) . '</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xxl-4 mb-4 mb-xxl-0">
                    <div class="card bg-body-tertiary border-transparent">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <!-- Heading -->
                                    <h4 class="fs-sm fw-normal text-body-secondary mb-1">Withdrawals</h4>

                                    <!-- Text -->
                                    <div class="fs-4 fw-semibold" id="filter-withdrawals">' . money($total_withdrawals) . '</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xxl-4 mb-4 mb-xxl-0" id="filter-total-transactions-container">
                    <div class="card bg-body-tertiary border-transparent">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <!-- Heading -->
                                    <h4 class="fs-sm fw-normal text-body-secondary mb-1">Total transactions</h4>

                                    <!-- Text -->
                                    <div class="fs-4 fw-semibold" id="filter-total-transactions">' . $total_data . '</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        ';
    }  else {
        $results_html .= '
            <tr>
                <td colspan="8">
                    <p class="text-muted">No transactions found matching the filter criteria.</p>
                </td>
            </tr>
        ';
    }

    $results_html .= '
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row align-items-center">
            <div class="col">
                <!-- Text -->
                <p class="text-body-secondary mb-0">Showing ' . $count_filter . ' items out of ' . $total_data . ' results found</p>
            </div>
            <div class="col-auto">
    ';

    if ($total_data > 0) {
        $results_html .= '
            <nav aria-label="Page navigation example">
                <ul class="pagination mb-0">
        ';

        $total_links = ceil($total_data / $limit);

        $previous_link = '';
        $next_link = '';
        $page_link = '';

        if ($total_links > 4) {
            if ($page < 5) {
                for ($count = 1; $count <= 5; $count++) {
                    $page_array[] = $count;
                }
                $page_array[] = '...';
                $page_array[] = $total_links;
            } else {
                $end_limit = $total_links - 5;
                if ($page > $end_limit) {
                    $page_array[] = 1;
                    $page_array[] = '...';

                    for ($count = $end_limit; $count <= $total_links; $count++) {
                        $page_array[] = $count;
                    }
                } else {
                    $page_array[] = 1;
                    $page_array[] = '...';
                    for ($count = $page - 1; $count <= $page + 1; $count++) {
                        $page_array[] = $count;
                    }
                    $page_array[] = '...';
                    $page_array[] = $total_links;
                }
            }
        } else {
            for ($count = 1; $count <= $total_links; $count++) {
                $page_array[] = $count;
            }
        }

        for ($count = 0; $count < count($page_array); $count++) {
            if ($page == $page_array[$count]) {
                $page_link .= '
                    <li class="page-item active">
                        <a class="page-link" href="javascript:;">'.$page_array[$count].'</a>
                    </li>
                ';

                $previous_id = $page_array[$count] - 1;
                if ($previous_id > 0) {
                    $previous_link = '
                        <li class="page-item">
                            <a class="page-link" href="javascript:;" data-page_number="'.$previous_id.'" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    ';
                } else {
                    $previous_link = '
                        <li class="page-item disabled">
                            <a class="page-link" href="javascript:;" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    ';
                }

                $next_id = $page_array[$count] + 1;
                if ($next_id >= $total_links) {
                    $next_link = '
                        <li class="page-item disabled">
                            <a class="page-link" href="javascript:;" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    ';
                } else {
                    $next_link = '
                        <li class="page-item">
                            <a class="page-link" href="javascript:;" aria-label="Next" data-page_number="'.$next_id.'">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    ';
                }

            } else {
                
                if ($page_array[$count] == '...') {
                    $page_link .= '
                        <li class="page-item disabled">
                            <a class="page-link" href="javascript:;">...</a>
                        </li>
                    ';
                } else {
                    $page_link .= '
                        <li class="page-item">
                            <a class="page-link page-link-go" href="javascript:;" data-page_number="'.$page_array[$count].'">'.$page_array[$count].'</a>
                        </li>
                    ';
                }
            }

        }

        $results_html .= $previous_link. $page_link . $next_link;
    }


    echo $results_stats;
    echo $results_html;
?>
                    
<script>
    // Toggle row highlight on checkbox change
    $(document).on('change', '.transaction-check-tt, .transaction-check, .transaction-check', function() {
        var $row = $(this).closest('tr');
        if (this.checked) {
            $row.addClass('table-warning');
        } else {
            $row.removeClass('table-warning');
        }
    });
    
    // Handle select all

    // select / unselect all
    $(document).on('change', '#select-all-transactions, #select-all', function() {
        var checked = this.checked;
        // apply to all matching checkboxes inside current DOM
        $('.transaction-check-tt, .transaction-check').each(function() {
            this.checked = checked;
            var $row = $(this).closest('tr');
            if (checked) $row.addClass('table-warning'); else $row.removeClass('table-warning');
        });
    });

    function setLoading(isLoading) {
        const approveBtn = document.getElementById("approve-btn");
        const rejectBtn = document.getElementById("reject-btn");
        const loadingDiv = document.getElementById("loading");

        if (isLoading) {
            approveBtn.disabled = true;
            rejectBtn.disabled = true;
            loadingDiv.style.display = "block";
        } else {
            approveBtn.disabled = false;
            rejectBtn.disabled = false;
            loadingDiv.style.display = "none";
        }
    }

    function processTransactions(action) {
        let selected = [];
        document.querySelectorAll(".transaction-check-tt:checked").forEach(cb => {
            selected.push(cb.value);
        });

        if (selected.length === 0) {
            $('.toast-body').html("⚠️ No transactions selected.");
            $('.toast').toast('show');
            $('.toast').removeClass('bg-success').addClass('bg-danger');
            return;
        }
        
        setLoading(true);

        fetch("<?= PROOT; ?>app/controller/transaction.approver.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ action: action, transactions: selected })
        })
        .then(res => res.json())
        .then(data => {
            $('.toast-body').html(data.message);
            $('.toast').toast('show');
            $('.toast').removeClass('bg-danger');

            if (data.updated) {
                data.updated.forEach(item => {
                    let row = document.querySelector(`tr[data-id='${item.id}']`);
                    let statusCell = row.querySelector(".status");

                    if (row && statusCell) {
                        statusCell.innerText = item.status;
                    }

                    // ✅ Change row color based on action
                    row.classList.remove("selected", "approved", "rejected");
                    if (action === "approve") {
                        row.classList.remove("table-warning");
                        row.classList.add("table-success");
                    } else if (action === "reject") {
                        row.classList.add("table-danger");
                    }

                });
            }

            // ✅ Uncheck all checkboxes after success
            document.getElementById("select-all-transactions").checked = false;
            document.querySelectorAll(".transaction-check-tt").forEach(cb => {
                cb.checked = false;
            });

        })
        .catch(err => console.error("Error:", err))
        .finally(() => setLoading(false));
    }

    // delegated clicks for approve/reject buttons to call page-level processTransactions if present
    $(document).on('click', '#approve-btn', function(e) {
        e.preventDefault();
        if (typeof processTransactions === 'function') {
            processTransactions('approve');
        } else {
            // fallback: trigger custom event so page-specific code can listen
            $(document).trigger('transactions:process', ['approve']);
        }
    });
    $(document).on('click', '#reject-btn', function(e) {
        e.preventDefault();
        if (typeof processTransactions === 'function') {
            processTransactions('reject');
        } else {
            $(document).trigger('transactions:process', ['reject']);
        }
    });

</script>

<script>
    $('#submit-export').on('click', function() {
        $('#submit-export').attr('disabled', true);
        $('#submit-export').text('Exporting ...');
        setTimeout(function () {
            $('#exportForm').submit();

            $('#submit-export').attr('disabled', false);
            $('#submit-export').text('Export');
            // location.reload();
        }, 2000)
    });

</script>