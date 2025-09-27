  <?php
    // upload today collection file

    require ('../../system/DatabaseConnector.php');
    
    $message = null;
    $status = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_date'])) {
        $uploadDate = $_POST['upload_date'] ?? null;
        $totalcollected = $_POST['totalcollected'] ?? 0;
        $note = sanitize($_POST['note']); // 

        // check if today date is already uploaded
        $check_sql = "SELECT * FROM daily_collections WHERE daily_collection_date = ? LIMIT 1";
        $check_stmt = $dbConnection->prepare($check_sql);
        $check_stmt->execute([$uploadDate]);
        if ($check_stmt->rowCount() > 0) {
            $message = "Collection for this date is already uploaded.";
        }

        // Validate file upload
        if (!isset($_FILES['collection_file']) || $_FILES['collection_file']['error'] !== UPLOAD_ERR_OK) {
            $message = "No file uploaded.";
            
        }

        $file = $_FILES['collection_file'];
        $allowedTypes = ['image/png', 'image/jpeg'];
        $maxSize = 6 * 1024 * 1024; // 6MB

        if (!in_array($file['type'], $allowedTypes)) {
            $message = "Only PNG and JPG files are allowed.";
        }

        if ($file['size'] > $maxSize) {
            $message = "File size exceeds 6MB.";
        }

        $uploadDir =  '../../assets/media/uploads/collection-files/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = basename($file['name']);
        
        $targetPath = $uploadDir . $filename;
        $targetPath = $uploadDir . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename); // prevent overwriting and sanitize filename

        $uniqueid = guidv4() . '-' . strtotime(date('Y-m-d H:i:s'));

        if ($message == null || $message == '') {
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                // insert into daily_collections
                $sql = "INSERT INTO daily_collections (daily_id, daily_collector_id, daily_collection_date, daily_total_collected, daily_proof_image, daily_note) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $dbConnection->prepare($sql);
                $result = $stmt->execute([$uniqueid, $collector_id, $uploadDate, $totalcollected, $targetPath, $note]);
                if (!$result) {
                    $message = "Database error: Could not save file info.";
                }
            } else {
                $message = "File upload failed.";
            }
        }

        if ($message) {
            $status = 'error';
        } else {
            $status = 'success';
            $message = "File uploaded successfully for date: " . sanitize($uploadDate);
        }
        echo json_encode(['status' => $status, 'message' => $message]);
    }
