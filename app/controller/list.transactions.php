<?php
// LIST AND SEARCH FOR transactions

require ('../../system/DatabaseConnector.php');
    $conn = $dbConnection;

    $limit = 10;
    $page = 1;

    if (isset($_POST['page']) && $_POST['page'] > 1) {
        $start = (($_POST['page'] - 1) * $limit);
        $page = $_POST['page'];
    } else {
        $start = 0;
    }

    // Get filters from POST
    $type = isset($_POST['type']) ? $_POST['type'] : '';
    $date_from = isset($_POST['date_from']) ? $_POST['date_from'] : '';
    $date_to = isset($_POST['date_to']) ? $_POST['date_to'] : '';
    $collector = isset($_POST['collector']) ? $_POST['collector'] : '';

    // merge both savings and withdrawals into one query
    $query = "
        SELECT * FROM (
            SELECT 
                saving_id AS transaction_id, 
                saving_customer_id AS customer_id, 
                saving_customer_account_number AS account_number,
                saving_collector_id AS collector_id, 
                saving_amount AS amount, 
                saving_date_collected AS transaction_date, 
                saving_status AS status, 
                'saving' AS type, 
                saving_advance_id AS advance_id, 
                created_at FROM savings 
                UNION ALL 
                    SELECT 
                        withdrawal_id AS transaction_id, 
                        withdrawal_customer_id AS customer_id, 
                        withdrawal_customer_account_number AS account_number, 
                        withdrawal_approver_id AS collector_id, 
                        withdrawal_amount_requested AS amount, 
                        withdrawal_date_requested AS transaction_date, 
                        withdrawal_status AS status, 
                        'withdrawal' AS type, 
                        withdrawal_advance_id AS advance_id, 
                        created_at FROM withdrawals
            ) 
        AS transactions WHERE ";
    // check if a collector is logged in, then show only their transactions
    if (admin_has_permission('collector') && !admin_has_permission('admin')) {
        $query .= " collector_id = '". $admin_id . "' ";
    } else {
        $query .= " 1=1 ";
    }

    // Transaction type filter
    if ($type && $type != 'all') {
        if ($type == 'deposit') {
            $query .= " AND type = 'saving' ";
        } elseif ($type == 'withdrawal') {
            $query .= " AND type = 'withdrawal' ";
        }
    }

    // Date filter
    if ($date_from && $date_to) {
        $query .= " AND transaction_date BETWEEN '$date_from' AND '$date_to' ";
    } elseif ($date_from) {
        $query .= " AND transaction_date >= '$date_from' ";
    } elseif ($date_to) {
        $query .= " AND transaction_date <= '$date_to' ";
    }

    // Collector filter
    if ($collector) {
        $query .= " AND collector_id IN (SELECT admin_id FROM susu_admins WHERE admin_id = '$collector') ";
    }

    // search query
    $search_query = ((isset($_POST['query'])) ? sanitize($_POST['query']) : '');
    $find_query = str_replace(' ', '%', $search_query);
    if ($search_query != '') {
        $q = str_replace(' ', '%', $_POST['query']);
        $query .= '
            AND (transaction_id LIKE "%'.$q.'%" 
            OR amount LIKE "%'.$q.'%" 
            OR account_number LIKE "%'.$q.'%" 
            OR transaction_date LIKE "%'.$q.'%" 
            OR status LIKE "%'.$q.'%" 
            OR type LIKE "%'.$q.'%" 
            OR created_at LIKE "%'.$q.'%")
        ';
    }

    $query .= 'ORDER BY created_at DESC ';

    // Fetch ALL matching rows (without LIMIT) so we can detect advance groups across the whole result set
    $statement = $conn->prepare($query);
    $statement->execute();
    $all_rows = $statement->fetchAll(PDO::FETCH_ASSOC);

    // detect advance groups (advance_id not empty)
    $advance_ids = [];
    $advance_groups = []; // advance_id => ['rows'=>[], 'sum'=>float]
    foreach ($all_rows as $r) {
        $aid = isset($r['advance_id']) ? $r['advance_id'] : null;
        if ($aid !== null && $aid !== '' ) {
            if (!isset($advance_groups[$aid])) {
                $advance_groups[$aid] = ['rows' => [], 'sum' => 0.0];
                $advance_ids[] = $aid;
            }
            $advance_groups[$aid]['rows'][] = $r;
            $advance_groups[$aid]['sum'] += (float)$r['amount'];
        }
    }

    // fetch advance summary rows from saving_advance table for detected advance_ids
    $advance_records = [];
    if (!empty($advance_ids)) {
        $placeholders = implode(',', array_fill(0, count($advance_ids), '?'));
        $sqlAdv = "SELECT * FROM saving_advance WHERE advance_id IN ($placeholders)";
        $stmtAdv = $conn->prepare($sqlAdv);
        $stmtAdv->execute($advance_ids);
        $advRes = $stmtAdv->fetchAll(PDO::FETCH_ASSOC);
        foreach ($advRes as $ar) {
            $advance_records[$ar['advance_id']] = $ar;
        }
    }

    // Build a combined display list:
    // - For each advance_id group, produce a single synthetic row (type 'advance') with summed amount.
    // - For rows that are not part of an advance group, keep them as-is.
    $display_list = [];
    $seen_advance = [];
    foreach ($all_rows as $r) {
        $aid = isset($r['advance_id']) ? $r['advance_id'] : null;
        if ($aid !== null && $aid !== '') {
            if (isset($seen_advance[$aid])) {
                // skip individual row because we will render a single combined row for this advance id
                continue;
            }
            $seen_advance[$aid] = true;

            // build synthetic advance row
            $group = $advance_groups[$aid] ?? ['sum' => (float)$r['amount'], 'rows' => [$r]];
            $advRec = $advance_records[$aid] ?? null;

            // choose representative values from first row in group
            $rep = $group['rows'][0];

            $synthetic = [
                'transaction_id' => 'advance_' . $aid,
                'customer_id'    => $rep['customer_id'],
                'account_number' => $rep['account_number'],
                'collector_id'   => $rep['collector_id'],
                'amount'         => $group['sum'],
                'transaction_date' => $advRec['created_at'] ?? $rep['transaction_date'],
                'status'         => $advRec['advance_status'] ?? $rep['status'],
                'type'           =>  (($rep['type'] == 'withdrawal')) ? 'advance_withdrawal' : 'advance_deposit', // check if it is advance deposit or withdrawal
                'advance_id'     => $aid,
                'created_at'     => $advRec['created_at'] ?? $rep['created_at']
            ];
            $display_list[] = $synthetic;
        } else {
            // normal non-advance row
            $display_list[] = $r;
        }
    }

    // sort display_list by created_at DESC to match original ORDER BY
    usort($display_list, function($a, $b) {
        $ta = isset($a['created_at']) ? strtotime($a['created_at']) : 0;
        $tb = isset($b['created_at']) ? strtotime($b['created_at']) : 0;
        return $tb - $ta;
    });

    // pagination on display_list
    $total_data = count($display_list);
    $count_filter = min($limit, $total_data - $start);
    if ($start < 0) $start = 0;
    $result = array_slice($display_list, $start, $limit);

    $output = '
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 0px">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="select-all" />
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
                        '. ((admin_has_permission('approver')) ? '<th class="fs-sm"></th>' : '') .'
                    </tr>
                </thead>
                <tbody>
    ';

if ($total_data > 0) {
    $i = $start + 1;
    foreach ($result as $row) {

        // handle synthetic advance row
        if (isset($row['type']) && ($row['type'] === 'advance' || $row['type'] === 'advance_deposit' || $row['type'] === 'advance_withdrawal')) {

            $advance_Type = ($row['type'] === 'advance_withdrawal') ? 'Withdrawal' : 'Deposit';

            // client and handler names (use representative values)
            $client_name = findCustomerByAccountNumber($row['account_number'])->customer_name ?? 'Unknown';
            $handler = findAdminById($row['collector_id'])->admin_name ?? 'Admin';

            $options = '';
            $amount_display = money($row['amount']);
            $status_display = htmlspecialchars($row['status']);
            // if you want badge for advance status
            if ($row['status'] == 'Pending') {
                $status_display = '<span class="badge bg-warning-subtle text-warning">Pending</span>';
            } elseif ($row['status'] == 'Approved') {
                $status_display = '<span class="badge bg-success-subtle text-success">Approved</span>';
            } elseif ($row['status'] == 'Rejected') {
                $status_display = '<span class="badge bg-danger-subtle text-danger">Rejected</span>';
            }

            // display combined advance row
            $output .= '<tr class="table-info" data-id="advance_'.$row['advance_id'].'">';
            $output .= '
                <td style="width: 0px">
                    <div class="form-check">
                        <input class="form-check-input transaction-check" type="checkbox" name="transaction_ids[]" value="' . ($row['type']) . '_' . htmlspecialchars($row['advance_id'], ENT_QUOTES) . '" />
                        <label class="form-check-label"></label>
                    </div>
                </td>
                <td>' . $i . '</td>
                <td>' . ucwords($client_name) . ' (' . $row['account_number'] . ')</td>
                <td>' . $amount_display . ' <span class="badge bg-secondary ms-2">Advance payment</span></td>
                <td>' . ucwords($handler) . '</td>
                <td><span class="fs-sm text-primary">' . $advance_Type . '</span></td>
                <td>' . $status_display . '</td>
                <td>' . pretty_date_notime($row['transaction_date']) . '</td>
                '. ((admin_has_permission('approver')) ? '<td class="status">' . $options . '</td>' : '') .'
            ';
            $output .= '</tr>';
            $i++;
            continue;
        }

        // normal saving/withdrawal row
        $client_name = findCustomerByAccountNumber($row['account_number'])->customer_name ?? 'Unknown';
        $handler = findAdminById($row['collector_id'])->admin_name ?? 'Admin';

        $options = '';

        // get type of transaction
        $type_label = 'Unknown';
        if ($row['type'] == 'saving') {
            $type_label = '<span class="fs-sm text-info">Deposit</span>';

            // check status of deposit transactions
            if ($row['status'] == 'Pending') {
                $status_html = '<span class="badge bg-warning-subtle text-warning">Pending</span>';
            } elseif ($row['status'] == 'Approved') {
                $status_html = '<span class="badge bg-success-subtle text-success">Approved</span>';
            } elseif ($row['status'] == 'Rejected') {
                $status_html = '<span class="badge bg-danger-subtle text-danger">Rejected</span>';
            } else {
                $status_html = htmlspecialchars($row['status']);
            }

            // show approve/reject links for approver if pending
            if ($status_html === '<span class="badge bg-warning-subtle text-warning">Pending</span>') {
                $options .= ' <a href="' . PROOT . 'app/transactions?d=1&approved=' . $row["transaction_id"] . '" class="btn btn-sm btn-light" onclick="return confirm(\'Are you sure you want to APPROVE this Deposit Transaction?\');">Approve</a>';
                $options .= ' <a href="' . PROOT . 'app/transactions?d=1&reject=' . $row["transaction_id"] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to REJECT this Deposit Transaction?\');">Reject</a>';
            }

        } elseif ($row['type'] == 'withdrawal') {
            $type_label = '<span class="fs-sm text-warning">Withdrawal</span>';

            if ($row['status'] == 'Pending') {
                $status_html = '<span class="badge bg-warning-subtle text-warning">Pending</span>';
            } elseif ($row['status'] == 'Approved') {
                $status_html = '<span class="badge bg-success-subtle text-success">Approved</span>';
            } elseif ($row['status'] == 'Paid') {
                $status_html = '<span class="badge bg-primary-subtle text-primary">Paid</span>';
            } elseif ($row['status'] == 'Rejected') {
                $status_html = '<span class="badge bg-danger-subtle text-danger">Rejected</span>';
            } else {
                $status_html = htmlspecialchars($row['status']);
            }

            if ($status_html === '<span class="badge bg-warning-subtle text-warning">Pending</span>') {
                $options .= ' <a href="' . PROOT . 'app/transactions?w=1&approved=' . $row["transaction_id"] . '" class="btn btn-sm btn-warning" onclick="return confirm(\'Are you sure you want to APPROVE this Withdrawal Transaction?\');">Approve</a>';
                $options .= ' <a href="' . PROOT . 'app/transactions?w=1&reject=' . $row["transaction_id"] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to REJECT this Withdrawal Transaction?\');">Reject</a>';
            }
            if ($status_html === '<span class="badge bg-success-subtle text-success">Approved</span>') {
                $options .= ' <a href="'.PROOT.'app/transactions?w=1&paid='.$row["transaction_id"].'" class="btn btn-sm btn-success" onclick="return confirm(\'Are you sure you want to set this Withdrawal Transaction as PAID?\');">Mark as Paid</a>';
            }

        } else {
            $status_html = htmlspecialchars($row['status']);
        }

        // set background color for today transactions
        $row_class = (date('Y-m-d', strtotime($row['transaction_date'])) == date('Y-m-d')) ? 'table-success' : '';

        $output .= '<tr '. ($row_class ? 'class="'.$row_class.'"' : '') .' data-id="' . (($row['type'] == 'saving') ? 'save_' : 'withdraw_') . $row["transaction_id"] . '">';
        $output .= '
                <td style="width: 0px">
                    <div class="form-check">
                        <input class="form-check-input transaction-check" type="checkbox" name="transaction_ids[]" value="' . (($row['type'] == 'saving') ? 'save_' : 'withdraw_') . $row["transaction_id"] . '" />
                        <label class="form-check-label"></label>
                    </div>
                </td>
                <td>' . $i . '</td>
                <td>' . ucwords($client_name) . ' (' . $row['account_number'] . ')</td>
                <td>' . money($row["amount"]) . '</td>
                <td>' .  ucwords($handler) . '</td>
                <td>' . $type_label . '</td>
                <td>' . $status_html . '</td>
                <td>' . pretty_date_notime($row['transaction_date']) . '</td>
                '. ((admin_has_permission('approver')) ? '<td class="status">' . $options . '</td>' : '') .' 
            </tr>
        ';
        $i++;
    }

    if (admin_has_permission('approver')) {
        $output .= '
            <div id="message" style="margin-top:10px;"></div>
            <div id="loading" style="display:none; color:blue;">⏳ Processing...</div>
            <tr>
                <td colspan="10"> 
                    <button class="btn btn-sm" id="approve-btn-new">✅ Approve Selected</button>
                    <button class="btn btn-sm" id="reject-btn-new">❌ Reject Selected</button>
                </td>
            </tr>
        ';
    }
} else {
    $output .= '
        <tr class="text-warning">
            <td colspan="10"> 
                <div class="alert alert-info">No data found!</div>
            </td>
        </tr>
    ';
}

$output .= '
                </tbody>
            </table>
        </div>
    </div>
    <div class="row align-items-center">
        <div class="col">
            <!-- Text -->
            <p class="text-body-secondary mb-0">Showing ' . min($limit, $count_filter) . ' items out of ' . $total_data . ' results found</p>
        </div>
        <div class="col-auto">
';

if ($total_data > 0) {
    $output .= '
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

    $output .= $previous_link. $page_link . $next_link;
}

echo $output . '
                    </ul>
                </nav>
            </div>
        </div>
    ';
?>
<script>
    // delegated checkbox highlight
    $(document).on('change', '.transaction-check', function() {
        var $row = $(this).closest('tr');
        if (this.checked) {
            $row.addClass('table-warning');
        } else {
            $row.removeClass('table-warning');
        }
    });

    // delegated select-all
    $(document).on('change', '#select-all', function() {
        var checked = this.checked;
        $('.transaction-check').each(function() {
            this.checked = checked;
            var $row = $(this).closest('tr');
            if (checked) $row.addClass('table-warning'); else $row.removeClass('table-warning');
        });
    });

    function setLoading(isLoading) {
        const approveBtn = document.getElementById("approve-btn-new");
        const rejectBtn = document.getElementById("reject-btn-new");
        const loadingDiv = document.getElementById("loading");
        if (!approveBtn || !rejectBtn || !loadingDiv) return;
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
        document.querySelectorAll(".transaction-check:checked").forEach(cb => {
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
                    if (!row) return;
                    let statusCell = row.querySelector(".status");
                    if (statusCell) statusCell.innerText = item.status;

                    row.classList.remove("selected", "approved", "rejected");
                    if (action === "approve") {
                        row.classList.remove("table-warning");
                        row.classList.add("table-success");
                    } else if (action === "reject") {
                        row.classList.add("table-danger");
                    }
                });
            }

            // Uncheck all checkboxes after success
            $('#select-all').prop('checked', false);
            $('.transaction-check').prop('checked', false);
        })
        .catch(err => console.error("Error:", err))
        .finally(() => setLoading(false));
    }

    // delegated clicks
    $(document).on('click', '#approve-btn-new', function(e) {
        e.preventDefault();
        processTransactions('approve');
    });
    $(document).on('click', '#reject-btn-new', function(e) {
        e.preventDefault();
        processTransactions('reject');
    });
</script>