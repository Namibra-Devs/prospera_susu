<?php 

	// USER SIGN OUT FILE

    require_once ("../system/DatabaseConnector.php");

    if (!isset($_SESSION['PRSADMIN'])) {
        session_destroy();
        redirect(PROOT . 'auth/sign-in');
    }

    $query = "
        UPDATE susu_admin_login_details 
        SET updateAt = ? 
        WHERE login_details_admin_id = ? 
        AND login_details_id = ?
    ";
    $statement = $dbConnection->prepare($query);
    $statement->execute([
        date("Y-m-d H:i:s"), 
        $_SESSION['PRSADMIN'], 
        $admin_data['login_details_id']
    ]);
    
    $message = "logged out from system";
    add_to_log($message, $_SESSION['PRSADMIN']);

    $log_message = ucwords($added_by) . ' [' . $added_by_id . '] added new customer ' . ucwords($name) . ' (' . $phone . ')';
    add_to_log($log_message, $added_by_id, $added_by);
    
    unset($_SESSION['PRSADMIN']);
    unset($_SESSION['last_activity']);
    
    session_destroy();

    redirect(PROOT . 'auth/sign-in');
