<?php 

	// USER SIGN OUT FILE

    require_once ("../system/DatabaseConnector.php");
    if (!isset($_SESSION['PRSADMIN'])) {
        session_destroy();
        redirect(PROOT . 'auth/sign-in');
    }

    $query = "
        UPDATE susu_login_details 
        SET update_at = ? 
        WHERE login_details_person_id = ? 
        AND login_details_id = ?
    ";
    $statement = $dbConnection->prepare($query);
    $statement->execute([
        date("Y-m-d H:i:s"), 
        $_SESSION['PRSADMIN'], 
        $admin_data['login_details_id']
    ]);
    

    $log_message = ucwords($added_by) . ' [' . $added_by_id . '] has logged out.';
    add_to_log($log_message, $added_by_id, $added_by);
    
    unset($_SESSION['PRSADMIN']);
    unset($_SESSION['last_activity']);
    
    session_destroy();

    redirect(PROOT . 'auth/sign-in');