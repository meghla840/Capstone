<?php
session_start();
include "backend/db.php";

// ---------------- AUTH CHECK ----------------
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient'){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM users WHERE id='$user_id'"));

// Fetch patient info
$patient = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM patients WHERE userId='{$user['userId']}'"));

$msg = "";

// ---------------- UPDATE PATIENT INFO ----------------
if(isset($_POST['updatePatient'])){
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $healthIssues = $_POST['healthIssues'];

    mysqli_query($conn,"UPDATE users SET phone='$phone', address='$address' WHERE id='$user_id'");
    mysqli_query($conn,"UPDATE patients SET healthIssues='$healthIssues' WHERE userId='{$user['userId']}'");

    echo json_encode(['status'=>'success']);
    exit;
}

// ---------------- FETCH APPOINTMENTS (FINAL FIX) ----------------
$userPhone = $user['phone'];
$userName  = $user['name'];
$appointmentsQuery = mysqli_query($conn,"
    SELECT 
        a.*,
        s.slotDate, 
        s.slotTime,
        d.clinic, 
        d.specialization,
        u.name AS doctorName
    FROM appointments a
    LEFT JOIN doctor_slots s ON a.slotId = s.id
    LEFT JOIN doctors d ON a.doctorId = d.userId
    LEFT JOIN users u ON d.userId = u.userId
    WHERE 
        (a.phone = '$userPhone' AND '$userPhone' != '')
        OR 
        (a.patientName = '$userName')
    ORDER BY a.appointment_date DESC
");

// Convert to array (IMPORTANT FIX)
$appointmentList = [];
if($appointmentsQuery){
    while($row = mysqli_fetch_assoc($appointmentsQuery)){
        $appointmentList[] = $row;
    }
}

// ---------------- FETCH TRANSPORT ----------------
$userPhone = $user['phone'];

$availableTransports = mysqli_query($conn,"
    SELECT 
        b.*,
        t.driverName,
        t.driverPhone,
        t.name AS vehicleName
    FROM book_ambulance b
    LEFT JOIN transport_vehicles t ON b.vehicle_name = t.name
    WHERE b.user_phone = '$userPhone'
    ORDER BY b.id DESC
");

// ---------------- REPORT UPLOAD ----------------
if(isset($_POST['uploadReport'])){

    $title = $_POST['title'];
    $description = $_POST['description'];

    $fileName = $_FILES['reportFile']['name'];
    $tmpName  = $_FILES['reportFile']['tmp_name'];

    $uploadDir = "uploads/reports/";

    if(!file_exists($uploadDir)){
        mkdir($uploadDir, 0777, true);
    }

    $filePath = $uploadDir . time() . "_" . basename($fileName);

    if(move_uploaded_file($tmpName, $filePath)){

        mysqli_query($conn, "
            INSERT INTO patient_reports 
            (patientId, title, description, filePath, reportDate)
            VALUES 
            ('{$patient['userId']}', '$title', '$description', '$filePath', NOW())
        ");

        $msg = "Report uploaded successfully";

    } else {
        $msg = "File upload failed";
    }
}

// ---------------- FETCH REPORTS ----------------
$reports = mysqli_query($conn,"
    SELECT * FROM patient_reports
    WHERE patientId='{$patient['userId']}'
    ORDER BY reportDate DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Patient Panel</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
<style>
body{font-family:Inter;background:#f4f5f7;margin:0;}
.header{background:linear-gradient(90deg,#49465b,#6e6c80);color:#fff;padding:18px;text-align:center;}
.container{max-width:1100px;margin:20px auto;}
.card{background:#fff;padding:20px;border-radius:12px;margin-bottom:20px;box-shadow:0 4px 12px rgba(0,0,0,0.08);}
.cardc{background:#fff;padding:25px;border-radius:12px; display:flex; flex-direction:column; align-items:center; text-align:center; margin-bottom:20px; box-shadow:0 6px 20px rgba(0,0,0,0.12);}
.cardc h3{margin:0;font-size:22px;color:#333;}
.cardc p{margin:6px 0;color:#555;font-size:15px;}
.cardc p b{color:#222;}
input, select, textarea{width:100%;padding:10px;margin:6px 0;border:1px solid #ccc;border-radius:8px;font-size:14px;}
button{background:#49465b;color:#fff;padding:10px;font-size:14px;border:none;border-radius:8px;cursor:pointer;transition:0.3s;}
button:hover{background:#6e6c80;}
button.delete-btn{background:#ef4444;}
button.quick-btn{background:#3b82f6;}

table{width:100%;border-collapse:collapse;margin-top:10px;font-size:14px;}
th,td{border:1px solid #ddd;padding:10px;text-align:left;}
th{background:#f3f4f6;font-weight:600;}
.no-data{text-align:center;color:#888;padding:15px;}
.msg{background:#22c55e;color:#fff;padding:10px;border-radius:8px;margin-bottom:10px;transition:0.5s;}
img.profile-pic{width:120px;height:120px;border-radius:50%;object-fit:cover;margin-bottom:10px;}
form.inline{display:inline;}
</style>
</head>
<body>

<div class="header" style="display:flex; align-items:center; justify-content:flex-start; gap:15px;">
    <a href="javascript:history.back()" style="color:#fff; text-decoration:none; font-size:18px; font-weight:bold;">← Back</a>
    <h2 style="margin:0; color:#fff; flex-grow:1; text-align:center;">Patient Panel</h2>
</div>

<div class="container">

<div id="msgBox" class="msg" style="display:none;"><?php if($msg) echo $msg; ?></div>

<!-- PROFILE CARD -->
<div class="cardc" id="profileCard">
    <img src="<?= htmlspecialchars($user['profilePic']) ?>" alt="Profile Picture" class="profile-pic">
    <h3 id="profileName"><?= htmlspecialchars($user['name']) ?></h3>
    <p><b>Email:</b> <span id="profileEmail"><?= htmlspecialchars($user['email']) ?></span></p>
    <p><b>Phone:</b> <span id="profilePhone"><?= htmlspecialchars($user['phone'] ?? '-') ?></span></p>
    <p><b>Address:</b> <span id="profileAddress"><?= htmlspecialchars($user['address'] ?? '-') ?></span></p>
    <p><b>Health Issues:</b> <span id="profileHealth"><?= htmlspecialchars($patient['healthIssues'] ?? '-') ?></span></p>
</div>

<!-- UPDATE INFO FORM -->
<div class="card">
    <h3>Update Info</h3>
    <form id="updatePatientForm">
        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="Phone" required>
        <input type="text" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>" placeholder="Address" required>
        <textarea name="healthIssues" placeholder="Health Issues"><?= htmlspecialchars($patient['healthIssues'] ?? '') ?></textarea>
        <button type="submit">Update</button>
    </form>
</div>

<!-- UPLOAD REPORT FORM -->
<div class="card">
<h3>Upload Report</h3>
<form method="POST" enctype="multipart/form-data">
    <input type="text" name="title" placeholder="Report Title" required>
    <textarea name="description" placeholder="Report Description"></textarea>
    <input type="file" name="reportFile" required>
    <button type="submit" name="uploadReport">Upload</button>
</form>
</div>

<!-- APPOINTMENTS -->
<div class="card">
<h3>Your Appointments</h3>

<table>
<tr>
<th>Doctor Name</th>
<th>Clinic</th>
<th>Specialization</th>
<th>Date</th>
<th>Time</th>
<th>Fee</th>
<th>Action</th>
</tr>

<?php if(count($appointmentList) > 0): ?>
    
    <?php foreach($appointmentList as $row): ?>
    <tr>
        <td><?= htmlspecialchars($row['doctorName'] ?? 'Doctor') ?></td>
        <td><?= htmlspecialchars($row['clinic'] ?? '-') ?></td>
        <td><?= htmlspecialchars($row['specialization'] ?? '-') ?></td>
        <td><?= $row['slotDate'] ?? $row['appointment_date'] ?></td>
        <td><?= $row['slotTime'] ?? '-' ?></td>
        <td><?= $row['fee'] ?? '-' ?></td>
        <td>
            <form action="findDoctor.php" method="GET">
                <input type="hidden" name="doctorId" value="<?= $row['doctorId'] ?>">
                <button type="submit">Find Doctor</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>

<?php else: ?>
    <tr>
        <td colspan="7" class="no-data">No Appointments Yet</td>
    </tr>
<?php endif; ?>

</table>
</div>

<div class="card" style="text-align:center;">
    <h3>Need Emergency Transport?</h3>
    <p>Quickly book an ambulance for your appointment</p>

    <a href="ambulance.php">
        <button >
            🚑 Book Ambulance
        </button>
    </a>
</div>

<div class="card">
<h3>Your Booked Transport</h3>

<table>
<tr>
<th>Driver Name</th>
<th>Driver Phone</th>
<th>Vehicle</th>
<th>Address</th>
<th>Date</th>
</tr>

<?php if(mysqli_num_rows($availableTransports) > 0): ?>
    
    <?php while($t = mysqli_fetch_assoc($availableTransports)): ?>
    <tr>
        <td><?= htmlspecialchars($t['driverName'] ?? '-') ?></td>
        <td><?= htmlspecialchars($t['driverPhone'] ?? '-') ?></td>
        <td><?= htmlspecialchars($t['vehicleName'] ?? $t['vehicle_name']) ?></td>
        <td><?= htmlspecialchars($t['address']) ?></td>
        <td><?= htmlspecialchars($t['created_at']) ?></td>
    </tr>
    <?php endwhile; ?>

<?php else: ?>
<tr>
<td colspan="5" class="no-data">No Transport Booked Yet</td>
</tr>
<?php endif; ?>

</table>
</div>
<!-- REPORT LIST -->
<div class="card">
<h3>Your Reports</h3>
<table>
<tr>
<th>Title</th>
<th>Description</th>
<th>File</th>
<th>Date</th>
</tr>

<?php if(mysqli_num_rows($reports) > 0): ?>
<?php while($r = mysqli_fetch_assoc($reports)): ?>
<tr>
<td><?= $r['title'] ?></td>
<td><?= $r['description'] ?></td>
<td>
<a href="<?= $r['filePath'] ?>" target="_blank">View</a>
</td>
<td><?= $r['reportDate'] ?></td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="4">No Reports</td></tr>
<?php endif; ?>

</table>
</div>

</div>


<script>
// AUTO FADE MSG
window.addEventListener('DOMContentLoaded', () => {
    const msg = document.getElementById('msgBox');
    if(msg && msg.textContent.trim() !== ''){
        setTimeout(()=>msg.style.display='none',2000);
    }
});

// AJAX Update Patient Info
document.getElementById('updatePatientForm').addEventListener('submit', function(e){
    e.preventDefault();
    let formData = new FormData(this);
    formData.append('updatePatient', true);

    fetch('', { method:'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success'){
            document.getElementById('profilePhone').textContent = data.user.phone;
            document.getElementById('profileAddress').textContent = data.user.address;
            document.getElementById('profileHealth').textContent = data.patient.healthIssues;

            let msgBox = document.getElementById('msgBox');
            msgBox.textContent = "✅ Info updated";
            msgBox.style.display = 'block';
            setTimeout(()=>msgBox.style.display='none', 2000);
        }
    });
});
</script>
</body>
</html>