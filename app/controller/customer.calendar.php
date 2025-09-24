<?php
// Always return JSON
header('Content-Type: application/json');
    
    // Show all errors instead of silent 500
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/system/logs/php-error.log');

    // Get customer savings for a specific 31-day cycle
    require ('../../system/DatabaseConnector.php');

    $customerId = isset($_GET['customer_id']) ? $_GET['customer_id'] : 0;
    $cycle = isset($_GET['cycle']) ? (int)$_GET['cycle'] : 0;

    if (!$customerId || $customerId <= 0) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid or missing customer_id"]);
        exit;
    }

    // Step 1: get first saving date for this customer
    $sql = "SELECT MIN(saving_date_collected) AS first_date FROM savings WHERE saving_customer_id = ?";
    $stmt = $dbConnection->prepare($sql);
    $stmt->execute([$customerId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || !$row['first_date']) {
        // Customer has no savings yet
        echo json_encode([
            "cycle" => $cycle,
            "cycle_start" => null,
            "cycle_end" => null,
            "saved_days" => []
        ]);
        exit;
    }

    $firstDate = $row['first_date'];

    // Step 2: calculate this cycle's start + end
    $cycleStart = date('Y-m-d', strtotime("$firstDate +".($cycle*31)." days"));
    $cycleEnd   = date('Y-m-d', strtotime("$cycleStart +30 days"));

    // Step 3: fetch savings within this 31-day window
    $sql = "SELECT saving_date_collected, saving_amount
            FROM savings
            WHERE saving_customer_id = ? 
            AND saving_date_collected BETWEEN ? AND ?";
    $stmt = $dbConnection->prepare($sql);
    $stmt->execute([$customerId, $cycleStart, $cycleEnd]);
    $savedDays = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Step 4: prepare results (map day number → amount)
    $result = [];
    foreach ($savedDays as $s) {
        $dayNum = (int)date('j', strtotime($s['saving_date_collected'])); // 1-31
        $result[$dayNum] = $s['saving_amount'];
    }

    // Step 5: return JSON response
    echo json_encode([
        'cycle' => $cycle,
        'cycle_start' => $cycleStart,
        'cycle_end' => $cycleEnd,
        'saved_days' => $result
    ]);
