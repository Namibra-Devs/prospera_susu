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

    $query = "SELECT * FROM collectors ";
    $search_query = ((isset($_POST['query'])) ? sanitize($_POST['query']) : '');
    $find_query = str_replace(' ', '%', $search_query);
    if ($search_query != '') {
        $query .= '
            WHERE collector_name LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR collector_email LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR collector_phone LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR collector_address LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR collector_state LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR collector_city LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR collector_status LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR created_at LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR collector_id LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" ';

    }
    $query .= 'ORDER BY id ASC ';

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
                    <th>Product</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th colspan="2">Price</th>
                </thead>
                <tbody>
    ';

if ($total_data > 0) {
	$i = 1;
	foreach ($result as $row) {
        // check if collector has photo
        if ($row['collector_photo'] != '') {
            $photo = $row['collector_photo'];
        } else {
            $photo = PROOT . 'assets/media/avatar.png';
        }

		$output .= '
            <tr role="button" data-bs-toggle="offcanvas" data-bs-target="#orderModal" aria-controls="orderModal">
                <td style="width: 0px">
                    <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="tableCheckOne" />
                    <label class="form-check-label" for="tableCheckOne"></label>
                    </div>
                </td>
                <td class="text-body-secondary">#3456</td>
                <td>Apple MacBook Pro</td>
                <td>2021-08-12</td>
                <td><span class="badge bg-success-subtle text-success">Completed</span></td>
                <td>$2,499</td>
                <td style="width: 0px">
                    <div class="dropdown">
                    <button class="btn btn-sm btn-link text-body-tertiary" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="material-symbols-outlined scale-125">more_horiz</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">Action</a></li>
                        <li><a class="dropdown-item" href="#">Another action</a></li>
                        <li><a class="dropdown-item" href="#">Something else here</a></li>
                    </ul>
                    </div>
                </td>
                </tr>

            <!-- DELETE Expenditure -->
            <div class="modal fade" id="deleteModal_' . $row["id"] . '" tabindex="-1" aria-labelledby="deleteModalLabel_' . $row["id"] . '" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header border-bottom-0 pb-0">
                            <h1 class="modal-title fs-5" id="deleteModalLabel_' . $row["id"] . '">Delete collector</h1>
                            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>
                                Collector with name '. strtoupper($row['collector_name']) . ' will be deleted.
                                <br>Are you sure you want to proceed to this action.
                            </p>
                            <a href="' . PROOT . 'app/collectors?delete=' . $row["collector_id"] . '" class="btn btn-secondary w-100 mt-4">Delete</a>
                        </div>
                    </div>
                </div>
            </div>
		';
		$i++;
	}

} else {
	$output .= '
		<tr class="text-warning">
			<td colspan="6"> 
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
