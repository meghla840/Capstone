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

/* ================= SEAT BOOKINGS ================= */
$seatBookings = mysqli_query($conn,"
SELECT * FROM seat_bookings 
WHERE hospital_id='".$hospital['id']."' 
ORDER BY id DESC
");

/* ================= APPOINTMENTS (DOCTOR NAME FIXED) ================= */
$appointments = mysqli_query($conn,"
SELECT a.*, u.name AS doctor_name
FROM appointments a
LEFT JOIN doctors d ON a.doctorId = d.userId
LEFT JOIN users u ON d.userId = u.userId
WHERE a.hospitalUserId='".$hospital['id']."'
ORDER BY a.id DESC
");

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Hospital Dashboard</title>
</head>

<body>

<h2>🛏 Seat Bookings</h2>

<table border="1" width="100%">
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

<hr>

<h2>📅 Appointments</h2>

<table border="1" width="100%">
<tr>
<th>Patient</th>
<th>Phone</th>
<th>Doctor Name</th>
<th>Problem</th>
<th>Date</th>
<th>Status</th>
</tr>

<?php while($row = mysqli_fetch_assoc($appointments)): ?>
<tr>
<td><?php echo $row['patientName']; ?></td>
<td><?php echo $row['phone']; ?></td>
<td><?php echo $row['doctor_name'] ?? 'N/A'; ?></td>
<td><?php echo $row['problem']; ?></td>
<td><?php echo $row['appointment_date']; ?></td>
<td><?php echo $row['status']; ?></td>
</tr>
<?php endwhile; ?>

</table>

</body>
</html>