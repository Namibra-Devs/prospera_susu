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

    $query = "SELECT * FROM susu_admins WHERE admin_permissions = 'collector' AND admin_status = 'active' ";
    $search_query = ((isset($_POST['query'])) ? sanitize($_POST['query']) : '');
    $find_query = str_replace(' ', '%', $search_query);
    if ($search_query != '') {
        $query .= '
            AND admin_name LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR admin_email LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR admin_phone LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR admin_address LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR admin_state LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR admin_city LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR admin_status LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR created_at LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR admin_id LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" ';

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
                    <th>Collector</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Location</th>
                    <th colspan="2">Joined date</th>
                </thead>
                <tbody>
    ';

if ($total_data > 0) {
	$i = 1;
	foreach ($result as $row) {
        // check if collector has photo
        if ($row['admin_profile'] != '') {
            $photo = $row['admin_profile'];
        } else {
            $photo = PROOT . 'assets/media/avatar.png';
        }

		$output .= '
            <tr onclick="window.location.href="javascript:;" role="link" tabindex="0">
                <td style="width: 0px">' . $i . '</td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar">
                            <img class="avatar-img" src="' . $photo . '" alt="..." />
                        </div>
                        <div class="ms-4">
                            <div>'. ucwords($row["admin_name"]) .'</div>
                            <div class="fs-sm text-body-secondary">
                                <a class="text-reset" href="mailto:'. ($row['admin_email']) .'">'. ($row['admin_email']) .'</a>
                            </div>
                        </div>
                    </div>
                </td>
                <td>
                    <a class="text-muted" href="tel:'. ($row["admin_phone"]) .'">'. ($row["admin_phone"]) .'</a>
                </td>
                <td>'. ($row["admin_address"]) .'</td>
                <td>'. $row["admin_state"] .', ' . $row["admin_city"] . '</td>
                <td>'. pretty_date_notime($row["created_at"]) . '</td>
                <td style="width: 0px">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-link text-body-tertiary" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="material-symbols-outlined scale-125">more_horiz</span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="'. PROOT . 'app/collectors/' . $row["admin_id"] .'">View</a></li>
                            <!-- <li><a class="dropdown-item" href="' . PROOT . 'app/collectors?edit=' . $row["admin_id"] . '">Update</a></li> -->
                            <li><a class="dropdown-item" href="#deleteModal_'. $row["id"] . '" data-bs-toggle="modal">Delete</a></li>
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
                                Collector with name '. strtoupper($row['admin_name']) . ' will be deleted.
                                <br>Are you sure you want to proceed to this action.
                            </p>
                            <a href="' . PROOT . 'app/collectors?delete=' . $row["admin_id"] . '" class="btn btn-secondary w-100 mt-4">Delete</a>
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
