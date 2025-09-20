<?php 

// LIST AND SEARCH FOR COLLECTORS

require ('../../system/DatabaseConnector.php');
$conn = $dbConnection;

    $today = date("Y-m-d");
    $limit = 10;
    $page = 1;

    if ($_POST['page'] > 1) {
        $start = (($_POST['page'] - 1) * $limit);
        $page = $_POST['page'];
    } else {
        $start = 0;
    }

    $query = "SELECT * FROM customers ";
    $search_query = ((isset($_POST['query'])) ? sanitize($_POST['query']) : '');
    $find_query = str_replace(' ', '%', $search_query);
    if ($search_query != '') {
        $query .= '
            WHERE customer_name LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR customer_phone LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR customer_email LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR customer_address LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR customer_region LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR customer_city LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR customer_id_number LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR customer_id_type LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR customer_start_date LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR customer_status LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR created_at LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR customer_added_by LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR customer_collector_id LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" ';

    }
    $query .= 'ORDER BY customer_name ASC ';

    $filter_query = $query . 'LIMIT ' . $start . ', ' . $limit . '';

    $statement = $conn->prepare($query);
	$statement->execute();
	$total_data = $statement->rowCount();

	$statement = $conn->prepare($filter_query);
	$statement->execute();
	$result = $statement->fetchAll();
    $count_filter = $statement->rowCount();

    $output = '
        <div class="table-responsive mb-7">
            <table class="table table-hover table-select table-round align-middle mb-0">
                <thead>
                    <th style="width: 0px">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="tableCheckAll" />
                            <label class="form-check-label" for="tableCheckAll"></label>
                        </div>
                    </th>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Default amount</th>
                    <th>Address</th>
                    <th>Start date</th>
                    <th>Joined date</th>
                    <th>Status</th>
                    <th colspan="2">Total saved</th>
                </thead>
                <tbody>
    ';

if ($total_data > 0) {
	$i = 1;
	foreach ($result as $row) {
        // check status of customer
        if ($row['customer_status'] == 'active') {
            $status_badge = '<span class="badge bg-success-subtle text-success">Active</span>';
        } elseif ($row['customer_status'] == 'inactive') {
            $status_badge = '<span class="badge bg-warning-subtle text-warning">Inactive</span>';
        } elseif ($row['customer_status'] == 'suspended') {
            $status_badge = '<span class="badge bg-danger-subtle text-danger">Suspended</span>';
        } else {
            $status_badge = '<span class="badge bg-secondary-subtle text-secondary">Unknown</span>';
        }

        // get total saved by customer
        $total_saved = 0;
        // $query_total = "SELECT SUM(payment_amount) AS total FROM payments WHERE payment_customer_id = ? AND payment_status = 'completed'";
        // $statement_total = $conn->prepare($query_total);
        // $statement_total->execute([$row['customer_id']]);
        // $result_total = $statement_total->fetchAll();
        // if ($statement_total->rowCount() > 0) {
        //     $total_saved = $result_total[0]['total'];
        // }

		$output .= '
            <tr class="align-middle">
                <td style="width: 0px">' . $i . '</td>
                <td class="text-body-secondary">#3456</td>
                <td>' . ucwords($row["customer_name"]) . '</td>
                <td>' . $row['customer_phone'] . '</td>
                <td>' . money($row['customer_default_daily_amount']) . '</td>
                <td>' . $row['customer_address'] . '</td>
                <td>' . pretty_date_notime($row['customer_start_date']) . '</td>
                <td>' . pretty_date_notime($row['created_at']) . '</td>
                <td>' . $status_badge . '</td>
                <td>' . money($total_saved) . '</td>
                <td style="width: 0px"><a href="' . PROOT . 'app/customers/' . $row["customer_id"] . '" class="btn btn-secondary w-100 mt-4"><span class="material-symbols-outlined">visibility</span></a></td>
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
