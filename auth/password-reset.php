<?php
    require_once("../connection/conn.php");
    if (!cadminIsLoggedIn() && !collectorIsLoggedIn()) {
        cadminLoginErrorRedirect();
    }

    include ('includes/header.inc.php');
    include ('includes/top-nav.inc.php');
    include ('includes/left-nav.inc.php');

    $errors = '';
    $old_password = '';
    $password = '';
    $confirm = '';

    if (cadminIsLoggedIn()) {
        $hashed = $admin_data['ckey'];
        $user_id = $admin_id;
        $table = 'puubu_admin';
        $id_column = 'admin_id';
        $redirect_path = ADROOT . 'settings';
        $log_user_type = 'admin';
    } elseif (collectorIsLoggedIn()) {
        $hashed = $collector_data['collector_password'];
        $user_id = $collector_id;
        $table = 'collectors';
        $id_column = 'collector_id';
        $redirect_path = COLLECTOR_ROOT . 'settings';
        $log_user_type = 'collector';
    } else {
        // Should not happen due to initial check, but as a fallback
        cadminLoginErrorRedirect();
    }

    if (isset($_POST['edit_password'])) {
        $old_password = sanitize($_POST['old_password']);
        $password = sanitize($_POST['password']);
        $confirm = sanitize($_POST['confirm']);

        if (empty($old_password) || empty($password) || empty($confirm)) {
            $errors = 'You must fill out all fields';
        } else {
            if (strlen($password) < 6) {
                $errors = 'Password must be at least 6 characters';
            }

            if ($password != $confirm) {
                $errors = 'The new password and confirm new password do not match.';
            }

            if (!password_verify($old_password, $hashed)) {
                $errors = 'Your old password does not match our records.';
            }
        }

        if (!empty($errors)) {
            // Errors will be displayed in
            // the form
            $errors;
            exit;
            } else {
                $new_hashed = password_hash($password, PASSWORD_BCRYPT);
                $query = '
                    UPDATE $table 
                    SET ckey = :ckey 
                    WHERE $id_column = :c_aid
                ';
                $satement = $conn->prepare($query);
                $result = $satement->execute(
                    array(
                        ':ckey' => $new_hashed,
                        ':c_aid' => $user_id
                    )
                    );
                    