  <?php
    // upload today collection file

    require ('../../system/DatabaseConnector.php');
    
    $message = null;
    $status = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $idcard = $_POST['idcard'] ?? null;
        $idnumber = $_POST['idnumber'] ?? 0;


        // Validate file upload
        if (!isset($_FILES['front_card']) || $_FILES['front_card']['error'] !== UPLOAD_ERR_OK) {
            $message = "No file uploaded.";   
        }
        
        // Validate file upload
        if (!isset($_FILES['back_card']) || $_FILES['back_card']['error'] !== UPLOAD_ERR_OK) {
            $message = "No file uploaded.";
        }

        $frontFile = $_FILES['front_card'];
        $backFile = $_FILES['back_card'];

        $allowedTypes = ['image/png', 'image/jpeg', 'application/pdf'];
        $maxSize = 10 * 1024 * 1024; // 10MB

        if (!in_array($frontFile['type'], $allowedTypes)) {
            $message = "Only PNG and JPG files are allowed on Front card.";
        }

         if (!in_array($backFile['type'], $allowedTypes)) {
            $message = "Only PNG and JPG files are allowed on Back card.";
        }

        if ($frontFile['size'] > $maxSize) {
            $message = "Front file size exceeds 10MB.";
        }
        
        if ($backFile['size'] > $maxSize) {
            $message = "Back file size exceeds 10MB.";
        }

        $uploadDir =  '../../assets/media/uploads/customers-media/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $frontFilename = basename($frontFile['name']);
        $backFilename = basename($backFile['name']);
        
        $front_targetPath = $uploadDir . $frontFilename;
        $front_targetPath = $uploadDir . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $frontFilename); // prevent overwriting and sanitize filename

        $back_targetPath = $uploadDir . $backFilename;
        $back_targetPath = $uploadDir . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $backFilename); // prevent overwriting and sanitize filename

        if ($message == null || $message == '') {
            if (move_uploaded_file($frontFile['tmp_name'], $front_targetPath)) {
                // insert into daily_collections
                $sql = "INSERT INTO daily_collections (daily_id, daily_collector_id, daily_collection_date, daily_total_collected, daily_proof_image, daily_note) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $dbConnection->prepare($sql);
                $result = $stmt->execute([$uniqueid, $admin_id, $uploadDate, $totalcollected, $front_targetPath, $note]);
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
