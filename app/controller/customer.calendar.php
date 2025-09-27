<?php
    // customer.calendar.php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    header('Content-Type: application/json');

    require ('../../system/DatabaseConnector.php');

    // Params
    $customer_id = isset($_GET['customer_id']) ? sanitize($_GET['customer_id']) : 0;
    $requested_cycle = isset($_GET['cycle']) ? $_GET['cycle'] : null; // keep null if not provided

    if ($customer_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid or missing customer_id']);
        exit;
    }

    // Get first saving date
    // --- 1) get the customer's very first saving date (the anchor)
    $sql = "
        SELECT MIN(saving_date_collected) AS first_date
        FROM savings
        WHERE saving_customer_id = ?
    ";
    $stmt = $dbConnection->prepare($sql);
    $stmt->execute([$customer_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || !$row['first_date']) {
        // No savings yet => return empty structured response
        echo json_encode([
            'current_cycle' => 0,
            'active_cycle' => 0,
            'cycle_start' => null,
            'cycle_end' => null,
            'saved_days' => new stdClass(), // empty object
            'commission_day' => null,
            'commission_amount' => null
        ]);
        exit;
    }

    $firstDate = new DateTime($row['first_date']);

    // --- 2) count DISTINCT saved days (important!)
    $sqlCount = "
        SELECT COUNT(DISTINCT saving_date_collected) AS total_saved_days
        FROM savings
        WHERE saving_customer_id = ?
    ";
    $stmt = $dbConnection->prepare($sqlCount);
    $stmt->execute([$customer_id]);
    $total_saved_days = (int)($stmt->fetchColumn() ?: 0);


    // Determine active cycle based on saved days:
    // days 1-31 => cycle 0, 32-62 => cycle 1 ... formula: floor(total_saved_days / 31)
    $active_cycle = intdiv($total_saved_days, 31); // integer division

    // --- 3) determine which cycle to show
    if ($requested_cycle === null || $requested_cycle === '' ) {
        // no cycle requested — auto-detect
        $use_cycle = $active_cycle;
    } else {
        // client requested a particular cycle: normalize to integer >= 0
        $use_cycle = max(0, intval($requested_cycle));
    }


    // --- 4) cycle start and end dates (31-day window)
    $cycleStart = clone $firstDate;
    $cycleStart->modify("+" . ($use_cycle * 31) . " days");
    $cycleEnd = clone $cycleStart;
    $cycleEnd->modify("+30 days");

        // --- 5) fetch savings in this cycle (ordered by date)
    $sql = "
        SELECT saving_id, saving_amount, saving_date_collected, saving_collector_id
        FROM savings
        WHERE saving_customer_id = ?
        AND saving_date_collected BETWEEN ? AND ?
        ORDER BY saving_date_collected ASC, saving_id ASC
    ";
    $stmt = $dbConnection->prepare($sql);
    $stmt->execute([$customer_id, $cycleStart->format('Y-m-d'), $cycleEnd->format('Y-m-d')]);

    // Aggregate savings by dayNumber (1..31). Each day will contain total amount and entries.
    $saved_days = []; // dayNumber => ['date'=>..., 'amount'=>..., 'entries'=>[...]]
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $date = $r['saving_date_collected'];
        // dayNumber relative to cycle start: diff in days + 1
        $dayNumber = (int)( (new DateTime($date))->diff($cycleStart)->days ) + 1;
        if ($dayNumber < 1 || $dayNumber > 31) continue; // safety

        if (!isset($saved_days[$dayNumber])) {
            $saved_days[$dayNumber] = [
                'date' => $date,
                'amount' => 0.00,
                'entries' => []
            ];
        }

        // accumulate amount and push entry
        $saved_days[$dayNumber]['amount'] = round($saved_days[$dayNumber]['amount'] + (float)$r['saving_amount'], 2);
        $saved_days[$dayNumber]['entries'][] = [
            'saving_id' => $r['saving_id'],
            'amount' => (float)$r['saving_amount'],
            'collector_id' => $r['saving_collector_id']
        ];
    }

    // --- 6) check commission for this cycle (if any)
    $sqlComm = "
        SELECT commission_id, commission_amount, commission_date
        FROM commissions
        WHERE commission_customer_id = ?
        AND commission_date BETWEEN ? AND ?
        ORDER BY commission_date ASC
        LIMIT 1
    ";
    $stmt = $dbConnection->prepare($sqlComm);
    $stmt->execute([$customer_id, $cycleStart->format('Y-m-d'), $cycleEnd->format('Y-m-d')]);
    $commRow = $stmt->fetch(PDO::FETCH_ASSOC);

    $commission_day = null;
    $commission_amount = null;
    if ($commRow) {
        $commDate = $commRow['commission_date'];
        $commission_day = (int)((new DateTime($commDate))->diff($cycleStart)->days) + 1;
        if ($commission_day < 1 || $commission_day > 31) {
            $commission_day = null; // safety
        } else {
            $commission_amount = (float)$commRow['commission_amount'];
        }
    }

    // --- 7) response
    echo json_encode([
        'current_cycle' => $use_cycle,
        'active_cycle' => $active_cycle,
        'cycle_start' => $cycleStart->format('Y-m-d'),
        'cycle_end' => $cycleEnd->format('Y-m-d'),
        'saved_days' => $saved_days,
        'commission_day' => $commission_day,
        'commission_amount' => $commission_amount
    ]);