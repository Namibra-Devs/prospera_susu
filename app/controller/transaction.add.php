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
        $payment_mode = sanitize($_POST['payment_mode']); 

        $is_advance_payment = (isset($_POST['is_advance_payment']) && $_POST['is_advance_payment'] === 'yes') ? true : false;
        $advance_payment = ($is_advance_payment && isset($_POST['advance_payment'])) ? sanitize($_POST['advance_payment']) : 0;


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

        // fetch the last saving_date_collected for the customer
        $last_date_sql = "SELECT MAX(saving_date_collected) as last_date 
                        FROM savings 
                        WHERE saving_customer_account_number = ?";
        $last_date_stmt = $dbConnection->prepare($last_date_sql);
        $last_date_stmt->execute([$customer_account_number]);
        $last_date_row = $last_date_stmt->fetch(PDO::FETCH_ASSOC);

        // start from last saved date + 1 day OR today if no previous saving
        $start_date = $last_date_row && $last_date_row['last_date'] 
            ? date('Y-m-d', strtotime($last_date_row['last_date'] . ' +1 day'))
            : date('Y-m-d'); // if no record exists, start from today

        // 
        if ($is_advance_payment) {
            if ($advance_payment <= 1) {
                $errors = 'Please select advance payment option.';
            } elseif ($advance_payment > 31) {
                $errors = 'Advance payment cannot be more than 31 days.';
            }
            // calculate total amount
            $transaction_amount = $transaction_amount * $advance_payment;
            $transaction_note .= ' (Advance payment for ' . $advance_payment . ' days)';

            // create a foreach loop to add multiple transactions for advance payment
            for ($i = 0; $i < $advance_payment; $i++) {

                // always move forward from the last saved date
                $next_date = date('Y-m-d', strtotime($start_date . " + $i days"));

                // safeguard: loop forward until we find a free date
                while (true) {
                    $check_sql = "SELECT 1 FROM savings WHERE saving_customer_account_number = ? AND saving_date_collected = ? LIMIT 1";
                    $check_stmt = $dbConnection->prepare($check_sql);
                    $check_stmt->execute([$customer_account_number, $next_date]);

                    if ($check_stmt->rowCount() > 0) {
                        // date already used → move to next day
                        $next_date = date('Y-m-d', strtotime($next_date . " +1 day"));
                    } else {
                        break; // free date found
                    }
                }

                $next_unique_id = guidv4() . '-' . strtotime(date("Y-m-d H:i:s")) . '-' . $i;


                $stmt = $dbConnection->prepare("INSERT INTO savings (saving_id, saving_customer_id, saving_customer_account_number, saving_collector_id, saving_amount, saving_date_collected, saving_note, saving_mode) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

                // check if there are no error before inserting
                if (empty($errors) || $errors == null) {
                    $stmt->execute([
                        $next_unique_id, 
                        $find_customer_row->customer_id, 
                        $customer_account_number, 
                        $collector_id, 
                        $transaction_amount / $advance_payment, 
                        $next_date, 
                        'Advance payment for ' . $customer_name . ' (' . $customer_account_number . ') for day ' . ($i + 1), 
                        $payment_mode
                    ]);
                    
                    // log message
                    if ($stmt) {
                        processMonthlyCommission($find_customer_row->customer_id);

                        // check if customer_start_date is null, then set it to the date of first saving
                        if ($find_customer_row->customer_start_date == null || $find_customer_row->customer_start_date == '0000-00-00') {
                            $update_start_date_sql = "UPDATE customers SET customer_start_date = ? WHERE customer_id = ?";
                            $update_start_date_stmt = $dbConnection->prepare($update_start_date_sql);
                            $update_start_date_stmt->execute([$next_date, $find_customer_row->customer_id]);
                        }

                        $log_message = ucwords($added_by) . ' [' . $added_by_id . '] added new transaction to ' . ucwords($customer_name) . ' (' . $customer_account_number . ') account for day ' . ($i + 1);
                        add_to_log($log_message, $added_by_id, $added_by);

                        $message = 'Transaction added successfully.';
                    } else {
                        $errors = 'An error occurred. Please try again.';
                    }
                }
            }
        } else {
            // check if today date or selected date already has a transaction
            // $check_sql = "SELECT * FROM savings WHERE saving_customer_account_number = ? AND saving_date_collected = ? LIMIT 1";
            // $check_stmt = $dbConnection->prepare($check_sql);
            // $check_stmt->execute([$customer_account_number, $transaction_date]);
            // if ($check_stmt->rowCount() > 0) {
            //     $errors = 'A transaction for ' . $customer_name . ' (' . $customer_account_number . ') already exists for ' . date('d M, Y', strtotime($transaction_date)) . '.';
            // }

            $next_date = $start_date;

            // safeguard: move forward until we find a free date
            while (true) {
                $check_sql = "SELECT 1 FROM savings WHERE saving_customer_account_number = ? AND saving_date_collected = ? LIMIT 1";
                $check_stmt = $dbConnection->prepare($check_sql);
                $check_stmt->execute([$customer_account_number, $next_date]);

                if ($check_stmt->rowCount() > 0) {
                    $next_date = date('Y-m-d', strtotime($next_date . " +1 day")); // move to next available day
                } else {
                    break;
                }
            }

            $unique_id = guidv4() . '-' . strtotime(date("Y-m-d H:i:s"));

            // insert into database
            $stmt = $dbConnection->prepare("INSERT INTO savings (saving_id, saving_customer_id, saving_customer_account_number, saving_collector_id, saving_amount, saving_date_collected, saving_note, saving_mode) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            // check if there are no error before inserting
            if ($errors) {
                // do nothing
            } else {
            
                $stmt->execute([$unique_id, $find_customer_row->customer_id, $customer_account_number, $collector_id, $transaction_amount, $transaction_date, $transaction_note, $payment_mode]);

                if ($stmt) {
                    processMonthlyCommission($find_customer_row->customer_id);

                    // check if customer_start_date is null, then set it to the date of first saving
                    if ($find_customer_row->customer_start_date == null || $find_customer_row->customer_start_date == '0000-00-00') {
                        $update_start_date_sql = "UPDATE customers SET customer_start_date = ? WHERE customer_id = ?";
                        $update_start_date_stmt = $dbConnection->prepare($update_start_date_sql);
                        $update_start_date_stmt->execute([$next_date, $find_customer_row->customer_id]);
                    }

                    // 
                    $log_message = ucwords($added_by) . ' [' . $added_by_id . '] added new transaction to ' . ucwords($customer_name) . ' (' . $customer_account_number . ') account';
                    add_to_log($log_message, $added_by_id, $added_by);

                    $message = 'Transaction added successfully.';
                } else {
                    $errors = 'An error occurred. Please try again.';
                }
            }
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