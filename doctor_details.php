<?php
session_start();
include "backend/db.php";

/* ================= GET DOCTOR ================= */
$doctorId = $_GET['id'] ?? null;

if(!$doctorId){
    die("Doctor not found");
}

/* ================= DOCTOR + USER JOIN ================= */
$query = "
SELECT d.*, u.name, u.profilePic 
FROM doctors d
LEFT JOIN users u ON d.userId = u.userId
WHERE d.userId = '$doctorId'
LIMIT 1
";

$res = mysqli_query($conn, $query);
$doctor = mysqli_fetch_assoc($res);

if(!$doctor){
    die("Doctor not found");
}

/* ================= VALUES ================= */
$doctorName = $doctor['name'] ?? 'Unknown Doctor';
$specialization = $doctor['specialization'] ?? 'Specialist';
$clinic = !empty($doctor['clinic']) ? $doctor['clinic'] : 'Medical';
$fees = !empty($doctor['consultationFees']) ? $doctor['consultationFees'] : 1000;

/* ================= SLOTS ================= */
$slotQuery = mysqli_query($conn, "
SELECT * FROM doctor_slots 
WHERE doctorId='{$doctor['userId']}' 
AND status='available'
ORDER BY slotDate, slotTime
");

$slots = [];
while($row = mysqli_fetch_assoc($slotQuery)){
    $slots[] = $row;
}

$hasSlots = count($slots) > 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Doctor Profile</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
<?php if(isset($_GET['success'])): ?>
<div id="successMsg" class="fixed top-24 right-6 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg transition-opacity duration-500">
    ✅ Appointment booked successfully!
</div>
<?php endif; ?>
<!-- HEADER -->
<div class="fixed top-0 left-0 w-full bg-[#49465b] text-white z-50 shadow">
  <div class="max-w-6xl mx-auto flex items-center justify-between p-4">
    <button onclick="history.back()" class="bg-white text-[#49465b] px-4 py-2 rounded-lg">← Back</button>
    <h1 class="font-bold text-lg">Doctor Details</h1>
    <div></div>
  </div>
</div>

<div class="h-20"></div>

<div class="max-w-6xl mx-auto p-6">

<!-- PROFILE CARD -->
<div class="bg-gradient-to-r from-[#49465b] to-[#6b6785] text-white rounded-2xl p-8 mb-6 text-center">

    <!-- IMAGE -->
    <div class="flex justify-center mb-4">
        <?php if(!empty($doctor['profilePic']) && $doctor['profilePic'] != 'default.png'): ?>
            <img src="<?= $doctor['profilePic'] ?>" class="w-28 h-28 rounded-full object-cover border-4 border-white shadow-lg">
        <?php else: ?>
            <div class="w-28 h-28 bg-white text-black rounded-full flex items-center justify-center text-2xl font-bold shadow-lg">
                <?= strtoupper(substr($doctorName,0,2)) ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- NAME -->
    <h2 class="text-3xl font-bold"><?= $doctorName ?></h2>
    <p class="opacity-80 mt-1"><?= $specialization ?></p>

    <!-- INFO BOX -->
    <div class="mt-6 bg-white text-black rounded-xl p-5 max-w-md mx-auto shadow space-y-2 text-left">
        <p><b>BMDC:</b> <?= $doctor['bmdc'] ?? 'N/A' ?></p>
        <p><b>Clinic:</b> <?= $clinic ?></p>
        <p><b>Fees:</b> ৳ <?= $fees ?></p>
    </div>

</div>

<!-- DETAILS CARD -->
<div class="bg-white p-6 rounded-xl shadow mb-6 max-w-4xl mx-auto">

<div class="grid md:grid-cols-2 gap-4 text-sm">
    <div><b>Experience:</b> <?= $doctor['experienceYears'] ?? 'N/A' ?> years</div>
    <div><b>Gender:</b> <?= $doctor['gender'] ?? 'N/A' ?></div>
    <div><b>Available Days:</b> <?= $doctor['availableDays'] ?? 'N/A' ?></div>
    <div><b>Available Time:</b> <?= $doctor['availableTimes'] ?? 'N/A' ?></div>
    <div><b>Education:</b> <?= $doctor['education'] ?? 'N/A' ?></div>
    <div><b>Degrees:</b> <?= $doctor['degrees'] ?? 'N/A' ?></div>
</div>

</div>

<!-- BOOK BUTTON -->
<div class="text-center mb-6">
<button onclick="toggleForm()" class="bg-[#49465b] text-white px-6 py-3 rounded-lg shadow hover:opacity-90 transition">
    Book Appointment
</button>
</div>

<!-- FORM -->
<div id="form" class="hidden bg-white p-6 rounded-xl shadow max-w-2xl mx-auto mb-10">

<form method="POST" action="bookAppointment.php" class="space-y-4">

<input type="hidden" name="doctor" value="<?= $doctor['userId'] ?>">

<div>
<label class="font-semibold">Patient Name</label>
<input type="text" name="patient_name" required class="w-full border p-2 rounded">
</div>
<textarea name="problem" placeholder="Describe your problem" class="w-full border p-2 rounded"></textarea>
<div>
<label class="font-semibold">Phone</label>
<input type="text" name="phone" required class="w-full border p-2 rounded">
</div>

<?php if($hasSlots): ?>

<div>
<label class="font-semibold">Select Day</label>
<select id="daySelect" class="w-full border p-2 rounded" required>
<option value="">Select Day</option>
<option>Sunday</option>
<option>Monday</option>
<option>Tuesday</option>
<option>Wednesday</option>
<option>Thursday</option>
<option>Friday</option>
<option>Saturday</option>
</select>
</div>

<div>
<label class="font-semibold">Select Date</label>
<input type="date" name="date" id="date" required class="w-full border p-2 rounded">
</div>

<?php else: ?>

<div>
<label class="font-semibold">Select Date</label>
<input type="date" name="date" required class="w-full border p-2 rounded">
</div>

<?php endif; ?>

<button type="submit" class="bg-green-600 text-white px-4 py-2 rounded w-full">
Confirm Appointment
</button>

</form>

</div>

</div>

<!-- FOOTER -->
<footer class="bg-[#49465b] text-white mt-10">
  <div class="max-w-6xl mx-auto p-6 text-center">

    <h2 class="text-lg font-bold">Doctor Appointment System</h2>
    <p class="text-sm opacity-80 mt-2">
        Find doctors, book appointments, and manage your health easily.
    </p>

    <div class="mt-4 text-sm opacity-70">
        © <?php echo date("Y"); ?> All rights reserved.
    </div>

  </div>
</footer>

<script>

setTimeout(()=>{
    const msg = document.getElementById("successMsg");
    if(msg){
        msg.style.opacity = "0";
        setTimeout(()=> msg.remove(), 500);
    }
}, 2000);

function toggleForm(){
document.getElementById('form').classList.toggle('hidden');
}

const slots = <?php echo json_encode($slots); ?>;

<?php if($hasSlots): ?>

const daySelect = document.getElementById("daySelect");
const dateInput = document.getElementById("date");

daySelect.addEventListener("change", function(){

const selectedDay = this.value;
dateInput.value = "";

let matchedDates = [];

slots.forEach(slot=>{
const dateObj = new Date(slot.slotDate);
const days = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
const dayName = days[dateObj.getDay()];

if(dayName === selectedDay){
matchedDates.push(slot.slotDate);
}
});

if(matchedDates.length > 0){
dateInput.value = matchedDates[0];
}

});

<?php endif; ?>

</script>

</body>
</html>