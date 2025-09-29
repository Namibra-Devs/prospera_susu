<?php

    // get customer balance amount on selected option

    require ('../../system/DatabaseConnector.php');

    if (isset($_GET['account_number'])) {

        $account_number = sanitize($_GET['account_number']);

        $balanceData = getCustomerBalance(0, $account_number);

        if ($balanceData) {
            echo json_encode(['customer_balance_amount' => $balanceData['balance']]);
        } else {
            echo json_encode(['customer_balance_amount' => null]);
        }
        
    } else {
        echo json_encode(['error' => 'Invalid request']);
    }
    

//     echo "Account: " . $balanceData['account_number'] . "<br>";
// echo "Total Saves: GHS " . $balanceData['total_saves'] . "<br>";
// echo "Total Withdrawals: GHS " . $balanceData['total_withdrawals'] . "<br>";
// echo "Total Commissions: GHS " . $balanceData['total_commissions'] . "<br>";
// echo "<strong>Balance: GHS " . $balanceData['balance'] . "</strong><br>"