<?php 

    require dirname(__DIR__)  . '/bootstrap.php';
	
    $driver = $_ENV['DB_DRIVER'];
    $hostname = $_ENV['DB_HOST'];
    $port = $_ENV['DB_PORT'];
    $database = $_ENV['DB_DATABASE'];
    $username = $_ENV['DB_USERNAME'];
    $password = $_ENV['DB_PASSWORD'];

    try {
        $string = $driver . ":host=" . $hostname . ";charset=utf8mb4;dbname=" . $database;
        $dbConnection = new \PDO(
            $string, $username, $password
        );
    } catch (\PDOException $e) {
        exit($e->getMessage());
    }
    session_start();

    if (isset($_SESSION['PRSUser'])) {
        $user_id = $_SESSION['PRSUser'];
        $data = array($user_id);
        $sql = "
            SELECT * FROM levina_users 
            WHERE user_id = ? 
            LIMIT 1
        ";
        $statement = $dbConnection->prepare($sql);
        $statement->execute($data);
        if ($statement->rowCount() > 0) {
            $user_data = $statement->fetchAll();
            $user_data = $user_data[0];

            $fn = explode(' ', $user_data['user_fullname']);
            $user_data['first'] = ucwords($fn[0]);
            $user_data['last'] = '';
            if (count($fn) > 1) {
                $user_data['last'] = ucwords($fn[1]);
            }

        } else {
            unset($_SESSION['PRSUser']);
            redirect(PROOT . 'app/');
        }
    }

    if (isset($_SESSION['PRSADMIN'])) {
 		$admin_id = $_SESSION['PRSADMIN'];

 		$sql = "
 			SELECT * FROM susu_admins 
 			WHERE susu_admins.admin_id = ? 
 			LIMIT 1
 		";
 		$statement = $dbConnection->prepare($sql);
 		$statement->execute([$admin_id]);
 		$admin_dt = $statement->fetchAll();
		if ($statement->rowCount() > 0) {
			$admin_data = $admin_dt[0];

			$details_data = $dbConnection->query("SELECT * FROM susu_admin_login_details WHERE susu_admin_login_details.login_details_admin_id = '" . $admin_id . "' ORDER BY id DESC LIMIT 1")->fetchAll();
			
			if (is_array($details_data) && count($details_data) > 0) {
				$admin_data = array_merge($admin_data, $details_data[0]);
			}

			$fn = explode(' ', $admin_data['admin_fullname']);
			$admin_data['first'] = ucwords($fn[0]);
			$admin_data['middle'] = '';
			if (count($fn) > 2) {
				$admin_data['middle'] = ucwords($fn[1]);
				$admin_data['first'] = $admin_data['first'] . ' ' . $admin_data['middle'];
			}
			$admin_data['last'] = '';
			if (count($fn) > 1) {
				$admin_data['last'] = ucwords($fn[1]);
			}
			$admin_permission = $admin_data['admin_permissions']; // get admin's permission
		} else {
			redirect(PROOT . 'auth/sign-out');
		}
		
	}

    //
    if (array_key_exists('PRSADMIN', $_SESSION)) {
        $added_by = 'admin';
        $added_by_id = $_SESSION['PRSADMIN'];
    } elseif (array_key_exists('PRSCOLLECTOR', $_SESSION)) {
        $added_by = 'collector';
        $added_by_id = $_SESSION['PRSCOLLECTOR'];
    }

    require_once ("Functions.php");
    require_once ("helpers.php");
    require_once dirname(__DIR__) . "/config.php";

    // Display on Messages on Errors And Success for users
 	$flash_message = '';
 	if (isset($_SESSION['flash_success'])) {
 	 	$flash_message = '
			<div aria-live="polite" aria-atomic="true" class="position-fixed top-0 start-50 translate-middle-x rounded-3" style="z-index: 9999;">
				<div class="p-3">
					<div class="toast show text-bg-success border-0" id="temporary">
                        <div class="toast-body">
                            ' . $_SESSION['flash_success'] . '
                        </div>
					</div>
				</div>
			</div>
		';
 	 	unset($_SESSION['flash_success']);
 	}

 	if (isset($_SESSION['flash_error'])) {
 	 	$flash_message = '
            <div aria-live="polite" aria-atomic="true" class="position-fixed top-0 start-50 translate-middle-x rounded-3" style="z-index: 9999;">
                <div class="p-3">
                    <div class="toast show text-bg-danger border-0" id="temporary">
                        <div class="toast-body">
                            ' . $_SESSION['flash_error'] . '
                        </div>
                    </div>
                </div>
            </div>
        ';
 	 	unset($_SESSION['flash_error']);
 	}
