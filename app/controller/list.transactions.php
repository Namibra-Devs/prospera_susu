<?php 

// LIST AND SEARCH FOR transactions

require ('../../system/DatabaseConnector.php');
    $conn = $dbConnection;

    $limit = 10;
    $page = 1;

    if ($_POST['page'] > 1) {
        $start = (($_POST['page'] - 1) * $limit);
        $page = $_POST['page'];
    } else {
        $start = 0;
    }

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
                        created_at FROM withdrawals
            ) 
        AS transactions WHERE ";
    // check if a collector is logged in, then show only their transactions
    if (admin_has_permission('collector') && !admin_has_permission('admin')) {
        $query .= " collector_id = '". $admin_id . "' ";
    } else {
        $query .= " 1=1 ";
    }
    // search query
    $search_query = ((isset($_POST['query'])) ? sanitize($_POST['query']) : '');
    $find_query = str_replace(' ', '%', $search_query);
    if ($search_query != '') {
        $query .= '
            AND (transaction_id LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR amount LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR account_number LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR transaction_date LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR status LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR type LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR created_at LIKE "%'.str_replace(' ', '%', $_POST['query']).'%")
		';

    }

    $query .= 'ORDER BY created_at DESC ';

    $filter_query = $query . 'LIMIT ' . $start . ', ' . $limit . '';

    $statement = $conn->prepare($query);
	$statement->execute();
	$total_data = $statement->rowCount();

	$statement = $conn->prepare($filter_query);
	$statement->execute();
	$result = $statement->fetchAll();
    $count_filter = $statement->rowCount();

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
                        '. ((admin_has_permission()) ? '<th class="fs-sm"></th>' : '') .'
                    </tr>
                </thead>
                <tbody>
    ';

if ($total_data > 0) {
	$i = 1;
	foreach ($result as $row) {

        // get customer name
        $client_name = findCustomerByAccountNumber($row['account_number'])->customer_name;
        if (!$client_name) {
            $client_name = 'Unknown';
        }

        // get handler name
        $handler = findAdminById($row['collector_id'])->admin_name;
        if (!$handler) {
            $handler = 'Admin';
        }

        $options = '';

        // get type of transaction
        $type = 'Unknown';
        if ($row['type'] == 'saving') {
            $type = '<span class="fs-sm text-info">Deposit</span>';

            // check status of deposite transactions
            if ($row['status'] == 'Pending') {
                $row['status'] = '<span class="badge bg-warning-subtle text-warning">Pending</span>';
            } elseif ($row['status'] == 'Approved') {
                $row['status'] = '<span class="badge bg-success-subtle text-success">Approved</span>';
            } elseif ($row['status'] == 'Rejected') {
                $row['status'] = '<span class="badge bg-danger-subtle text-danger">Rejected</span>';
            }

            // show approve button if status is pending
            if ($row['status'] == '<span class="badge bg-warning-subtle text-warning">Pending</span>') {
                $options .= ' <a href="' . PROOT . 'app/transactions?d=1&approved=' . $row["transaction_id"] . '" class="btn btn-sm btn-light" onclick="return confirm(\'Are you sure you want to APPROVE this Deposite Transaction?\');">Approve</a>';
            }
        } elseif ($row['type'] == 'withdrawal') {
            $type = '<span class="fs-sm text-warning">Withdrawal</span>';

            // check status of withdrawal transactions
            if ($row['status'] == 'Pending') {
                $row['status'] = '<span class="badge bg-warning-subtle text-warning">Pending</span>';
            } elseif ($row['status'] == 'Approved') {
                $row['status'] = '<span class="badge bg-success-subtle text-success">Approved</span>';
            } elseif ($row['status'] == 'Paid') {
                $row['status'] = '<span class="badge bg-primary-subtle text-primary">Paid</span>';
            } elseif ($row['status'] == 'Rejected') {
                $row['status'] = '<span class="badge bg-danger-subtle text-danger">Rejected</span>';
            }

            // show approve button if status is pending
            if ($row['status'] == '<span class="badge bg-warning-subtle text-warning">Pending</span>') {
                $options .= ' <a href="' . PROOT . 'app/transactions?w=1&approved=' . $row["transaction_id"] . '" class="btn btn-sm btn-warning" onclick="return confirm(\'Are you sure you want to APPROVE this Withdrawal Transaction?\');">Approve</a>';
                $options .= ' <a href="' . PROOT . 'app/transactions?w=1&reject=' . $row["transaction_id"] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to REJECT this Withdrawal Transaction?\');">Reject</a>';
            }

            // show paid button if status is approved
            if ($row['status'] == '<span class="badge bg-success-subtle text-success">Approved</span>') {
                $options .= ' <a href="'.PROOT.'app/transactions?w=1&paid='.$row["transaction_id"].'" class="btn btn-sm btn-success" onclick="return confirm(\'Are you sure you want to set this Withdrawal Transaction as PAID?\');">Mark as Paid</a>';
            }
        }
        // set background color for all today transactions
        if (date('Y-m-d', strtotime($row['transaction_date'])) == date('Y-m-d')) {
            $output .= '<tr class="table-success" data-id="' . (($row['type'] == 'saving') ? 'save_' : 'withdraw_') . $row["transaction_id"] . '">';
        } else {
            $output .= '<tr data-id="' . (($row['type'] == 'saving') ? 'save_' : 'withdraw_') . $row["transaction_id"] . '">';
        }

		$output .= '
                <td style="width: 0px">
                    <div class="form-check">
                        <input class="form-check-input transaction-check" type="checkbox" name="transaction_ids[]" id="tableCheckOne" value="' . (($row['type'] == 'saving') ? 'save_' : 'withdraw_') . $row["transaction_id"] . '" />
                        <label class="form-check-label" for="tableCheckOne"></label>
                    </div>
                </td>
                <td>' . $i . '</td>
                <td>' . ucwords($client_name) . ' (' . $row['account_number'] . ')</td>
                <td>' . money($row["amount"]) . '</td>
                <td>' .  ucwords($handler) . '</td>
                <td>' . $type . '</td>
                <td>' . $row['status'] . '</td>
                '. ((admin_has_permission()) ? '<td class="status">' . $options . '</td>' : '') .' 
            </tr>
		';
		$i++;
	}
    if (admin_has_permission()) {
        $output .= '
            <div id="message" style="margin-top:10px;"></div>
            <div id="loading" style="display:none; color:blue;">⏳ Processing...</div>
            <tr colspan="10">
                <button class="btn btn-sm" id="approve-btn">✅ Approve Selected</button>
                <button class="btn btn-sm" id="reject-btn">❌ Reject Selected</button>
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
            <p class="text-body-secondary mb-0">Showing ' . $count_filter . ' items out of ' . $total_data . ' results found</p>
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
   document.getElementById("select-all").addEventListener("change", function(e) {
        document.querySelectorAll(".transaction-check").forEach(cb => {
            cb.checked = e.target.checked;
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
        document.querySelectorAll(".transaction-check:checked").forEach(cb => {
            selected.push(cb.value);
        });

        if (selected.length === 0) {
            $('.toast-body').html("⚠️ No transactions selected.");
            $('.toast').toast('show');
            $('.toast').removeClass('bg-success').addClass('bg-danger');
           //  alert();
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
            console.log(data.message);
            $('.toast-body').html(data.message);
            $('.toast').toast('show');
            $('.toast').removeClass('bg-danger');

            if (data.updated) {
                data.updated.forEach(item => {
                    let row = document.querySelector(`tr[data-id='${item.id}'] .status`);
                    if (row) row.innerText = item.status;
                });
            }

            // ✅ Uncheck all checkboxes after success
            document.getElementById("select-all").checked = false;
            document.querySelectorAll(".transaction-check").forEach(cb => {
                cb.checked = false;
            });

        })
        .catch(err => console.error("Error:", err))
        .finally(() => setLoading(false));
    }

    document.getElementById("approve-btn").addEventListener("click", () => processTransactions("approve"));
    document.getElementById("reject-btn").addEventListener("click", () => processTransactions("reject"));

</script>