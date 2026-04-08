<?php
session_start();
include "backend/db.php";

/* ================= GET HOSPITAL ID ================= */
$hospital_id = $_GET['id'] ?? null;

if(!$hospital_id){
    die("No hospital selected");
}

/* ================= LOGIN CHECK ================= */
$loggedIn = isset($_SESSION['user_id']);

/* ================= FETCH HOSPITAL ================= */
$hospitalRes = mysqli_query($conn, "SELECT * FROM hospitals WHERE id='$hospital_id'");
$hospital = mysqli_fetch_assoc($hospitalRes);

if(!$hospital){
    die("Hospital not found");
}

/* ================= FETCH HOSPITAL OWNER (OPTIONAL) ================= */
$userRes = mysqli_query($conn, "SELECT * FROM users WHERE userId='".$hospital['userId']."'");
$user = mysqli_fetch_assoc($userRes);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Hospital Details</title>
<script src="https://cdn.tailwindcss.com"></script>

<style>
body{ background:#f4f6f9; }
.toast{
    position:fixed;
    top:20px;
    right:20px;
    padding:12px 18px;
    border-radius:8px;
    color:#fff;
    font-size:14px;
    z-index:9999;
    opacity:0;
    animation: fadeInOut 3s forwards;
}

.toast.success{
    background:#22c55e;
}

.toast.error{
    background:#ef4444;
}

@keyframes fadeInOut{
    0%{ opacity:0; transform:translateY(-20px); }
    10%{ opacity:1; transform:translateY(0); }
    80%{ opacity:1; }
    100%{ opacity:0; transform:translateY(-20px); }
}
header { 
  position: sticky;
  top: 0;
  z-index: 9999;
  background: rgba(75,75,113,0.85);
  color:white;
  padding:1rem 2rem;
  display:flex;
  align-items:center;
  justify-content:space-between;
}

header h1 { font-size:1.5rem; margin:0; }

.back-btn{
    background:#fff;
    color:#49465b;
    border:none;
    padding:6px 10px;
    border-radius:6px;
    cursor:pointer;
    font-weight:bold;
}

.card{
    max-width:950px;margin:40px auto;background:#fff;
    border-radius:16px;box-shadow:0 6px 18px rgba(0,0,0,0.08);
    padding:30px;text-align:center;
}

.avatar{
    width:90px;height:90px;border-radius:50%;
    background:#49465b;color:#fff;
    display:flex;align-items:center;justify-content:center;
    font-size:28px;margin:auto;
}

.name{ font-size:22px;font-weight:bold;margin-top:15px;color:#49465b; }
.info{ color:#6b7280;font-size:14px; }
.name{ font-size:22px;font-weight:bold;margin-top:15px;color:#49465b; }
.info{ color:#6b7280;font-size:14px; }
.hospital-box{
    margin-top:25px;padding:15px;border:1px solid #eee;
    border-radius:10px;background:#fafafa;text-align:left;
}

.section{ margin-top:30px;text-align:left; }
.section-title{ font-weight:bold;color:#49465b;margin-bottom:10px;font-size:16px; }

.table{ width:100%;border-collapse:collapse; }
.table th{ background:#49465b;color:#fff;padding:12px;text-align:center; }
.table td{ padding:12px;border-bottom:1px solid #eee;text-align:center; }

.btn{
    background:#49465b;color:#fff;
    padding:6px 10px;border-radius:6px;
    font-size:13px;text-decoration:none;
}

.modal{
    display:none;position:fixed;top:0;left:0;
    width:100%;height:100%;
    background:rgba(0,0,0,0.5);
    justify-content:center;align-items:center;
}

.modal-content{
    background:#fff;padding:20px;border-radius:10px;width:320px;
}

input, textarea{
    width:100%;padding:8px;margin-top:8px;
    border:1px solid #ccc;border-radius:5px;
}

.close{ float:right;cursor:pointer;font-weight:bold; }
</style>
</head>

<body>

  <!-- HEADER -->
<header>
  <button class="btn-back" onclick="goBack()">← Back</button>
  <h1>Hospital Details</h1>
  <div></div>
</header>
    <?php if(isset($_SESSION['success'])): ?>
<div id="toast" class="toast success">
    <?= $_SESSION['success']; ?>
</div>
<?php unset($_SESSION['success']); endif; ?>

<?php if(isset($_SESSION['error'])): ?>
<div id="toast" class="toast error">
    <?= $_SESSION['error']; ?>
</div>
<?php unset($_SESSION['error']); endif; ?>

<div class="card">

    <!-- Avatar -->
    <div class="avatar">
        <?= strtoupper(substr($user['name'],0,1)) ?>
    </div>

    <!-- User Info -->
    <div class="name"><?= htmlspecialchars($user['name']) ?></div>
    <div class="info">📧 <?= htmlspecialchars($user['email']) ?></div>
    <div class="info">📞 <?= htmlspecialchars($user['phone'] ?? 'Not set') ?></div>
    <div class="info">Role: <?= htmlspecialchars($user['role']) ?></div>

    <!-- Hospital -->
    <?php if($hospital): ?>
    <div class="hospital-box">
        <div class="section-title">🏥 Hospital Information</div>
        <div class="info"><b>Name:</b> <?= $hospital['hospitalName'] ?></div>
        <div class="info"><b>Location:</b> <?= $hospital['location'] ?></div>
        <div class="info"><b>Total Beds:</b> <?= $hospital['totalBeds'] ?></div>
        <div class="info"><b>Available Beds:</b> <?= $hospital['availableBeds'] ?></div>
    </div>
    <?php endif; ?>
    <!-- BOOK SEAT -->
    <div class="section">
       <button class="btn" onclick="openSeatModal()">🛏 Book Seat</button>
    </div>

    <div id="seatModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeSeatModal()">✖</span>

            <h3 class="font-bold">Book Hospital Seat</h3>

            <form method="POST" action="save_seat.php">

                <input type="hidden" name="hospital_id" value="<?= $hospital['id'] ?>">

                <label>Your Name</label>
                <input type="text" name="patient_name" required>

                <label>Phone</label>
                <input type="text" name="phone" required>

                <label>Date</label>
                <input type="date" name="date" required>

                     <div style="margin-top:10px;">
    <label style="font-weight:600; display:block; margin-bottom:5px;">
        Day
    </label>

    <select name="day" required 
        style="width:100%; padding:8px; border:1px solid #ccc; border-radius:5px;">
        <option value="">Select Day</option>
        <option value="Saturday">Saturday</option>
        <option value="Sunday">Sunday</option>
        <option value="Monday">Monday</option>
        <option value="Tuesday">Tuesday</option>
        <option value="Wednesday">Wednesday</option>
        <option value="Thursday">Thursday</option>
        <option value="Friday">Friday</option>
    </select>
</div>


                <button class="btn mt-3 w-full">Book Seat</button>
            </form>
        </div>
    </div>

    <!-- SERVICES -->
    <div class="section">
        <div class="section-title">🏥 Services</div>

        <table class="table">
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Fee</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $services = json_decode($hospital['services'], true);
            $fees = json_decode($hospital['fees'], true);

            if(is_array($services)){
                for($i=0;$i<count($services);$i++){
            ?>
                <tr>
                    <td><?= $services[$i] ?></td>
                    <td><?= $fees[$i] ?? '' ?></td>
                </tr>
            <?php } } ?>
            </tbody>
        </table>
    </div>

    <!-- DOCTORS -->
    <div class="section">
        <div class="section-title">👨‍⚕️ Doctors</div>

        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Specialization</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>

            <?php
            $doctorIds = json_decode($hospital['doctorsAvailable'], true);

            if(is_array($doctorIds)){
                foreach($doctorIds as $docId){

                    $docRes = mysqli_query($conn, "SELECT name, specialization FROM users WHERE userId='$docId'");
                    $doc = mysqli_fetch_assoc($docRes);

                    if(!$doc) continue;
            ?>
                <tr>
                    <td><?= $doc['name'] ?></td>
                    <td><?= $doc['specialization'] ?: 'General' ?></td>
                    <td>
                        <button class="btn" onclick="openModal('<?= $docId ?>')">📅 Book</button>
                    </td>
                </tr>
            <?php } } ?>

            </tbody>
        </table>
    </div>

</div>

<!-- APPOINTMENT MODAL -->
<div id="modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">✖</span>

        <h3 class="font-bold">Book Appointment</h3>

        <form method="POST" action="save_appointment.php">

            <input type="hidden" name="doctor_id" id="doctorId">
            <input type="hidden" name="hospital_id" value="<?= $hospital['id'] ?>">

            <label>Name</label>
            <input type="text" name="patient_name" required>

            <label>Phone</label>
            <input type="text" name="phone" required>

            <label>Date</label>
            <input type="date" name="date" required>

            <label>Problem</label>
            <textarea name="problem" required></textarea>

            <button class="btn mt-3 w-full">Submit</button>
        </form>
    </div>
</div>

<script>
function goBack(){
    window.history.back();
}

function openModal(docId){
    document.getElementById("doctorId").value = docId;
    document.getElementById("modal").style.display = "flex";
}

function closeModal(){
    document.getElementById("modal").style.display = "none";
}

function openSeatModal(){
    document.getElementById("seatModal").style.display = "flex";
}

function closeSeatModal(){
    document.getElementById("seatModal").style.display = "none";
}
</script>

</body>
</html>