<?php
include "backend/db.php";

date_default_timezone_set("Asia/Dhaka");
$currentTime = date("H:i:s");

/* ================= GET PHARMACY + MEDICINES ================= */
$query = "
SELECT p.*, m.name AS medicineName
FROM pharmacies p
LEFT JOIN pharmacy_medicines m ON p.userId = m.pharmacyId
";
$result = mysqli_query($conn, $query);

/* ================= GROUP DATA ================= */
$pharmacies = [];

while($row = mysqli_fetch_assoc($result)){
    $id = $row['userId'];

    if(!isset($pharmacies[$id])){

        $openTime = $row['opening_time'];
        $closeTime = $row['closing_time'];

        // Default Closed
        $status = "Closed";

        if($openTime && $closeTime){
            if($currentTime >= $openTime && $currentTime <= $closeTime){
                $status = "Open";
            }
        }

        $pharmacies[$id] = [
            "id" => $id,
            "name" => $row['pharmacyName'],
            "address" => $row['pharmAddress'],
            "lat" => $row['latitude'] ?? (23.81 + rand(0,10)/1000),
            "lng" => $row['longitude'] ?? (90.41 + rand(0,10)/1000),
            "medicines" => [],
            "status" => $status
        ];
    }

    if($row['medicineName']){
        $pharmacies[$id]["medicines"][] = $row['medicineName'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Find Medicine Nearby</title>

<link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet"/>
<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

<style>
#map {
  height: 350px;
  border-radius: 12px;
}

.card-hover:hover{
  transform: translateY(-6px) scale(1.01);
  transition: 0.3s;
}

.search-box{
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

/* ================= GRID ================= */
.hospital-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 22px;
}

@media (max-width: 1024px) {
  .hospital-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
  .hospital-grid { grid-template-columns: repeat(1, 1fr); }
}

/* ================= CARD ================= */
.hospital-card {
  position: relative;
  background: #fff;
  border-radius: 18px;
  overflow: hidden;
  cursor: pointer;
  border-top: 5px solid #49465b;
  box-shadow: 0 10px 25px rgba(0,0,0,0.08);
  

  transform: translateY(30px) scale(0.98);
  opacity: 0;

  animation: cardEntry 0.7s ease forwards;
  transition: all 0.4s ease;
}

/* 🔥 SMOOTH HOVER */
.hospital-card:hover {
  transform: translateY(-12px) scale(1.03);
  box-shadow: 0 25px 55px rgba(73,70,91,0.25);
  border-left: 2px solid #49465b;
}

/* ================= IMAGE ================= */
.hospital-card img {
  width: 100%;
  height: 180px;
  object-fit: cover;
  transition: all 0.6s ease;
  filter: brightness(0.95);
}

.hospital-card:hover img {
  transform: scale(1.1);
  filter: brightness(1.05);
}

/* ================= INFO ================= */
.hospital-info {
  padding: 18px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  text-align: center;
  align-items: center;
}

/* TITLE */
.hospital-info h2 {
  font-size: 1.15rem;
  font-weight: 700;
  color: #2f2c3d;
  transition: 0.3s;
}

.hospital-card:hover h2 {
  letter-spacing: 0.4px;
}

/* TEXT */
.hospital-info div {
  font-size: 0.85rem;
  color: #666;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

/* ================= BUTTON ================= */
.hospital-info button {
  margin-top: 10px;
  background: linear-gradient(135deg, #49465b, #6c6a81);
  color: white;
  padding: 8px 18px;
  border-radius: 10px;
  font-size: 0.85rem;

  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

/* shimmer effect */
.hospital-info button::before {
  content: "";
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(120deg, transparent, rgba(255,255,255,0.3), transparent);
  transition: 0.5s;
}

.hospital-info button:hover::before {
  left: 100%;
}

.hospital-info button:hover {
  transform: scale(1.05);
}

/* ================= STATUS BADGE ================= */
.hospital-info span {
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
}

/* Open */
.text-green-600 {
  background: rgba(34,197,94,0.1);
  animation: pulse 1.5s infinite;
}

/* Closed */
.text-red-500 {
  background: rgba(239,68,68,0.1);
}

/* ================= ANIMATION ================= */
@keyframes cardEntry {
  0% {
    opacity: 0;
    transform: translateY(30px) scale(0.95);
  }
  100% {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

/* stagger */
.hospital-card:nth-child(1){animation-delay:0.05s}
.hospital-card:nth-child(2){animation-delay:0.1s}
.hospital-card:nth-child(3){animation-delay:0.15s}
.hospital-card:nth-child(4){animation-delay:0.2s}
.hospital-card:nth-child(5){animation-delay:0.25s}
.hospital-card:nth-child(6){animation-delay:0.3s}

/* ================= GLOW BORDER ================= */
.hospital-card::after {
  content: "";
  position: absolute;
  inset: 0;
  border-radius: 18px;
  background: radial-gradient(circle at top left, rgba(73,70,91,0.15), transparent);
  opacity: 0;
  transition: 0.4s;
}

.hospital-card:hover::after {
  opacity: 1;
}

/* ================= PAGE FADE ================= */
body {
  animation: fadePage 0.5s ease;
}

@keyframes fadePage {
  from {
    opacity: 0;
    transform: scale(0.98);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

/* ================= STATUS PULSE ================= */
@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.5; }
  100% { opacity: 1; }
}

/* ================= HEADER ================= */
.header-sticky {
  position: sticky;
  top: 0;
  z-index: 999;
  backdrop-filter: blur(10px);
  background: rgba(73,70,91,0.9);
}
</style>
</head>

<body class="bg-gray-100">

<!-- ================= HEADER ================= -->
<div class="bg-[#49465b] text-white px-10 py-4 flex items-center gap-4 header-sticky">

  <button onclick="history.back()"
    class="flex items-center gap-2 bg-white text-[#49465b] px-4 py-2 rounded-lg shadow hover:bg-gray-100 transition">

    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
      fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round"
        stroke-width="2" d="M15 19l-7-7 7-7" />
    </svg>

    Back
  </button>

  <h1 class="text-xl font-bold flex-1 text-center">
    Find Medicine Nearby
  </h1>

</div>

<!-- ================= SEARCH ================= -->
<div class="bg-[#49465b] px-10 py-4">

  <div class="search-box bg-white p-3 rounded-xl flex gap-2 w-full">

    <input id="searchInput"
      type="text"
      placeholder="Search pharmacy or medicine..."
      class="w-full p-2 border rounded"/>

    <button onclick="searchNow()"
      class="bg-[#49465b] text-white px-6 rounded">
      Search
    </button>

  </div>

</div>



<!-- ================= PHARMACY LIST ================= -->
<div class="px-10 py-10 hospital-grid" id="pharmacyList">

<?php foreach($pharmacies as $p): ?>

<div class="hospital-card hospital-item"
     data-name="<?php echo strtolower($p['name']); ?>"
     data-medicine="<?php echo strtolower(implode(',', $p['medicines'])); ?>">

  <!-- IMAGE -->
  <img src="https://images.unsplash.com/photo-1587854692152-cbe660dbde88" alt="Pharmacy">

  <div class="hospital-info">

    <!-- NAME -->
    <h2><?php echo $p['name']; ?></h2>

    <!-- STATUS -->
    <div>
      <span class="<?php echo $p['status'] === 'Open' ? 'text-green-600' : 'text-red-500'; ?>">
        ● <?php echo $p['status']; ?>
      </span>
    </div>

    <!-- ADDRESS -->
    <div>Address:  <?php echo $p['address']; ?></div>

    <!-- MEDICINES -->
    <div style="text-align:center;">
     Medicines: <?php echo implode(", ", array_slice($p['medicines'],0,3)); ?>
    </div>

    <!-- BUTTON -->
    <button onclick="openPharmacyDetails('<?php echo $p['id']; ?>')"
      style="margin-top:8px;background:#49465b;color:white;padding:8px 16px;border-radius:8px;">
      View Details
    </button>

  </div>

</div>

<?php endforeach; ?>

</div>

<!-- ================= MAP ================= -->
<div class="px-10 mt-5" id="mapSection">
  <div id="map"></div>
</div>
<!-- ================= JS ================= -->
<script>

const pharmacies = <?php echo json_encode(array_values($pharmacies)); ?>;

/* SEARCH */
function searchNow(){
  const input = document.getElementById("searchInput").value.toLowerCase();
  const items = document.querySelectorAll(".hospital-item");

  items.forEach(item => {
    const name = item.getAttribute("data-name");
    const meds = item.getAttribute("data-medicine");

    if(name.includes(input) || meds.includes(input)){
      item.style.display = "block";
    } else {
      item.style.display = "none";
    }
  });

  if(input.length > 0){
    document.getElementById("mapSection").style.display = "none";
  } else {
    document.getElementById("mapSection").style.display = "block";
  }
}

/* ENTER KEY */
document.getElementById("searchInput")
.addEventListener("keyup", function(e){
  if(e.key === "Enter"){
    searchNow();
  }
});

/* DETAILS */
function openPharmacyDetails(id){
  window.location.href = "pharmacy_details.php?id=" + id;
}

/* MAP */
function initMap(){

  const map = new google.maps.Map(document.getElementById("map"), {
    zoom: 12,
    center: { lat: 23.8103, lng: 90.4125 }
  });

  if(navigator.geolocation){
    navigator.geolocation.getCurrentPosition(pos => {

      const user = {
        lat: pos.coords.latitude,
        lng: pos.coords.longitude
      };

      map.setCenter(user);

      new google.maps.Marker({
        position: user,
        map: map,
        title: "You are here"
      });

    });
  }

  pharmacies.forEach(p => {

    const marker = new google.maps.Marker({
      position: { lat: parseFloat(p.lat), lng: parseFloat(p.lng) },
      map: map,
      title: p.name
    });

    const info = new google.maps.InfoWindow({
      content: `
        <div>
          <strong>${p.name}</strong><br>
          <small>${p.address}</small><br>
          <button onclick="openPharmacyDetails('${p.id}')"
            style="margin-top:5px;background:#49465b;color:white;padding:5px;border:none;border-radius:5px;">
            View Details
          </button>
        </div>
      `
    });

    marker.addListener("click", () => {
      info.open(map, marker);
    });

  });
}

</script>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCAmHRq2FeG3OX0dpgmqQrSQmZDWJN2YIo&callback=initMap" async defer></script>

</body>
</html>