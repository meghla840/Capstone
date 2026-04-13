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



<style>
body { background:#f4f6f9; }

/* HEADER */
.header {
  background:#49465b;
  color:#fff;
  padding:14px;
  display:flex;
  justify-content:center;
  position:sticky;
  top:0;
  z-index: 999;
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
/* GRID */
.hospital-grid {
  max-width: 1200px;
  margin: auto;
  padding: 20px;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}

/* responsive */
@media (max-width: 1024px) {
  .hospital-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .hospital-grid {
    grid-template-columns: repeat(1, 1fr);
  }
}

/* CARD STYLE */
/* CARD (vertical profile style) */
.hospital-card {
  background: #fff;
  border-radius: 18px;
  overflow: hidden;
  text-align: center;
  border-top: 5px solid #49465b;

  position: relative;
  transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);

  transform: translateY(20px);
  opacity: 0;
  animation: cardEnter 0.7s ease forwards;
}

/* smooth stagger feel */
.hospital-card:nth-child(1){ animation-delay: 0.05s; }
.hospital-card:nth-child(2){ animation-delay: 0.1s; }
.hospital-card:nth-child(3){ animation-delay: 0.15s; }
.hospital-card:nth-child(4){ animation-delay: 0.2s; }

/* LUXURY HOVER LIFT */
.hospital-card:hover {
  transform: translateY(-14px) scale(1.03);
  box-shadow: 0 25px 60px rgba(0,0,0,0.18);
  border-left: 5px solid #2f2c3d;
}

/* IMAGE WRAPPER */
.hospital-img-wrapper {
  width: 100%;
  height: 190px;
  overflow: hidden;
  background: #f3f4f6;
  position: relative;
}

/* IMAGE SLOW ZOOM */
.hospital-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.8s ease, filter 0.5s ease;
}

/* soft cinematic zoom */
.hospital-card:hover .hospital-img {
  transform: scale(1.12);
  filter: brightness(0.85) saturate(1.1);
}

/* SUBTLE LIGHT SWEEP EFFECT */
.hospital-img-wrapper::after {
  content: "";
  position: absolute;
  top: 0;
  left: -75%;
  width: 50%;
  height: 100%;
  background: linear-gradient(
    120deg,
    transparent,
    rgba(255,255,255,0.35),
    transparent
  );
  transform: skewX(-20deg);
}

.hospital-card:hover .hospital-img-wrapper::after {
  animation: shine 1.2s ease;
}

/* TEXT ANIMATION */
.hospital-name {
  transition: 0.3s ease;
}

.hospital-card:hover .hospital-name {
  letter-spacing: 0.4px;
  color: #2f2c3d;
}

/* BUTTON PREMIUM */
.view-btn {
  padding: 6px 14px;
  border-radius: 10px;
  background: #49465b;
  color: white;
  border: none;
  font-size: 13px;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

/* glow effect */
.view-btn:hover {
  background: #2f2c3d;
  transform: translateY(-2px) scale(1.05);
  box-shadow: 0 10px 20px rgba(47,44,61,0.25);
}

/* smooth card entry */
@keyframes cardEnter {
  from {
    opacity: 0;
    transform: translateY(30px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

/* shine animation */
@keyframes shine {
  0% { left: -75%; }
  100% { left: 130%; }
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
<div class="hospital-grid" id="hospitalList">

<?php while($row = mysqli_fetch_assoc($result)) { 

$image = !empty($row['image']) ? $row['image'] : 'https://via.placeholder.com/80';

?>
<div class="hospital-card"
     data-name="<?= strtolower($row['hospitalName'] ?? '') ?>"
     data-location="<?= strtolower($row['location'] ?? '') ?>"
     data-lat="<?= $row['latitude'] ?? '' ?>"
     data-lng="<?= $row['longitude'] ?? '' ?>">


<!-- IMAGE TOP -->

   <?php
$image = $row['profilePic'] ?? 'default.png';

if ($image !== 'default.png' && !str_starts_with($image, 'uploads/')) {
    $image = 'uploads/' . $image;
}
?>
<div class="hospital-img-wrapper">
    <img class="hospital-img"
         src="<?= $image ?>"
         onerror="this.src='default.png'">
</div>

  <!-- INFO BELOW -->
  <div class="hospital-info">

    <div class="hospital-name">
      <?= htmlspecialchars($row['hospitalName']) ?>
    </div>

    <div class="hospital-text">
      📞 <?= htmlspecialchars($row['hphone']) ?>
    </div>

    <div class="hospital-text">
      📍 <?= htmlspecialchars($row['location']) ?>
    </div>

    <div class="hospital-text" style="color:#f1c40f;">
      ⭐ <?= number_format($row['rating'] ?? 4.5,1) ?>
    </div>

    <!-- BUTTONS -->
    <div style="display:flex; gap:8px; margin-top:8px; justify-content:center; flex-wrap:wrap;">

      <a href="hospital_details.php?id=<?= $row['id'] ?>" class="view-btn">
        View
      </a>

      <?php if(!empty($row['latitude']) && !empty($row['longitude'])) { ?>
        <a target="_blank"
           href="https://www.google.com/maps/dir/?api=1&destination=<?= $row['latitude'] ?>,<?= $row['longitude'] ?>"
           class="view-btn"
           style="background:#2f2c3d;">
           🧭 Map
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

function initMap(){

  const map = new google.maps.Map(document.getElementById("map"), {
    zoom: 7,
    center: { lat: 23.6850, lng: 90.3563 }
  });

  <?php
  $result2 = mysqli_query($conn, "SELECT * FROM hospitals");
  while($row = mysqli_fetch_assoc($result2)){
    if(!empty($row['latitude']) && !empty($row['longitude'])){
  ?>
    new google.maps.Marker({
      position: { lat: <?= $row['latitude'] ?>, lng: <?= $row['longitude'] ?> },
      map: map,
      title: "<?= addslashes($row['hospitalName']) ?>"
    });
  <?php }} ?>

}





</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCAmHRq2FeG3OX0dpgmqQrSQmZDWJN2YIo&callback=initMap" async defer></script>
<!-- FOOTER -->
 <footer>
      
            <div class="copyright">
                <p>Copyright &copy; 2025 QuicAid. All rights reserved.</p>
            </div>
       
    </footer>

</body>
</html>