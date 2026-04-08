<?php
session_start();
include "backend/db.php";

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor'){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM users WHERE id='$user_id'"));
$userId = $user['userId'];
$doctor = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM doctors WHERE userId='$userId'"));
$msg = "";

/*
IMPORTANT:
doctor table relation should match users.userId
We use user's userId to fetch doctor
*/


/* ================= UPDATE DOCTOR ================= */
if(isset($_POST['updateDoctor'])){
    ob_clean();
    header('Content-Type: application/json');
    $experience = $_POST['experience'];
    $consultationFees = $_POST['consultationFees'];
    $clinic = $_POST['clinic'];
    $specialization = $_POST['specialization'];
    $gender = $_POST['gender'];
    $education = $_POST['education'];
    $degrees = $_POST['degrees'];

    $user_id = $_SESSION['user_id'];
    $user = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM users WHERE id='$user_id'"));

    if(!$user){
        echo json_encode(['status'=>'error','message'=>'User not found']);
        exit;
    }
    
    $userUserId = $user['userId'];

    $query = "UPDATE doctors SET 
    clinic='$clinic',
    specialization='$specialization',
    gender='$gender',
    education='$education',
    degrees='$degrees',
    experienceYears='$experience',
    consultationFees='$consultationFees'
    WHERE userId='$userUserId'";

    $result = mysqli_query($conn,$query);

    if(!$result){
        echo json_encode([
            'status'=>'error',
            'message'=>mysqli_error($conn)
        ]);
        exit;
    }

    $updatedDoctor = mysqli_fetch_assoc(
        mysqli_query($conn,"SELECT * FROM doctors WHERE userId='$userUserId'")
    );

    echo json_encode([
        'status'=>'success',
        'doctor'=>$updatedDoctor
    ]);
    exit;
}





// ---------------- FETCH APPOINTMENTS ----------------
$appointments = mysqli_query($conn,"
SELECT a.*, s.slotDate, s.slotTime 
FROM appointments a
LEFT JOIN doctor_slots s ON a.slotId = s.id
WHERE a.doctorId='$userId'
ORDER BY a.appointment_date DESC
");

$totalAppointments = mysqli_num_rows($appointments);

if(isset($_POST['approve'])){
    $id = $_POST['appointmentId'];

    mysqli_query($conn,"UPDATE appointments SET status='approved' WHERE id='$id'");
}

if(isset($_POST['decline'])){
    $id = $_POST['appointmentId'];

    mysqli_query($conn,"UPDATE appointments SET status='declined' WHERE id='$id'");
}
// -------- SAVE AVAILABILITY --------
if(isset($_POST['saveAvailability'])){
    $days = implode(",", $_POST['availableDays']);
    $times = $_POST['availableTimes'];
    $clinic = $_POST['clinic'];

    mysqli_query($conn, "INSERT INTO doctor_availability 
        (doctorId, days, timeRange, clinic)
        VALUES ('$userId','$days','$times','$clinic')");

    $msg = "✅ Availability added!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Doctor Panel</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
<style>
body{font-family:Inter;background:#f4f5f7;margin:0;}
.header{background:linear-gradient(90deg,#49465b,#6e6c80);color:#fff;padding:18px;text-align:center;}
.container{max-width:1100px;margin:20px auto;}

/* Cards */
.card{background:#fff;padding:20px;border-radius:12px;margin-bottom:20px;box-shadow:0 4px 12px rgba(0,0,0,0.08);}
.cardc{background:#fff;padding:25px;border-radius:12px; display:flex; flex-direction:column; align-items:center; text-align:center; margin-bottom:20px; box-shadow:0 6px 20px rgba(0,0,0,0.12);}

.cardc h3{margin:0;font-size:22px;color:#333;}
.cardc p{margin:6px 0;color:#555;font-size:15px;}
.cardc p b{color:#222;}

/* Inputs and buttons */
input, select{width:100%;padding:10px;margin:6px 0;border:1px solid #ccc;border-radius:8px;font-size:14px;}
button{background:#49465b;color:#fff;padding:10px;font-size:14px;border:none;border-radius:8px;cursor:pointer;transition:0.3s;}
button:hover{background:#6e6c80;}

/* Tables */
table{width:100%;border-collapse:collapse;margin-top:10px;font-size:14px;}
th,td{border:1px solid #ddd;padding:10px;text-align:left;}
th{background:#f3f4f6;font-weight:600;}
.no-data{text-align:center;color:#888;padding:15px;}

/* Message */
.msg{background:#22c55e;color:#fff;padding:10px;border-radius:8px;margin-bottom:10px;transition:0.5s;}
</style>
</head>
<body>

<div class="header" style="display:flex; align-items:center; justify-content:flex-start; gap:15px;">
    <a href="javascript:history.back()" style="color:#fff; text-decoration:none; font-size:18px; font-weight:bold;">← Back</a>
    <h2 style="margin:0; color:#fff; flex-grow:1; text-align:center;">Doctor Panel</h2>
</div>

<div class="container">

<div id="msgBox" class="msg" style="display:none;"><?php if($msg) echo $msg; ?></div>

<!-- PROFILE CARD -->
<div class="cardc" id="profileCard">
    <h3 id="profileName"><?= htmlspecialchars($user['name']) ?></h3>
    <p><b>Email:</b> <span id="profileEmail"><?= htmlspecialchars($user['email']) ?></span></p>
    <p><b>Phone:</b> <span id="profilePhone"><?= htmlspecialchars($user['phone']) ?></span></p>
    <p><b>Clinic:</b> <span id="profileClinic"><?= htmlspecialchars($doctor['clinic'] ?? '-') ?></span></p>
    <p><b>Specialization:</b> <span id="profileSpecialization"><?= htmlspecialchars($doctor['specialization'] ?? '-') ?></span></p>
    <p><b>Gender:</b> <span id="profileGender"><?= htmlspecialchars($doctor['gender'] ?? '-') ?></span></p>
    <p><b>Education:</b> <span id="profileEducation"><?= htmlspecialchars($doctor['education'] ?? '-') ?></span></p>
    <p><b>Degrees:</b> <span id="profileDegrees"><?= htmlspecialchars($doctor['degrees'] ?? '-') ?></span></p>
    <p><b>Experience:</b> <span id="profileExperience"><?= htmlspecialchars($doctor['experienceYears'] ?? '-') ?></span></p>
<p><b>Fees:</b> <span id="profileFees"><?= htmlspecialchars($doctor['consultationFees'] ?? '-') ?></span></p>
</div>

<!-- UPDATE INFO FORM -->
<div class="card">
    <h3>Update Info</h3>
    <form id="updateDoctorForm">
        <input type="text" name="clinic" value="<?= htmlspecialchars($doctor['clinic'] ?? '') ?>" placeholder="Clinic" required>
        <input type="text" name="specialization" value="<?= htmlspecialchars($doctor['specialization'] ?? '') ?>" placeholder="Specialization" required>
        <select name="gender" required>
            <option value="">Select Gender</option>
            <option value="Male" <?= ($doctor['gender'] ?? '')=='Male'?'selected':'' ?>>Male</option>
            <option value="Female" <?= ($doctor['gender'] ?? '')=='Female'?'selected':'' ?>>Female</option>
            <option value="Other" <?= ($doctor['gender'] ?? '')=='Other'?'selected':'' ?>>Other</option>
        </select>
        <input type="text" name="education" value="<?= htmlspecialchars($doctor['education'] ?? '') ?>" placeholder="Education" required>
        <input type="text" name="degrees" value="<?= htmlspecialchars($doctor['degrees'] ?? '') ?>" placeholder="Degrees (comma separated)" required>
        <input type="number" name="experience" value="<?= htmlspecialchars($doctor['experienceYears'] ?? '') ?>" placeholder="Years of Experience" required>

        <input type="number" name="consultationFees" value="<?= htmlspecialchars($doctor['consultationFees'] ?? '') ?>" placeholder="Consultation Fees" required>
        <button type="submit">Update</button>
    </form>
</div>

<!-- DOCTOR AVAILABILITY -->
<div class="card">
    <h3>Set Availability</h3>

    <form method="POST">
        <label>Available Days</label>
        <select name="availableDays[]" multiple required>
            <option value="Mon">Monday</option>
            <option value="Tue">Tuesday</option>
            <option value="Wed">Wednesday</option>
            <option value="Thu">Thursday</option>
            <option value="Fri">Friday</option>
            <option value="Sat">Saturday</option>
            <option value="Sun">Sunday</option>
        </select>

        <label>Available Time (e.g. 3PM - 8PM)</label>
        <input type="text" name="availableTimes" placeholder="3PM - 8PM" required>

        <label>Clinic Address</label>
        <input type="text" name="clinic" value="<?= htmlspecialchars($doctor['clinic'] ?? '') ?>" required>

        <button name="saveAvailability">Save Availability</button>
    </form>
</div>
<!-- SHOW AVAILABILITY -->
<div class="card">
    <h3>Doctor Availability</h3>

    <table>
        <tr>
            <th>Days</th>
            <th>Time</th>
            <th>Clinic</th>
            <th>Action</th>
        </tr>

        <?php 
        $availability = mysqli_query($conn,"SELECT * FROM doctor_availability WHERE doctorId='$userId' ORDER BY id DESC");
        ?>

        <?php if(mysqli_num_rows($availability) > 0): ?>
            <?php while($a = mysqli_fetch_assoc($availability)): ?>
            <tr>
                <td><?= htmlspecialchars($a['days']) ?></td>
                <td><?= htmlspecialchars($a['timeRange']) ?></td>
                <td><?= htmlspecialchars($a['clinic']) ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="deleteId" value="<?= $a['id'] ?>">
                        <button name="deleteAvailability" style="background:red;">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="4" class="no-data">No availability set</td>
            </tr>
        <?php endif; ?>
    </table>
</div>


<!-- APPOINTMENTS -->
<div class="card">
<h3>Patient Appointments</h3>

<table>
<tr>
<th>Patient Name</th>
<th>Phone</th>
<th>Reason</th>
<th>Appointment Date</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php if(mysqli_num_rows($appointments) > 0): ?>
    <?php while($row = mysqli_fetch_assoc($appointments)): ?>
    <tr>
        <td><?= htmlspecialchars($row['patientName']) ?></td>
        <td><?= htmlspecialchars($row['phone']) ?></td>
        <td><?= htmlspecialchars($row['problem']) ?></td>
        <td><?= htmlspecialchars($row['appointment_date']) ?></td>
        <td><?= htmlspecialchars($row['status']) ?></td>
        <td>
            <?php if($row['status'] == 'pending'): ?>
                
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="appointmentId" value="<?= $row['id'] ?>">
                    <button name="approve">Approve</button>
                </form>

                <form method="POST" style="display:inline;">
                    <input type="hidden" name="appointmentId" value="<?= $row['id'] ?>">
                    <button name="decline" style="background:red;">Decline</button>
                </form>

            <?php else: ?>
                <b><?= ucfirst($row['status']) ?></b>
            <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="6" class="no-data">No appointments found</td>
    </tr>
<?php endif; ?>

</table>
</div>

<script>
// AUTO FADE MSG
window.addEventListener('DOMContentLoaded', () => {
    const msg = document.getElementById('msgBox');
    if(msg && msg.textContent.trim() !== ''){
        setTimeout(()=>msg.style.display='none',2000);
    }
});

// AJAX Update Doctor Info

document.getElementById('updateDoctorForm').addEventListener('submit', function(e){
    e.preventDefault();
    let formData = new FormData(this);
    formData.append('updateDoctor', true);

    fetch(window.location.href, {
    method:'POST',
    body: formData
})
.then(res => res.text())   // 🔥 change this
.then(text => {
    console.log(text);     // debug
    let data = JSON.parse(text);

    if(data.status === 'success'){
        document.getElementById('profileClinic').textContent = data.doctor.clinic;
        document.getElementById('profileSpecialization').textContent = data.doctor.specialization;
        document.getElementById('profileGender').textContent = data.doctor.gender;
        document.getElementById('profileEducation').textContent = data.doctor.education;
        document.getElementById('profileDegrees').textContent = data.doctor.degrees;
        document.getElementById('profileExperience').textContent = data.doctor.experienceYears;
        document.getElementById('profileFees').textContent = data.doctor.consultationFees;
        let msgBox = document.getElementById('msgBox');
        msgBox.textContent = "✅ Doctor info updated";
        msgBox.style.display = 'block';
        setTimeout(()=>msgBox.style.display='none', 2000);
    } else {
        alert("Error: " + data.message);
    }
});
});
</script>
</body>
</html>