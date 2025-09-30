  <?php
    // upload today collection file

    require ('../../system/DatabaseConnector.php');
    
    $message = null;
    $status = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $idcard = $_POST['idcard'] ?? null;
        $idnumber = $_POST['idnumber'] ?? 0;
        $customer_id = sanitize($_POST['customerid']);

        $customer = findCustomerByID($customer_id);
        if (!$customer) {
            $message = "Unknown customer to update !";
        }

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
        $f_name =  time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $frontFilename); // prevent overwriting and sanitize filename
        $front_targetPath = $uploadDir . $f_name;
        $f_move = move_uploaded_file($frontFile['tmp_name'], $front_targetPath);

        $back_targetPath = $uploadDir . $backFilename;
        $b_name = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $backFilename); // prevent overwriting and sanitize filename
        $back_targetPath = $uploadDir . $b_name;
        $b_move = move_uploaded_file($backFile['tmp_name'], $back_targetPath);
 
        if ($message == null || $message == '' || empty($message)) {
            if ($f_move && $b_move) {

                if ($customer->customer_id_photo_front != NULL || $customer->customer_id_photo_front != '') {
                    $filepath = BASEURL . 'assets/media/uploads/customers-media/' . $customer->customer_id_photo_front;
                    unlink($filepath); 
                }
                
                if ($customer->customer_id_photo_back != NULL || $customer->customer_id_photo_back != '') {
                    $filepath = BASEURL . 'assets/media/uploads/customers-media/' . $customer->customer_id_photo_back;
                    unlink($filepath);
                }

                // insert into daily_collections
                $sql = "UPDATE customers SET customer_id_type = ?, customer_id_number = ?, customer_id_photo_front = ?, customer_id_photo_back = ? WHERE customer_id = ?";
                $stmt = $dbConnection->prepare($sql);
                $result = $stmt->execute([$idcard, $idnumber, $f_name, $b_name, $customer_id]);
                if (!$result) {
                    $message = "Database error: Could not update file info.";
                }
            } else {
                $message = "File upload failed.";
            }
        }

        if ($message) {
            $status = 'error';
        } else {
            $status = 'success';
            $message = "File uploaded successfully";
        }
        echo json_encode(['status' => $status, 'message' => $message]);
    }