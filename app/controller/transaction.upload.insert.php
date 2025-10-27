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
        if (count($row) < 5) continue;

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

    unset($_SESSION['preview_data']);

    echo "<div class='alert alert-success'>✅ Successfully inserted {$inserted} records into savings table!</div>";