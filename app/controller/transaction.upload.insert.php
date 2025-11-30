<?php
    require ('../../system/DatabaseConnector.php');

    if (!isset($_SESSION['preview_data'])) {
        echo "<div class='alert alert-danger'>No data to insert.</div>";
        exit;
    }
    $data = $_SESSION['preview_data'];
    $inserted = 0;
    
    $stmt = $dbConnection->prepare("INSERT INTO savings (saving_id, saving_customer_id, saving_customer_account_number, saving_collector_id, saving_amount, saving_date_collected, saving_note, saving_mode) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($data as $row) {
        if (count($row) < 6) continue;

        list($account_number, $amount, $date, $note, $payment_mode, $advance_option) = $row;
        // get cusomer account number to fetch customer_id
        $get_customer = findCustomerByAccountNumber($account_number);
        $customer_name = ucwords($get_customer->customer_name);
        
        // check if advance_option is yes
        if ($advance_option == 'yes' || $advance_option == 'YES' || $advance_option == '1' || $advance_option == 'y' || $advance_option == 'Y') {
            // grab customer default amount
            $default_amount = $get_customer->customer_default_daily_amount;

            // check if deposit amount id devisible by default aount
            if ($amount % $default_amount !== 0) {
                echo 'Amount must be in multiples of ' . money($default_amount) . ' !';
                exit;
            }

            // calculate advance days
            $advance_payment_days = ($amount / $default_amount);

            // insert into advance table
            $transaction_note .= ' (Advance payment for ' . $advance_payment_days . ' days)';

            // insert advance payment details into advance_payments table
            $advanceSql = "INSERT INTO saving_advance (advance_id, advance_amount, advance_days) VALUE (?, ?, ?)";
            $advanceStmt = $dbConnection->prepare($advanceSql);
            $advance_id = guidv4() . '-' . strtotime(date("Y-m-d H:i:s"));
            $advanceStmt->execute([$advance_id, $amount, $advance_payment_days]);
            
            // loop days and insert into savings table
            // create a foreach loop to add multiple transactions for advance payment
            for ($i = 0; $i < $advance_payment_days; $i++) {

                // always move forward from the last saved date
                $next_date = date('Y-m-d', strtotime($start_date . " + $i days"));

                // safeguard: loop forward until we find a free date
                while (true) {
                    $check_sql = "SELECT 1 FROM savings WHERE saving_customer_account_number = ? AND saving_date_collected = ? LIMIT 1";
                    $check_stmt = $dbConnection->prepare($check_sql);
                    $check_stmt->execute([$account_number, $next_date]);

                    if ($check_stmt->rowCount() > 0) {
                        // date already used → move to next day
                        $next_date = date('Y-m-d', strtotime($next_date . " +1 day"));
                    } else {
                        break; // free date found
                    }
                }
                $next_unique_id = guidv4() . '-' . strtotime(date("Y-m-d H:i:s")) . '-' . $i;

                $stmt = $dbConnection->prepare("INSERT INTO savings (saving_id, saving_customer_id, saving_customer_account_number, saving_collector_id, saving_amount, saving_date_collected, saving_note, saving_mode, saving_advance_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

                // check if there are no error before inserting
                if (empty($errors) || $errors == null) {
                    $stmt->execute([
                        $next_unique_id, 
                        $get_customer->customer_id, 
                        $account_number, 
                        $admin_id, 
                        $get_customer->customer_default_daily_amount, 
                        $next_date, 
                        'Advance payment for ' . $customer_name . ' (' . $account_number . ') for day ' . ($i + 1), 
                        $payment_mode, 
                        $advance_id
                    ]);
                    
                    // log message
                    if ($stmt) {
                        processMonthlyCommission($get_customer->customer_id);

                        // check if customer_start_date is null, then set it to the date of first saving
                        if ($get_customer->customer_start_date == null || $get_customer->customer_start_date == '0000-00-00') {
                            $update_start_date_sql = "UPDATE customers SET customer_start_date = ? WHERE customer_id = ?";
                            $update_start_date_stmt = $dbConnection->prepare($update_start_date_sql);
                            $update_start_date_stmt->execute([$next_date, $get_customer->customer_id]);
                        }

                        $log_message = ucwords($added_by) . ' [' . $admin_id . '] added new transaction to ' . ucwords($customer_name) . ' (' . $account_number . ') account for day ' . ($i + 1);
                        add_to_log($log_message, $admin_id, $added_by);

                        $message = 'Transaction added successfully.';
                    } else {
                        $errors = 'An error occurred. Please try again.';
                    }
                }
            }
        }

        $unique_id = guidv4() . '-' . strtotime(date("Y-m-d H:i:s"));
        // get cusomer account number to fetch customer_id
        $customer_id = findCustomerByAccountNumber($row[0]);
        if (!$customer_id) continue; // skip if account number not found

        // set date to fit database
        $newdate = date('Y-m-d H:i:s', strtotime($row[2]));
        $row[2] = $newdate;

        $stmt->execute([
            $unique_id,
            $customer_id->customer_id, // customer_id (to be fetched from account_number)
            $row[0] ?? null, // account_number
            $admin_id ?? null, // collector_id
            $row[1] ?? null, // amount
            $row[2] ?? null, // date
            $row[3] ?? null, // note
            strtolower($row[4]) ?? null, // payment_mode
        ]);
        $inserted++;
    }

    if (isset($_SESSION['file_upload_data'])) {
        $uploadData = $_SESSION['file_upload_data'];
        $uploadData = $uploadData[0]; // get the first element
        $unique_id = guidv4() . '-' . strtotime(date("Y-m-d H:i:s"));

        $sql = "INSERT INTO `susu_savings_uploaded_files`(`file_id`, `file_name`, `file_path`, `file_total_rows`, `file_valid_rows`, `file_invalid_rows`, `file_in_file_amount`, `file_in_system_amount`, `file_upload_status`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $statement = $dbConnection->prepare($sql);
        $statement->execute([
            $unique_id,
            $uploadData['file_name'], 
            $uploadData['file_path'], 
            $uploadData['total_rows'], 
            $uploadData['valid_rows'], 
            $uploadData['invalid_rows'], 
            $uploadData['in_file_amount'], 
            $uploadData['in_system_amount'], 
            $uploadData['upload_status']
        ]);
        unset($_SESSION['file_upload_data']);
    }

    // if (isset($_SESSION['upload_file_path'])) {
    //     $filePath = '../../' . $_SESSION['upload_file_path'];
    //     if (file_exists($filePath)) {
    //         unlink($filePath); // delete the file after processing
    //     }
    //     unset($_SESSION['upload_file_path']);
    // }

    unset($_SESSION['preview_data']);

    echo "<div class='alert alert-success'>✅ Successfully inserted {$inserted} records into savings table!</div>";