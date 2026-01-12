<?php
session_start();
include '../includes/db_connection.php';

if (!isset($_GET['id'])) {
    die("Invalid Student ID");
}

$id = $_GET['id'];

$stmt = $pdo->prepare("
    SELECT 
        s.* 
    FROM students s
    WHERE s.id = ?
");
$stmt->execute([$id]);
$std = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$std) {
    die("Student not found");
}

/* QR CODE DATA */
$qrData = urlencode(
    "FASTTRACK COLLEGE\n" .
    "Admission No: {$std['admission_number']}\n" .
    "Student: {$std['surname']} {$std['first_name']} {$std['middle_name']}\n" .
    "Level: {$std['level']}\n" .
    "Academic Year: {$std['year_of_admission']}"
);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Admission Letter | Fasttrack College</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
    body {
        background: #f4f6f9;
        font-family: "Poppins", sans-serif;
    }

    .letter-wrapper {
        max-width: 850px;
        min-height: 1123px;
        margin: 20px auto;
        background: #ffffff;
        padding: 40px 55px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.12);
        page-break-inside: avoid;
    }

    .school-header {
        text-align: center;
        border-bottom: 4px solid #000;
        padding-bottom: 18px;
        margin-bottom: 28px;
    }

    .school-header img {
        max-height: 90px;
        margin-bottom: 8px;
    }

    .school-header h2 {
        font-weight: 900;
        letter-spacing: 2px;
        margin-bottom: 4px;
        font-size: 30px;
        text-transform: uppercase;
    }

    .school-header p {
        margin: 0;
        font-size: 14px;
        line-height: 1.5;
    }

    .ref-date {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
        font-size: 14px;
    }

    .letter-title {
        text-align: center;
        font-weight: bold;
        text-decoration: underline;
        margin: 25px 0;
        font-size: 17px;
    }

    p {
        font-size: 15px;
        line-height: 1.6;
        margin-bottom: 14px;
    }

    .signature-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-top: 45px;
        page-break-inside: avoid;
    }

    .signature-left {
        width: 60%;
    }

    .signature-right {
        width: 35%;
        text-align: right;
    }

    .signature-right img {
        width: 120px;
        height: 120px;
    }

    @media print {
        body {
            background: none;
        }

        .no-print {
            display: none;
        }

        .letter-wrapper {
            box-shadow: none;
            margin: 0;
            padding: 35px 50px;
        }
    }
    </style>
</head>

<body>

    <div class="no-print text-center my-3">
        <button onclick="window.print()" class="btn btn-primary">
            Print Admission Letter
        </button>
    </div>

    <div class="letter-wrapper">

        <!-- SCHOOL HEADER -->
        <div class="school-header">
            <img src="../assets/images/logo.png" alt="Fasttrack College Logo">

            <h2>FAST TRACK COLLEGE</h2>

            <p><strong>Integrity • Excellence • Inclusivity • Innovation • Leadership</strong></p>

            <p>
                P.O. Box 73, Agona Ashanti – Ghana<br>
                Tel: +233551483163 | Email: info@fasttrack.edu.gh | Website: www.fasttrack.edu.gh
            </p>
        </div>

        <!-- REF & DATE -->
        <div class="ref-date">
            <div>
                <strong>Our Ref:</strong>
                FTC/ADM/<?= htmlspecialchars($std['admission_number']); ?>
            </div>
            <div>
                <strong>Date:</strong>
                <?= date('d F Y'); ?>
            </div>
        </div>

        <!-- STUDENT -->
        <p>
            Dear
            <strong><?= htmlspecialchars($std['surname'].' '.$std['first_name'].' '.$std['middle_name']); ?></strong>,
        </p>

        <!-- TITLE -->
        <div class="letter-title">LETTER OF ADMISSION</div>

        <!-- BODY -->
        <p>
            We are pleased to formally inform you that you have been offered
            <strong>provisional admission</strong> into
            <strong><?= htmlspecialchars($std['level']); ?></strong>,
            at <strong>Fast Track College</strong> .

        <p>
            This offer of admission is made following a careful assessment of your
            academic credentials. You are required to report to the school on the
            official reopening date with all required admission documents and
            personal items as stipulated by the school.
        </p>

        <p>
            Please note that this admission is subject to your strict compliance
            with all rules, regulations, and codes of conduct governing students of
            Fasttrack College. Failure to comply may result in disciplinary action
            or withdrawal of this offer.
        </p>

        <p>
            We congratulate you on your successful admission and warmly welcome
            you to Fasttrack College. We are confident that you will take full
            advantage of the academic opportunities and disciplined environment
            provided by the institution.
        </p>

        <!-- SIGNATURE + QR -->
        <div class="signature-row">

            <!-- LEFT -->
            <div class="signature-left">
                <p>Yours faithfully,</p>
                <br>
                <p>
                    _______________________________<br>
                    <strong>THE HEADMASTER</strong><br>
                    <strong>FAST TRACK COLLEGE</strong>
                </p>
            </div>

            <!-- RIGHT -->
            <div class="signature-right">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= $qrData; ?>"
                    alt="Admission Verification QR Code">
                <div style="font-size:12px; margin-top:6px;">
                    Admission Verification
                </div>
            </div>

        </div>

    </div>

</body>

</html>