<?php 

	// USER SIGN OUT FILE

    require_once ("../system/DatabaseConnector.php");

    if (!isset($_SESSION['SUADMIN'])) {
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
        $_SESSION['SUADMIN'], 
        $admin_data['login_details_id']
    ]);
    
    $message = "logged out from system";
    add_to_log($message, $_SESSION['SUADMIN']);
    
    unset($_SESSION['SUADMIN']);
    unset($_SESSION['last_activity']);
    
    session_destroy();

    redirect(PROOT . 'auth/sign-in');
