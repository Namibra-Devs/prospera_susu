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

    // Get filters from POST
    $id = isset($_POST['date_from']) ? sanitize($_POST['id']) : '';
    $date_from = isset($_POST['date_from']) ? sanitize($_POST['date_from']) : '';
    $date_to = isset($_POST['date_to']) ? sanitize($_POST['date_to']) : '';

    // merge both savings and withdrawals into one query
    $query = "
        SELECT * FROM savings AS transactions 
        WHERE saving_collector_id = '" . $id . "'
    ";

    // Date filter
    if ($date_from && $date_to) {
        $query .= " AND created_at BETWEEN '$date_from' AND '$date_to' ";
    } elseif ($date_from) {
        $query .= " AND created_at >= '$date_from' ";
    } elseif ($date_to) {
        $query .= " AND created_at <= '$date_to' ";
    }

    // search query
    $search_query = ((isset($_POST['query'])) ? sanitize($_POST['query']) : '');
    $find_query = str_replace(' ', '%', $search_query);
    if ($search_query != '') {
        $query .= '
            AND (saving_id LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR saving_amount LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR saving_customer_account_number LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR created_at LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR saving_status LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
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
                        <th class="fs-sm"></th>
                        <th class="fs-sm">Client</th>
                        <th class="fs-sm">Amount</th>
                        <th class="fs-sm">Handler</th>
                        <th class="fs-sm">Type</th>
                        <th class="fs-sm">Status</th>
                        <th class="fs-sm">Date</th>
                    </tr>
                </thead>
                <tbody>
    ';

if ($total_data > 0) {
	$i = 1;
	foreach ($result as $row) {

        // get customer name
        $client_name = findCustomerByAccountNumber($row['saving_customer_account_number'])->customer_name;
        if (!$client_name) {
            $client_name = 'Unknown';
        }

        // get handler name
        $handler = findAdminById($row['saving_collector_id'])->admin_name;
        if (!$handler) {
            $handler = 'Admin';
        }


        // get type of transaction
        $type = '<span class="fs-sm text-info">Deposit</span>';

        // check status of deposite transactions
        if ($row['saving_status'] == 'Pending') {
            $row['saving_status'] = '<span class="badge bg-warning-subtle text-warning">Pending</span>';
        } elseif ($row['saving_status'] == 'Approved') {
            $row['saving_status'] = '<span class="badge bg-success-subtle text-success">Approved</span>';
        } elseif ($row['saving_status'] == 'Rejected') {
            $row['saving_status'] = '<span class="badge bg-danger-subtle text-danger">Rejected</span>';
        }
        
        // set background color for all today transactions
        if (date('Y-m-d', strtotime($row['created_at'])) == date('Y-m-d')) {
            $output .= '<tr class="table-success" data-id="save_' . $row["saving_id"] . '">';
        } else {
            $output .= '<tr data-id="save_ '. $row["saving_id"] . '">';
        }

		$output .= '
                <td>' . $i . '</td>
                <td>' . ucwords($client_name) . ' (' . $row['saving_customer_account_number'] . ')</td>
                <td>' . money($row["saving_amount"]) . '</td>
                <td>' .  ucwords($handler) . '</td>
                <td>' . $type . '</td>
                <td>' . $row['saving_status'] . '</td>
                <td>' . pretty_date_notime($row['created_at']) . '</td>
            </tr>
		';
		$i++;
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