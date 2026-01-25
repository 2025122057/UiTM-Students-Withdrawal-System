<?php
require_once 'includes/db.php';
require_once 'includes/auth_check.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM withdrawals WHERE id = ?");
$stmt->execute([$id]);
$record = $stmt->fetch();

if (!$record || ($_SESSION['role'] != 'admin' && $record['user_id'] != $_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Export PDF - #
        <?php echo $record['id']; ?>
    </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: white;
            font-family: serif;
            font-size: 12pt;
        }

        .print-container {
            max-width: 800px;
            margin: 20px auto;
            border: 1px solid #ddd;
            padding: 40px;
        }

        .header-title {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .section-title {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 20px;
            text-transform: uppercase;
        }

        .field-row {
            margin-bottom: 10px;
            display: flex;
        }

        .field-label {
            width: 200px;
            font-weight: bold;
        }

        .field-value {
            flex: 1;
            border-bottom: 1px dotted #000;
        }

        .checkbox-container {
            display: flex;
            flex-wrap: wrap;
        }

        .checkbox-item {
            width: 50%;
            margin-bottom: 5px;
        }

        .checkbox-box {
            display: inline-block;
            width: 15px;
            height: 15px;
            border: 1px solid #000;
            margin-right: 5px;
            text-align: center;
            line-height: 12px;
        }

        @media print {
            .no-print {
                display: none;
            }

            .print-container {
                border: none;
                margin: 0;
                padding: 0;
            }

            body {
                margin: 0;
            }
        }
    </style>
</head>

<body>
    <div class="no-print text-center my-4">
        <button onclick="window.print()" class="btn btn-primary btn-lg">Download as PDF / Print</button>
        <a href="view.php?id=<?php echo $id; ?>" class="btn btn-secondary btn-lg">Back</a>
    </div>

    <div class="print-container">
        <div class="header-title">
            <h3>UNIVERSITI TEKNOLOGI MARA</h3>
            <h4>BORANG MENARIK DIRI DARI UiTM</h4>
        </div>

        <div class="section-title">A. MAKLUMAT PELAJAR</div>
        <div class="field-row">
            <span class="field-label">NAMA:</span>
            <span class="field-value">
                <?php echo strtoupper(htmlspecialchars($record['name'])); ?>
            </span>
        </div>
        <div class="field-row">
            <span class="field-label">NO. PELAJAR:</span>
            <span class="field-value">
                <?php echo htmlspecialchars($record['student_id']); ?>
            </span>
            <span class="field-label ms-3">NO. K/P:</span>
            <span class="field-value">
                <?php echo htmlspecialchars($record['ic_number']); ?>
            </span>
        </div>
        <div class="field-row">
            <span class="field-label">KOD PROGRAM:</span>
            <span class="field-value">
                <?php echo htmlspecialchars($record['program_code']); ?>
            </span>
            <span class="field-label ms-3">SEMESTER:</span>
            <span class="field-value">
                <?php echo htmlspecialchars($record['semester']); ?>
            </span>
        </div>
        <div class="field-row">
            <span class="field-label">NO. TELEFON:</span>
            <span class="field-value">
                <?php echo htmlspecialchars($record['phone']); ?>
            </span>
        </div>
        <div class="field-row">
            <span class="field-label">E-MEL:</span>
            <span class="field-value">
                <?php echo htmlspecialchars($record['email']); ?>
            </span>
        </div>

        <div class="section-title">B. SEBAB MENARIK DIRI</div>
        <div class="checkbox-container mt-2">
            <?php
            $reasons = [
                "Tidak minat bidang pengajian",
                "Masalah kewangan",
                "Bertukar Universiti/Kolej",
                "Masalah kesihatan",
                "Masalah peribadi",
                "Mendapat pekerjaan",
                "Lain-lain"
            ];
            foreach ($reasons as $r): ?>
                <div class="checkbox-item">
                    <span class="checkbox-box">
                        <?php echo ($record['reason'] == $r || (!in_array($record['reason'], array_slice($reasons, 0, 6)) && $r == 'Lain-lain')) ? 'X' : ''; ?>
                    </span>
                    <?php echo $r; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if ($record['other_reason']): ?>
            <div class="mt-2">
                <strong>Catatan:</strong>
                <?php echo htmlspecialchars($record['other_reason']); ?>
            </div>
        <?php endif; ?>

        <div class="section-title">C. ALAMAT PELAJAR</div>
        <div class="mt-2 border p-2" style="min-height: 80px;">
            <?php echo nl2br(htmlspecialchars($record['address'])); ?>
        </div>

        <div class="mt-5 pt-5">
            <p><strong>PENGAKUAN PELAJAR</strong></p>
            <p>Saya akui semua maklumat yang diberikan adalah benar.</p>
            <div class="mt-4 d-flex justify-content-between">
                <span>................................................<br>Tandatangan Pelajar</span>
                <span>Tarikh:
                    <?php echo date('d/m/Y', strtotime($record['created_at'])); ?>
                </span>
            </div>
        </div>

        <div class="mt-5 border-top pt-3">
            <p class="small text-muted text-center">Generated via UiTM Withdrawal System - IMS566</p>
        </div>
    </div>

    <script>
        // Auto trigger print dialog if needed
        // window.print();
    </script>
</body>

</html>