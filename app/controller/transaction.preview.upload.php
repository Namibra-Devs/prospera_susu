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
    $fileNewName = time() . "_" . $fileName;
    $tempFile = $targetDir . $fileNewName;
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
    // calculate total amount
    $total_amount = ($data[0][6] ?? 0);

    if (empty($data)) {
        echo "<div class='alert alert-warning'>No records found in file.</div>";
        exit;
    }

    // group the data in an array of keys to match the database columns
    $groupedData = [];
    $uploadData = [];
    foreach ($data as $row) {
        
        list($account_number, $amount, $date, $note, $payment_mode, $advance_option) = $row;
        $isValid = true;
        $errors = [];

        // get cusomer account number to fetch customer_id
        $customer_id = findCustomerByAccountNumber($account_number);
        // if (!$customer_id) continue; // skip if account number not found

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

        // check if advance_option is yes
        if ($advance_option == 'yes' || $advance_option == 'YES' || $advance_option == '1' || $advance_option == 'y' || $advance_option == 'Y') {
            // grab customer default amount
            $default_amount = $customer_id->customer_default_daily_amount;

            // check if deposit amount id devisible by default aount
            if ($amount % $default_amount !== 0) {
                $errors[] = 'Amount must be in multiples of ' . money($default_amount) . ' !';
            }

            // calculate advance days
            $advance_payment_days = ($amount / $default_amount);

            // insert into advance table
            $transaction_note .= ' (Advance payment for ' . $advance_payment_days . ' days)';

            // insert advance payment details into advance_payments table
            $advanceSql = "INSERT INTO saving_advance (advance_id, advance_amount, advance_days) VALUE (?, ?, ?)";
            $advanceStmt = $dbConnection->prepare($advanceSql);
            $advance_id = guidv4() . '-' . strtotime(date("Y-m-d H:i:s"));
            $advanceStmt->execute([$advance_id, $amount, $advance_payment_days]);
            
            // loop days and insert into savings table

        }

        $groupedData[] = [
            "account_number" => $account_number, 
           // "collector_id" => $collector_id, 
            "customer_id" => $customer_id, 
            "amount" => $amount, 
            "date" => $date, 
            "note" => $note, 
            "payment_mode" => $payment_mode, 
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

    $uploadData[] = [
        "file_path" => substr($targetDir, 6), // remove the first "../../"
        "file_name" => $fileNewName, 
        "total_rows" => count($data),
        "valid_rows" => count(array_filter($groupedData, fn($row) => $row['valid'])),
        "invalid_rows" => count(array_filter($groupedData, fn($row) => !$row['valid'])), 
        "in_file_amount" => $total_amount,
        "in_system_amount" => array_sum(array_column($groupedData, 'amount')),
        "upload_status" => "Pending",
    ];

    // Store preview data in session, but before check all rows are valid
    $count_not_valid = count(array_filter($groupedData, fn($row) => !$row['valid']));
    if ($count_not_valid > 0) {
        // echo "<div class='alert alert-danger'>No valid records to preview. Please check the errors.</div>";
        // exit;
    } else {
        echo "<div class='alert alert-success'>Preview generated. Please review the data before inserting.</div>";
        // only store valid rows
        $_SESSION['preview_data'] = $data;
        // seesion data for file insertion
        $_SESSION['file_upload_data'] = $uploadData;


        // $_SESSION['preview_data'] = array_map(function($row) {
        //     return $row['valid'] ? [
        //         $row['account_number'],
        //        // $row['collector_id'],
        //         $row['amount'],
        //         $row['date'],
        //         $row['note'],
        //         $row['payment_mode'],
        //     ] : null;
        // }, array_filter($groupedData, fn($row) => $row['valid']));
    }

?>

<h5 class="mt-2">Preview transactions</h5>

<div class="d-flex justify-content-between mb-3" style="background-color: rgba(113, 44, 249, .15); border: rgba(113, 44, 249, .3)">
    <div class="p-2" style="background-color: rgba(113, 44, 249, .15); border: rgba(113, 44, 249, .3)">
        Total Rows: <strong><?= count($data) ?></strong>
    </div>
    <div class="p-2" style="background-color: rgba(113, 44, 249, .15); border: rgba(113, 44, 249, .3)">
        Valid Rows: <strong><?= count(array_filter($groupedData, fn($row) => $row['valid'])) ?></strong>
    </div>
    <div class="p-2" style="background-color: rgba(113, 44, 249, .15); border: rgba(113, 44, 249, .3)">
        Invalid Rows: <strong><?= $count_not_valid; ?></strong>
    </div>
    <div class="p-2" style="background-color: rgba(113, 44, 249, .15); border: rgba(113, 44, 249, .3)">
        Total Amount in File: <strong><?= money($total_amount) ?></strong>
    </div>
    <div class="p-2" style="background-color: rgba(113, 44, 249, .15); border: rgba(113, 44, 249, .3)">
        Total Amount on System: <strong><?= money(array_sum(array_column($groupedData, 'amount'))) ?></strong>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered">
        <thead class="table-primary">
            <tr>
                <th>Account Number</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Note</th>
                <th>Payment Mode</th>
                <th>Advance</th>
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
                <td><?= sanitize($row['advance_option']) ?></td>
                <td>
                    <em class="text-info"><?= implode(", ", array_map('sanitize', $row['errors'])) ?></em>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>