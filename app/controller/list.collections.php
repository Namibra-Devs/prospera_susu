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

    $query = "SELECT * FROM daily_collections WHERE ";
	// check if a collector is logged in, then show only their transactions
    if (collector_is_logged_in()) {
        global $collector_id;
        $query .= " daily_collector_id = '". $collector_id . "' ";
    } else {
        $query .= " 1=1 ";
    }

	// search query
    $search_query = ((isset($_POST['query'])) ? sanitize($_POST['query']) : '');
    $find_query = str_replace(' ', '%', $search_query);
    if ($search_query != '') {
        $query .= '
            AND (daily_id LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR daily_collector_id LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR daily_collection_date LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR daily_total_collected LIKE "%'.str_replace(' ', '%', $_POST['query']).'%" 
            OR customer_collector_id LIKE "%'.str_replace(' ', '%', $_POST['query']).'%") 
		';
    }
    $query .= 'ORDER BY daily_collection_date DESC ';

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
					<th>Title</th>
					<th>Status</th>
					<th>Uploader</th>
					<th colspan="2">Handler(Verifier)</th>
				</thead>
				<tbody>
    ';

if ($total_data > 0) {
	$i = 1;
	foreach ($result as $row) {

        // check status of collection
        if ($row['daily_status'] == 'Verified') {
            $status_badge = '<span class="badge bg-success-subtle text-success">Verified</span>';
        } elseif ($row['daily_status'] == 'Pending') {
            $status_badge = '<span class="badge bg-warning-subtle text-warning">Pending</span>';
        } else {
            $status_badge = '<span class="badge bg-secondary-subtle text-secondary">Unknown</span>';
        }

		// find uploader name
		$uploader = 'Unknown';
		$uploader_row = findCollectorById($row['daily_collector_id']);
		if ($uploader_row) {
			$uploader = $uploader_row->collector_name;
		}

		// find handler/verifier name
		$handler = 'N/A';
		if ($row['daily_verified_by'] != null) {
			$handler_row = findAdminById($row['daily_verified_by']);
			if ($handler_row) {
				$handler = $handler_row['admin_name'];
			}
		}

		// options for admin only
		$options = '';
		if (admin_is_logged_in()) {
			if ($row['daily_status'] == 'Pending') {
				$options .= '<li><a class="dropdown-item" href="'. PROOT .'app/collection-verify?id='. $row['daily_id'] .'">Verify collection</a></li>';
			}
		}

		// options for collector and admin
		if (collector_is_logged_in() || admin_is_logged_in()) {
			$options .= '
				<li><a class="dropdown-item" href="#viewModal_'. $row['id'] .'" data-bs-toggle="modal">View details</a></li>
				<li><hr class="dropdown-divider" /></li>
				<li><a class="dropdown-item text-danger" href="'. PROOT .'app/collection-delete?id='. $row['daily_id'] .'" onclick="return confirm(\'Are you sure you want to delete this collection record?\');">Delete collection</a></li>
			';
		}

		// set background color for all today transactions
        if (date('Y-m-d', strtotime($row['daily_collection_date'])) == date('Y-m-d')) {
            $output .= '<tr class="table-success">';
        } else {
            $output .= '<tr>';
        }

		$output .= '
				<td style="width: 0px">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" id="tableCheckOne" />
						<label class="form-check-label" for="tableCheckOne"></label>
					</div>
				</td>
				<td>
					<div class="d-flex align-items-center">
						<div class="avatar">
							<img class="avatar-img" src="' . PROOT . 'assets/media/uploads/collection-files/'. basename($row['daily_proof_image']) . '" alt="..." />
						</div>
						<div class="ms-4">
							<div>' . $row["daily_id"] . '</div>
							<div class="fs-sm text-body-secondary">Upload on '. pretty_date_notime($row["daily_collection_date"]) .'</div>
						</div>
					</div>
				</td>
				<td>' . $status_badge . '</td>
                <td>' . ucwords($uploader) . '</td>
                <td>' . ucwords($handler) . '</td>
                <td style="width: 0px">
					<div class="dropdown">
						<button class="btn btn-sm btn-link text-body-tertiary" role="button" data-bs-toggle="dropdown" aria-expanded="false">
							<span class="material-symbols-outlined scale-125">more_horiz</span>
						</button>
						
						<ul class="dropdown-menu">
							' . $options . '
						</ul>
					</div>
				</td>
            </tr>

			<!-- View modal -->
			<div class="modal fade" id="viewModal_' . $row["id"] . '" tabindex="-1" aria-labelledby="viewModalLabel_' . $row["id"] . '" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header border-bottom-0 pb-0">
                            <h1 class="modal-title fs-5" id="viewModalLabel_' . $row["id"] . '">View colection details</h1>
                            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
							<img src="' . PROOT . 'assets/media/uploads/collection-files/'. basename($row['daily_proof_image']) . '" class="img-thumbnail" />
							<small><a href="' . PROOT . 'assets/media/uploads/collection-files/'. basename($row['daily_proof_image']) . '" target="_blank"">view image in new tab</a></small>

							<!-- Header -->
							<h3 class="fs-6 mt-4 mb-2">Details</h3>
						
							<!-- Details -->
							<div class="vstack gap-3 card bg-body">
								<div class="card-body py-3">
									<div class="row align-items-center gx-4">
										<div class="col-auto">
											<span class="text-body-secondary">Date</span>
										</div>
										<div class="col">
											<hr class="my-0 border-style-dotted" />
										</div>
										<div class="col-auto">
											<span class="material-symbols-outlined text-body-tertiary me-1">date_range</span> ' . pretty_date_notime($row["daily_collection_date"]) . '
										</div>
									</div>
									<div class="row align-items-center gx-4">
										<div class="col-auto">
										<span class="text-body-secondary">Satus</span>
										</div>
										<div class="col">
										<hr class="my-0 border-style-dotted" />
										</div>
										<div class="col-auto">
											' . $status_badge . '
										</div>
									</div>
									<div class="row align-items-center gx-4">
										<div class="col-auto">
										<span class="text-body-secondary">Uploader</span>
										</div>
										<div class="col">
										<hr class="my-0 border-style-dotted" />
										</div>
										<div class="col-auto"><span class="material-symbols-outlined text-body-tertiary me-1">face</span> ' . $uploader . '</div>
									</div>
									<div class="row align-items-center gx-4">
										<div class="col-auto">
										<span class="text-body-secondary">Handler</span>
										</div>
										<div class="col">
										<hr class="my-0 border-style-dotted" />
										</div>
										<div class="col-auto"><span class="material-symbols-outlined text-body-tertiary me-1">man</span> ' . $handler . '</div>
									</div>
								</div>
							</div>
    
                            <!-- <div class="row gx-3">
								<div class="col">
									<button class="btn btn-light w-100" type="button">Approve</button>
								</div>
								<div class="col">
									<button class="btn btn-light w-100" type="button">Message</button>
								</div>
							</div> -->
                            <a href="'. PROOT .'app/collections?approve='. $row['daily_id'] .'" onclick="return confirm(\'Are you sure you want to VERIFY this collection record?\');" class="btn btn-secondary w-100 mt-4">Verify</a>
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
