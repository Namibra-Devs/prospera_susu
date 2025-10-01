<?php

// generate automatic email (eg; collector_ps_01@susu.com) for collectors
// function generate_collector_email($name) {
// 	$name = strtolower($name);
// 	$name = str_replace(' ', '_', $name);
// 	$random_number = rand(10, 99);
// 	return $name . '_' . $random_number . '@susu.com';
// }

function generateCollectorEmail($collectorId, $name) {
	$name = strtolower($name);
	$name = str_replace(' ', '_', $name);

	return "collector_" . $name . "_" . $collectorId . "@susu.com";
}

function isCollectorEmail($email) {
	if (preg_match('/^collector_([a-zA-Z0-9_]+)_([0-9]+)@susu\.com$/', $email, $matches)) {
		$name = $matches[1];         // 'john'
		$collectorId = $matches[2];  // '123'
		// echo "Valid collector email. Name: $name, ID: $collectorId";
		return true;
	}
	return false;
}

////////////////////////////////////////////////////// FOR USER

// Sessions For login
function userLogin($user_id) {
	$_SESSION['PRSUser'] = $user_id;
    $_SESSION['flash_success'] = 'You are now logged in!';
    redirect(PROOT . 'app/');
}

function user_is_logged_in() {
	if (isset($_SESSION['PRSUser']) && $_SESSION['PRSUser'] > 0) {
		return true;
	}
	return false;
}

// Redirect admin if !logged in
function user_login_redirect($url = 'auth/signin') {
	$_SESSION['flash_error'] = 'You must be logged in to access that page.';
	redirect(PROOT . $url);
}














/////////////////////////////////////////////////// FOR ADMIN

// Sessions For login
function adminLogin($admin_id) {
	$_SESSION['PRSADMIN'] = $admin_id;
	global $dbConnection;

	$data = array(date("Y-m-d H:i:s"), $admin_id);
	$query = "
		UPDATE susu_admins 
		SET updated_at = ? 
		WHERE admin_id = ?
	";
	$statement = $dbConnection->prepare($query);
	$result = $statement->execute($data);
	if (isset($result)) {
		
		$log_message = 'Admin [' . $admin_id . '] has logged in!';
    	add_to_log($log_message, $admin_id, 'admin');
		
		// get other details
		$a = getBrowserAndOs();
		$a = json_decode($a);

		$browser = $a->browser;
		$operatingSystem = $a->operatingSystem;
		$refferer = $a->refferer;

		// insert into login details table
		$SQL = "
			INSERT INTO `susu_login_details`(`login_details_id`, `login_details_person`, `login_details_person_id`, `login_details_device`, `login_details_os`, `login_details_refferer`, `login_details_browser`, `login_details_ip`) 
			VALUE (?, ?, ?, ?, ?, ?, ?, ?)
		";
		$statement = $dbConnection->prepare($SQL);
		$statement->execute([
			guidv4() . '-' . strtotime(date("Y-m-d H:m:s")), 
			'admin',
			$admin_id, 
			getDeviceType(), 
			$operatingSystem, 
			$refferer, 
			$browser, 
			getIPAddress(),
		]);

		$_SESSION['last_activity'] = time();
		$_SESSION['flash_success'] = 'You are now logged in!';
		redirect(PROOT . 'index');
	}
}

function admin_is_logged_in() {
	if (isset($_SESSION['PRSADMIN']) && $_SESSION['PRSADMIN'] > 0) {
		return true;
	}
	return false;
}

// Redirect admin if !logged in
function admin_login_redirect($url = 'auth/sign-out') {
	$_SESSION['flash_error'] = 'You must be logged in to access that page.';
	redirect(PROOT . $url);
}

// Redirect admin if do not have permission
function admin_permission_redirect($url = 'index') {
	$_SESSION['flash_error'] = 'You do not have permission in to access that page.';
	redirect(PROOT . $url);
}

function admin_has_permission($permission = 'admin') {
	global $admin_data;
	$permissions = explode(',', $admin_data['admin_permissions']);
	if (in_array($permission, $permissions, true)) {
		return true;
	}
	return false;
}

// GET ALL ADMINS
function get_all_admins($status = 'active') {
	global $dbConnection;
	global $admin_data;
	$output = '';

	$query = "
		SELECT * FROM susu_admins 
		WHERE admin_status = ? 
		AND admin_permissions != ?
	";
	$statement = $dbConnection->prepare($query);
	$statement->execute([$status, 'collector']);
	$result = $statement->fetchAll();

	foreach ($result as $row) {
		$admin_last_login = $row["updated_at"];
		if ($admin_last_login == NULL) {
			$admin_last_login = '<span class="text-secondary">Never</span>';
		} else {
			$admin_last_login = pretty_date($admin_last_login);
		}
		$output .= '
			<tr>
				<td>
		';
					
		if ($row['admin_id'] != $admin_data['admin_id']) {
			if ($row["admin_status"] == 'inactive') {
				$output .= '
					<a href="' . PROOT . 'app/archived-admins?restore='.$row["admin_id"].'" class="btn btn-light"><span class="material-symbols-outlined">settings_backup_restore</span></a>
				';
			} else {
				$output .= '
					<a href="' . PROOT . 'app/admins?delete='.$row["admin_id"].'" class="btn btn-light"><span class="material-symbols-outlined">delete</span></a>
				';
			}
		}

		$output .= '
				</td>
				<td>
					<div class="d-flex align-items-center">
                        <div class="avatar">
                          <img class="avatar-img" src="' . PROOT . (($row["admin_profile"] != NULL) ? $row["admin_profile"] : 'assets/media/avatar.png') . '" alt="..." />
                        </div>
                        <div class="ms-4">
                          <div>' . ucwords($row["admin_name"]) . '</div>
                          <div class="fs-sm text-body-secondary">
                            <a class="text-reset" href="mailto:' . $row["admin_email"] . '">' . $row["admin_email"] . '</a>
                          </div>
                        </div>
                      </div>
				<td>' . strtoupper($row["admin_permissions"]) . '</td>
				<td><a class="text-muted" href="tel:' . $row["admin_phone"] . '">' . $row["admin_phone"] . '</a></td>
				<td>' . pretty_date($row["created_at"]) . '</td>
				<td>' . $admin_last_login . '</td>
			</tr>
		';
	}
	return $output;
}


// get number of clients
function get_number_of_clients() {
	global $dbConnection;
	$statement = $dbConnection->query("SELECT * FROM levina_leads WHERE lead_status = 0")->rowCount();
	return $statement;
}

// get number of users
function get_number_of_customers() {
	global $dbConnection;
	$statement = $dbConnection->query("SELECT * FROM customers WHERE customer_status = 'active'")->rowCount();
	return $statement;
}

// get total amount of commissions
function get_total_commission_amount() {
	global $dbConnection;
	$statement = $dbConnection->query("SELECT SUM(commission_amount) AS total FROM commissions")->fetch(PDO::FETCH_ASSOC);
	return $statement['total'] ?? 0;
}

// get total deposit amount
function get_total_deposits_amount() {
	global $dbConnection;
	$statement = $dbConnection->query("SELECT SUM(saving_amount) AS total FROM savings WHERE saving_status = 'Approved'")->fetch(PDO::FETCH_ASSOC);
	return $statement['total'] ?? 0;
}

// get total withdrawal amount
function get_total_withdrawal_amount() {
	global $dbConnection;
	$statement = $dbConnection->query("SELECT SUM(withdrawal_amount_requested) AS total FROM withdrawals WHERE withdrawal_status = 'Approved'")->fetch(PDO::FETCH_ASSOC);
	return $statement['total'] ?? 0;
}


// get admin by email
function findAdminByEmail($email) {
    global $dbConnection;

    $query = "
        SELECT * FROM susu_admins 
        WHERE admin_email = ? 
		AND admin_status = ? 
		LIMIT 1
    ";
    $statement = $dbConnection->prepare($query);
    $statement->execute([$email, 'active']);
    $user = $statement->fetch(PDO::FETCH_OBJ);
    return $user;
}

// get admin by id
function findAdminById($id) {
    global $dbConnection;

    $query = "
        SELECT * FROM susu_admins 
        WHERE admin_id = ? 
		AND admin_status = ? 
		LIMIT 1
    ";
    $statement = $dbConnection->prepare($query);
    $statement->execute([$id, 'active']);
    $user = $statement->fetch(PDO::FETCH_OBJ);
    return $user;
}



// GET ADMIN PROFILE DETAILS
function get_admin_profile($id) {
	global $dbConnection;
	$output = '';

	$query = "
		SELECT * FROM susu_admins 
		WHERE admin_id = ? 
		AND admin_status = ? 
		LIMIT 1
	";
	$statement = $dbConnection->prepare($query);
	$statement->execute([$id, 'active']);
	$rows = $statement->fetchAll();
	$row = $rows[0];

	$output = '
		<div class="row align-items-center">
			<div class="col-auto">
				<div class="avatar avatar-xl">
					<img class="avatar-img" src="' . (($row["admin_profile"] == NULL) ? 'assets/media/avatar.png' : PROOT . 'assets/media/uploads/admin-media/' . basename($row["admin_profile"])) . '" alt="..." />
				</div>
			</div>
			<div class="col">
				<h2 class="fs-5 mb-0"> ' . ucwords($row["admin_name"]) . ' </h2>
				<div class="text-body-secondary"> Admin ' . get_person_role() . ' </div>
			</div>
		</div>
		<hr />
		<div class="mb-4">
			<div class="form-label">Bio</div>
			<div>
				Hi! I\'m an ....
			</div>
		</div>
		<div class="mb-4">
			<div class="form-label">Email</div>
			<a href="javascript:;" class="text-body"> ' . $row["admin_email"] . ' </a>
		</div>
		<div class="mb-4">
			<div class="form-label">Phone</div>
			<a href="tel:+1234567890" class="text-body"> ' . $row["admin_phone"] . ' </a>
		</div>

		<div class="card border-transparent">
			<div class="card-body py-0">
				<ul class="list-group list-group-flush">
					<li class="list-group-item px-0">
						<div class="row align-items-center">
							<div class="col-auto">
								<span class="material-symbols-outlined text-body-tertiary">credit_card</span>
							</div>
							<div class="col">Joined at <small class="text-body-secondary ms-1">(' . pretty_date($row["created_at"]) . ')</small></div>
						</div>
					</li>
					<li class="list-group-item px-0">
						<div class="row align-items-center">
							<div class="col-auto">
								<span class="material-symbols-outlined text-body-tertiary">credit_card</span>
							</div>
							<div class="col">Last login <small class="text-body-secondary ms-1">(' . pretty_date($row["updated_at"]) . ')</small></div>
							<div class="col-auto">
								<span class="badge bg-success-subtle text-success">' . date("F j, Y, g:i a") . '</span>
							</div>
						</div>
					</li>
				</ul>
			</div>
		</div>
	';
	return $output;
}







//////////////////////////////////////// CUSTOMERS
// generate unique account number for new customer
function generateAccountNumber($dbConnection) {
    // Get current year
    $year = date("Y");
    $prefix = "PRS" . $year;

    // Query to find the latest account number for this year
    $sql = "SELECT customer_account_number 
            FROM customers 
            WHERE customer_account_number LIKE ? 
            ORDER BY customer_account_number DESC 
            LIMIT 1
	";
    $stmt = $dbConnection->prepare($sql);
    $likePrefix = $prefix . "%";
    // $stmt->bind_param("s", $likePrefix);
    $stmt->execute([$likePrefix]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $nextNumber = 1;

    if ($row !== false) {
        // Extract the numeric part (last 5 digits)
        $lastAccNum = $row['customer_account_number'];
        $lastSequence = intval(substr($lastAccNum, -5));
        $nextNumber = $lastSequence + 1;
    }

    // Pad with leading zeros (00001, 00002, etc.)
    $accountNumber = $prefix . str_pad($nextNumber, 5, "0", STR_PAD_LEFT);

    return $accountNumber;
}

// get list of customers
function collector_get_customers() {
	global $dbConnection;
	$statement = $dbConnection->query("SELECT * FROM customers WHERE customer_status = 'active' ORDER BY customer_name ASC")->fetchAll(PDO::FETCH_ASSOC);
	return $statement;
}

// get customer by account number
function findCustomerByAccountNumber($number) {
    global $dbConnection;

    $query = "
        SELECT * FROM customers 
        WHERE customer_account_number = ? 
		AND customer_status = ? 
		LIMIT 1
    ";
    $statement = $dbConnection->prepare($query);
    $statement->execute([$number, 'active']);
    $user = $statement->fetch(PDO::FETCH_OBJ);
    return $user;
}


// get customer by account number
function findCustomerByID($id) {
    global $dbConnection;

    $query = "
        SELECT * FROM customers 
        WHERE customer_id = ? 
		AND customer_status = ? 
		LIMIT 1
    ";
    $statement = $dbConnection->prepare($query);
    $statement->execute([$id, 'active']);
    $user = $statement->fetch(PDO::FETCH_OBJ);
    return $user;
}

// sum customer saves by status
function sum_customer_saves($customer_id, $status = 'Approved') {
	global $dbConnection;

	$query = "
		SELECT SUM(saving_amount) AS total 
		FROM savings 
		WHERE saving_customer_id = ? 
		AND saving_status = ?
	";
	$statement = $dbConnection->prepare($query);
	$statement->execute([$customer_id, $status]);
	$row = $statement->fetch(PDO::FETCH_ASSOC);

	return $row['total'] ?? 0;
}

// sum customer withdrawals by status
function sum_customer_withdrawals($customer_id, $status = 'Approved') {
	global $dbConnection;

	$query = "
		SELECT SUM(withdrawal_amount_requested) AS total 
		FROM withdrawals 
		WHERE withdrawal_customer_id = ? 
		AND withdrawal_status = ?
	";
	$statement = $dbConnection->prepare($query);
	$statement->execute([$customer_id, $status]);
	$row = $statement->fetch(PDO::FETCH_ASSOC);

	return $row['total'] ?? 0;
}

// process monthly commission for a customer
function processMonthlyCommission($customer_id) {
    global $dbConnection;

    // Step 1: get the very first saving date for this customer
    $sqlStart = "SELECT MIN(saving_date_collected) AS first_date 
                FROM savings 
                WHERE saving_customer_id = ?";
    $stmtStart = $dbConnection->prepare($sqlStart);
    $stmtStart->execute([$customer_id]);
    $rowStart = $stmtStart->fetch(PDO::FETCH_ASSOC);

    if (!$rowStart || !$rowStart['first_date']) {
        return; // no savings yet
    }

    $firstSavingDate = $rowStart['first_date'];

    // Step 2: find the first saving in every 31-day cycle
    $sql = "
        SELECT 
            saving_id,
            saving_customer_id,
            saving_amount,
            saving_date_collected,
            FLOOR(DATEDIFF(saving_date_collected, ?)/31) AS cycle_no
        FROM savings
        WHERE saving_customer_id = ?
        ORDER BY saving_date_collected ASC
    ";

    $stmt = $dbConnection->prepare($sql);
    $stmt->execute([$firstSavingDate, $customer_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Track first record per cycle
    $firstDays = [];
    foreach ($rows as $row) {
        $cycle = $row['cycle_no'];
        if (!isset($firstDays[$cycle])) {
            $firstDays[$cycle] = $row; // first saving for this cycle
        }
    }

    // Step 3: insert commissions
    $insert = $dbConnection->prepare("
        INSERT INTO commissions (commission_id, commission_customer_id, commission_amount, commission_date) 
        VALUES (?, ?, ?, ?)
    ");

    foreach ($firstDays as $day) {
        // prevent duplicates
        $check = $dbConnection->prepare("
            SELECT commission_id 
            FROM commissions 
            WHERE commission_customer_id = ? AND commission_date = ?
        ");
        $check->execute([$day['saving_customer_id'], $day['saving_date_collected']]);

        if ($check->rowCount() == 0) {
            $insert->execute([
				guidv4() . '-' . strtotime(date("Y-m-d H:m:s")), 
				$day['saving_customer_id'], 
				$day['saving_amount'], 
				$day['saving_date_collected']
			]);
        }
    }
}

// get customer amount {s}
function getCustomerBalance($customer_id, $account_number) {
    global $dbConnection;
	
	$sql = "
        SELECT 
			c.customer_id,
			c.customer_account_number,
			COALESCE(s.total_saves, 0) AS total_saves,
			COALESCE(w.total_withdrawals, 0) AS total_withdrawals,
			COALESCE(cm.total_commissions, 0) AS total_commissions,
			(COALESCE(s.total_saves, 0) 
			- COALESCE(w.total_withdrawals, 0) 
			- COALESCE(cm.total_commissions, 0)) AS balance
		FROM customers c
		LEFT JOIN (
			SELECT saving_customer_id, SUM(saving_amount) AS total_saves
			FROM savings
			WHERE saving_status = 'Approved'
			GROUP BY saving_customer_id
		) AS s ON c.customer_id = s.saving_customer_id
		LEFT JOIN (
			SELECT withdrawal_customer_id, SUM(withdrawal_amount_requested) AS total_withdrawals
			FROM withdrawals
			WHERE (withdrawal_status = 'Approved' OR withdrawal_status = 'Paid')
			GROUP BY withdrawal_customer_id
		) AS w ON c.customer_id = w.withdrawal_customer_id
		LEFT JOIN (
			SELECT commission_customer_id, SUM(commission_amount) AS total_commissions
			FROM commissions
			GROUP BY commission_customer_id
		) AS cm ON c.customer_id = cm.commission_customer_id
		WHERE (c.customer_id = ? OR c.customer_account_number = ?)
    ";
    $stmt = $dbConnection->prepare($sql);
    $stmt->execute([$customer_id, $account_number]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}







////////////////////////////////// GENERAL
// add to logs
function add_to_log($message, $person, $type) {
	global $dbConnection;

	$log_id = guidv4() . '-' . strtotime(date('Y-m-d H:m:s'));
	$sql = "
		INSERT INTO `susu_logs`(`log_id`, `log_message`, `log_person`, `log_type`) 
		VALUES (?, ?, ?, ?)
	";
	$statement = $dbConnection->prepare($sql);
	$result = $statement->execute([$log_id, $message, $person, $type]);

	if ($result) {
		return true;
	}
	return false;
}

// get logs for admins
function get_logs($admin) {
	global $dbConnection;
	$output = '';

	$where = '';
	if (!admin_has_permission()) {
		$where = ' WHERE susu_logs.log_person = "' . $admin . '" ';
	}

	$sql = "
		SELECT * FROM susu_logs 
		INNER JOIN susu_admins 
		ON susu_admins.admin_id = susu_logs.log_person
		$where 
		ORDER BY susu_logs.created_at DESC
		LIMIT 10
	";
	$statement = $dbConnection->prepare($sql);
	$statement->execute();
	$rows = $statement->fetchAll();

	if ($statement->rowCount() > 0): 
		foreach ($rows as $row) {
			$admin_name = explode(' ', $row['admin_name']);
			$admin_name = ucwords($admin_name[0]);

			$output .= '
				<li data-icon="account_circle">
					<div>
						<h6 class="fs-base mb-1">' . (($row["log_person"] == $admin) ? 'You': $admin_name) . ' <span class="fs-sm fw-normal text-body-secondary ms-1">' . pretty_date($row["created_at"]) .'</span></h6>
						<p class="mb-0">' . $row["log_message"] . '</p>
					</div>
				</li>
			';
		}
	else:
		$output .= '
				<div class="alert alert-info">
					No data found!
				</div>
			';
	endif;

	return $output;
}

function idle_user() {

    // Check the last activity time
    if (isset($_SESSION['last_activity'])) {
        $idleTime = time() - $_SESSION['last_activity'];

        // If the idle time exceeds the timeout period
        if ($idleTime > IDLE_TIMEOUT) {
            // Destroy the session and log out the user
            //session_unset();
            //session_destroy();

            // Redirect to the login page or show a message
			// $_SESSION['flash_error'] = 'Session expired. Please log in again!';
			//redirect(PROOT . 'auth/login');
            //exit;
			return false;
        }
    }

    // Update the last activity timestamp
    $_SESSION['last_activity'] = time();
	return true;
}

function get_person_role() {
	global $admin_data;
	$out = 'User';
	// get logged in person role
	if (array_key_exists('PRSADMIN', $_SESSION)) {
		$out = 'Admin';
		// check if admin is a super admin or approver
		if (admin_has_permission()) {
			$out = ' (Super)';
		} elseif (admin_has_permission('approver') && !admin_has_permission('admin')) {
			$out = ' (Approver)';
		} elseif (admin_has_permission('collector') && !admin_has_permission('admin')) {
			$out = ' (Collector)';
		}
	}
	
	return $out;
}

function get_total_customer() {
	global $dbConnection;

	$sql = $dbConnection->query("SELECT * FROM customers WHERE customer_status = 'active'")->rowCount();
	return $sql ?? 0;
}
