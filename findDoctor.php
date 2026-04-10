<?php
session_start();
include "backend/db.php";

/* ✅ GROUP BY to remove duplicate doctors */
$query = "
SELECT d.userId, d.specialization, d.gender, d.clinic, u.name
FROM doctors d
LEFT JOIN users u ON d.userId = u.userId
GROUP BY d.userId
";

$result = mysqli_query($conn, $query);

if(!$result){
    die("Query Failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Find Doctor</title>

<link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com"></script>

<style>
body { background:#f4f6f9; }

/* HEADER */
.header {
  background:#49465b;
  color:#fff;
  padding:14px;
  text-align:center;
  font-weight:bold;
}

/* SEARCH BAR */
.search-bar {
  background:#49465b;
  padding:15px;
  display:flex;
  gap:10px;
  flex-wrap:wrap;
}

.search-bar input,
.search-bar select {
  flex:1;
  min-width:180px;
  border-radius:8px;
}

/* CARD */
.doctor-card {
  border-left:6px solid #49465b;
  transition:0.3s;
  border-radius:14px;
  padding:18px;
}

.doctor-card:hover {
  transform:translateY(-6px);
  box-shadow:0 12px 28px rgba(0,0,0,0.12);
}

/* BUTTON */
.view-btn {
  background:#49465b;
  color:#fff;
}

.view-btn:hover {
  background:#2f2c3d;
}

/* BACK BUTTON */
.back-btn {
  background:transparent;
  border:1px solid #49465b;
  color:white;
  padding:6px 12px;
  border-radius:6px;
  font-weight:500;
  text-decoration:none;
}

.back-btn:hover {
  background:#49465b;
  color:#fff;
}

/* FOOTER */
footer {
            background:#49465b;
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: 40px;
        }
</style>
<script src="https://unpkg.com/lucide@latest"></script>
</head>

<body>

<div class="header flex justify-between items-center px-4">
  
  <!-- BACK BUTTON -->
  <button class="back-btn" onclick="history.back()">
  <i data-lucide="arrow-left"></i>
</button>

  <div>Find Doctor</div>

  <div></div>
</div>

<!-- SEARCH -->
<div class="search-bar">

  <input type="text" id="searchName" placeholder="🔍 Doctor Name" class="input input-bordered">

  <select id="filterSpeciality" class="select select-bordered">
    <option value="">All Speciality</option>
    <option value="medicine">Medicine</option>
    <option value="eye">Eye</option>
    <option value="cardiology">Cardiologist</option>
    <option value="neurologist">Neurologist</option>
    <option value="psychologist">Psychologist</option>
    <option value="dermatologist">Dermatologist</option>
  </select>

  <select id="filterGender" class="select select-bordered">
    <option value="">All Gender</option>
    <option value="male">Male</option>
    <option value="female">Female</option>
  </select>

</div>

<!-- DOCTOR LIST -->
<div class="max-w-4xl mx-auto p-5 space-y-4" id="doctorList">

<?php while($row = mysqli_fetch_assoc($result)) { ?>

<div class="doctor-card bg-white shadow"
     data-name="<?= strtolower($row['name'] ?? '') ?>"
     data-speciality="<?= strtolower($row['specialization'] ?? '') ?>"
     data-gender="<?= strtolower($row['gender'] ?? '') ?>">

  <div class="flex gap-4 items-center">

    <!-- INFO -->
    <div class="flex-1">
      <h3 class="font-bold text-lg text-[#49465b]">
        <?= htmlspecialchars($row['name'] ?? 'Unknown') ?>
      </h3>

      <p class="text-sm text-gray-600">
        🏥 <?= htmlspecialchars($row['specialization'] ?? 'N/A') ?>
      </p>

      <p class="text-sm text-gray-500">
        🏥 Clinic: <?= htmlspecialchars($row['clinic'] ?? 'N/A') ?>
      </p>

      <p class="text-sm text-gray-500">
        ⚧ Gender: <?= htmlspecialchars($row['gender'] ?? 'N/A') ?>
      </p>
    </div>

    <!-- BUTTON -->
    <div>
      <a href="doctor_details.php?id=<?= $row['userId'] ?>" class="btn btn-sm view-btn">
        View
      </a>
    </div>

  </div>
</div>

<?php } ?>

</div>

<!-- FOOTER -->
 <footer>
      
            <div class="copyright">
                <p>Copyright &copy; 2025 QuicAid. All rights reserved.</p>
            </div>
       
    </footer>

<script>
const nameInput = document.getElementById('searchName');
const specialitySelect = document.getElementById('filterSpeciality');
const genderSelect = document.getElementById('filterGender');

[nameInput, specialitySelect, genderSelect].forEach(el => {
  el.addEventListener('keyup', filterDoctors);
  el.addEventListener('change', filterDoctors);
});

function filterDoctors(){

  let nameValue = nameInput.value.toLowerCase();
  let specialityValue = specialitySelect.value.toLowerCase();
  let genderValue = genderSelect.value.toLowerCase();

  document.querySelectorAll('.doctor-card').forEach(card => {

    let name = card.dataset.name;
    let speciality = card.dataset.speciality;
    let gender = card.dataset.gender;

    if(
      name.includes(nameValue) &&
      (specialityValue === "" || speciality === specialityValue) &&
      (genderValue === "" || gender === genderValue)
    ){
      card.style.display = "block";
    } else {
      card.style.display = "none";
    }

  });
}

  lucide.createIcons();

</script>

</body>
</html>