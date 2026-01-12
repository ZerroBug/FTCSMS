<?php
require('../includes/db_connection.php');
require('../libs/fpdf/fpdf.php');

$student_id = $_GET['student_id'] ?? null;
if (!$student_id) {
    die('Student ID is required.');
}

/* ================= FETCH STUDENT INFO ================= */
$stmtStudent = $pdo->prepare("
    SELECT *
    FROM students
    WHERE id = ?
");
$stmtStudent->execute([$student_id]);
$student = $stmtStudent->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die('Student not found.');
}

/* ================= FETCH SUBJECTS ================= */
/* Assuming you have an assessment_results table linking student_id + subject_id */
$stmtSubjects = $pdo->prepare("
    SELECT DISTINCT s.id AS subject_id, s.subject_name
    FROM subjects s
    JOIN assessment_results ar ON ar.subject_id = s.id
    WHERE ar.student_id = ?
    ORDER BY s.subject_name
");
$stmtSubjects->execute([$student_id]);
$subjects = $stmtSubjects->fetchAll(PDO::FETCH_ASSOC);

/* ================= FETCH RESULTS ================= */
$stmtGrades = $pdo->prepare("
    SELECT subject_id, semester, academic_year, SUM(score) AS total_score
    FROM assessment_results
    WHERE student_id = ?
    GROUP BY subject_id, semester, academic_year
    ORDER BY academic_year ASC
");
$stmtGrades->execute([$student_id]);
$grades = $stmtGrades->fetchAll(PDO::FETCH_ASSOC);

/* ================= GPA FUNCTION ================= */
function calculateGradeGPA($score) {
    if ($score >= 80) return ['grade'=>'A1','gpa'=>4.0];
    if ($score >= 70) return ['grade'=>'B2','gpa'=>3.5];
    if ($score >= 60) return ['grade'=>'B3','gpa'=>3.0];
    if ($score >= 55) return ['grade'=>'C4','gpa'=>2.5];
    if ($score >= 50) return ['grade'=>'C5','gpa'=>2.0];
    if ($score >= 45) return ['grade'=>'C6','gpa'=>1.5];
    if ($score >= 40) return ['grade'=>'D7','gpa'=>1.0];
    if ($score >= 35) return ['grade'=>'E8','gpa'=>0.5];
    return ['grade'=>'F9','gpa'=>0.0];
}

/* ================= ORGANIZE RESULTS ================= */
$grades_by_year_subject = [];
foreach ($grades as $g) {
    $year = $g['academic_year'];
    $subId = $g['subject_id'];
    $semKey = strtolower(str_replace(' ', '', $g['semester']));

    $grades_by_year_subject[$year][$subId][$semKey] = $g['total_score'];
}

/* ================= CREATE PDF ================= */
$pdf = new FPDF('P','mm','A4');
$pdf->AddPage();

/* ================= HEADER ================= */
$pdf->Image('../assets/images/logo.png',10,10,25);
$pdf->SetFont('Arial','B',12);
$pdf->SetXY(40,10);
$pdf->Cell(100,6,'FAST TRACK COLLEGE',0,1);

$pdf->SetFont('Arial','',9);
$pdf->SetX(40);
$pdf->Cell(100,5,'Headmaster, P.O. Box 73, Agona Ashanti - Ghana',0,1);
$pdf->SetX(40);
$pdf->Cell(100,5,'Tel: +233551483163',0,1);
$pdf->SetX(40);
$pdf->Cell(100,5,'E-Mail: info@fasttrack.edu.gh',0,1);

/* ================= STUDENT INFO ================= */
$pdf->SetFont('Arial','B',12);
$pdf->SetXY(145,10);
$fullName = trim($student['first_name'].' '.$student['middle_name'].' '.$student['surname']);
$pdf->Cell(60,6,$fullName,0,1);

$pdf->SetFont('Arial','',9);
$pdf->SetX(145);
$pdf->Cell(60,5,'Program/Level: '.$student['level'],0,1);
$pdf->SetX(145);
$pdf->Cell(60,5,'DOB: '.$student['dob'],0,1);
$pdf->SetX(145);
$pdf->Cell(60,5,'Year Admitted: '.$student['year_of_admission'],0,1);
$pdf->SetX(145);
$pdf->Cell(60,5,'Student ID: '.$student['admission_number'],0,1);

$pdf->Ln(12);

/* ================= TITLE ================= */
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,8,'OFFICIAL TRANSCRIPT',0,1,'C');
$pdf->Ln(6);

/* ================= TRANSCRIPT ================= */
$colCourse = 70;
$colSemester = 60;
$colSub = $colSemester / 2;

foreach ($grades_by_year_subject as $year => $subjectsGrades) {

    /* Detect Semester 2 */
    $hasSecondSemester = false;
    foreach ($subjectsGrades as $sg) {
        if (!empty($sg['secondsemester']) && $sg['secondsemester'] > 0) {
            $hasSecondSemester = true;
            break;
        }
    }

    $pdf->SetFillColor(200,200,200);
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(0,8,$year.' Academic Record',1,1,'C',true);

    /* TABLE HEADER */
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell($colCourse,8,'Course Title',1,0,'C');

    if ($hasSecondSemester) {
        $pdf->Cell($colSemester,8,'Semester 1',1,0,'C');
        $pdf->Cell($colSemester,8,'Semester 2',1,1,'C');
    } else {
        $pdf->Cell($colSemester * 2,8,'Semester 1',1,1,'C');
    }

    /* SUB HEADER */
    $pdf->Cell($colCourse,7,'',1,0);

    if ($hasSecondSemester) {
        $pdf->Cell($colSub,7,'GPA',1,0,'C');
        $pdf->Cell($colSub,7,'Grade',1,0,'C');
        $pdf->Cell($colSub,7,'GPA',1,0,'C');
        $pdf->Cell($colSub,7,'Grade',1,1,'C');
    } else {
        $pdf->Cell($colSemester,7,'GPA',1,0,'C');
        $pdf->Cell($colSemester,7,'Grade',1,1,'C');
    }

    /* TABLE BODY */
    $pdf->SetFont('Arial','',9);
    $totalGPA = 0;
    $totalSubjects = count($subjects);

    foreach ($subjects as $sub) {
        $subId = $sub['subject_id'];
        $sem1Score = $subjectsGrades[$subId]['firstsemester'] ?? 0;
        $sem2Score = $subjectsGrades[$subId]['secondsemester'] ?? 0;

        $sem1 = calculateGradeGPA($sem1Score);
        $sem2 = calculateGradeGPA($sem2Score);

        $pdf->Cell($colCourse,7,$sub['subject_name'],1);

        if ($hasSecondSemester) {
            $pdf->Cell($colSub,7,$sem1['gpa'],1,0,'C');
            $pdf->Cell($colSub,7,$sem1['grade'],1,0,'C');
            $pdf->Cell($colSub,7,$sem2['gpa'],1,0,'C');
            $pdf->Cell($colSub,7,$sem2['grade'],1,1,'C');
            $totalGPA += ($sem1['gpa'] + $sem2['gpa']);
        } else {
            $pdf->Cell($colSemester,7,$sem1['gpa'],1,0,'C');
            $pdf->Cell($colSemester,7,$sem1['grade'],1,1,'C');
            $totalGPA += $sem1['gpa'];
        }
    }

    /* SUMMARY */
    $semesterCount = $hasSecondSemester ? 2 : 1;
    $cumulativeGPA = $totalSubjects > 0
        ? round($totalGPA / ($totalSubjects * $semesterCount), 2)
        : 0;

    $pdf->Ln(4);
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(0,6,'Academic Summary',0,1);
    $pdf->SetFont('Arial','',9);
    $pdf->Cell(0,5,'Cumulative GPA: '.$cumulativeGPA.' | Credits Earned: '.$totalSubjects,0,1);
    $pdf->Ln(6);
}

/* ================= OUTPUT ================= */
$pdf->Output('I','Official_Transcript_'.$fullName.'.pdf');