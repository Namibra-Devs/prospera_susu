<?php
    require ('../../system/DatabaseConnector.php');

    require '../../vendor/autoload.php';
    use PhpOffice\PhpSpreadsheet\IOFactory;

    $validPaymentModes = ['cash','bank','airteltigomoney','mtnmobilemoney','telecelcash'];
    if (!isset($_FILES['file'])) {
        echo "<div class='alert alert-danger'>No file uploaded!</div>";
        exit;
    }

    $fileName = $_FILES['file']['name'];
    $fileTmp = $_FILES['file']['tmp_name'];
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($ext, ['csv', 'xls', 'xlsx'])) {
        echo "<div class='alert alert-danger'>Invalid file format! Only CSV or Excel allowed.</div>";
        exit;
    }

    // Save temporary file
    $targetDir = "../../assets/media/uploads/transactions-media/";
    if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
    $tempFile = $targetDir . time() . "_" . $fileName;
    move_uploaded_file($fileTmp, $tempFile);

    $data = [];
    $total_amount = 0;
    
    // Read file based on extension
    if ($ext == 'csv') {
        $csv = fopen($tempFile, 'r');
        fgetcsv($csv); // skip header
        while(($row = fgetcsv($csv)) !== FALSE) {
            $data[] = $row;
        }
        fclose($csv);
    } else {
        $spreadsheet = IOFactory::load($tempFile);
        $sheet = $spreadsheet->getActiveSheet();
        foreach ($sheet->toArray(null, true, true, true) as $index => $row) {
            if ($index == 1) continue; // skip header
            $data[] = array_values($row);
        }
    }

    $_SESSION['preview_data'] = $data;

    if (empty($data)) {
        echo "<div class='alert alert-warning'>No records found in file.</div>";
        exit;
    }

    // group the data in an array of keys to match the database columns
    $groupedData = [];
    foreach ($data as $row) {

        list($account_number, $amount, $date, $note, $payment_mode, $total_amount) = $row;
        $isValid = true;
        $errors = [];

        // get cusomer account number to fetch customer_id
        $customer_id = findCustomerByAccountNumber($account_number);
        // if (!$customer_id) continue; // skip if account number not found

        // Required field checks
        // if (empty($row[0]) || empty($row[1]) || empty($row[2]) || empty($row[3]) || empty($row[4])) {
        //     $isValid = false;
        //     echo "<div class='alert alert-danger'>Missing required fields</div>";
        //     exit;
        // }

        if (!$customer_id) { $isValid = false; $errors[] = "Missing customer ID"; }
        if (!$account_number) { $isValid = false; $errors[] = "Missing customer account"; }
        // if (!$collector_id) { $isValid = false; echo "<div class='alert alert-danger'>Missing collector ID</div>"; }
        if (!is_numeric($amount) || $amount <= 0) { $isValid = false; $errors[] = "Invalid amount"; }
        if (!$date) { $isValid = false; $errors[] = "Missing date"; }
        // Payment mode validation
        if (!in_array(strtolower($payment_mode), $validPaymentModes)) {
            $isValid = false;
            $errors[] = "Invalid payment mode";
            // exit;
        }

        $groupedData[] = [
            "account_number" => $account_number, 
           // "collector_id" => $collector_id, 
            "customer_id" => $customer_id, 
            "amount" => $amount, 
            "date" => $date, 
            "note" => $note, 
            "payment_mode" => $payment_mode, 
            "total_amount" => $total_amount,
            // "status" => $status, 
            "valid" => $isValid, 
            "errors" => $errors,
        ];

        // $groupedData[] = [
        //     $row[0] ?? null, // account_number
        //    //  $row[1] ?? null, // collector_id
        //     $row[1] ?? null, // amount
        //     $row[2] ?? null, // date
        //     $row[3] ?? null, // note
        //     $row[4] ?? null, // payment_mode
        // ];
    }

?>

<h5 class="mt-2">Preview transactions (<?= count($data) ?> rows)</h5>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead class="table-primary">
            <tr>
                <th>Account Number</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Note</th>
                <th>Payment Mode</th>
                <th>Total Amount</th>
                <th>Errors</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                foreach ($groupedData as $row): 
                    // check for invalid rows and set row to red
                    if (!$row['valid']) {
                        echo "<tr style='--bs-table-bg: #f8d7da;'>";
                    } else {
                        echo "<tr>";
                    }
            ?>
                <td><?= sanitize($row['account_number']) ?></td>
                <!-- <td><?= sanitize($row['collector_id']) ?></td> -->
                <td><?= sanitize($row['amount']) ?></td>
                <td><?= sanitize($row['date']) ?></td>
                <td><?= sanitize($row['note']) ?></td>
                <td><?= sanitize($row['payment_mode']) ?></td>
                <td><?= sanitize($row['total_amount']) ?></td>
                <td>
                    <em class="text-info"><?= implode(", ", array_map('sanitize', $row['errors'])) ?></em>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>