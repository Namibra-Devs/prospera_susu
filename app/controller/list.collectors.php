<?php 

// LIST AND SEARCH FOR COLLECTORS

require ('../../system/DatabaseConnector.php');

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

    $output = '
        <div class="card mb-6">
            <div class="table-responsive">
                <table class="table table-flush align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            ' .  ((admin_has_permission()) ? '<th scope="col">Handler</th>' : '') . '
                            <th>Customer</th>
                            <th>Gram</th>
                            <th>Volume</th>
                            <th>Price</th>
                            <th>Amount</th>
                            <th></th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
    ';

if ($total_data > 0) {
	$i = 1;
	foreach ($result as $row) {

		$type = "";
		if ($row["sale_type"] == 'out') {
			// out-trade
			$type = '
				<span class="badge bg-danger-subtle text-danger">buy-gold</span>
			';
		} else if ($row["sale_type"] == 'in') {
			// in-trade
			$type = '
				<span class="badge bg-success-subtle text-success">sell-gold</span>
			';
		} else if ($row["sale_type"] == 'exp') {
			$type = '
				<span class="badge bg-secondary-subtle text-secondary">expenditure</span>
			';
		}

		$d = strtotime($row['sca']);
		$arrayOutput = array(
			'reference' => $row['sale_id'], 
			'customername' => urlencode($row['sale_customer_name']), 
			'gram' => $row['sale_gram'], 
			'volume' => $row['sale_volume'], 
			'density' => $row['sale_density'], 
			'pounds' => $row['sale_pounds'], 
			'carat' => $row['sale_carat'], 
			'total_amount' => $row['sale_total_amount'], 
			'current_price' => $row['sale_price'], 
			'by' => $row['sale_by'], 
			'date' => $d, 
			'message' => ''
		);
		$outputData = json_encode($arrayOutput);

		$option1 = '';
		if ($row['sale_type'] != 'exp') {
			$option1 = '
				&nbsp;
				<a href=' . PROOT . 'auth/print?data=' . $outputData . ' title="Print receipt" target="_blank" class="btn btn-sm btn-light">
					<span class="material-symbols-outlined"> print </span>
				</a>
			';
		}
		
        $option2 =  '
			<div class="p-2"></div>
			<div class="d-flex justify-content-center">
				<!-- <button class="btn btn-sm btn-dark"><i class="bi bi-receipt me-2"></i>Print receipt</button>&nbsp -->

				' . (($row["sdate"] == date("Y-m-d") && $row["sale_pushed"] == 0) ? '<a href="#deleteModal_'. $row["sid"] . '" data-bs-toggle="modal" class="btn btn-sm btn-danger"><span class="material-symbols-outlined me-2"> delete </span> Delete</a>' : '') . '
			</div>
        ';
		if ($row['sale_type'] == 'exp') {
			$option2 = '<div class="d-flex  justify-content-center">' . 
				(($row["sdate"] == date("Y-m-d")) ? '<a href="#deleteExpModal_'. $row["sid"] . '" data-bs-toggle="modal" class="btn btn-sm btn-danger"><span class="material-symbols-outlined me-2"> delete </span> Delete</a>' : '')
				. '</div>'
			;
		}

        $option3 = '';
		if ($row['sale_status'] == 1) {
			$option1 = '';
			$option2 = '';
			if (admin_has_permission() && $row["sdate"] == date("Y-m-d")) {
				$option3 = '
					<a href="' . PROOT . 'account/trades.delete.requests?pd=' . $row["sale_id"] . '" class="btn btn-sm btn-danger mt-2 mb-2"><span class="material-symbols-outlined me-2"> delete </span> Delete</a>
				';
			}
		} else if ($row['sale_status'] == 2) {
			$option1 = '';
			$option2 = '';
			$option3 = '';
		}
		$output .= '	
				<tr class="' . (($row["sdate"] == $today) ? 'bg-danger' : '') . '">
	                <td>' . $i . '</td>
	                ' . (admin_has_permission() ? ' <td><a href="javascript:;" data-bs-target="#adminModal_' . $row["aid"] . '" data-bs-toggle="modal"><span class="d-block text-heading">' . ucwords($row["admin_fullname"]) . '</span></a></td> ' : '') . '
	                <td class="text-xs">
						' . (($row["sale_customer_name"] != null) ? ucwords($row["sale_customer_name"]) : '') . ' <span class="material-symbols-outlined mx-2"> trending_flat </span> ' . $row["sale_customer_contact"] . '
					</td>
	                <td>' . $row["sale_gram"] . '</td>
	                <td>' . $row["sale_volume"] . '</td>
	                <td>' . (($row['sale_type'] == 'exp') ? '' : money($row["sale_price"])) . '</td>
	                <td>' . money($row["sale_total_amount"]) . '</td>
	                <td>' . $type . '</td>
	                <td>' . pretty_date($row["sca"]) . '</td>
	                <td class="text-end">
	                    <button type="button" class="btn btn-sm btn-dark" title="More" data-bs-target="#saleModal_' . $row["sid"] . '" data-bs-toggle="modal">
	                       <span class="material-symbols-outlined"> visibility </span>
	                    </button> ' . $option1 . '
	                </td>
	            </tr>

	            <!-- Trade details -->
	            <div class="modal fade" id="saleModal_' . $row["sid"] . '" tabindex="-1" aria-labelledby="saleModalLabel_' . $row["sid"] . '" aria-hidden="true" style="backdrop-filter: blur(5px);">
					<div class="modal-dialog modal-sm modal-dialog-centered">
						<div class="modal-content overflow-hidden">
							<div class="modal-header pb-0 border-0">
								<h1 class="modal-title h4" id="saleModalLabel_' . $row["sid"] . '">' . $row["sale_id"] . (admin_has_permission() ? '<br>by ' . ucwords($row["admin_fullname"]) : '' )  . '</h1>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>
							<div class="modal-body">
								<ul class="list-group list-group-flush">
									<div class="list-group-item px-0">
										<div class="row align-items-center">
											<div class="col ms-n2">
												<h6 class="fs-base fw-normal mb-1">Total amount,</h6>
											</div>
											<div class="col-auto">
												<time class="text-body-secondary" datetime="01/01/2025">' . money($row["sale_total_amount"]) . '</time>
											</div>
										</div>
									</div>
									<div class="list-group-item px-0">
										<div class="row align-items-center">
											<div class="col ms-n2">
												<h6 class="fs-base fw-normal mb-1">Price,</h6>
											</div>
											<div class="col-auto">
												<time class="text-body-secondary" datetime="01/01/2025">' . money($row["sale_price"]) . '</time>
											</div>
										</div>
									</div>
									<div class="list-group-item px-0">
										<div class="row align-items-center">
											<div class="col ms-n2">
												<h6 class="fs-base fw-normal mb-1">Gram,</h6>
											</div>
											<div class="col-auto">
												<time class="text-body-secondary" datetime="01/01/2025">' . $row["sale_gram"] . '</time>
											</div>
										</div>
									</div>
									<div class="list-group-item px-0">
										<div class="row align-items-center">
											<div class="col ms-n2">
												<h6 class="fs-base fw-normal mb-1">Volume,</h6>
											</div>
											<div class="col-auto">
												<time class="text-body-secondary" datetime="01/01/2025">' . $row["sale_volume"] . '</time>
											</div>
										</div>
									</div>
									<div class="list-group-item px-0">
										<div class="row align-items-center">
											<div class="col ms-n2">
												<h6 class="fs-base fw-normal mb-1">Density,</h6>
											</div>
											<div class="col-auto">
												<time class="text-body-secondary" datetime="01/01/2025">' . $row["sale_density"] . '</time>
											</div>
										</div>
									</div>
									<div class="list-group-item px-0">
										<div class="row align-items-center">
											<div class="col ms-n2">
												<h6 class="fs-base fw-normal mb-1">Pounds,</h6>
											</div>
											<div class="col-auto">
												<time class="text-body-secondary" datetime="01/01/2025">' . $row["sale_pounds"] . '</time>
											</div>
										</div>
									</div>
									<div class="list-group-item px-0">
										<div class="row align-items-center">
											<div class="col ms-n2">
												<h6 class="fs-base fw-normal mb-1">Carat,</h6>
											</div>
											<div class="col-auto">
												<time class="text-body-secondary" datetime="01/01/2025">' . $row["sale_carat"] . '</time>
											</div>
										</div>
									</div>
									<div class="list-group-item px-0">
										<div class="row align-items-center">
											<div class="col ms-n2">
												<h6 class="fs-base fw-normal mb-1">Customer,</h6>
											</div>
											<div class="col-auto">
												<time class="text-body-secondary" datetime="01/01/2025">' . (($row["sale_customer_name"] != null) ? ucwords($row["sale_customer_name"]) : '') . ' | Contact: ' . $row["sale_customer_contact"] . '</time>
											</div>
										</div>
									</div>
									<div class="list-group-item px-0">
										<div class="row align-items-center">
											<div class="col ms-n2">
												<h6 class="fs-base fw-normal mb-1">Note,</h6>
											</div>
											<div class="col-auto">
												<time class="text-body-secondary" datetime="01/01/2025">' . $row["sale_comment"] . '</time>
											</div>
										</div>
									</div>
									<div class="list-group-item px-0">
										<div class="row align-items-center">
											<div class="col ms-n2">
												<h6 class="fs-base fw-normal mb-1">Date,</h6>
											</div>
											<div class="col-auto">
												<time class="text-body-secondary" datetime="01/01/2025">' . pretty_date($row["sca"]) . '</time>
											</div>
										</div>
									</div>
								</ul>
								' . $option2 . '
								' . $option3 . '
							</div>
						</div>
					</div>
				</div>

				<!-- DELETE TRADE -->
				<div class="modal fade" id="deleteModal_' . $row["sid"] . '" tabindex="-1" aria-labelledby="deleteModalLabel_' . $row["sid"] . '" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true" style="backdrop-filter: blur(5px);">
				    <div class="modal-dialog modal-dialog-centered">
				        <div class="modal-content overflow-hidden">
				            <div class="modal-header pb-0 border-0">
				                <h1 class="modal-title h4" id="deleteModalLabel_' . $row["sid"] . '">Delete trade!</h1>
				                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				            </div>
				            <div class="modal-body p-0">
			                    <div class="px-6 py-5 border-bottom">
			                       <p>
			                       Trade of Volume '.$row["sale_volume"].', Gram ' . $row["sale_gram"] . ', Price ' . money($row["sale_price"]) . ' and Amount ' . money($row["sale_total_amount"]) . ' from customer ' . (($row["sale_customer_name"] != null) ? ucwords($row["sale_customer_name"]) : '') . ' will be deleted!
			                       </p>
			                       <br>
			                       Trade ID: ' . $row["sale_id"] . '
			                       <br>
			                       <p>
			                       		Are you sure you want to proceed to this action.
			                       </p>
			                    </div>
			                    <div class="px-6 py-5 d-flex justify-content-center">
			                        <a href="' . PROOT . 'auth/trade.delete/' . $row["sale_id"] . '" class="btn btn-sm btn-danger"><i class="bi bi-trash me-2"></i>Yes, Confirm delete</a>&nbsp;&nbsp;
			                        <button type="button" class="btn btn-sm btn-dark" data-bs-dismiss="modal">No, cancel</button>
			                    </div>
				            </div>
				        </div>
				    </div>
				</div>

				<!-- DELETE Expenditure -->
				<div class="modal fade" id="deleteExpModal_' . $row["sid"] . '" tabindex="-1" aria-labelledby="deleteExpModalLabel_' . $row["sid"] . '" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
					<div class="modal-dialog modal-sm modal-dialog-centered">
						<div class="modal-content overflow-hidden">
							<div class="modal-header pb-0 border-0">
								<h1 class="modal-title h4" id="deleteExpModalLabel_' . $row["sid"] . '">Delete expenditure!</h1>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>
							<div class="modal-body p-0">
								<div class="px-6 py-5 border-bottom">
									<p>
										<i>'.$row["sale_comment"].', with an amount of ' . money($row["sale_total_amount"]) . '</i> 
										<br><br>
										Are you sure you want to proceed to this action.
									</p>
								</div>
								<div class="px-6 py-5 bg-body-secondary d-flex justify-content-center">
									<a href="' . PROOT . 'account/expenditure?delete=' . $row["sale_id"] . '" class="btn btn-sm btn-danger"><i class="bi bi-trash me-2"></i>Yes, Confirm delete</a>&nbsp;&nbsp;
									<button type="button" class="btn btn-sm btn-dark"data-bs-dismiss="modal">No, cancel</button>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- HANDLER DETAILS -->
				<div class="modal fade" id="adminModal_' . $row["aid"] . '" tabindex="-1" aria-labelledby="adminModalLabel_' . $row["aid"] . '" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true" style="backdrop-filter: blur(5px);">
					<div class="modal-dialog modal-dialog-centered">
						<div class="modal-content overflow-hidden">
							<div class="modal-header pb-0 border-0">
								<h1 class="modal-title h4" id="adminModalLabel_' . $row["aid"] . '">Handler details</h1>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>
							<div class="modal-body">
								<ul class="list-group">
									<li class="list-group-item" style="padding: 0.1rem 1rem;">
				                        <small class="text-muted">Profile,</small>
				                        <p>' . (($row["admin_profile"] != '') ? '<div class="avatar"><img src="' . PROOT . $row["admin_profile"] . '" class="img-fluid" /></div>' : 'No Profile') . '</p>
				                    </li>
				                    <li class="list-group-item" style="padding: 0.1rem 1rem;">
				                        <small class="text-muted">Full  name,</small>
				                        <p>' . ucwords($row["admin_fullname"]) . '</p>
				                    </li>
				                    <li class="list-group-item" style="padding: 0.1rem 1rem;">
				                        <small class="text-muted">Email,</small>
				                        <p>' . $row["admin_email"] . '</p>
				                    </li>
				                    <li class="list-group-item" style="padding: 0.1rem 1rem;">
				                        <small class="text-muted">Date Joined</small>
				                        <p>' . pretty_date($row["admin_joined_date"]) . '</p>
				                    </li>
				                    <li class="list-group-item" style="padding: 0.1rem 1rem;">
				                        <small class="text-muted">Last Login</small>
				                        <p>' . (($row["admin_last_login"] == NULL) ? 'NEVER' : pretty_date($row["admin_last_login"])) . '</p>
				                    </li>
								</ul>
								<div class="px-6 py-5 d-flex justify-content-center">
									<a class="btn btn-light" href="' . PROOT . 'account/admins"><i class="bi bi-people me-2"></i>All admins</a>
								</div>
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
			<td colspan="8"> 
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
