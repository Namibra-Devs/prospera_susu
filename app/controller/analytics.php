<?php
    require ('../../system/DatabaseConnector.php');

    header('Content-Type: application/json');

    try {
        $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

        // Deposits
        $stmt = $dbConnection->prepare("
            SELECT MONTH(saving_date_collected) AS month, SUM(saving_amount) AS total 
            FROM savings 
            WHERE YEAR(saving_date_collected) = ? AND saving_status = 'Approved'
            GROUP BY MONTH(saving_date_collected)
        ");
        $stmt->execute([$year]);
        $deposits = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Withdrawals
        $stmt = $dbConnection->prepare("
            SELECT MONTH(withdrawal_date_requested) AS month, SUM(withdrawal_amount_requested) AS total 
            FROM withdrawals 
            WHERE YEAR(withdrawal_date_requested) = ? AND withdrawal_status = 'Approved'
            GROUP BY MONTH(withdrawal_date_requested)
        ");
        $stmt->execute([$year]);
        $withdrawals = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Commissions
        $stmt = $dbConnection->prepare("
            SELECT MONTH(commission_date) AS month, SUM(commission_amount) AS total 
            FROM commissions 
            WHERE YEAR(commission_date) = ?
            GROUP BY MONTH(commission_date)
        ");
        $stmt->execute([$year]);
        $commissions = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Build response
        $response = [
            "year" => $year,
            "deposits" => $deposits,
            "withdrawals" => $withdrawals,
            "commissions" => $commissions
        ];

        echo json_encode($response);
    } catch (Exception $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }