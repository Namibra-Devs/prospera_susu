<?php
    require ('../../system/DatabaseConnector.php');

    $errors = null;
    $message = null;
    
    $added_by = null;
    $added_by_id = null;
    if (array_key_exists('PRSADMIN', $_SESSION)) {
        $added_by = 'admin';
        $added_by_id = $_SESSION['PRSADMIN'];
    } elseif (array_key_exists('PRSCOLLECTOR', $_SESSION)) {
        $added_by = 'collector';
        $added_by_id = $_SESSION['PRSCOLLECTOR'];
    }

    // check if is posted
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['select_customer'])) {
        // get form data
        $customer_info = sanitize($_POST['select_customer']);
        list($customer_name, $customer_account_number) = explode(',', $customer_info);
        $transaction_amount = sanitize($_POST['default_amount']);
        $transaction_date = sanitize($_POST['today_date']);
        $transaction_note = sanitize($_POST['note']);

        $is_advance_payment = (isset($_POST['is_advance_payment']) && $_POST['is_advance_payment'] === 'yes') ? true : false;
        $advance_payment = ($is_advance_payment && isset($_POST['advance_payment'])) ? sanitize($_POST['advance_payment']) : 0;

        $unique_id = guidv4() . '-' . strtotime(date("Y-m-d H:m:s"));

        $find_customer_row = findCustomerByAccountNumber($customer_account_number);
        if (!$find_customer_row) {
            $errors = 'Customer not found !';
        }

        // validate inputs
        $required = array('select_customer', 'default_amount', 'payment_mode', 'today_date');
        foreach ($required as $f) {
            if (empty($f)) {
                $errors = $f . ' is required !';
                break;
            }
        }

        if ($is_advance_payment) {
            if ($advance_payment <= 1) {
                $errors = 'Please select advance payment option.';
            } elseif ($advance_payment > 30) {
                $errors = 'Advance payment cannot be more than 30 days.';
            }
            // calculate total amount
            $transaction_amount = $transaction_amount * $advance_payment;
            $transaction_note .= ' (Advance payment for ' . $advance_payment . ' days)';

            // create a foreach loop to add multiple transactions for advance payment
            for ($i = 1; $i < $advance_payment; $i++) {
                $next_date = date('Y-m-d', strtotime($transaction_date . ' + ' . $i . ' days'));
                $next_unique_id = guidv4() . '-' . strtotime(date("Y-m-d H:m:s")) . '-' . $i;
                $stmt = $dbConnection->prepare("INSERT INTO savings (saving_id, saving_customer_id, saving_customer_account_number, saving_collector_id, saving_amount, saving_date_collected, saving_note) VALUES (?, ?, ?, ?, ?, ?, ?)");
                
                $stmt->execute([$next_unique_id, $find_customer_row->customer_id, $customer_account_number, $collector_id, $transaction_amount / $advance_payment, $next_date, 'Advance payment for ' . $customer_name . ' (' . $customer_account_number . ') for day ' . ($i + 1)]);
                
                // log message
                if ($stmt) {
                    $log_message = ucwords($added_by) . ' [' . $added_by_id . '] added new transaction to ' . ucwords($customer_name) . ' (' . $customer_account_number . ') account for day ' . ($i + 1);
                    add_to_log($log_message, $added_by_id, $added_by);

                    $message = 'Transaction added successfully.';
                } else {
                    $errors = 'An error occurred. Please try again.';
                }
            }
        }

        // insert into database
        $stmt = $dbConnection->prepare("INSERT INTO savings (saving_id, saving_customer_id, saving_customer_account_number, saving_collector_id, saving_amount, saving_date_collected, saving_note) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([$unique_id, $find_customer_row->customer_id, $customer_account_number, $collector_id, $transaction_amount, $transaction_date, $transaction_note]);

        if ($stmt) {
            // 
            $log_message = ucwords($added_by) . ' [' . $added_by_id . '] added new transaction to ' . ucwords($customer_name) . ' (' . $customer_account_number . ') account';
            add_to_log($log_message, $added_by_id, $added_by);

            $message = 'Transaction added successfully.';
        } else {
            $errors = 'An error occurred. Please try again.';
        }
    } else {
        $errors = 'Invalid request.';
    }

    if ($errors) {
        echo json_encode(['status' => 'error', 'message' => $errors]);
    } else {
        echo json_encode(['status' => 'success', 'message' => $message]);
    }
?>