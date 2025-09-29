<?php

    // get customer balance amount on selected option

    require ('../../system/DatabaseConnector.php');

    if (isset($_GET['customer_name']) && isset($_GET['account_number'])) {

        $customer_name = sanitize($_GET['customer_name']);
        $account_number = sanitize($_GET['account_number']);

        $stmt = $dbConnection->prepare("SELECT customer_default_daily_amount FROM customers WHERE customer_account_number = ? LIMIT 1");
        $stmt->execute([$account_number]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // $row = $result->fetch_assoc();
            echo json_encode(['customer_balance_amount' => $row['customer_default_daily_amount']]);
        } else {
            echo json_encode(['customer_balance_amount' => null]);
        }
        
    } else {
        echo json_encode(['error' => 'Invalid request']);
    }
    