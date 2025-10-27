<?php
    require ('../../system/DatabaseConnector.php');

    require '../../vendor/autoload.php';
    use PhpOffice\PhpSpreadsheet\IOFactory;


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
        // get cusomer account number to fetch customer_id
        $customer_id = findCustomerByAccountNumber($row[0]);
        if (!$customer_id) continue; // skip if account number not found

        $groupedData[] = [
            $row[0] ?? null, // account_number
           //  $row[1] ?? null, // collector_id
            $row[1] ?? null, // amount
            $row[2] ?? null, // date
            $row[3] ?? null, // note
            $row[4] ?? null, // payment_mode
        ];
    }

?>

<h5 class="mt-2">Preview transactions (<?= count($data) ?> rows)</h5>
<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="table-primary">
            <tr>
                <th>Account Number</th>
                <!-- <th>Collector ID</th> -->
                <th>Amount</th>
                <th>Date</th>
                <th>Note</th>
                <th>Payment Mode</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($groupedData as $row): ?>
            <tr>
                <td><?= sanitize($row[0]) ?></td>
                <td><?= sanitize($row[1]) ?></td>
                <td><?= sanitize($row[2]) ?></td>
                <td><?= sanitize($row[3]) ?></td>
                <td><?= sanitize($row[4]) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>