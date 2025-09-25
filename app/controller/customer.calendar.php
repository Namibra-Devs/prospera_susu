<?php
    // customer.calendar.php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    header('Content-Type: application/json');

    require ('../../system/DatabaseConnector.php');

    // Params
    $customer_id = isset($_GET['customer_id']) ? sanitize($_GET['customer_id']) : 0;
    $cycle = isset($_GET['cycle']) ? (int)$_GET['cycle'] : null;

    if ($customer_id <= 0) {
        echo json_encode(["error" => "Invalid customer_id"]);
        exit;
    }

    // Get first saving date
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
            "cycle" => 0,
            "cycle_start" => null,
            "cycle_end" => null,
            "saved_days" => [],
            "commission_day" => null,
            "active_cycle" => 0
        ]);
        exit;
    }

    $firstDate = new DateTime($row['first_date']);

    // Count total savings days
    $sqlCount = "
        SELECT COUNT(*) as total_days 
        FROM savings 
        WHERE saving_customer_id = ?
    ";
    $stmt = $dbConnection->prepare($sqlCount);
    $stmt->execute([$customer_id]);
    $totalDays = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total_days'];

    // Auto detect cycle if none given
    if ($cycle === null) {
        $cycle = intdiv($totalDays, 31);
    }

    // Cycle boundaries
    $cycleStart = clone $firstDate;
    $cycleStart->modify("+" . ($cycle * 31) . " days");
    $cycleEnd = clone $cycleStart;
    $cycleEnd->modify("+30 days");

    // Fetch savings in this cycle
    $sql = "
        SELECT saving_id, saving_amount, saving_date_collected, saving_collector_id
        FROM savings
        WHERE saving_customer_id = ?
        AND saving_date_collected BETWEEN ? AND ?
    ";
    $stmt = $dbConnection->prepare($sql);
    $stmt->execute([$customer_id, $cycleStart->format("Y-m-d"), $cycleEnd->format("Y-m-d")]);

    $savedDays = [];
    while ($s = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $dayNumber = (new DateTime($s['saving_date_collected']))->diff($cycleStart)->days + 1;
        $savedDays[$dayNumber] = [
            "amount" => $s['saving_amount'], 
            "date" => $s['saving_date_collected'], 
            "collector_id" => $s['saving_collector_id']
        ];
    }

    // Check commission
    $sqlCommission = "
        SELECT commission_date 
        FROM commissions 
        WHERE commission_customer_id = ? 
        AND commission_date BETWEEN ? AND ?
        LIMIT 1
    ";
    $stmt = $dbConnection->prepare($sqlCommission);
    $stmt->execute([$customer_id, $cycleStart->format("Y-m-d"), $cycleEnd->format("Y-m-d")]);
    $commission = $stmt->fetch(PDO::FETCH_ASSOC);

    $commissionDay = null;
    if ($commission && $commission['commission_date']) {
        $commissionDay = (new DateTime($commission['commission_date']))->diff($cycleStart)->days + 1;
    }

    echo json_encode([
        "cycle" => $cycle,
        "active_cycle" => intdiv($totalDays, 31),
        "cycle_start" => $cycleStart->format("Y-m-d"),
        "cycle_end" => $cycleEnd->format("Y-m-d"),
        "saved_days" => $savedDays,
        "commission_day" => $commissionDay
    ]);