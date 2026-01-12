<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['teacher_id'])) {
    header("Location: ../index.php");
    exit;
}

$teacher_id    = $_SESSION['teacher_id'];
$teacher_name  = $_SESSION['teacher_name'];
$teacher_email = $_SESSION['teacher_email'];
$staff_id      = $_SESSION['staff_id'];
$teacher_photo = $_SESSION['teacher_photo'];

/* ================= TEACHER SUBJECTS ================= */
$stmt = $pdo->prepare("
    SELECT DISTINCT ts.subject_id, s.subject_name
    FROM teacher_subjects ts
    JOIN subjects s ON ts.subject_id = s.id
    WHERE ts.teacher_id = ?
    ORDER BY s.subject_name
");
$stmt->execute([$teacher_id]);
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ================= ACADEMIC YEARS ================= */
$yearStmt = $pdo->query("
    SELECT DISTINCT year_name
    FROM academic_years
    WHERE status = 'Active'
    ORDER BY year_name DESC
");
$academicYears = $yearStmt->fetchAll(PDO::FETCH_COLUMN);

/* ================= ASSESSMENT TYPES ================= */
$typeStmt = $pdo->query("
    SELECT DISTINCT type
    FROM assessments
    WHERE status = 'Active'
    ORDER BY type ASC
");
$assessmentTypes = $typeStmt->fetchAll(PDO::FETCH_COLUMN);

/* ================= YEAR GROUPS ================= */
$yearGroupStmt = $pdo->query("
    SELECT DISTINCT year_group
    FROM students
    ORDER BY year_group ASC
");
$yearGroups = $yearGroupStmt->fetchAll(PDO::FETCH_COLUMN);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Capture Assessment — FTCSMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/styles.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
    body {
        font-family: 'Poppins', sans-serif;
        background: #f4f6f9;
    }

    .section-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, .08);
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .score-input {
        width: 90px;
        text-align: center;
    }

    .table thead {
        background: #412461;
        color: #fff;
        font-weight: 600;
    }

    .table tbody tr:nth-child(even) {
        background: #f8f9fa;
    }

    .table tbody tr:hover {
        background: #e9e2f5;
    }

    .table input.form-control {
        border-radius: 8px;
        text-align: center;
        font-weight: 500;
    }

    /* Highlight saved row */
    .saved-row {
        background-color: #d4edda !important;
        /* light green */
        transition: background-color 0.5s ease, opacity 0.5s ease;
    }
    </style>
</head>

<body>

    <?php include '../includes/teacher_sidebar.php'; ?>
    <?php include '../includes/teacher_topbar.php'; ?>

    <main class="main">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-semibold mb-1">
                    <i class="fas fa-clipboard-list text-primary me-2"></i>
                    Capture Assessment
                </h4>
                <small class="text-muted">Select assessment details and enter student scores</small>
            </div>
        </div>

        <!-- Filter -->
        <div class="card shadow-sm p-4 mb-4">
            <form class="row g-3 align-items-end" onsubmit="return false;">
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Academic Year</label>
                    <select class="form-select" id="academicYear">
                        <option value="">Select</option>
                        <?php foreach($academicYears as $ay): ?>
                        <option value="<?= htmlspecialchars($ay) ?>"><?= htmlspecialchars($ay) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Semester</label>
                    <select class="form-select" id="semesterSelect">
                        <option value="">Select</option>
                        <option value="First Semester">First Semester</option>
                        <option value="Second Semester">Second Semester</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Assessment Type</label>
                    <select class="form-select" id="assessmentType">
                        <option value="">Select</option>
                        <?php foreach($assessmentTypes as $t): ?>
                        <option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Year Group</label>
                    <select class="form-select" id="yearGroup">
                        <option value="">Select</option>
                        <?php foreach($yearGroups as $yg): ?>
                        <option value="<?= htmlspecialchars($yg) ?>"><?= htmlspecialchars($yg) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Overall Score</label>
                    <input type="number" class="form-control" id="overallScore" min="1">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Subject</label>
                    <select class="form-select" id="subjectSelect">
                        <option value="">Select Subject</option>
                        <?php foreach($subjects as $s): ?>
                        <option value="<?= $s['subject_id'] ?>"><?= htmlspecialchars($s['subject_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 text-end mt-3">
                    <button type="button" class="btn btn-secondary px-4 py-2" id="refreshBtn">
                        <i class="fas fa-sync-alt me-2"></i> Refresh
                    </button>
                    <button type="button" class="btn btn-primary px-4 py-2 ms-2" id="filterBtn">
                        <i class="fas fa-filter me-2"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Students -->
        <div class="section-card d-none" id="studentsSection">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Admission No</th>
                            <th>Score</th>
                        </tr>
                    </thead>
                    <tbody id="studentsBody"></tbody>
                </table>
            </div>
        </div>

    </main>

    <script>
    const filterBtn = document.getElementById('filterBtn');
    const refreshBtn = document.getElementById('refreshBtn');
    const studentsSection = document.getElementById('studentsSection');
    const studentsBody = document.getElementById('studentsBody');

    const academicYear = document.getElementById('academicYear');
    const semesterSelect = document.getElementById('semesterSelect');
    const assessmentType = document.getElementById('assessmentType');
    const overallScore = document.getElementById('overallScore');
    const subjectSelect = document.getElementById('subjectSelect');
    const yearGroup = document.getElementById('yearGroup');

    /* ================= FILTER (NEW STUDENTS) ================= */
    filterBtn.addEventListener('click', () => {
        if (!academicYear.value || !semesterSelect.value || !assessmentType.value ||
            !overallScore.value || !subjectSelect.value || !yearGroup.value) {
            alert('Please fill all fields.');
            return;
        }

        const payload = {
            academic_year: academicYear.value,
            semester: semesterSelect.value,
            assessment_type: assessmentType.value,
            subject_id: subjectSelect.value,
            year_group: yearGroup.value,
            overall_score: overallScore.value
        };

        fetch('../handlers/load_students_by_subject.php?' + new URLSearchParams(payload))
            .then(res => res.json())
            .then(data => {
                studentsBody.innerHTML = '';
                if (!data.length) {
                    studentsBody.innerHTML =
                        `<tr><td colspan="4" class="text-center">No students found</td></tr>`;
                } else {
                    data.forEach((s, i) => {
                        studentsBody.innerHTML += `
<tr>
<td>${i+1}</td>
<td>${s.full_name}</td>
<td>${s.admission_number}</td>
<td>
<input type="number" class="form-control score-input"
data-student-id="${s.id}"
data-subject-id="${subjectSelect.value}"
data-year-group="${yearGroup.value}"
data-academic-year="${academicYear.value}"
data-semester="${semesterSelect.value}"
data-assessment-type="${assessmentType.value}"
max="${overallScore.value}" min="0">
</td>
</tr>`;
                    });
                }
                studentsSection.classList.remove('d-none');
            });
    });

    /* ================= REFRESH (EXISTING SCORES) ================= */
    refreshBtn.addEventListener('click', () => {
        if (!academicYear.value || !semesterSelect.value || !assessmentType.value ||
            !subjectSelect.value || !yearGroup.value) {
            alert('Fill all fields before refresh.');
            return;
        }

        const payload = {
            academic_year: academicYear.value,
            semester: semesterSelect.value,
            assessment_type: assessmentType.value,
            subject_id: subjectSelect.value,
            year_group: yearGroup.value,
            fetch_existing: 1
        };

        fetch('../handlers/load_existing_assessments.php?' + new URLSearchParams(payload))
            .then(res => res.json())
            .then(data => {
                studentsBody.innerHTML = '';
                if (!data.length) {
                    studentsBody.innerHTML =
                        `<tr><td colspan="4" class="text-center">No existing scores</td></tr>`;
                } else {
                    data.forEach((s, i) => {
                        studentsBody.innerHTML += `
<tr>
<td>${i+1}</td>
<td>${s.full_name}</td>
<td>${s.admission_number}</td>
<td>
<input type="number" class="form-control score-input"
value="${s.score}"
max="${s.overall_score}"
data-id="${s.id}"
data-student-id="${s.student_id}"
data-subject-id="${subjectSelect.value}"
data-year-group="${yearGroup.value}"
data-academic-year="${academicYear.value}"
data-semester="${semesterSelect.value}"
data-assessment-type="${assessmentType.value}"
min="0">
</td>
</tr>`;
                    });
                }
                studentsSection.classList.remove('d-none');
            });
    });

    // ---------------- Input Validation ----------------
    document.addEventListener('input', e => {
        if (!e.target.classList.contains('score-input')) return;

        const maxScore = parseFloat(e.target.max);
        const val = parseFloat(e.target.value);

        if (isNaN(val)) return;

        if (val > maxScore) {
            alert(`Actual score cannot exceed the overall score (${maxScore})`);
            e.target.value = '';
            setTimeout(() => e.target.focus(), 0);
            return;
        }

        if (val < 0) {
            alert('Score cannot be less than 0');
            e.target.value = '';
            setTimeout(() => e.target.focus(), 0);
        }
    });

    // ---------------- Auto Save / Update on Blur ----------------
    document.addEventListener('blur', e => {
        if (!e.target.classList.contains('score-input')) return;

        const val = e.target.value.trim();
        if (val === '' || isNaN(val)) return;

        const score = parseFloat(val);
        const overall_score = parseFloat(e.target.max);
        if (score < 0 || score > overall_score) {
            alert(`Score must be between 0 and ${overall_score}`);
            e.target.value = '';
            e.target.focus();
            return;
        }

        const id = e.target.dataset.id || null;
        const student_id = e.target.dataset.studentId;
        const subject_id = e.target.dataset.subjectId;
        const year_group = e.target.dataset.yearGroup;
        const academic_year = e.target.dataset.academicYear;
        const semester = e.target.dataset.semester;
        const assessment_type = e.target.dataset.assessmentType;

        fetch('../handlers/process_capture_assessment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id,
                    student_id,
                    subject_id,
                    year_group,
                    academic_year,
                    semester,
                    assessment_type,
                    score,
                    overall_score
                })
            })
            .then(res => res.json())
            .then(res => {
                if (!res.success) {
                    alert('Failed to save/update: ' + res.message);
                    return;
                }
                if (!id && res.id) {
                    e.target.dataset.id = res.id;
                }

                // Highlight row green and fade out
                const row = e.target.closest('tr');
                if (row) {
                    row.classList.add('saved-row');
                    setTimeout(() => {
                        row.style.transition = 'opacity 0.8s ease';
                        row.style.opacity = '0';
                        setTimeout(() => row.remove(), 800);
                    }, 600); // Keep green for 0.6s before fade
                }
            })
            .catch(err => console.error('Error saving score:', err));
    }, true);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>