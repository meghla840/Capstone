<?php
include "backend/db.php";

/* ---------------- BLOCK USER ---------------- */
if(isset($_GET['block'])){
    $id = $_GET['block'];
    mysqli_query($conn, "UPDATE users SET status='blocked' WHERE id='$id'");
    header("Location: admin.php");
    exit();
}

/* ---------------- UNBLOCK USER ---------------- */
if(isset($_GET['unblock'])){
    $id = $_GET['unblock'];
    mysqli_query($conn, "UPDATE users SET status='active' WHERE id='$id'");
    header("Location: admin.php");
    exit();
}

/* ---------------- FETCH USERS ---------------- */
$users = mysqli_query($conn, "SELECT * FROM users");

/* ---------------- FETCH DOCTORS ---------------- */
$doctors = mysqli_query($conn, "
SELECT d.*, u.name, u.profilePic 
FROM doctors d
LEFT JOIN users u ON d.userId = u.userId
");

/* ---------------- FETCH HOSPITALS ---------------- */
$hospitals = mysqli_query($conn, "
SELECT h.*, u.name 
FROM hospitals h
LEFT JOIN users u ON h.userId = u.userId
");

/* ---------------- FETCH PATIENTS ---------------- */
$patients = mysqli_query($conn, "
SELECT p.*, u.name 
FROM patients p
LEFT JOIN users u ON p.userId = u.userId
");

/* ---------------- FETCH TRANSPORTS ---------------- */
$transports = mysqli_query($conn, "
SELECT t.*, tv.name, tv.capacity, tv.status, tv.driverName, tv.driverPhone, tv.driverLocation, tv.driverPhoto
FROM transport_vehicles tv
LEFT JOIN transports t ON tv.transportId = t.id
");
/* ---------------- FETCH REVIEWS ---------------- */
$reviews = mysqli_query($conn, "
SELECT m.*, u.name, u.role, u.phone 
FROM msg m
LEFT JOIN users u ON m.user_id = u.id
ORDER BY m.created_at DESC
");
/* ---------------- FETCH TRAFFIC ALERTS ---------------- */
$alerts = mysqli_query($conn, "
SELECT ta.*, u.name 
FROM traffic_alerts ta
LEFT JOIN users u ON ta.user_id = u.id
ORDER BY ta.created_at DESC
");

?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Panel</title>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
body{font-family:Arial;background:#f3f6f9;margin:0;}
header{
    background:#49465b;
    color:white;
    padding:15px;
    position: sticky;
    top: 0;
    z-index: 999;
}
.container{padding:20px;}
.card{background:#fff;padding:15px;border-radius:10px;margin-bottom:20px;}
table{
    width:100%;
    border-collapse:collapse;
    table-layout:fixed; /* ✅ important for alignment */
}

th, td{
    padding:12px 10px;
    border-bottom:1px solid #e5e5e5;
    vertical-align:middle;
    word-wrap:break-word; /* long text wrap */
}

th{
    background:#49465b;
    color:#fff;
    font-weight:600;
    text-align:left;
}

tr:hover{
    background:#f5f7fa;
}

/* Button alignment */
td .btn{
    margin:2px 2px;
}

/* Status column alignment */
.status{
    font-weight:500;
    text-transform:capitalize;
}
.btn{padding:5px 10px;border:none;background:#49465b;color:#fff;border-radius:6px;cursor:pointer;}
@keyframes popupFadeIn {
  from {
    opacity: 0;
    transform: scale(0.85);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.popup-animate {
  animation: popupFadeIn 0.25s ease-out;
}
.popup{
 display:none;
 position:fixed;
 top:50%;
 left:50%;
 transform:translate(-50%,-50%);
 background:linear-gradient(135deg,#ffffff,#f8f9fb);
 padding:25px;
 border-radius:15px;
 width:420px;
 box-shadow:0 15px 40px rgba(0,0,0,0.25);
 z-index:100;
 animation:fadeIn 0.3s ease;
}

.popup h3{
 margin-top:0;
 color:#49465b;
}

.popup p{
 margin:6px 0;
 color:#333;
}
#popup {
  animation: bgFade 0.2s ease-out;
}

@keyframes bgFade {
  from { opacity: 0; }
  to { opacity: 1; }
}

.popup-close{
 float:right;
 cursor:pointer;
 color:white;
 background:red;
 padding:2px 8px;
 border-radius:50%;
 font-weight:bold;
}
.poetry{
    margin-top:15px;
    font-size:16px;
    line-height:1.8;
    max-width:800px;
    margin-left:auto;
    margin-right:auto;
    opacity:0;
    animation: fadeUp 1.5s ease forwards;
}

/* Fade animations */
@keyframes fadeDown{
    from{
        opacity:0;
        transform:translateY(-20px);
    }
    to{
        opacity:1;
        transform:translateY(0);
    }
}

@keyframes fadeUp{
    from{
        opacity:0;
        transform:translateY(30px);
    }
    to{
        opacity:1;
        transform:translateY(0);
    }
}

@keyframes fadeIn{
 from {opacity:0; transform:translate(-50%,-60%);}
 to {opacity:1; transform:translate(-50%,-50%);}
}
.section-title{
    font-size:22px;
    font-weight:700;
    color:#49465b;
    position:relative;
    display:inline-block;
    margin-bottom:15px;
}

.section-title::after{
    content:"";
    position:absolute;
    left:0;
    bottom:-6px;
    width:60%;
    height:4px;
    background:linear-gradient(90deg,#49465b,#6e6c80,#a5a3b8);
    border-radius:10px;
}
</style>
</head>

<body>

<header>
    <div style="display:flex;align-items:center;justify-content:space-between;">

        <!-- Left: Back Button -->
        <div style="flex:1;">
            <button onclick="history.back()" class="btn">⬅ Back</button>
        </div>

        <!-- Center: Title -->
        <div style="flex:1;text-align:center;">
            <h2 style="margin:0;">Admin Panel</h2>
        </div>

        <!-- Right: Empty (for balance) -->
        <div style="flex:1;"></div>

    </div>
</header>

<div class="container">
<!-- PROJECT PROFILE / ABOUT SECTION -->
<div class="card" style="text-align:center; background:linear-gradient(135deg,#49465b,#6e6c80); color:white;">

    <!-- LOGO / IMAGE -->
    <div style="margin-bottom:15px;">
        <?php
$logoQuery = mysqli_query($conn, "SELECT profilePic FROM users WHERE role='admin' LIMIT 1");
$logoData = mysqli_fetch_assoc($logoQuery);
?>

<img src="<?= !empty($logoData['profilePic']) && file_exists($logoData['profilePic']) 
    ? $logoData['profilePic'] 
    : 'https://via.placeholder.com/90'; ?>"
alt="QuickAid Logo"
style="width:90px;height:90px;border-radius:50%;object-fit:contain;border:3px solid white;box-shadow:0 6px 18px rgba(0,0,0,0.2);">
    </div>

    <!-- TITLE -->
    <h1 style="margin:0; font-size:28px;">QuickAid</h1>

    <!-- MOTIVE -->
     <p class="poetry">
    “সময় যেন কারও মৃত্যুর কারণ না হয়—<br>
    প্রতিটি সেকেন্ডই একেকটি জীবনের অমূল্য আশ্রয়।<br><br>

    জীবনের নীরব যুদ্ধে, প্রতিটি হৃদস্পন্দন বলে বেঁচে থাকার গল্প,<br>
    আর QuickAid ছুটে চলে সেই গল্পগুলো বাঁচাতে।<br><br>

    কারণ এক মুহূর্তের দেরি মানেই কখনো চিরবিদায়—<br>
    তাই আমরা প্রতিটি সেকেন্ডকে রক্ষা করতে প্রতিশ্রুতিবদ্ধ।<br><br>

    QuickAid — দ্রুত সেবা নয় শুধু,<br>
    এটি এক টুকরো আশা,<br>
    যা বলে— “এখনও সময় আছে, এখনও বাঁচা সম্ভব।” ✨”
    </p>


   <!-- CONTACT -->
<?php
$admin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT phone,email FROM users WHERE role='admin' LIMIT 1"));
?>

<div style="margin-top:18px; font-size:14px;">
    <p><i class="bi bi-telephone"></i> Phone: <?= $admin['phone'] ?? 'N/A'; ?></p>
    <p><i class="bi bi-envelope"></i> Email: <?= $admin['email'] ?? 'N/A'; ?></p>
</div>

</div>
<!-- USERS -->
<div class="card">
<h3 class="section-title">All Users</h3>
<table>
<tr><th>Name</th><th>Email</th><th>Role</th><th>Action</th><th>Status</th></tr>

<?php while($u = mysqli_fetch_assoc($users)){ ?>
<tr>
<td><?= $u['name'] ?></td>
<td><?= $u['email'] ?></td>
<td><?= $u['role'] ?></td>
<td>

<button class="btn" onclick='showUser(<?= json_encode($u) ?>)'>View</button>

<?php if($u['status'] == 'active'){ ?>
    <button class="btn blockBtn" data-id="<?= $u['id'] ?>">Block</button>
<?php } else { ?>
    <button class="btn unblockBtn" data-id="<?= $u['id'] ?>" style="background:green;">Unblock</button>
<?php } ?>

</td>
<td class="status"><?= $u['status'] ?></td>


</tr>
<?php } ?>

</table>
</div>

<!-- DOCTORS -->
<div class="card">
<h3 class="section-title">Doctors</h3>
<table>
<tr><th>Name</th><th>Specialization</th><th>BMDC</th><th>Action</th></tr>

<?php while($d = mysqli_fetch_assoc($doctors)){ ?>
<tr>
<td><?= $d['name'] ?></td>
<td><?= $d['specialization'] ?></td>
<td><?= $d['bmdc'] ?></td>
<td>
<button class="btn" onclick='showDoctor(<?= json_encode($d) ?>)'>View</button>
</td>
</tr>
<?php } ?>

</table>
</div>

<!-- HOSPITAL -->
<div class="card">
<h3 class="section-title">Hospitals</h3>
<table>
<tr><th>Name</th><th>Location</th><th>Beds</th><th>Action</th></tr>

<?php while($h = mysqli_fetch_assoc($hospitals)){ ?>
<tr>
<td><?= $h['hospitalName'] ?></td>
<td><?= $h['location'] ?></td>
<td><?= $h['availableBeds'] ?>/<?= $h['totalBeds'] ?></td>
<td>
<button class="btn" onclick='showHospital(<?= json_encode($h) ?>)'>View</button>
</td>
</tr>
<?php } ?>

</table>
</div>

<!-- PATIENT -->
<div class="card">
<h3 class="section-title">Patients</h3>
<table>
<tr><th>Name</th><th>Blood</th><th>Address</th><th>Action</th></tr>

<?php while($p = mysqli_fetch_assoc($patients)){ ?>
<tr>
<td><?= $p['name'] ?></td>
<td><?= $p['bloodGroup'] ?></td>
<td><?= $p['address'] ?></td>
<td>
<button class="btn" onclick='showPatient(<?= json_encode($p) ?>)'>View</button>
</td>
</tr>
<?php } ?>

</table>
</div>


<!-- TRANSPORT -->
<div class="card">
<h3 class="section-title">Transport Vehicles</h3>
<table>
<tr><th>Name</th><th>Capacity</th><th>Status</th><th>Driver</th><th>Action</th></tr>

<?php while($t = mysqli_fetch_assoc($transports)){ ?>
<tr>
<td><?= $t['name'] ?></td>
<td><?= $t['capacity'] ?></td>
<td><?= $t['status'] ?></td>
<td><?= $t['driverName'] ?></td>
<td>
<button class="btn" onclick='showTransport(<?= json_encode($t) ?>)'>View</button>
</td>
</tr>
<?php } ?>

</table>
</div>



</div>

<!-- POPUP -->
<div id="popup" class="popup">
<span class="popup-close" onclick="closePopup()">✖</span>
<div id="popupContent"></div>
</div>
<!-- REVIEWS -->
<div class="card">
<h3 class="section-title">Reviews</h3>

<table>
<tr>
    <th>Name</th>
    <th>Role</th>
    <th>Phone</th>
    <th>Message</th>
    <th>Date</th>
</tr>

<?php 
if(mysqli_num_rows($reviews) > 0){
    while($r = mysqli_fetch_assoc($reviews)){ 
?>
<tr>
    <td><?= htmlspecialchars($r['name'] ?? 'N/A') ?></td>
    <td><?= htmlspecialchars($r['role'] ?? 'N/A') ?></td>
    <td><?= htmlspecialchars($r['phone'] ?? 'N/A') ?></td>

    <!-- Message truncate -->
    <td 
    style="cursor:pointer;color:#49465b;font-weight:500;"
    onclick='showReview(<?= json_encode($r) ?>)'
>
    <?= strlen($r['message']) > 60 
        ? substr(htmlspecialchars($r['message']),0,60).'...' 
        : htmlspecialchars($r['message']) ?>
</td>

    <td><?= $r['created_at'] ?></td>
</tr>
<?php 
    } 
}else{
?>
<tr>
    <td colspan="5" style="text-align:center;">No reviews found</td>
</tr>
<?php } ?>

</table>
</div>

<!-- TRAFFIC ALERTS -->
<div class="card">
<h3 class="section-title">⚠️ Incident & Road Alerts</h3>

<table>
<tr>
    <th>User</th>
    <th>Location</th>
    <th>Message</th>
    <th>Status</th>
    <th>Date</th>
    <th>Action</th>
</tr>

<?php 
if(mysqli_num_rows($alerts) > 0){
    while($a = mysqli_fetch_assoc($alerts)){ 
?>
<tr>
    <td><?= htmlspecialchars($a['name'] ?? 'N/A') ?></td>
    
    <td>
        <?= htmlspecialchars($a['location'] ?? 'N/A') ?>
    </td>

    <td>
    <?= !empty($a['message']) 
        ? htmlspecialchars($a['message']) 
        : 'Need Help' 
    ?>
    </td>

    <td class="status">
        <?= htmlspecialchars($a['status']) ?>
    </td>

    <td>
        <?= $a['created_at'] ?>
    </td>
    <td>
    <?php if($a['status'] == 'pending'){ ?>
        <button class="btn approveBtn" data-id="<?= $a['id'] ?>">Approve</button>
    <?php } else { ?>
        <span style="color:green;font-weight:bold;">Approved</span>
    <?php } ?>
</td>
</tr>
<?php 
    } 
}else{
?>
<tr>
    <td colspan="5" style="text-align:center;">No traffic alerts found</td>
</tr>
<?php } ?>

</table>
</div>
<script>
/* REVIEW POPUP */
function showReview(r){
 showPopup(`
  <div style="
      background:#ffffff;
      border-radius:20px;
      padding:26px;
      box-shadow:0 25px 60px rgba(0,0,0,0.2);
      font-family:Arial, sans-serif;
      position:relative;
      overflow:hidden;
  ">

    <!-- Top Accent Bar -->
    <div style="
        position:absolute;
        top:0;
        left:0;
        width:100%;
        height:5px;
        background:linear-gradient(90deg,#49465b,#6e6c80,#a5a3b8);
    "></div>

    <!-- Header -->
    <div style="
        display:flex;
        align-items:center;
        justify-content:space-between;
        margin-bottom:20px;
    ">

        <div style="display:flex; align-items:center; gap:14px;">

            <!-- Avatar -->
            <div style="
                width:58px;
                height:58px;
                border-radius:50%;
                background:linear-gradient(135deg,#49465b,#6e6c80);
                display:flex;
                align-items:center;
                justify-content:center;
                color:#fff;
                font-weight:bold;
                font-size:22px;
                box-shadow:0 8px 20px rgba(0,0,0,0.25);
            ">
                ${r.name ? r.name.charAt(0).toUpperCase() : 'U'}
            </div>

            <div>
                <h2 style="margin:0; color:#222; font-size:22px;">
                    ${r.name ?? 'Unknown'}
                </h2>
                <div style="font-size:13px; color:#777;">
                    ${r.role ?? 'User'}
                </div>
            </div>

        </div>

        <!-- Subtle Role Tag -->
        <span style="
            font-size:11px;
            padding:5px 12px;
            border-radius:20px;
            background:#f1f2f6;
            color:#444;
            border:1px solid #e4e6eb;
        ">
            ${r.role ?? 'N/A'}
        </span>

    </div>

    <!-- Info Section -->
    <div style="
        display:flex;
        gap:10px;
        margin-bottom:18px;
    ">

        <div style="
            flex:1;
            background:#f9fafc;
            padding:12px;
            border-radius:12px;
            border:1px solid #eee;
            font-size:14px;
        ">
            📞 <b>Phone</b><br>
            <span style="color:#555;">
                ${r.phone ?? 'N/A'}
            </span>
        </div>

    </div>

    <!-- Message -->
    <div style="
        background:linear-gradient(180deg,#ffffff,#f9fafc);
        border-radius:14px;
        padding:18px;
        border:1px solid #eee;
        line-height:1.8;
        color:#333;
        font-size:14px;
        box-shadow:inset 0 0 0 1px rgba(255,255,255,0.6);
        white-space:pre-wrap;
        word-wrap:break-word;
    ">
        💬 ${r.message}
    </div>

  </div>
 `);
}

function showPopup(html){
 document.getElementById('popupContent').innerHTML = html;
 document.getElementById('popup').style.display='block';
}

function closePopup(){
 document.getElementById('popup').style.display='none';
}

/* USER */
function showUser(u){
 showPopup(`
  <div style="
      background:#ffffff;
      border-radius:14px;
      padding:22px;
      box-shadow:0 12px 30px rgba(0,0,0,0.18);
      font-family:Arial;
      max-width:100%;
  ">

    <!-- Header -->
    <div style="text-align:center; margin-bottom:15px;">
        <div style="
            width:70px;
            height:70px;
            margin:0 auto 10px;
            border-radius:50%;
            background:linear-gradient(135deg,#49465b,#6e6c80);
            display:flex;
            align-items:center;
            justify-content:center;
            color:#fff;
            font-size:26px;
            font-weight:bold;
        ">
            ${u.name ? u.name.charAt(0).toUpperCase() : 'U'}
        </div>

        <h2 style="margin:0; color:#49465b;">${u.name}</h2>

        <span style="
            display:inline-block;
            margin-top:6px;
            padding:4px 12px;
            font-size:12px;
            background:#49465b;
            color:#fff;
            border-radius:20px;
            letter-spacing:0.5px;
        ">
            ${u.role}
        </span>
    </div>

    <!-- Info -->
    <div style="border-top:1px solid #eee; padding-top:15px;">
        <p style="margin:8px 0; font-size:14px;">
            📧 <b>Email:</b> ${u.email}
        </p>

        <p style="margin:8px 0; font-size:14px;">
            📞 <b>Phone:</b> ${u.phone ?? 'N/A'}
        </p>
    </div>

  </div>
 `);
}

/* DOCTOR */
function showDoctor(d){
 showPopup(`
  <div style="
      background:#ffffff;
      border-radius:14px;
      padding:22px;
      box-shadow:0 12px 30px rgba(0,0,0,0.18);
      font-family:Arial;
  ">

    <!-- Header -->
    <div style="text-align:center; margin-bottom:15px;">
        <div style="
            width:70px;
            height:70px;
            margin:0 auto 10px;
            border-radius:50%;
            background:linear-gradient(135deg,#49465b,#6e6c80);
            display:flex;
            align-items:center;
            justify-content:center;
            color:#fff;
            font-size:26px;
            font-weight:bold;
        ">
            ${d.name ? d.name.charAt(0).toUpperCase() : 'D'}
        </div>

        <h2 style="margin:0; color:#49465b;">${d.name}</h2>

        <p style="
            margin-top:5px;
            font-size:13px;
            color:#777;
        ">
            ${d.specialization ?? 'Specialist'}
        </p>
    </div>

    <!-- Info -->
    <div style="border-top:1px solid #eee; padding-top:15px; font-size:14px;">

        <p style="margin:8px 0;">
            🪪 <b>BMDC:</b> ${d.bmdc}
        </p>

        <p style="margin:8px 0;">
            🏥 <b>Clinic:</b> ${d.clinic ?? 'N/A'}
        </p>

        <p style="margin:8px 0;">
            💰 <b>Fees:</b> ${d.consultationFees ?? 'N/A'}
        </p>

    </div>

  </div>
 `);
}

/* HOSPITAL */
function showHospital(h){
 showPopup(`
  <div style="
      background:#ffffff;
      border-radius:14px;
      padding:22px;
      box-shadow:0 12px 30px rgba(0,0,0,0.18);
      font-family:Arial;
  ">

    <!-- Header -->
    <div style="
        background:linear-gradient(135deg,#49465b,#6e6c80);
        color:#fff;
        padding:15px;
        border-radius:10px;
        text-align:center;
        margin-bottom:18px;
    ">
        <h2 style="margin:0;">🏥 ${h.hospitalName}</h2>
        <p style="margin:5px 0; font-size:13px; opacity:0.9;">
            Healthcare Facility
        </p>
    </div>

    <!-- Info Grid -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; font-size:14px;">

        <div style="background:#f8f9fb; padding:12px; border-radius:8px;">
            📍 <b>Location</b><br>
            ${h.location}
        </div>

        <div style="background:#f8f9fb; padding:12px; border-radius:8px;">
            📞 <b>Phone</b><br>
            ${h.hphone ?? 'N/A'}
        </div>

    </div>

    <!-- Beds Info -->
    <div style="
        margin-top:15px;
        background:#eef2f7;
        padding:15px;
        border-radius:10px;
        text-align:center;
    ">
        <p style="margin:0; font-size:13px; color:#555;">Available Beds</p>
        <h2 style="margin:5px 0; color:#49465b;">
            ${h.availableBeds} / ${h.totalBeds}
        </h2>

        <div style="
            width:100%;
            height:8px;
            background:#ddd;
            border-radius:20px;
            overflow:hidden;
            margin-top:8px;
        ">
            <div style="
                width:${(h.availableBeds / h.totalBeds) * 100}%;
                height:100%;
                background:linear-gradient(90deg,#4caf50,#8bc34a);
            "></div>
        </div>
    </div>

  </div>
 `);
}

/* PATIENT */
function showPatient(p){
 showPopup(`
  <div style="
      background:#ffffff;
      border-radius:14px;
      padding:22px;
      box-shadow:0 12px 30px rgba(0,0,0,0.18);
      font-family:Arial;
  ">

    <!-- Header -->
    <div style="text-align:center; margin-bottom:15px;">
        <div style="
            width:70px;
            height:70px;
            margin:0 auto 10px;
            border-radius:50%;
            background:linear-gradient(135deg,#49465b,#6e6c80);
            display:flex;
            align-items:center;
            justify-content:center;
            color:#fff;
            font-size:24px;
            font-weight:bold;
        ">
            🧑
        </div>

        <h2 style="margin:0; color:#49465b;">${p.name}</h2>
        
        <span style="
            display:inline-block;
            margin-top:6px;
            padding:4px 12px;
            font-size:12px;
            background:#49465b;
            color:#fff;
            border-radius:20px;
        ">
            Patient
        </span>
    </div>

    <!-- Info -->
    <div style="border-top:1px solid #eee; padding-top:15px; font-size:14px;">

        <!-- Blood Group -->
        <div style="
            background:#f3f6f9;
            border-left:4px solid #49465b;
            padding:12px;
            border-radius:8px;
            margin-bottom:10px;
        ">
            🩸 <b>Blood Group:</b>
            <span style="
                font-size:15px;
                font-weight:bold;
                color:#49465b;
                margin-left:5px;
            ">
                ${p.bloodGroup}
            </span>
        </div>

        <!-- Address -->
        <div style="
            background:#f8f9fb;
            padding:12px;
            border-radius:8px;
        ">
            📍 <b>Address:</b><br>
            ${p.address}
        </div>

    </div>

  </div>
 `);
}

/* TRANSPORT */
function showTransport(t){
 showPopup(`
  <div style="
      background:#ffffff;
      border-radius:14px;
      padding:22px;
      box-shadow:0 12px 30px rgba(0,0,0,0.18);
      font-family:Arial;
  ">

    <!-- Header -->
    <div style="
        background:linear-gradient(135deg,#49465b,#6e6c80);
        color:#fff;
        padding:15px;
        border-radius:10px;
        text-align:center;
        margin-bottom:18px;
    ">
        <h2 style="margin:0;">🚑 ${t.name}</h2>
        <p style="margin:5px 0; font-size:13px; opacity:0.9;">
            Emergency Transport
        </p>
    </div>

    <!-- Status + Capacity -->
    <div style="display:flex; justify-content:space-between; gap:10px; margin-bottom:15px;">

        <div style="
            flex:1;
            background:#f8f9fb;
            padding:12px;
            border-radius:8px;
            text-align:center;
        ">
            <p style="margin:0; font-size:12px; color:#777;">Capacity</p>
            <h3 style="margin:5px 0; color:#49465b;">${t.capacity}</h3>
        </div>

        <div style="
            flex:1;
            background:${t.status === 'available' ? '#e8f5e9' : '#ffebee'};
            padding:12px;
            border-radius:8px;
            text-align:center;
        ">
            <p style="margin:0; font-size:12px; color:#777;">Status</p>
            <h3 style="
                margin:5px 0;
                color:${t.status === 'available' ? '#2e7d32' : '#c62828'};
                text-transform:capitalize;
            ">
                ${t.status}
            </h3>
        </div>

    </div>

    <!-- Driver Info -->
    <div style="
        background:#f3f6f9;
        padding:15px;
        border-radius:10px;
    ">

        <h4 style="margin-top:0; color:#49465b;">👨‍✈️ Driver Details</h4>

        <p style="margin:6px 0;">
            <b>Name:</b> ${t.driverName}
        </p>

        <p style="margin:6px 0;">
            📞 <b>Phone:</b> ${t.driverPhone}
        </p>

        <p style="margin:6px 0;">
            📍 <b>Location:</b> ${t.driverLocation}
        </p>

    </div>

  </div>
 `);
}

// BLOCK + UNBLOCK using EVENT DELEGATION
document.addEventListener('click', function(e){

    /* ---------------- BLOCK ---------------- */
    if(e.target.classList.contains('blockBtn')){

        let btn = e.target;
        let userId = btn.getAttribute('data-id');

        Swal.fire({
            title: "Are you sure?",
            text: "You want to block this user!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, block it!"
        }).then((result) => {

            if(result.isConfirmed){

                // optional: disable button
                btn.disabled = true;

                fetch('user_action.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=block&id=' + userId
                })
                .then(res => res.text())
                .then(data => {

                    showToast("User blocked successfully");

                    let row = btn.closest('tr');

                    // update status
                    row.querySelector('.status').innerText = 'blocked';

                    // replace button
                    btn.outerHTML = `<button class="btn unblockBtn" data-id="${userId}" style="background:green;">Unblock</button>`;
                });

            }

        });
    }

    /* ---------------- UNBLOCK ---------------- */
    if(e.target.classList.contains('unblockBtn')){

        let btn = e.target;
        let userId = btn.getAttribute('data-id');

        Swal.fire({
            title: "Are you sure?",
            text: "You want to unblock this user!",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Yes, unblock!"
        }).then((result) => {

            if(result.isConfirmed){

                btn.disabled = true;

                fetch('user_action.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=unblock&id=' + userId
                })
                .then(res => res.text())
                .then(data => {

                    showToast("User unblocked successfully");

                    let row = btn.closest('tr');

                    // update status
                    row.querySelector('.status').innerText = 'active';

                    // replace button
                    btn.outerHTML = `<button class="btn blockBtn" data-id="${userId}">Block</button>`;
                });

            }

        });
    }

});


// TOAST FUNCTION
function showToast(message){

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true
    });

    Toast.fire({
        icon: 'success',
        title: message
    });

}

function showPopup(html){
    const popup = document.getElementById('popup');
    const content = document.getElementById('popupContent');

    content.innerHTML = html;

    // animation class add
    content.classList.remove('popup-animate'); 
    void content.offsetWidth; // reflow trick (restart animation)
    content.classList.add('popup-animate');

    popup.style.display = 'block';
}

//TRAFFIC
document.addEventListener('click', function(e){

    if(e.target.classList.contains('approveBtn')){

        let btn = e.target;
        let id = btn.getAttribute('data-id');

        Swal.fire({
            title: "Approve this alert?",
            text: "This will mark it as important",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Yes, approve"
        }).then((result) => {

            if(result.isConfirmed){

                fetch('traffic_action.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=approve&id=' + id
                })
                .then(res => res.text())
                .then(data => {

                    showToast("Alert approved");

                    let row = btn.closest('tr');
                    row.querySelector('.status').innerText = 'approved';

                    btn.outerHTML = `<span style="color:green;font-weight:bold;">Approved</span>`;
                });

            }

        });
    }

});

</script>

</body>
</html>