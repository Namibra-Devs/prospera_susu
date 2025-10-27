<?php
    header("Content-Type: application/json");
    require ('../../system/DatabaseConnector.php');

    $data = json_decode(file_get_contents("php://input"), true);
    $response = ["message" => "⚠️ Invalid request", "updated" => []];

    if (!empty($data['action']) && !empty($data['transactions'])) {
        $status = ($data['action'] === "approve") ? "Approved" : "Rejected";
    
        foreach ($data['transactions'] as $trans) {
            list($type, $id) = explode("_", $trans);
            $newdate = date('Y-m-d H:i:s');

            if ($type === "save") {
                $sql = "UPDATE savings SET saving_status = ?, saving_approved_by = ?, saving_operation_date = ? WHERE saving_id = ?";
            } elseif ($type === "withdraw") {
                $sql = "UPDATE withdrawals SET withdrawal_status = ?, withdrawal_approver_id = ?, withdrawal_date_approved = ? WHERE withdrawal_id = ?";
            } else {
                continue;
            }

            $stmt = $dbConnection->prepare($sql);
            $result = $stmt->execute([$status, $admin_id, $newdate, $id]);
            if ($result) {
                $log_message =  'Admin [' . $admin_id . '] set ' . $type .' [' . $id . '] status to ' . $data['action'] . '!';
                add_to_log($log_message, $admin_id, 'admin');
        
                $response['updated'][] = ["id" => $trans, "status" => $status];

            }
        }
        
        $response['message'] = "✅ Selected transactions have been {$status}.";
    }

    echo json_encode($response);