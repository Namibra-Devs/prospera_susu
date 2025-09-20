<?php

// find the logged in person
function who_logged_in($session) {
	$person = null;
	$string = issetElse($_SESSION, $session, 0);
	if ($string == 'PRSUser') {
		$person = 'user';
	} else if ($string == 'PRSCollector') {
		$person = 'collector';
	} else if ($string == 'PRSADMIN') {
		$person = 'admin';
	}
	return $person;
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
		//login_details_id

		$_SESSION['last_activity'] = time();
		$_SESSION['flash_success'] = 'You are now logged in!';
		// redirect(PROOT . 'index');
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

function admin_has_permission($permission = 'Super Admin') {
	global $admin_data;
	if ($admin_data['admin_role'] == $permission) {
		return true;
	}
	// $permissions = explode(',', $admin_data['admin_permissions']);
	// if (in_array($permission, $permissions, true)) {
	// 	return true;
	// }
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
				<a href="' . PROOT . 'acc/admins?delete='.$row["admin_id"].'" class="btn btn-sm btn-light"><span class="material-symbols-outlined">delete</span></a>
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
				<td>' . strtoupper($row["admin_role"]) . '</td>
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


// get user by id
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

// get user by email
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






















////////////////////////////////// GENERAL
// add to logs
// function add_to_log($message, $log_admin) {
// 	global $conn;

// 	$log_id = guidv4();
// 	$sql = "
// 		INSERT INTO `susu_logs`(`log_id`, `log_message`, `log_admin`) 
// 		VALUES (?, ?, ?)
// 	";
// 	$statement = $conn->prepare($sql);
// 	$result = $statement->execute([$log_id, $message, $log_admin]);

// 	if ($result) {
// 		return true;
// 	}
// 	return false;
// }

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