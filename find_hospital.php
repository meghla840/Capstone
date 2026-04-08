<?php
session_start();
include "backend/db.php";

$result = mysqli_query($conn, "SELECT * FROM hospitals");

if(!$result){
    die("Query Failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Find Hospital</title>

<link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com"></script>

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<style>
body { background:#f4f6f9; }

/* HEADER */
.header {
  background:#49465b;
  color:#fff;
  padding:14px;
  display:flex;
  justify-content:center;
  position:relative;
}

.header-title {
  font-weight:bold;
}

.back-btn {
  position:absolute;
  left:15px;
  background:transparent;
  border:none;
  
  padding:5px 10px;
  border-radius:6px;
}

/* SEARCH BAR */
.search-bar {
  background:#49465b;
  padding:15px;
  display:flex;
  gap:10px;
  flex-wrap:wrap;
  align-items:center;
}

.search-bar input,
.search-bar select {
  flex:1;
  min-width:180px;
  border-radius:8px;
}

/* SEARCH BUTTON */
.search-btn {
  background:#2f2c3d;   /* darker than header */
  color:#ffffff;        /* light text */
  border:none;
  transition:0.3s;
  font-weight:500;
}

.search-btn:hover {
  background:#1f1c2b;   /* even darker on hover */
  color:#e5e7eb;        /* soft light gray */
  transform:translateY(-1px);
}

/* CARD */
.hospital-card {
  border-left:6px solid #49465b;
  transition:0.3s;
  border-radius:14px;
  padding:18px;
}

.hospital-card:hover {
  transform:translateY(-6px);
  box-shadow:0 12px 28px rgba(0,0,0,0.12);
}
.view-btn {
  background:#49465b;
  color:#ffffff;
  border:none;
  transition:0.3s;
}

.view-btn:hover {
  background:#2f2c3d;
  color:#e5e7eb;
  transform:translateY(-1px);
}

/* MAP */
#map {
  height:250px;
  margin:20px;
  border-radius:12px;
  box-shadow:0 4px 12px rgba(0,0,0,0.08);
}
footer {
            background: #49465b;
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: 40px;
        }
</style>
<script src="https://unpkg.com/lucide@latest"></script>
</head>

<body>

<!-- HEADER -->
<div class="header">
<button class="back-btn" onclick="history.back()">
  <i data-lucide="arrow-left"></i>
</button>
  <div class="header-title">Find Hospital</div>
</div>

<!-- SEARCH -->
<div class="search-bar">

  <input type="text" id="searchName" placeholder="🔍 Hospital Name" class="input input-bordered">
  <input type="text" id="searchLocation" placeholder="📍 Location" class="input input-bordered">

  <select id="sortOption" class="select select-bordered">
    <option value="">Sort By</option>
    <option value="name">Name</option>
    <option value="distance">Distance (Nearest)</option>
  </select>

  <button onclick="filterHospitals()" class="btn search-btn">
    🔍 Search
  </button>

</div>

<!-- LIST -->
<div class="max-w-3xl mx-auto p-5 space-y-4" id="hospitalList">

<?php while($row = mysqli_fetch_assoc($result)) { 

$image = !empty($row['image']) ? $row['image'] : 'https://via.placeholder.com/80';

?>

<div class="hospital-card bg-white shadow"
     data-name="<?= strtolower($row['hospitalName'] ?? '') ?>"
     data-location="<?= strtolower($row['location'] ?? '') ?>"
     data-beds="<?= $row['totalBeds'] ?? 0 ?>"
     data-lat="<?= $row['latitude'] ?? '' ?>"
     data-lng="<?= $row['longitude'] ?? '' ?>">

  <div class="flex gap-4 items-center">

    <!-- IMAGE -->
    <img src="<?= $image ?>"
         onerror="this.src='https://via.placeholder.com/80'"
         class="w-20 h-20 object-cover rounded-lg">

    <!-- INFO -->
    <div class="flex-1">
      <h3 class="font-bold text-[#49465b] text-lg">
        <?= htmlspecialchars($row['hospitalName']) ?>
      </h3>

      <p class="text-sm text-gray-600">📞 <?= htmlspecialchars($row['hphone']) ?></p>
      <p class="text-sm text-gray-500">📍 <?= htmlspecialchars($row['location']) ?></p>

      <div class="text-yellow-500 text-sm mt-1">
        ⭐ <?= number_format($row['rating'] ?? 4.5,1) ?>
      </div>
    </div>

    <!-- ACTIONS -->
    <div class="flex flex-col gap-2">

     <a href="hospital_details.php?id=<?= $row['id'] ?>" class="btn btn-sm view-btn">
  View
</a>

      <?php if(!empty($row['latitude']) && !empty($row['longitude'])) { ?>
      <a target="_blank"
         href="https://www.google.com/maps/dir/?api=1&destination=<?= $row['latitude'] ?>,<?= $row['longitude'] ?>"
         class="btn btn-outline btn-sm">
        🧭
      </a>
      <?php } ?>

    </div>

  </div>

</div>

<?php } ?>

</div>

<!-- MAP -->
<div id="map"></div>

<script>
 lucide.createIcons();
// ===== USER LOCATION =====
let userLat = null;
let userLng = null;

if (navigator.geolocation) {
  navigator.geolocation.getCurrentPosition(position => {
    userLat = position.coords.latitude;
    userLng = position.coords.longitude;
  });
}

// ===== DISTANCE FUNCTION =====
function getDistance(lat1, lon1, lat2, lon2) {
  function toRad(x) {
    return x * Math.PI / 180;
  }

  let R = 6371;
  let dLat = toRad(lat2 - lat1);
  let dLon = toRad(lon2 - lon1);

  let a =
    Math.sin(dLat/2) * Math.sin(dLat/2) +
    Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
    Math.sin(dLon/2) * Math.sin(dLon/2);

  let c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
  return R * c;
}

// ===== LIVE SEARCH =====
const nameInput = document.getElementById('searchName');
const locInput = document.getElementById('searchLocation');

[nameInput, locInput].forEach(input => {
  input.addEventListener('keyup', filterHospitals);
});

function filterHospitals() {

  let nameValue = nameInput.value.toLowerCase();
  let locValue = locInput.value.toLowerCase();

  document.querySelectorAll('.hospital-card').forEach(card => {

    let name = card.dataset.name;
    let loc = card.dataset.location;

    if(name.includes(nameValue) && loc.includes(locValue)){
      card.style.display = "block";
    } else {
      card.style.display = "none";
    }

  });
}

// ===== SORT =====
document.getElementById('sortOption').addEventListener('change', function(){

  let list = document.getElementById('hospitalList');
  let cards = Array.from(document.querySelectorAll('.hospital-card'));

  let type = this.value;

  cards.sort((a,b)=>{

    if(type === 'name'){
      return a.dataset.name.localeCompare(b.dataset.name);
    }

    if(type === 'distance' && userLat && userLng){

      let distA = getDistance(userLat, userLng, a.dataset.lat, a.dataset.lng);
      let distB = getDistance(userLat, userLng, b.dataset.lat, b.dataset.lng);

      return distA - distB;
    }

  });

  cards.forEach(card => list.appendChild(card));

});

// ===== MAP =====
var map = L.map('map').setView([23.6850, 90.3563], 7);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
  attribution:'© OpenStreetMap'
}).addTo(map);

<?php
$result2 = mysqli_query($conn, "SELECT * FROM hospitals");
while($row = mysqli_fetch_assoc($result2)){

if(!empty($row['latitude']) && !empty($row['longitude'])){
?>

L.marker([<?= $row['latitude'] ?>, <?= $row['longitude'] ?>])
.bindPopup("<?= addslashes($row['hospitalName']) ?>")
.addTo(map);

<?php }} ?>

</script>
<!-- FOOTER -->
 <footer>
      
            <div class="copyright">
                <p>Copyright &copy; 2025 QuicAid. All rights reserved.</p>
            </div>
       
    </footer>

</body>
</html>