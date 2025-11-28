<?php 
    require ('../../system/DatabaseConnector.php');

    if (!admin_is_logged_in()) {
        admin_login_redirect();
    }
    
    require '../../vendor/autoload.php';

    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    use PhpOffice\PhpSpreadsheet\Writer\Xls;
    use PhpOffice\PhpSpreadsheet\Writer\Csv;
    use PhpOffice\PhpSpreadsheet\IOFactory;

    
    // Dompdf, Mpdf or Tcpdf (as appropriate)
    // $className = \PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf::class;
    // IOFactory::registerWriter('Pdf', $className);

    // $class = \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf::class;
    // \PhpOffice\PhpSpreadsheet\IOFactory::registerWriter('Pdf', $class);

    // check if export data session is available
    $session_data = issetElse($_SESSION, 'transaction_stats', []);
    if (empty($session_data) || !is_array($session_data)) {
        $_SESSION['flash_error'] = "No data to export!";
        redirect(PROOT . "app/transactions/collectors.filter");
        exit;
    }

    // sanitize and validate requested export type
    $allowedTypes = ['xlsx','xls','csv','pdf'];
    $exp_type = isset($_GET['export-type']) ? strtolower(sanitize($_GET['export-type'])) : '';
    if (!in_array($exp_type, $allowedTypes)) {
        $_SESSION['flash_error'] = "Invalid export type requested!";
        redirect(PROOT . "app/transactions/collectors.filter");
        exit;
    }

    // optional status/label used for filename/logging
    $exp_status = isset($_GET['status']) ? sanitize($_GET['status']) : 'filter';

    // register PDF writer (mpdf) if available
    try {
        $pdfClass = \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf::class;
        \PhpOffice\PhpSpreadsheet\IOFactory::registerWriter('Pdf', $pdfClass);
    } catch (\Throwable $e) {
        // ignore if pdf writer not available; we'll catch later if requested
    }

    if (isset($_GET['export-type'])) {
        $rows = $session_data;
        $count_row = count($rows);

        if ($count_row > 0) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Header
            $sheet->setCellValue('A1', 'Client');
            $sheet->setCellValue('B1', 'Amount');
            $sheet->setCellValue('C1', 'Handler');
            $sheet->setCellValue('D1', 'Type');
            $sheet->setCellValue('E1', 'Status');
            $sheet->setCellValue('F1', 'Date');

            $rowCount = 2;
            foreach ($rows as $row) {
                // support both array and object rows
                $account_number = isset($row->account_number) ? $row->account_number : (isset($row['account_number']) ? $row['account_number'] : null);
                $collector_id = isset($row->collector_id) ? $row->collector_id : (isset($row['collector_id']) ? $row['collector_id'] : null);
                $amount = isset($row->amount) ? $row->amount : (isset($row['saving_amount']) ? $row['saving_amount'] : (isset($row['withdrawal_amount_requested']) ? $row['withdrawal_amount_requested'] : 0));
                $typeVal = isset($row->type) ? $row->type : (isset($row['type']) ? $row['type'] : null);
                $statusVal = isset($row->status) ? $row->status : (isset($row['status']) ? $row['status'] : '');
                $transaction_date = isset($row->transaction_date) ? $row->transaction_date : (isset($row['saving_operation_date']) ? $row['saving_operation_date'] : (isset($row['withdrawal_date_approved']) ? $row['withdrawal_date_approved'] : ''));


                // get customer name
                // $client_name = findCustomerByAccountNumber($row->account_number)->customer_name;
                // if (!$client_name) {
                //     $client_name = 'Unknown';
                // }

                // get customer name
                $client_name = 'Unknown';
                if ($account_number) {
                    $c = findCustomerByAccountNumber($account_number);
                    if ($c && isset($c->customer_name)) $client_name = $c->customer_name;
                }

                // get handler name
                // $handler = findAdminById($row->collector_id)->admin_name;
                // if (!$handler) {
                //     $handler = 'Admin';
                // }
                $handler = 'Admin';
                if ($collector_id) {
                    $h = findAdminById($collector_id);
                    if ($h && isset($h->admin_name)) $handler = $h->admin_name;
                }

                // if ($row->type === 'saving') {
                //     $trans_type = 'Saving';
                // } elseif ($row->type === 'withdrawal') {
                //     $trans_type = 'Withdrawal';
                // } else {
                //     $trans_type = 'Unknown';
                // }

                if ($typeVal === 'saving') {
                    $trans_type = 'Saving';
                } elseif ($typeVal === 'withdrawal') {
                    $trans_type = 'Withdrawal';
                } else {
                    $trans_type = ucfirst($typeVal ?? 'Unknown');
                }

                // $sheet->setCellValue('A' . $rowCount, $client_name);
                // $sheet->setCellValue('B' . $rowCount, $row->amount);
                // $sheet->setCellValue('C' . $rowCount, ucwords($handler));
                // $sheet->setCellValue('D' . $rowCount, $trans_type);
                // $sheet->setCellValue('E' . $rowCount, ucwords($row->status));
                // $sheet->setCellValue('F' . $rowCount, date('d M Y H:i:s', strtotime($row->transaction_date)));
                // $rowCount++;
                $sheet->setCellValue('A' . $rowCount, $client_name);
                $sheet->setCellValue('B' . $rowCount, $amount);
                $sheet->setCellValue('C' . $rowCount, ucwords($handler));
                $sheet->setCellValue('D' . $rowCount, $trans_type);
                $sheet->setCellValue('E' . $rowCount, ucwords($statusVal));
                $sheet->setCellValue('F' . $rowCount, $transaction_date ? date('d M Y H:i:s', strtotime($transaction_date)) : '');
                $rowCount++;
            }

            // $FileExtType = $exp_type;
            // $fileName = "Prospera-susu-Filter-" . $exp_status . "-sheet";

            // build safe filename
            $safeStatus = preg_replace('/[^A-Za-z0-9_\-]/', '-', $exp_status);
            $fileBase = "Prospera-susu-Filter-" . $safeStatus . "-sheet";
            $newFileName = $fileBase . '.' . $exp_type;

            // logging (only if function and admin id available)
            if (function_exists('add_to_log')) {
                global $admin_id;
                $added_by = issetElse($_SESSION, 'admin_name', 'system');
                $message = "exported " . strtoupper($exp_type) . " filter data";
                $log_message = ucwords($added_by) . ' [' . ($admin_id ?? '0') . '] ' . $message;
                add_to_log($log_message, $admin_id ?? null, $added_by);
            }

            // clear any output buffers to avoid corrupting binary output
            while (ob_get_level()) ob_end_clean();

            // if ($FileExtType == 'xlsx') {
            //     $writer = new Xlsx($spreadsheet);
            //     $NewFileName = $fileName . '.xlsx';
            // } elseif($FileExtType == 'xls') {
            //     $writer = new Xls($spreadsheet);
            //     $NewFileName = $fileName . '.xls';
            // } elseif($FileExtType == 'csv') {
            //     $writer = new Csv($spreadsheet);
            //     $NewFileName = $fileName . '.csv';
            // } elseif($FileExtType == 'pdf') {
            //     //$writer = new Csv($spreadsheet);

            //     $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Pdf');
            //     $writer = new \PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf($spreadsheet);

            //     $NewFileName = $fileName . '.pdf';
            // }
            try {
                if ($exp_type == 'xlsx') {
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment; filename="' . rawurlencode($newFileName) . '"');
                    $writer = new Xlsx($spreadsheet);
                    $writer->save('php://output');
                } elseif ($exp_type == 'xls') {
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment; filename="' . rawurlencode($newFileName) . '"');
                    $writer = new Xls($spreadsheet);
                    $writer->save('php://output');
                } elseif ($exp_type == 'csv') {
                    header('Content-Type: text/csv; charset=UTF-8');
                    header('Content-Disposition: attachment; filename="' . rawurlencode($newFileName) . '"');
                    $writer = new Csv($spreadsheet);
                    // ensure delimiter and BOM for Excel compatibility
                    echo "\xEF\xBB\xBF";
                    $writer->save('php://output');
                } elseif ($exp_type == 'pdf') {
                    // try to create a PDF writer via IOFactory
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename="' . rawurlencode($newFileName) . '"');
                    // IOFactory::createWriter may throw if Pdf writer not registered
                    $writer = IOFactory::createWriter($spreadsheet, 'Pdf');
                    $writer->save('php://output');
                }
                // ensure session flag for success
                $_SESSION['flash_success'] = "Downloaded!";
            } catch (\Throwable $e) {
                // report and redirect if writing failed
                $_SESSION['flash_error'] = "Export failed: " . $e->getMessage();
                redirect(PROOT . "app/transactions/collectors.filter");
                exit;
            }

            // $message = "exported " . strtoupper($FileExtType) . " filter data";
            // // 
            // $log_message = ucwords($added_by) . ' [' . $admin_id . '] ' . $message;
            // add_to_log($log_message, $admin_id, $added_by);

            // $writer->save($NewFileName);
            // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            // header('Content-Disposition: attactment; filename="' . urlencode($NewFileName) . '"');
            // $writer->save('php://output');

           // $_SESSION['flash_success'] = "Downloaded!";

            // cleanup session export data if present
            unset($_SESSION['transaction_stats']);

            // stop further output
            exit;

        } else {
            $_SESSION['flash_error'] = "No Record Found!";
        }

        unset($_SESSION['transaction_stats']);
        exit;
    } else {
        $_SESSION['flash_error'] = "Invalid request!";
        redirect(PROOT . "app/");
        exit;
    }
    ?>
