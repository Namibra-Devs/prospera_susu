<?php
    // customer.calendar.php

    // Always return JSON
    header('Content-Type: application/json');
    
    // Show all errors instead of silent 500
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/system/logs/php-error.log');

    require ('../../system/DatabaseConnector.php');

    // Get request params
    $customer_id = isset($_GET['customer_id']) ? $_GET['customer_id'] : 0;
    $cycle = isset($_GET['cycle']) ? (int)$_GET['cycle'] : 0;

    if (!$customer_id || $customer_id <= 0) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid or missing customer_id"]);
        exit;
    }

    // Get customer first saving date
    $sqlFirst = "
        SELECT MIN(saving_date_collected) AS first_date 
        FROM savings 
        WHERE saving_customer_id = ?
    ";
    $stmt = $dbConnection->prepare($sqlFirst);
    $stmt->execute([$customer_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || !$row['first_date']) {
        echo json_encode([
            "cycle" => $cycle,
            "cycle_start" => null,
            "cycle_end" => null,
            "saved_days" => [],
            "commission_day" => null
        ]);
        exit;
    }

    $firstDate = new DateTime($row['first_date']);

    // Calculate cycle start & end relative to first saving
    $cycleStart = clone $firstDate;
    $cycleStart->modify("+" . ($cycle * 31) . " days");
    $cycleEnd = clone $cycleStart;
    $cycleEnd->modify("+30 days");

    // Fetch all savings for this cycle
    $sql = "
        SELECT saving_date_collected, saving_amount 
        FROM savings 
        WHERE saving_customer_id = ? 
        AND saving_date_collected BETWEEN ? AND ?
    ";
    $stmt = $dbConnection->prepare($sql);
    $stmt->execute([$customer_id, $cycleStart->format("Y-m-d"), $cycleEnd->format("Y-m-d")]);

    $savedDays = [];
    while ($s = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $dayNumber = (new DateTime($s['saving_date_collected']))->diff($cycleStart)->days + 2; // 1-31
        $savedDays[$dayNumber] = $s['saving_amount'];
    }

    // Check if commission was deducted for this cycle
    $sqlCommission = "SELECT commission_date 
                    FROM commissions 
                    WHERE commission_customer_id = ? 
                    AND commission_date BETWEEN ? AND ?
                    LIMIT 1";
    $stmt = $dbConnection->prepare($sqlCommission);
    $stmt->execute([$customer_id, $cycleStart->format("Y-m-d"), $cycleEnd->format("Y-m-d")]);
    $commission = $stmt->fetch(PDO::FETCH_ASSOC);

    $commissionDay = null;
    if ($commission && $commission['commission_date']) {
        $commissionDay = (new DateTime($commission['commission_date']))->diff($cycleStart)->days + 1;
    }

    // Response
    echo json_encode([
        "cycle" => $cycle,
        "cycle_start" => $cycleStart->format("Y-m-d"),
        "cycle_end" => $cycleEnd->format("Y-m-d"),
        "saved_days" => $savedDays,
        "commission_day" => $commissionDay
    ]);