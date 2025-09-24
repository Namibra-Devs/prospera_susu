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

// get user details by id
function get_id_details($dbConnection, $id) {
	$statement = $dbConnection->query("SELECT * FROM levina_users WHERE user_id = '" . $id . "'")->fetch(PDO::FETCH_ASSOC);
	return $statement;
}

 // get list of products
 function count_products() {
	global $dbConnection;
	$statement = $dbConnection->query("SELECT * FROM levina_products WHERE product_trash = 0")->rowCount();
	return $statement;
 }
 
// get list of products
function get_products() {
	global $dbConnection;
	$statement = $dbConnection->query("SELECT * FROM levina_products WHERE product_trash = 0 ORDER BY createdAt DESC")->fetchAll(PDO::FETCH_ASSOC);
	return $statement;
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
function get_all_admins() {
	global $dbConnection;
	global $admin_data;
	$output = '';

	$query = "
		SELECT * FROM susu_Admins 
		WHERE admin_status = ?
	";
	$statement = $dbConnection->prepare($query);
	$statement->execute([0]);
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
			$output .= '
				<a href="' . PROOT . 'app/admins?delete='.$row["admin_id"].'" class="btn btn-sm btn-light"><span class="material-symbols-outlined">delete</span></a>
			';
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
function get_number_of_users() {
	global $dbConnection;
	$statement = $dbConnection->query("SELECT * FROM levina_users WHERE user_trash = 0")->rowCount();
	return $statement;
}

// get number of products
function get_number_of_products() {
	global $dbConnection;
	$statement = $dbConnection->query("SELECT * FROM levina_products WHERE product_trash = 0")->rowCount();
	return $statement;
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
    $statement->execute([$email, 0]);
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
    $statement->execute([$id, 0]);
    $user = $statement->fetch(PDO::FETCH_OBJ);
    return $user;
}





////////////////// COLECTOR
function collectorLogin($collector_id) {
	$_SESSION['PRSCOLLECTOR'] = $collector_id;
	global $dbConnection;

	$data = array(date("Y-m-d H:i:s"), $collector_id);
	$query = "
		UPDATE collectors 
		SET updated_at = ? 
		WHERE collector_id = ?
	";
	$statement = $dbConnection->prepare($query);
	$result = $statement->execute($data);
	if (isset($result)) {
		
		$log_message = 'Collector [' . $collector_id . '] has logged in!';
    	add_to_log($log_message, $collector_id, 'collector');
		
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
			'collector',
			$collector_id, 
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

function collector_is_logged_in() {
	if (isset($_SESSION['PRSCOLLECTOR']) && $_SESSION['PRSCOLLECTOR'] > 0) {
		return true;
	}
	return false;
}

// Redirect collector if !logged in
function collector_login_redirect($url = 'auth/sign-out') {
	$_SESSION['flash_error'] = 'You must be logged in to access that page.';
	redirect(PROOT . $url);
}

// get collector by email
function findCollectorByEmail($email) {
    global $dbConnection;

    $query = "
        SELECT * FROM collectors 
        WHERE collector_email = ? 
		-- AND admin_status = ? 
		LIMIT 1
    ";
    $statement = $dbConnection->prepare($query);
    $statement->execute([$email]);
    $user = $statement->fetch(PDO::FETCH_OBJ);
    return $user;
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













////////////////////////////////// COLLECTORS
function findCollectorByID($id) {
    global $dbConnection;

    $query = "
        SELECT * FROM collectors 
        WHERE collector_id = ? 
		AND collector_status = ? 
		LIMIT 1
    ";
    $statement = $dbConnection->prepare($query);
    $statement->execute([$id, 'active']);
    $collector = $statement->fetch(PDO::FETCH_OBJ);
    return $collector;
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

