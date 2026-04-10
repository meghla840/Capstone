<?php
session_start();
include "backend/db.php";

// ---------------- AUTH CHECK ----------------
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'transport'){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user
$user = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM users WHERE id='$user_id'"));

// Fetch transport info
$transport = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM transports WHERE userId='$user_id'"));

$msg = "";

// ---------------- UPDATE TRANSPORT INFO (AJAX) ----------------
if(isset($_POST['updateTransport'])){
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    mysqli_query($conn,"UPDATE users SET phone='$phone', address='$address' WHERE id='$user_id'");
    mysqli_query($conn,"UPDATE transports SET transportAddress='$address' WHERE userId='$user_id'");

    $user = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM users WHERE id='$user_id'"));
    $transport = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM transports WHERE userId='$user_id'"));

    echo json_encode([
        'status' => 'success',
        'user' => $user,
        'transport' => $transport
    ]);
    exit;
}

// ---------------- FETCH TRANSPORT REQUESTS ----------------
$bookings = mysqli_query($conn,"SELECT * FROM book_ambulance ORDER BY created_at DESC");

// ---------------- ADD / UPDATE VEHICLE ----------------
if(isset($_POST['saveVehicle'])){
    $vehicleId = $_POST['vehicleId'] ?? 0;
    $name = $_POST['name'];
    $capacity = $_POST['capacity'];
    $status = $_POST['status'];
    $driverName = $_POST['driverName'] ?? '';
    $driverNID = $_POST['driverNID'] ?? '';
    $driverPhone = $_POST['driverPhone'] ?? '';

    // ✅ NEW FIELDS
    $driverLocation = $_POST['driverLocation'] ?? '';
    $driverLat = $_POST['driverLat'] ?? NULL;
    $driverLng = $_POST['driverLng'] ?? NULL;

    // Upload folder
    $uploadDir = "uploads/drivers/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $driverPhoto = 'images/default-driver.png';

    if(isset($_FILES['driverPhoto']) && $_FILES['driverPhoto']['tmp_name']){
        $ext = pathinfo($_FILES['driverPhoto']['name'], PATHINFO_EXTENSION);
        $driverPhoto = $uploadDir . time() . "_driver." . $ext;
        move_uploaded_file($_FILES['driverPhoto']['tmp_name'], $driverPhoto);
    }

    // UPDATE
    if($vehicleId > 0){
        mysqli_query($conn,"UPDATE transport_vehicles SET 
            name='$name',
            capacity='$capacity',
            status='$status',
            driverName='$driverName',
            driverNID='$driverNID',
            driverPhone='$driverPhone',
            driverLocation='$driverLocation',
            driverLat='$driverLat',
            driverLng='$driverLng',
            driverPhoto='$driverPhoto'
            WHERE id='$vehicleId' AND transportId='$user_id'");
    } 
    // INSERT
    else {
        mysqli_query($conn,"INSERT INTO transport_vehicles 
            (transportId,name,capacity,status,driverName,driverNID,driverPhone,driverLocation,driverLat,driverLng,driverPhoto)
            VALUES ('$user_id','$name','$capacity','$status','$driverName','$driverNID','$driverPhone','$driverLocation','$driverLat','$driverLng','$driverPhoto')");
    }

    header("Location: transport_dashboard.php");
    exit;
}

   

// ---------------- DELETE VEHICLE ----------------
if(isset($_GET['deleteVehicle'])){
    $vehicleId = intval($_GET['deleteVehicle']);
    mysqli_query($conn,"DELETE FROM transport_vehicles WHERE id='$vehicleId' AND transportId='$user_id'");
    header("Location: transport_dashboard.php");
    exit;
}

// ---------------- FETCH VEHICLES ----------------
$vehicles = mysqli_query($conn,"SELECT * FROM transport_vehicles WHERE transportId='$user_id' ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Transport Dashboard</title>
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
table{width:100%;border-collapse:collapse;margin-top:10px;font-size:14px;}
th,td{border:1px solid #ddd;padding:10px;text-align:left;}
th{background:#f3f4f6;font-weight:600;}
.no-data{text-align:center;color:#888;padding:15px;}
.msg{background:#22c55e;color:#fff;padding:10px;border-radius:8px;margin-bottom:10px;transition:0.5s;}
img.profile-pic{width:120px;height:120px;border-radius:50%;object-fit:cover;margin-bottom:10px;}
img.driver-pic{width:80px;height:80px;border-radius:50%;object-fit:cover;}
a{color:#49465b;text-decoration:none;}
a:hover{text-decoration:underline;}
#map{height:400px;width:100%;margin-top:15px;border-radius:12px;}
</style>
</head>
<body>

<div class="header" style="display:flex; align-items:center; justify-content:flex-start; gap:15px;">
    <a href="javascript:history.back()" style="color:#fff; text-decoration:none; font-size:18px; font-weight:bold;">← Back</a>
    <h2 style="margin:0; color:#fff; flex-grow:1; text-align:center;">Transport Dashboard</h2>
</div>

<div class="container">

<div id="msgBox" class="msg" style="display:none;"></div>

<!-- PROFILE CARD -->
<div class="cardc" id="profileCard">
    <img src="<?= htmlspecialchars($user['profilePic']) ?>" class="profile-pic">
    <h3><?= htmlspecialchars($user['name']) ?></h3>
    <p><b>Email:</b> <?= htmlspecialchars($user['email']) ?></p>
    <p><b>Phone:</b> <span id="profilePhone"><?= htmlspecialchars($user['phone'] ?? '-') ?></span></p>
    <p><b>Address:</b> <span id="profileAddress"><?= htmlspecialchars($user['address'] ?? '-') ?></span></p>
</div>

<!-- UPDATE INFO -->
<div class="card">
    <h3>Update Info</h3>
    <form id="updateTransportForm">
        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
        <input type="text" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>" required>
        <button type="submit">Update</button>
    </form>
</div>

<!-- VEHICLE FORM -->
<div class="card">
<h3>Add / Edit Vehicle</h3>
<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="vehicleId">
    <input type="text" name="name" placeholder="Vehicle Name" required>
    <input type="number" name="capacity" placeholder="Capacity" required>
    <select name="status" required>
        <option value="available">Available</option>
        <option value="busy">Busy</option>
        <option value="maintenance">Maintenance</option>
    </select>
    <input type="text" name="driverName" placeholder="Driver Name" required>
    <input type="text" name="driverNID" placeholder="Driver NID" required>
    <input type="text" name="driverPhone" placeholder="Driver Phone" required>
    <input type="text" name="driverLocation" placeholder="Driver Location (e.g. Dhaka)">

    <input type="file" name="driverPhoto">
    <button type="submit" name="saveVehicle">Save Vehicle</button>
</form>
</div>

<!-- VEHICLE LIST -->
<div class="card">
<h3>Vehicles List</h3>
<table>
<tr>
<th>Vehicle</th>
<th>Capacity</th>
<th>Status</th>
<th>Driver</th>
<th>Phone</th>
<th>Photo</th>
<th>Action</th>
</tr>
<?php if(mysqli_num_rows($vehicles) > 0): ?>
<?php while($v = mysqli_fetch_assoc($vehicles)): ?>
<tr>
<td><?= htmlspecialchars($v['name']) ?></td>
<td><?= $v['capacity'] ?></td>
<td><?= ucfirst($v['status']) ?></td>
<td><?= htmlspecialchars($v['driverName']) ?></td>
<td><?= htmlspecialchars($v['driverPhone']) ?></td>
<td><img src="<?= htmlspecialchars($v['driverPhoto']) ?>" class="driver-pic"></td>
<td>
    <a href="?editVehicle=<?= $v['id'] ?>">Edit</a> | 
    <a href="?deleteVehicle=<?= $v['id'] ?>">Delete</a>
</td>
</tr>

<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="7" class="no-data">No Vehicles Added Yet</td></tr>
<?php endif; ?>
</table>
</div>

<!-- MAP -->
<div class="card">
<h3>Live Driver Locations</h3>
<div id="map"></div>
</div>

<!-- BOOKED AMBULANCE USERS -->
<div class="card">
<h3>Booked Ambulance Users</h3>
<table>
<tr>
<th>ID</th>
<th>Name</th>
<th>Phone</th>
<th>Address</th>
<th>Vehicle</th>
<th>Driver Phone</th>
<th>Booked At</th>
</tr>

<?php if(mysqli_num_rows($bookings) > 0): ?>
<?php while($b = mysqli_fetch_assoc($bookings)): ?>
<tr>
<td><?= $b['id'] ?></td>
<td><?= htmlspecialchars($b['name']) ?></td>
<td><?= htmlspecialchars($b['user_phone']) ?></td>
<td><?= htmlspecialchars($b['address']) ?></td>
<td><?= htmlspecialchars($b['vehicle_name']) ?></td>
<td><?= htmlspecialchars($b['driver_phone']) ?></td>
<td><?= $b['created_at'] ?></td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="7" class="no-data">No Bookings Found</td></tr>
<?php endif; ?>

</table>
</div>

<script>
document.getElementById('updateTransportForm').addEventListener('submit', function(e){
    e.preventDefault();
    let formData = new FormData(this);
    formData.append('updateTransport', true);

    fetch('', { method:'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success'){
            document.getElementById('profilePhone').textContent = data.user.phone;
            document.getElementById('profileAddress').textContent = data.user.address;
            let msg = document.getElementById('msgBox');
            msg.innerText = "Info updated";
            msg.style.display='block';
            setTimeout(()=>msg.style.display='none',2000);
        }
    });
});
</script>

</body>
</html>