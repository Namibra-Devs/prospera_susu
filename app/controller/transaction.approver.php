<?php
    header("Content-Type: application/json");
    require ('../../system/DatabaseConnector.php');

    $data = json_decode(file_get_contents("php://input"), true);
    $response = ["message" => "⚠️ Invalid request", "updated" => []];

    // validate payload
    $action = isset($data['action']) ? $data['action'] : '';
    $transactions = isset($data['transactions']) && is_array($data['transactions']) ? $data['transactions'] : [];

    if (!in_array($action, ['approve', 'reject']) || empty($transactions)) {
        echo json_encode($response);
        exit;
    }
    $status = ($action === "approve") ? "Approved" : "Rejected";
    $newdate = date('Y-m-d H:i:s');


    foreach ($transactions as $trans) {
        // sanitize input a bit
        $trans = trim($trans);
        try {
            $sql = null;
            $params = [];

            // advance withdrawal: advance_withdrawal_{id}
            if (preg_match('/^advance_withdrawal_(.+)$/', $trans, $m)) {
                $aid = $m[1];
                // update withdrawals rows that belong to that advance_id
                $sql = "UPDATE withdrawals SET withdrawal_status = ?, withdrawal_approver_id = ?, withdrawal_date_approved = ? WHERE withdrawal_advance_id = ?";
                $params = [$status, $admin_id, $newdate, $aid];

            // advance deposit (or generic advance): advance_deposit_{id} OR advance_{id}
            } elseif (preg_match('/^advance_deposit_(.+)$/', $trans, $m) || preg_match('/^advance_(.+)$/', $trans, $m)) {
                $aid = $m[1];
                // update saving_advance summary record
                // (set advance_status and record approver/updated time)
                $sql = "UPDATE savings SET saving_status = ?, saving_approved_by = ?, saving_operation_date = ? WHERE saving_advance_id = ?";
                $params = [$status, $admin_id, $newdate, $aid];

            // regular save or withdraw rows: save_{id} or withdraw_{id}
            } elseif (preg_match('/^(save|withdraw)_(.+)$/', $trans, $m)) {
                $type = $m[1];
                $id = $m[2];
                if ($type === "save") {
                    $sql = "UPDATE savings SET saving_status = ?, saving_approved_by = ?, saving_operation_date = ? WHERE saving_id = ?";
                    $params = [$status, $admin_id, $newdate, $id];
                } else { // withdraw
                    $sql = "UPDATE withdrawals SET withdrawal_status = ?, withdrawal_approver_id = ?, withdrawal_date_approved = ? WHERE withdrawal_id = ?";
                    $params = [$status, $admin_id, $newdate, $id];
                }

            } else {
                // unknown format — skip but record error
                $response['message'] = "Unrecognized transaction id format: {$trans}";
                continue;
            }

            if ($sql) {
                $stmt = $dbConnection->prepare($sql);
                $ok = $stmt->execute($params);
                if ($ok) {
                    // update saving advance table
                    $newSQL = "UPDATE saving_advance SET advance_status = ?, advance_operated_by = ?, updated_at = ? WHERE advance_id = ?";
                    $newParams = [$status, $admin_id, $newdate, $aid];
                    $newStmt = $dbConnection->prepare($newSQL);
                    $newStmt->execute($newParams);


                    // log if function exists
                    if (function_exists('add_to_log')) {
                        $log_message =  'Admin [' . $admin_id . '] set ' . $trans .' status to ' . $status . '!';
                        add_to_log($log_message, $admin_id, 'admin');
                    }
                    $response['updated'][] = ["id" => $trans, "status" => $status];
                } else {
                    $response['message'] = "Failed to update: {$trans}";
                }
            }
        } catch (Exception $e) {
            $response['message'] = "Exception for {$trans}: " . $e->getMessage();
        }
    }

    $response['message'] = "✅ Selected transactions processed. (" . count($response['updated']) . " updated)";
    echo json_encode($response);


    // if (!empty($data['action']) && !empty($data['transactions'])) {
    //     $status = ($data['action'] === "approve") ? "Approved" : "Rejected";
    //     foreach ($data['transactions'] as $trans) {
    //         // advance_deposit_4f501fc0-3805-4777-a2e1-0b1ae3d73d54-1764456827
    //         // Extract type and ID and check if its starts with "advance_deposit" or "advance_withdrawal"
    //         $admin_id = $_SESSION['admin_id'];
    //         if (strpos($trans, "advance_deposit_") === 0) {
    //             $type = "advance_deposit";
    //             $id = substr($trans, strlen("advance_deposit_"));
    //             $sql = "UPDATE saving_status SET saving_status = ?, saving_approved_by = ?, saving_operation_date = ? WHERE advance_id = ?";
    //         } elseif (strpos($trans, "advance_withdrawal_") === 0) {
    //             $type = "advance_withdrawal";
    //             $id = substr($trans, strlen("advance_withdrawal_"));
    //             $sql = "UPDATE withdrawals SET withdrawal_status = ?, withdrawal_approver_id = ?, withdrawal_date_approved = ? WHERE advance_id = ?";
    //         } else {
    //             // Each transaction ID is prefixed with its type (e.g., "save_123", "withdraw_456")
    //             list($type, $id) = explode("_", $trans, 2);
    //             $newdate = date('Y-m-d H:i:s');
    
    //             if ($type === "save") {
    //                 $sql = "UPDATE savings SET saving_status = ?, saving_approved_by = ?, saving_operation_date = ? WHERE saving_id = ?";
    //             } elseif ($type === "withdraw") {
    //                 $sql = "UPDATE withdrawals SET withdrawal_status = ?, withdrawal_approver_id = ?, withdrawal_date_approved = ? WHERE withdrawal_id = ?";
    //             } else {
    //                 continue;
    //             }
    //         }


    //         $stmt = $dbConnection->prepare($sql);
    //         $result = $stmt->execute([$status, $admin_id, $newdate, $id]);
    //         if ($result) {
    //             $log_message =  'Admin [' . $admin_id . '] set ' . $type .' [' . $id . '] status to ' . $data['action'] . '!';
    //             add_to_log($log_message, $admin_id, 'admin');
        
    //             $response['updated'][] = ["id" => $trans, "status" => $status];

    //         }
    //     }
        
    //     $response['message'] = "✅ Selected transactions have been {$status}.";
    // }

    // echo json_encode($response);