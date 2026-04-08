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

/* ================= DEFAULT VALUES ================= */
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

<div class="max-w-4xl mx-auto mt-10 bg-white p-6 rounded-xl shadow">

    <!-- Profile -->
    <div class="flex items-center gap-6">
        <?php if(!empty($doctor['profilePic']) && $doctor['profilePic'] != 'default.png'): ?>
            <img src="<?= $doctor['profilePic'] ?>" class="w-24 h-24 rounded-full object-cover border-4 border-blue-500">
        <?php else: ?>
            <div class="w-24 h-24 bg-gray-300 rounded-full flex items-center justify-center text-xl font-bold">
                <?= strtoupper(substr($doctorName,0,2)) ?>
            </div>
        <?php endif; ?>

        <div>
            <h2 class="text-2xl font-bold"><?= $doctorName ?></h2>
            <p class="text-gray-600"><?= $specialization ?></p>
            <p class="text-gray-600">BMDC: <?= $doctor['bmdc'] ?? 'N/A' ?></p>
            <p class="text-gray-600">Clinic: <?= $clinic ?></p>
            <p class="text-gray-600">Fees: ৳ <?= $fees ?></p>
        </div>
    </div>

    <hr class="my-6">

    <!-- Info -->
    <div class="grid grid-cols-2 gap-4 text-sm">
        <div><b>Experience:</b> <?= $doctor['experienceYears'] ?? 'N/A' ?> years</div>
        <div><b>Gender:</b> <?= $doctor['gender'] ?? 'N/A' ?></div>
        <div><b>Available Days:</b> <?= $doctor['availableDays'] ?? 'N/A' ?></div>
        <div><b>Available Time:</b> <?= $doctor['availableTimes'] ?? 'N/A' ?></div>
        <div><b>Education:</b> <?= $doctor['education'] ?? 'N/A' ?></div>
        <div><b>Degrees:</b> <?= $doctor['degrees'] ?? 'N/A' ?></div>
    </div>

    <!-- Button -->
    <div class="mt-6">
        <button onclick="toggleForm()" class="bg-blue-600 text-white px-4 py-2 rounded">
            Book Appointment
        </button>
    </div>

    <!-- FORM -->
    <div id="form" class="hidden mt-6 border p-4 rounded bg-gray-50">

        <form method="POST" action="bookAppointment.php" class="space-y-4">

            <input type="hidden" name="doctor" value="<?= $doctor['userId'] ?>">

            <input type="text" name="patient_name" placeholder="Patient Name" required class="w-full border p-2">
            <input type="text" name="phone" placeholder="Phone" required class="w-full border p-2">

            <input type="date" name="date" required class="w-full border p-2">

            <select name="time" id="timeSlot" required class="w-full border p-2">
                <option value="">Select Time</option>
            </select>

            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded w-full">
                Confirm Appointment
            </button>

        </form>
    </div>

</div>

<script>
function toggleForm(){
    document.getElementById('form').classList.toggle('hidden');
}

const slots = <?php echo json_encode($slots); ?>;
const availableTimes = "<?= $doctor['availableTimes'] ?? '' ?>";

/* ================= TIME GENERATOR ================= */
function generateTimeSlots(timeRange){

    const timeSelect = document.getElementById("timeSlot");
    timeSelect.innerHTML = "<option value=''>Select Time</option>";

    if(!timeRange) return;

    let parts = timeRange.split("-");
    let start = parts[0].trim();
    let end = parts[1].trim();

    function convertTo24(time){
        let [t, modifier] = time.split(" ");
        let [hours, minutes] = t.split(":");

        if(modifier === "PM" && hours != "12") hours = parseInt(hours) + 12;
        if(modifier === "AM" && hours == "12") hours = "00";

        return `${hours.toString().padStart(2,'0')}:${minutes}`;
    }

    let startTime = convertTo24(start);
    let endTime = convertTo24(end);

    let current = new Date(`1970-01-01T${startTime}:00`);
    let endDate = new Date(`1970-01-01T${endTime}:00`);

    while(current <= endDate){

        let hh = current.getHours().toString().padStart(2,'0');
        let mm = current.getMinutes().toString().padStart(2,'0');

        let time = `${hh}:${mm}`;

        let option = document.createElement("option");
        option.value = time;
        option.textContent = time;

        timeSelect.appendChild(option);

        current.setMinutes(current.getMinutes() + 15);
    }
}

/* ================= SLOT LOGIC ================= */
function loadSlotsFromDB(){
    const timeSelect = document.getElementById("timeSlot");
    timeSelect.innerHTML = "<option value=''>Select Time</option>";

    slots.forEach(slot=>{
        let opt = document.createElement("option");
        opt.value = slot.slotTime;
        opt.textContent = slot.slotTime;
        timeSelect.appendChild(opt);
    });
}

/* ================= AUTO LOAD ================= */
if(slots.length > 0){
    loadSlotsFromDB(); // DB slot priority
}else{
    generateTimeSlots(availableTimes); // fallback
}
</script>

</body>
</html>