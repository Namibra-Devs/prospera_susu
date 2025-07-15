<?php
    // check if user is idle 

    require_once ("../db_connection/conn.php");

    if (isset($_POST['type'])) 
        if ($_POST['type'] == 'idle') {
            if (!idle_user()) {

                // add to log message
                $message = "automatically logged out becuase of idleness.";
                add_to_log($message, $admin_id);
                
                session_unset();
                session_destroy();

                echo 'idle';
    
                // Redirect to the login page or show a message
                // $_SESSION['flash_error'] = 'Session expired. Please log in again!';
                // redirect(PROOT . 'auth/login');
                // exit;
            }
        }
    