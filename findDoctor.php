<?php
session_start();
include "backend/db.php";

/* ✅ GROUP BY to remove duplicate doctors */
$query = "
SELECT 
  d.userId,
  d.specialization,
  d.gender,
  d.clinic,
  u.name,
  u.profilePic
FROM doctors d
LEFT JOIN users u ON d.userId = u.userId
"
;

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
    background: #fff;
    border-radius: 18px;
    padding: 16px;
    text-align: center;
    position: relative;
    overflow: hidden;

    border: 1px solid rgba(73, 70, 91, 0.12);
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);

    transition: all 0.4s ease;
    transform: translateY(0);
}

/* FLOAT hover */
.doctor-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 22px 55px rgba(0,0,0,0.18);
}

/* top gradient glow line */
.doctor-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    height: 4px;
    width: 100%;
    background: linear-gradient(90deg, #49465b, #7c78a3, #49465b);
    background-size: 200% 100%;
    animation: moveGlow 3s linear infinite;
}

@keyframes moveGlow {
    0% { background-position: 0% 50%; }
    100% { background-position: 200% 50%; }
}

/* IMAGE (premium circle style) */
.doctor-card img {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #49465b;
    transition: all 0.4s ease;
    box-shadow: 0 6px 15px rgba(0,0,0,0.12);
}

/* hover image effect */
.doctor-card:hover img {
    transform: scale(1.08) rotate(3deg);
    border-color: #7c78a3;
}

/* NAME */
.doctor-card h2 {
    font-size: 18px;
    font-weight: 700;
    margin-top: 10px;
    color: #49465b;
    transition: 0.3s;
}

.doctor-card:hover h2 {
    letter-spacing: 0.5px;
    color: #2f2c3d;
}

/* TEXT INFO */
.doctor-card p {
    font-size: 13px;
    margin: 3px 0;
    color: #666;
    transition: 0.3s;
}

.doctor-card:hover p {
    transform: translateX(2px);
    color: #444;
}

/* VIEW BUTTON (glass + shine effect) */
.doctor-card a {
    margin-top: 12px;
    display: inline-block;
    width: 100%;
    padding: 9px 14px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 500;
    color: #fff;

    background: linear-gradient(135deg, #49465b, #2f2c3d);
    position: relative;
    overflow: hidden;

    transition: 0.3s ease;
}

/* shine animation */
.doctor-card a::after {
    content: "";
    position: absolute;
    top: 0;
    left: -120%;
    width: 100%;
    height: 100%;
    background: rgba(255,255,255,0.25);
    transform: skewX(-25deg);
    transition: 0.5s;
}

.doctor-card a:hover::after {
    left: 120%;
}

.doctor-card a:hover {
    transform: scale(1.05);
}

/* FADE IN animation (page load feel premium) */
.doctor-card {
    opacity: 0;
    animation: fadeUp 0.6s ease forwards;
}

@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(25px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
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
<div class="max-w-6xl mx-auto p-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5" id="doctorList">

<?php while($row = mysqli_fetch_assoc($result)) { ?>

<div class="doctor-card bg-white rounded-2xl shadow-md p-6 flex flex-col items-center text-center hover:-translate-y-2 transition"

     data-name="<?= strtolower(htmlspecialchars($row['name'])) ?>"
     data-speciality="<?= strtolower(htmlspecialchars($row['specialization'])) ?>"
     data-gender="<?= strtolower(htmlspecialchars($row['gender'])) ?>"
>

  <?php 
$imgPath = "uploads/" . $row['profilePic'];
$hasImage = !empty($row['profilePic']) && file_exists($imgPath);
?>

<?php if($hasImage): ?>
    <img src="<?= $imgPath ?>"
         class="w-24 h-24 rounded-full border-4 border-[#49465b] object-cover shadow">
<?php else: ?>
    <div class="w-24 h-24 rounded-full border-4 border-[#49465b] shadow flex items-center justify-center bg-gray-100">

        <!-- Default Doctor Avatar -->
        <svg class="w-12 h-12 text-gray-500" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-4.4 0-8 2.2-8 5v2h16v-2c0-2.8-3.6-5-8-5z"/>
        </svg>

    </div>
<?php endif; ?>

  <div class="mt-4">
    <h2 class="font-bold text-[#49465b] text-lg">
      <?= htmlspecialchars($row['name']) ?>
    </h2>

    <span style="
    display:inline-block;
    padding:4px 10px;
    background:#f1f1f6;
    border-radius:20px;
    font-size:12px;
    color:#49465b;
">
    <?= htmlspecialchars($row['specialization']) ?>
</span>

    <p class="text-xs text-gray-400">
      <?= htmlspecialchars($row['clinic']) ?>
    </p>

    <p class="text-xs text-gray-500">
       <?= htmlspecialchars($row['gender']) ?>
    </p>
  </div>

  <a href="doctor_details.php?id=<?= $row['userId'] ?>"
     class="mt-4 w-full bg-[#49465b] text-white py-2 rounded-xl hover:bg-[#2f2c3d]">
     View Profile
  </a>

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
      card.style.display = "flex";
    } else {
      card.style.display = "none";
    }

  });
}

  lucide.createIcons();

</script>

</body>
</html>