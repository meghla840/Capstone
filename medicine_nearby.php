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
</style>
</head>

<body class="bg-gray-100">

<!-- ================= HEADER ================= -->
<div class="bg-[#49465b] text-white px-10 py-4 flex items-center gap-4">

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

<!-- ================= MAP ================= -->
<div class="px-10 mt-5" id="mapSection">
  <div id="map"></div>
</div>

<!-- ================= PHARMACY LIST ================= -->
<div class="px-10 py-10 grid grid-cols-1 gap-6" id="pharmacyList">

<?php foreach($pharmacies as $p): ?>

<div class="bg-white p-5 rounded-xl shadow-md card-hover hospital-item"
     data-name="<?php echo strtolower($p['name']); ?>"
     data-medicine="<?php echo strtolower(implode(',', $p['medicines'])); ?>">

  <div class="flex justify-between items-center">

    <!-- LEFT -->
    <div>

      <h2 class="text-xl font-bold text-[#49465b]">
        <?php echo $p['name']; ?>
      </h2>

      <!-- STATUS -->
      <div class="mt-1">
        <span class="text-xs px-2 py-1 rounded 
          <?php echo $p['status'] === 'Open' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'; ?>">
          <?php echo $p['status']; ?>
        </span>
      </div>

      <p class="text-sm text-gray-600 mt-1">
        📍 <?php echo $p['address']; ?>
      </p>

      <p class="text-xs text-blue-600 mt-2">
        <?php echo implode(", ", array_slice($p['medicines'],0,3)); ?>
      </p>

    </div>

    <!-- BUTTON -->
    <button onclick="openPharmacyDetails('<?php echo $p['id']; ?>')"
      class="bg-[#49465b] text-white px-4 py-2 rounded-lg hover:bg-[#6c6a81]">
      View
    </button>

  </div>

</div>

<?php endforeach; ?>

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

<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap" async defer></script>

</body>
</html>