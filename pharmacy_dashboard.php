<?php
session_start();
include "backend/db.php";

// ---------------- AUTH CHECK ----------------
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pharmacy'){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM users WHERE id='$user_id'"));

// Fetch pharmacy-specific info
$pharmacy = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM pharmacies WHERE userId='{$user['userId']}'"));

$msg = "";

$currentTime = date("H:i");

$opening = $pharmacy['opening_time'];
$closing = $pharmacy['closing_time'];

$status = "Closed";

if($opening && $closing){
    if($currentTime >= $opening && $currentTime <= $closing){
        $status = "Open";
    }
}
// ---------------- UPDATE PHARMACY INFO (AJAX) ----------------
if(isset($_POST['updatePharmacy'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $opening_time = $_POST['opening_time'];
    $closing_time = $_POST['closing_time'];

    // Update users table
    mysqli_query($conn,"UPDATE users 
        SET name='$name', email='$email', phone='$phone', address='$address' 
        WHERE id='$user_id'
    ");

    // Update pharmacy table
    mysqli_query($conn,"UPDATE pharmacies 
        SET pharmacyName='$name', 
            pharmAddress='$address',
            opening_time='$opening_time',
            closing_time='$closing_time'
        WHERE userId='{$user['userId']}'
    ");

    // Refresh data
    $user = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM users WHERE id='$user_id'"));
    $pharmacy = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM pharmacies WHERE userId='{$user['userId']}'"));

    echo json_encode([
        'status' => 'success',
        'user' => $user,
        'pharmacy' => $pharmacy
    ]);
    exit;
}

// ---------------- FETCH PHARMACY MEDICINES ----------------
$medicines = mysqli_query($conn,"
    SELECT * FROM pharmacy_medicines
    WHERE pharmacyId='{$pharmacy['userId']}'
    ORDER BY created_at DESC
");

// ---------------- AVAILABLE MEDICINE COUNT ----------------
$availableCountResult = mysqli_query($conn, "
    SELECT SUM(quantity) AS availableCount 
    FROM pharmacy_medicines 
    WHERE pharmacyId='{$pharmacy['userId']}' AND availability='available'
");
$availableCountRow = mysqli_fetch_assoc($availableCountResult);
$availableCount = $availableCountRow['availableCount'] ?? 0;

// ---------------- ADD / UPDATE MEDICINE ----------------
if(isset($_POST['saveMedicine'])){
    $medId = $_POST['medId'] ?? 0;
    $name = $_POST['name'];
    $details = $_POST['details'];
    $price = $_POST['price'];
    $availability = $_POST['availability'];
    $quantity = $_POST['quantity'] ?? 0;

    if($medId > 0){
        mysqli_query($conn,"UPDATE pharmacy_medicines SET name='$name', details='$details', price='$price', availability='$availability', quantity='$quantity' WHERE id='$medId' AND pharmacyId='{$pharmacy['userId']}'");
    } else {
        mysqli_query($conn,"INSERT INTO pharmacy_medicines (pharmacyId, name, details, price, availability, quantity) VALUES ('{$pharmacy['userId']}','$name','$details','$price','$availability','$quantity')");
    }
    header("Location: pharmacy_dashboard.php");
    exit;
}

// ---------------- DELETE MEDICINE ----------------
if(isset($_GET['deleteMedicine'])){
    $medId = intval($_GET['deleteMedicine']);
    mysqli_query($conn,"DELETE FROM pharmacy_medicines WHERE id='$medId' AND pharmacyId='{$pharmacy['userId']}'");
    header("Location: pharmacy_dashboard.php");
    exit;
}
$pharmacyId = $pharmacy['id'];

$reviews = mysqli_query($conn, "
    SELECT * 
    FROM pharmacy_reviews
    WHERE pharmacyId = '$pharmacyId'
    ORDER BY created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Pharmacy Panel</title>
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
a{color:#49465b;text-decoration:none;}
a:hover{text-decoration:underline;}
</style>
</head>
<body>

<div class="header" style="display:flex; align-items:center; justify-content:flex-start; gap:15px;">
    <a href="javascript:history.back()" style="color:#fff; text-decoration:none; font-size:18px; font-weight:bold;">← Back</a>
    <h2 style="margin:0; color:#fff; flex-grow:1; text-align:center;">Pharmacy Panel</h2>
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
    <p><b>Status:</b> 
    <span style="color: <?= $status == 'Open' ? 'green' : 'red' ?>; font-weight:bold;">
        <?= $status ?>
    </span>
</p>

<p><b>Opening Time:</b> <?= $opening ?? '-' ?></p>
<p><b>Closing Time:</b> <?= $closing ?? '-' ?></p>
</div>

<!-- UPDATE INFO FORM -->
<div class="card">
    <h3>Update Info</h3>
    <form id="updatePharmacyForm">
    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" placeholder="Pharmacy Name" required>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" placeholder="Email" required>
    <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="Phone" required>
    <input type="text" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>" placeholder="Address" required>
    <input type="time" name="opening_time" value="<?= htmlspecialchars($pharmacy['opening_time'] ?? '') ?>" required>
    <input type="time" name="closing_time" value="<?= htmlspecialchars($pharmacy['closing_time'] ?? '') ?>" required>
    <button type="submit">Update</button>
</form>
</div>

<!-- ADD / EDIT MEDICINE -->
<div class="card">
<h3>Add / Edit Medicine</h3>
<form method="POST">
    <input type="hidden" name="medId" value="">
    <input type="text" name="name" placeholder="Medicine Name" required>
    <textarea name="details" placeholder="Details"></textarea>
    <input type="number" name="price" step="0.01" placeholder="Price" required>
    <input type="number" name="quantity" step="1" placeholder="Quantity Available" required>
    <select name="availability" required>
        <option value="available">Available</option>
        <option value="unavailable">Unavailable</option>
    </select>
    <button type="submit" name="saveMedicine">Save Medicine</button>
</form>
</div>

<!-- MEDICINES LIST -->
<div class="card">
<h3>Medicine Inventory (Total Available: <?= $availableCount ?>)</h3>
<table>
<tr>
<th>Name</th>
<th>Details</th>
<th>Price</th>
<th>Availability</th>
<th>Quantity</th>
<th>Action</th>
</tr>
<?php if(mysqli_num_rows($medicines) > 0): ?>
<?php while($m = mysqli_fetch_assoc($medicines)): ?>
<tr>
<td><?= htmlspecialchars($m['name']) ?><?php if($m['availability']=='available'): ?> (<?= $m['quantity'] ?> available)<?php endif; ?></td>
<td><?= htmlspecialchars($m['details']) ?></td>
<td><?= $m['price'] ?></td>
<td><?= ucfirst($m['availability']) ?></td>
<td><?= $m['quantity'] ?></td>
<td>
    <a href="?deleteMedicine=<?= $m['id'] ?>">Delete</a>
</td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="6" class="no-data">No Medicines Added Yet</td></tr>
<?php endif; ?>
</table>
</div>
<!-- REVIEWS LIST -->
<div class="card">
    <h3>Customer Reviews</h3>

    <table>
        <tr>
            <th>Name</th>
            <th>Rating</th>
            <th>Comment</th>
            <th>Reply</th>
            <th>Status</th>
            <th>Date</th>
        </tr>

        <?php if(mysqli_num_rows($reviews) > 0): ?>
            <?php while($r = mysqli_fetch_assoc($reviews)): ?>
                <tr>
                    <td><?= htmlspecialchars($r['userName']) ?></td>

                    <td>
                        <?php 
                        $rating = (int)$r['rating'];
                        echo str_repeat("⭐", $rating);
                        if($rating == 0) echo "No rating";
                        ?>
                    </td>

                    <td><?= htmlspecialchars($r['comment']) ?></td>

                    <td>
                        <?= $r['reply'] ? htmlspecialchars($r['reply']) : '<span style="color:#999;">No reply</span>' ?>
                    </td>

                    <td>
                        <span style="color:<?= $r['status']=='approved'?'green':'orange' ?>">
                            <?= ucfirst($r['status']) ?>
                        </span>
                    </td>

                    <td>
                        <?= date("d M Y", strtotime($r['created_at'])) ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" class="no-data">No reviews yet</td>
            </tr>
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

// AJAX Update Pharmacy Info
document.getElementById('updatePharmacyForm').addEventListener('submit', function(e){
    e.preventDefault();

    let formData = new FormData(this);
    formData.append('updatePharmacy', true);

    fetch('', { method:'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success'){

            // Update UI text
            document.getElementById('profilePhone').textContent = data.user.phone;
            document.getElementById('profileAddress').textContent = data.user.address;
            document.getElementById('profileName').textContent = data.user.name;
            document.getElementById('profileEmail').textContent = data.user.email;

            // Show message
            let msgBox = document.getElementById('msgBox');
            msgBox.textContent = "✅ Info updated";
            msgBox.style.display = 'block';

            setTimeout(()=>msgBox.style.display='none', 2000);

            // ✅ Reload page to update opening/closing status
            setTimeout(()=>location.reload(), 500);
        }
    });
});
</script>
</body>
</html>