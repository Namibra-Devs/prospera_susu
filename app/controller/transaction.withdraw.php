<?php
    require ('../../system/DatabaseConnector.php');

    $errors = null;
    $message = null;

    $added_by = null;
    $added_by_id = $admin_id;
    if (admin_has_permission()) {
        $added_by = 'admin';
    } else {
        $errors = 'You don\'t have permission to take this action !';
    }

    // check if is posted
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdrawal_select_customer'])) {

        // get form data
        $customer_info = sanitize($_POST['withdrawal_select_customer']);
        list($customer_name, $customer_account_number) = explode(',', $customer_info);
        $transaction_balance = sanitize($_POST['customer_balance']);
        $transaction_amount = sanitize($_POST['amount-to-withdraw']);
        $payment_mode = sanitize($_POST['withdrawal_payment_mode']); 
        $transaction_date = sanitize($_POST['withdrawal_today_date']);
        $transaction_note = sanitize($_POST['withdrawal_note']);

        $find_customer_row = findCustomerByAccountNumber($customer_account_number);
        if (!$find_customer_row) {
            $errors = 'Customer not found !';
        }

        // account balance
        $a = getCustomerBalance(0, $customer_account_number);
        $b = $a['balance'];

        if ((float)$transaction_balance !== (float)$b) {
            $errors = 'Balance has been temptered !';
        }

         if ((float)$b <= 0) {
            $errors = 'Insufficient Balance !';
        }

        if ((float)$transaction_amount > (float)$b) {
            $errors = 'Amount to withdraw is greater that balance available !';
        }
        $new_balance = (float)($b - $transaction_amount);

        // validate inputs
        $required = array('withdrawal_select_customer', 'amount-to-withdraw', 'withdrawal_payment_mode', 'withdrawal_today_date');
        foreach ($required as $f) {
            if (empty($f)) {
                $errors = $f . ' is required !';
                break;
            }
        }

        $unique_id = guidv4() . '-' . strtotime(date("Y-m-d H:i:s"));

        // insert into database
        $stmt = $dbConnection->prepare("INSERT INTO withdrawals (withdrawal_id, withdrawal_customer_id, withdrawal_customer_account_number, withdrawal_account_balance, withdrawal_approver_id, withdrawal_amount_requested, withdrawal_date_requested, withdrawal_note, withdrawal_mode) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

            // check if there are no error before inserting
            if ($errors) {
                // do nothing
            } else {
            
                $stmt->execute([$unique_id, $find_customer_row->customer_id, $customer_account_number, $new_balance, $admin_id, $transaction_amount, $transaction_date, $transaction_note, $payment_mode]);

                if ($stmt) {
                    // 
                    $log_message = ucwords($added_by) . ' [' . $added_by_id . '] made new withdrawal transaction for ' . ucwords($customer_name) . ' (' . $customer_account_number . ') account';
                    add_to_log($log_message, $added_by_id, $added_by);

                    $message = 'Withdrawal transaction added successfully.';
                } else {
                    $errors = 'An error occurred. Please try again.';
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