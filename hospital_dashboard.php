<?php
session_start();
include "backend/db.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ================= USER ================= */
$userRes = mysqli_query($conn,"SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($userRes);

$userId = $user['userId'];



/* ================= HOSPITAL ================= */
$hospitalRes = mysqli_query($conn,"SELECT * FROM hospitals WHERE userId='$userId'");
$hospital = mysqli_fetch_assoc($hospitalRes);

if(!$hospital){
    mysqli_query($conn,"INSERT INTO hospitals (userId,hospitalName,hphone,services,fees,doctorsAvailable)
    VALUES ('$userId','','','[]','[]','[]')");

    $hospitalRes = mysqli_query($conn,"SELECT * FROM hospitals WHERE userId='$userId'");
    $hospital = mysqli_fetch_assoc($hospitalRes);
}

$hospital = array_merge([
    'hospitalName'=>'',
    'profilePic'=>'uploads/default.png',
    'totalBeds'=>0,
    'availableBeds'=>0,
    'services'=>'[]',
    'fees'=>'[]',
    'doctorsAvailable'=>'[]',
    'location'=>'',
    'hphone'=>''
], $hospital);

/* ================= ARRAYS ================= */
$servicesArr = json_decode($hospital['services'] ?? '[]', true);
$feesArr = json_decode($hospital['fees'] ?? '[]', true);

if(!is_array($servicesArr)) $servicesArr = [];
if(!is_array($feesArr)) $feesArr = [];

$doctorsArr = json_decode($hospital['doctorsAvailable'], true) ?? [];

/* ================= DOCTORS LIST ================= */
function getDoctorsList($conn){
    $list = [];
    $res = mysqli_query($conn,"SELECT * FROM doctors");

    while($d = mysqli_fetch_assoc($res)){

        $u = mysqli_fetch_assoc(
            mysqli_query($conn,"SELECT name FROM users WHERE userId='".$d['userId']."'")
        );

        // ✅ SAFE decode
        $days = json_decode($d['availableDays'], true);
        if(!is_array($days)) $days = [];

        $list[$d['userId']] = [
            'name' => $u['name'] ?? '',
            'specialization' => $d['specialization'],
            'fees' => $d['consultationFees'] ?? '',
            'experience' => $d['experienceYears'] ?? '',
            'education' => $d['education'] ?? '',
            'degrees' => $d['degrees'] ?? '',
            'gender' => $d['gender'] ?? '',
            'days' => $days,
            'time' => $d['availableTimes'] ?? ''
        ];
    }

    return $list;
}


$doctorsList = getDoctorsList($conn);

/* ================= UPDATE HOSPITAL ================= */
if(isset($_POST['updateHospital'])){

    $hospitalName = $_POST['hospitalName'];
    $phone = $_POST['phone'];
    $totalBeds = $_POST['totalBeds'];
    $availableBeds = $_POST['availableBeds'];
    $location = $_POST['location'];

    $servicesJson = json_encode($_POST['services'] ?? []);
    $feesJson = json_encode($_POST['fees'] ?? []);
    $selectedDoctors = json_encode(array_values($_POST['doctors'] ?? []));

    if(!empty($_FILES['profilePic']['name'])){
        $targetDir = "uploads/";
        $fileName = time().'_'.basename($_FILES["profilePic"]["name"]);
        $targetFile = $targetDir.$fileName;

        if(move_uploaded_file($_FILES["profilePic"]["tmp_name"], $targetFile)){
            $profilePic = $targetFile;
        }
    } else {
        $profilePic = $hospital['profilePic'];
    }

    mysqli_query($conn,"UPDATE hospitals SET 
        hospitalName='$hospitalName',
        hphone='$phone',
        totalBeds='$totalBeds',
        availableBeds='$availableBeds',
        services='$servicesJson',
        fees='$feesJson',
        doctorsAvailable='$selectedDoctors',
        location='$location',
        profilePic='$profilePic'
        WHERE userId='$userId'
    ");

    mysqli_query($conn,"UPDATE users SET phone='$phone' WHERE id='$user_id'");

    $_SESSION['msg'] = "✅ Hospital Updated";
}

/* ================= ADD DOCTOR ================= */
if(isset($_POST['addDoctor'])){

    $docName = $_POST['docName'];
    $specialization = $_POST['specialization'];
    $bmdc = $_POST['bmdc'];

    $fees = $_POST['consultationFees'];
    $experience = $_POST['experienceYears'];

    $education = $_POST['education'];
    $degrees = $_POST['degrees'];
    $gender = $_POST['gender'];

    $availableDays = json_encode($_POST['availableDays'] ?? []);
    $availableTimes = $_POST['availableTimes'] ?? '';
    $availableDate = $_POST['availableDate'] ?? '';

    $newUserId = 'doc_'.time();

    /* ✅ IMAGE UPLOAD */
    $profilePic = "uploads/default.png";

    if(!empty($_FILES['doctorPic']['name'])){
        $targetDir = "uploads/";
        $fileName = time().'_'.basename($_FILES["doctorPic"]["name"]);
        $targetFile = $targetDir.$fileName;

        if(move_uploaded_file($_FILES["doctorPic"]["tmp_name"], $targetFile)){
            $profilePic = $targetFile;
        }
    }

    /* ✅ INSERT USER WITH IMAGE */
    mysqli_query($conn,"INSERT INTO users (userId,name,role,profilePic)
    VALUES ('$newUserId','$docName','doctor','$profilePic')");

    /* ✅ INSERT DOCTOR */
    mysqli_query($conn,"INSERT INTO doctors 
    (userId,specialization,bmdc,clinic,consultationFees,experienceYears,education,degrees,gender,availableDays,availableTimes,availableDate)
    VALUES 
    ('$newUserId','$specialization','$bmdc','".$hospital['hospitalName']."','$fees','$experience','$education','$degrees','$gender','$availableDays','$availableTimes','$availableDate')
    ");

    $doctorsArr[] = $newUserId;

    mysqli_query($conn,"UPDATE hospitals 
    SET doctorsAvailable='".json_encode(array_values($doctorsArr))."' 
    WHERE userId='$userId'");

    $_SESSION['msg'] = "✅ Doctor Added with Profile Pic";

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

/* ================= REMOVE DOCTOR ================= */
if(isset($_GET['removeDoctor'])){
    $removeId = $_GET['removeDoctor'];

    $doctorsArr = array_values(array_diff($doctorsArr, [$removeId]));
    mysqli_query($conn,"DELETE FROM doctors WHERE userId='$removeId'");
    mysqli_query($conn,"DELETE FROM users WHERE userId='$removeId'");

    mysqli_query($conn,"UPDATE hospitals SET doctorsAvailable='".json_encode($doctorsArr)."' WHERE userId='$userId'");

    $_SESSION['msg'] = "✅ Doctor Removed";
}


/* ================= SEAT ACTIONS ================= */
if(isset($_GET['confirmSeat'])){
    $id = $_GET['confirmSeat'];
    mysqli_query($conn,"UPDATE seat_bookings SET status='Confirmed' WHERE id='$id'");
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

if(isset($_GET['deleteSeat'])){
    $id = $_GET['deleteSeat'];

    $seat = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM seat_bookings WHERE id='$id'"));

    if($seat){
        mysqli_query($conn,"UPDATE hospitals 
        SET availableBeds = availableBeds + 1 
        WHERE id='".$seat['hospital_id']."'");

        mysqli_query($conn,"DELETE FROM seat_bookings WHERE id='$id'");
    }

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}


/* ================= APPOINTMENTS ================= */
/* ================= APPOINTMENTS ================= */

// hospital er doctor list
$doctorIds = !empty($doctorsArr) 
    ? "'" . implode("','", $doctorsArr) . "'" 
    : "''";

$appointments = mysqli_query($conn,"
SELECT a.*, u.name AS doctor_name
FROM appointments a
LEFT JOIN doctors d ON a.doctorId = d.userId
LEFT JOIN users u ON d.userId = u.userId

WHERE 
    a.hospitalUserId='".$hospital['id']."'   -- direct hospital booking
    OR a.doctorId IN ($doctorIds)            -- doctor under this hospital

ORDER BY a.id DESC
");
$seatBookings = mysqli_query($conn,"
SELECT * FROM seat_bookings 
WHERE hospital_id='".$hospital['id']."' 
ORDER BY id DESC
");
if(isset($_POST['bookAppointment'])){
    $doctorId = $_POST['doctorId'];
    $patientName = $_POST['patientName'];
    $patientPhone = $_POST['patientPhone'];
    $date = $_POST['date'];

    mysqli_query($conn,"INSERT INTO appointments 
    (hospitalUserId,doctorId,patientName,phone,appointment_date,status,problem)
    VALUES (
        '".$hospital['id']."',   // 🔥 FIXED (আগে ছিল $userId)
        '$doctorId',
        '$patientName',
        '$patientPhone',
        '$date',
        'Pending',
        'N/A'
    )");

    $_SESSION['msg'] = "✅ Appointment Booked";

    // 🔥 VERY IMPORTANT LINE
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

if(isset($_GET['confirmAppointment'])){
    $id = $_GET['confirmAppointment'];

    mysqli_query($conn,"UPDATE appointments SET status='Confirmed' WHERE id='$id'");

    $_SESSION['msg'] = "✅ Appointment Confirmed";

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}
/* ================= DELETE APPOINTMENT ================= */
if(isset($_GET['deleteAppointment'])){
    $id = $_GET['deleteAppointment'];

    mysqli_query($conn,"DELETE FROM appointments WHERE id='$id'");

    $_SESSION['msg'] = "❌ Appointment Deleted";

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}


$serviceCount = max(count($servicesArr), 1);

// doctor availability
if(isset($_POST['addAvailability'])){
    $doctorId = $_POST['doctorId'];
    $days = implode(",", $_POST['days']);
    $time = $_POST['timeRange'];
    $clinic = $hospital['hospitalName'];

    mysqli_query($conn,"INSERT INTO doctor_availability 
    (doctorId, hospitalId, days, timeRange, clinic)
    VALUES (
        '$doctorId',
        '".$hospital['id']."',
        '$days',
        '$time',
        '$clinic'
    )");

    $_SESSION['msg'] = "✅ Availability Added";
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Hospital Dashboard</title>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body{font-family:Arial;background:#f4f6f9;margin:0;}
.profile-center{
    text-align:center;
    margin-bottom:20px;
}

.profile-center h2{
    margin:10px 0 5px;
    color:#49465b;
}

.profile-center p{
    margin:3px 0;
    color:#666;
}

.profile-img{
    width:130px;
    height:130px;
    border-radius:50%;
    object-fit:cover;
    border:4px solid #49465b;
    transition:0.3s;
}

.profile-img:hover{
    transform:scale(1.05);
}

/* TABLE DESIGN */
.profile-table{
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
    border-radius:10px;
    overflow:hidden;
}

.profile-table th{
    background:#49465b;
    color:#fff;
    padding:14px;
    text-align:left;
    font-weight:500;
}

.profile-table td{
    padding:14px;
    background:#fff;
    border-bottom:1px solid #eee;
    color:#333;
}

/* zebra effect */
.profile-table tr:nth-child(even) td{
    background:#f9fafc;
}

/* hover */
.profile-table tr:hover td{
    background:#eef1f7;
}

/* DARK MODE */
body.dark .profile-center h2{
    color:#fff;
}

body.dark .profile-center p{
    color:#ccc;
}

body.dark .profile-table th{
    background:#6c63ff;
}

body.dark .profile-table td{
    background:#1e2535;
    border-color:#2f3a55;
    color:#e4e6eb;
}

body.dark .profile-table tr:nth-child(even) td{
    background:#232a3d;
}

body.dark .profile-table tr:hover td{
    background:#2a3248;
}

/* dark mode support */
body.dark .profile-table th{
    background:#6c63ff;
}

body.dark .profile-table td{
    background:#1e2535;
    border-color:#2f3a55;
}

body.dark .profile-table tr:hover td{
    background:#2a3248;
}
.header{background:#49465b;color:#fff;padding:15px;display:flex;justify-content:space-between;}
.container{max-width:1100px;margin:20px auto;}
.card{background:#fff;padding:20px;border-radius:10px;margin-bottom:20px;}
input,select{width:100%;padding:8px;margin:5px 0;}
button{padding:8px 15px;background:#49465b;color:#fff;border:none;border-radius:6px;}
table{width:100%;border-collapse:collapse;margin-top:10px;}
th,td{border:1px solid #ddd;padding:8px;text-align:left;}
th{background:#eee;}
.profile-pic{width:100px;height:100px;border-radius:50%;}
.profile-card{
    animation: fadeIn 0.6s ease-in-out;
}

.profile-top{
    display:flex;
    align-items:center;
    gap:20px;
    flex-wrap:wrap;
}

.profile-img{
    width:120px;
    height:120px;
    border-radius:50%;
    object-fit:cover;
    border:4px solid #49465b;
    transition:0.3s;
}

.profile-img:hover{
    transform:scale(1.05);
}

.profile-info h2{
    margin:0;
}

.profile-info p{
    margin:5px 0;
    color:#555;
}

.stats{
    display:flex;
    gap:15px;
    margin-top:20px;
    flex-wrap:wrap;
}

.stat-box{
    flex:1;
    min-width:150px;
    background:linear-gradient(135deg,#49465b,#6c63ff);
    color:#fff;
    padding:15px;
    border-radius:10px;
    text-align:center;
    transition:0.3s;
}

.stat-box:hover{
    transform:translateY(-5px);
}

.stat-box h4{
    margin:0;
    font-size:14px;
    opacity:0.9;
}

.stat-box p{
    font-size:22px;
    margin-top:5px;
    font-weight:bold;
}

.section{
    margin-top:15px;
}

.section ul{
    padding-left:18px;
}

.section li{
    margin:5px 0;
}

/* animation */
@keyframes fadeIn{
    from{opacity:0;transform:translateY(10px);}
    to{opacity:1;transform:translateY(0);}
}
body.dark{
    background:#1e1e2f;
    color:#e0e0e0;
}

body.dark .card{
    background:#2a2a3d;
    color:#e0e0e0;
}

body.dark table{
    color:#e0e0e0;
}

body.dark th{
    background:#3a3a55;
}

body.dark input,
body.dark select{
    background:#3a3a55;
    color:#fff;
    border:1px solid #555;
}

body.dark button{
    background:#6c63ff;
}
.dropdown {
    position: relative;
    display: inline-block;
}

.dropdown button {
    width: 100%;
    padding: 8px;
    background: #49465b;
    color: #fff;
    border: none;
    border-radius: 6px;
}

.dropdown-content {
    display: none;
    position: absolute;
    background: #fff;
    min-width: 200px;
    border: 1px solid #ddd;
    padding: 10px;
    z-index: 1;
}

.dropdown-content label {
    display: block;
    margin-bottom: 5px;
}
button.selectdays {
    width: 100%;
    background-color: transparent;
    border: 1px solid #ccc;   /* optional */
    padding: 12px;
    box-sizing: border-box;
    border-radius: 6px;
    cursor: pointer;
    text-align: center;
    color: black;
}

button.selectdays:hover {
    background-color: rgba(0, 0, 0, 0.05); /* subtle hover effect */
}

button.selectdays:active {
    transform: scale(0.98);
}
</style>
</head>

<body>

<div class="header">
<button onclick="history.back()">⬅ Back</button>
<h2>Hospital Dashboard</h2>
<button onclick="toggleDarkMode()">🌙 Dark Mode</button>
</div>

<div class="container">

<?php if(isset($msg)) echo "<div class='card'>$msg</div>"; ?>

<!-- PROFILE -->
<div class="card profile-card">



<!-- CENTER PROFILE -->
<div class="profile-center">

    <img src="<?php echo $hospital['profilePic']; ?>" class="profile-img">

    <h2><?php echo htmlspecialchars($hospital['hospitalName']); ?></h2>

    <p>📞 <?php echo htmlspecialchars($hospital['hphone']); ?></p>
    <p>📧 <?php echo htmlspecialchars($user['email'] ?? ''); ?></p>

</div>

<hr>

<!-- TABLE -->


<table class="profile-table">
    <tr>
        <th>Total Beds</th>
        <td><?php echo $hospital['totalBeds']; ?></td>
    </tr>

    <tr>
        <th>Available Beds</th>
        <td><?php echo $hospital['availableBeds']; ?></td>
    </tr>

    <tr>
        <th>Location</th>
        <td><?php echo htmlspecialchars($hospital['location']); ?></td>
    </tr>

    <tr>
        <th>Services</th>
        <td>
            <?php 
            foreach($servicesArr as $i=>$s){
                echo $s . " (Fee: " . ($feesArr[$i] ?? '') . ")<br>";
            }
            ?>
        </td>
    </tr>

    <tr>
        <th>Doctors</th>
        <td>
            <?php 
            foreach($doctorsArr as $uid){
                echo ($doctorsList[$uid]['name'] ?? '') . " (" . ($doctorsList[$uid]['specialization'] ?? '') . ")<br>";
            }
            ?>
        </td>
    </tr>
</table>

</div>
<!-- UPDATE HOSPITAL -->
<div class="card">
<h3>Update Hospital</h3>

<form method="POST" enctype="multipart/form-data">

<input type="file" name="profilePic">

<table>
<tr>
<th>Hospital Name</th>
<th>Phone</th>
<th>Total Beds</th>
<th>Available Beds</th>
<th>Location</th>
</tr>

<tr>
<td><input name="hospitalName" value="<?php echo $hospital['hospitalName']; ?>"></td>
<td><input name="phone" value="<?php echo $hospital['hphone']; ?>"></td>
<td><input type="number" name="totalBeds" value="<?php echo $hospital['totalBeds']; ?>"></td>
<td><input type="number" name="availableBeds" value="<?php echo $hospital['availableBeds']; ?>"></td>
<td><input name="location" value="<?php echo $hospital['location']; ?>"></td>
</tr>
</table>

<br>

<h4>Services & Fees</h4>

<table id="serviceTable">
<tr>
<th>Service</th>
<th>Fee</th>
</tr>

<?php for($i=0;$i<$serviceCount;$i++): ?>
<tr>
<td><input name="services[]" value="<?php echo $servicesArr[$i] ?? ''; ?>"></td>
<td><input name="fees[]" value="<?php echo $feesArr[$i] ?? ''; ?>"></td>
</tr>
<?php endfor; ?>

</table>

<br>

<button type="button" onclick="addServiceRow()">➕ Add Row</button>

<br>


<h4>Doctors</h4>
<select name="doctors[]" multiple>
<?php foreach($doctorsList as $uid=>$info): ?>
<option value="<?php echo $uid; ?>" <?php if(in_array($uid,$doctorsArr)) echo "selected"; ?>>
<?php echo $info['name'].' ('.$info['specialization'].')'; ?>
</option>
<?php endforeach; ?>
</select>

<br><br>
<button name="updateHospital">Save</button>

</form>
</div>

<!-- ADD DOCTOR -->

<div class="card">
<h3>Add Doctor</h3>


<form method="POST" enctype="multipart/form-data">

<input name="docName" placeholder="Doctor Name" required>
<input name="specialization" placeholder="Specialization" required>
<input name="bmdc" placeholder="BMDC" required>

<input name="consultationFees" placeholder="Consultation Fee" required>
<input name="experienceYears" placeholder="Experience (Years)" required>

<input name="education" placeholder="Education">
<input name="degrees" placeholder="Degrees">

<select name="gender">
<option value="">Select Gender</option>
<option value="Male">Male</option>
<option value="Female">Female</option>
</select>

<input type="file" name="doctorPic">

<button name="addDoctor">Add</button>
</form>
</div>
<!-- DOCTORS -->
<div class="card">
<h3>Doctors</h3>
<table>
<th>Name</th>
<th>Specialization</th>
<th>Fees</th>
<th>Experience</th>


<th>Action</th>
<?php foreach($doctorsArr as $uid): ?>
<tr>
<td><?php echo $doctorsList[$uid]['name'] ?? ''; ?></td>
<td><?php echo $doctorsList[$uid]['specialization'] ?? ''; ?></td>

<td><?php echo $doctorsList[$uid]['fees'] ?? ''; ?></td>
<td><?php echo $doctorsList[$uid]['experience'] ?? ''; ?></td>





<td>
<a href="?removeDoctor=<?php echo $uid; ?>">Remove</a>
</td>

</tr>
<?php endforeach; ?>
</table>
</div>
<!-- Assign Doctor Availability  -->

<div class="card">
<h3>Assign Doctor Availability</h3>

<form method="POST">

<!-- Doctor Select -->
<select name="doctorId" required>
<option value="">Select Doctor</option>
<?php foreach($doctorsArr as $uid): ?>
<option value="<?= $uid ?>">
<?= $doctorsList[$uid]['name'] ?? '' ?>
</option>
<?php endforeach; ?>
</select>

<!-- Days -->
<label>Select Days</label>
<select name="days[]" multiple required>
<option value="Sat">Sat</option>
<option value="Sun">Sun</option>
<option value="Mon">Mon</option>
<option value="Tue">Tue</option>
<option value="Wed">Wed</option>
<option value="Thu">Thu</option>
<option value="Fri">Fri</option>
</select>

<!-- Time -->
<input name="timeRange" placeholder="e.g. 5PM - 9PM" required>

<button name="addAvailability">Save</button>

</form>
</div>

<!-- List for Doctor availability -->
<div class="card">
<h3>Doctor Availability List</h3>

<table>
<tr>
<th>Doctor</th>
<th>Days</th>
<th>Time</th>
<th>Clinic</th>
<th>Action</th>
</tr>

<?php 
$res = mysqli_query($conn,"
SELECT * FROM doctor_availability 
WHERE hospitalId='".$hospital['id']."' 
ORDER BY id DESC
");
?>

<?php if(mysqli_num_rows($res) > 0): ?>
<?php while($row = mysqli_fetch_assoc($res)): ?>
<tr>

<td>
<?= $doctorsList[$row['doctorId']]['name'] ?? 'Unknown' ?>
</td>

<td><?= htmlspecialchars($row['days']) ?></td>
<td><?= htmlspecialchars($row['timeRange']) ?></td>
<td><?= htmlspecialchars($row['clinic']) ?></td>

<td>
<a href="?deleteAvail=<?= $row['id'] ?>" 
onclick="return confirm('Delete?')">❌</a>
</td>

</tr>
<?php endwhile; ?>
<?php else: ?>
<tr>
<td colspan="5">No Data</td>
</tr>
<?php endif; ?>

</table>
</div>
<!-- APPOINTMENTS -->
<div class="card">
<h3>Book Appointment</h3>

<form method="POST">
<input name="patientName" placeholder="Patient Name" required>
<input name="patientPhone" placeholder="Patient Phone" required>
<input type="date" name="date" required>

<select name="doctorId" required>
<option value="">Select Doctor</option>
<?php foreach($doctorsArr as $uid): ?>
<option value="<?php echo $uid; ?>">
<?php echo $doctorsList[$uid]['name'] ?? ''; ?>
</option>
<?php endforeach; ?>
</select>

<button name="bookAppointment">Book</button>
</form>
</div>

<div class="card">
<h3>🛏 Seat Bookings</h3>

<table>
<tr>
<th>Patient</th>
<th>Phone</th>
<th>Date</th>
<th>Day</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php while($row = mysqli_fetch_assoc($seatBookings)): ?>
<tr>
<td><?php echo $row['patient_name']; ?></td>
<td><?php echo $row['phone']; ?></td>
<td><?php echo $row['date']; ?></td>
<td><?php echo $row['day']; ?></td>
<td><?php echo $row['status'] ?? 'Pending'; ?></td>
<td>
<a href="?confirmSeat=<?php echo $row['id']; ?>">✔</a>
<a href="?deleteSeat=<?php echo $row['id']; ?>">❌</a>
</td>
</tr>
<?php endwhile; ?>



</table>
</div>






<!-- APPOINTMENTS -->
<div class="card">
<h3>Appointments</h3>

<table>
<tr>
<th>Patient</th>
<th>Phone</th>
<th>Doctor Name</th>
<th>Problem</th>
<th>Date</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php while($row = mysqli_fetch_assoc($appointments)): ?>
<tr>
<td><?php echo $row['patientName']; ?></td>
<td><?php echo $row['phone']; ?></td>
<td><?php echo $row['doctor_name'] ?? 'N/A'; ?></td>
<td><?php echo $row['problem']; ?></td>
<td><?php echo $row['appointment_date']; ?></td>
<td><?php echo $row['status'] ?? 'Pending'; ?></td>
<td>
    <a href="?confirmAppointment=<?php echo $row['id']; ?>"> Confirm</a>
<a href="?deleteAppointment=<?php echo $row['id']; ?>" 
   onclick="return confirm('Delete this appointment?')">
    Delete
</a>
</td>
</tr>
<?php endwhile; ?>

</table>
</div>

</div>
</div>


</body>
<script>
function addServiceRow(){
    let table = document.getElementById("serviceTable");

    let row = table.insertRow();

    row.innerHTML = `
        <td><input name="services[]"></td>
        <td><input name="fees[]"></td>
    `;
}

function toggleDarkMode(){
    document.body.classList.toggle("dark");

    // save preference
    if(document.body.classList.contains("dark")){
        localStorage.setItem("theme","dark");
    } else {
        localStorage.setItem("theme","light");
    }
}

function toggleDropdown(){
    let dropdown = document.getElementById("dayDropdown");
    dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
}

// load saved theme
window.onload = function(){
    if(localStorage.getItem("theme") === "dark"){
        document.body.classList.add("dark");
    }
}

</script>
</html>