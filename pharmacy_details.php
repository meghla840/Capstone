<?php
session_start();
include "backend/db.php";

/* ================= GET ID SAFE ================= */
$id = mysqli_real_escape_string($conn, $_GET['id']);

/* ================= PHARMACY ================= */
$pharmacyQuery = "
SELECT p.*, u.phone, u.email, u.name
FROM pharmacies p
LEFT JOIN users u ON p.userId = u.userId
WHERE p.userId='$id'
";
$pharmacy = mysqli_fetch_assoc(mysqli_query($conn,$pharmacyQuery));

/* ================= ID HANDLING ================= */
$pharmacyId = $pharmacy['id'];              // ✅ for reviews
$medicinePharmacyId = $pharmacy['userId'];  // ✅ for medicines (your DB uses this)

if(!$pharmacyId){
    die("Pharmacy not found!");
}

/* ================= MEDICINES ================= */
$result = mysqli_query($conn,"SELECT * FROM pharmacy_medicines WHERE pharmacyId='$medicinePharmacyId'");

/* ================= REVIEWS ================= */
$reviews = mysqli_query($conn,"SELECT * FROM pharmacy_reviews WHERE pharmacyId='$pharmacyId' ORDER BY id DESC");

function timeAgo($time){
    $diff = time() - strtotime($time);
    if($diff < 60) return "Just now";
    elseif($diff < 3600) return floor($diff/60)." min ago";
    elseif($diff < 86400) return floor($diff/3600)." hr ago";
    else return floor($diff/86400)." days ago";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Pharmacy Details</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
    #modal.flex #modalBox {
  transform: scale(1);
  opacity: 1;
}

#modalBox {
  transition: all 0.25s ease;
}
</style>
</head>

<body class="bg-gray-100">

<!-- HEADER -->
<div class="fixed top-0 left-0 w-full bg-[#49465b] text-white z-50 shadow">
  <div class="max-w-6xl mx-auto flex items-center justify-between p-4">
    <button onclick="history.back()" class="bg-white text-[#49465b] px-4 py-2 rounded-lg">← Back</button>
    <h1 class="font-bold text-lg">Pharmacy Details</h1>
    <div></div>
  </div>
</div>

<div class="h-20"></div>

<div class="max-w-6xl mx-auto p-6">

<!-- PROFILE -->
<div class="bg-gradient-to-r from-[#49465b] to-[#6b6785] text-white rounded-2xl p-6 mb-6 text-center">

    <div class="w-20 h-20 bg-white text-black rounded-full flex items-center justify-center mx-auto text-2xl font-bold">
        <?php echo strtoupper(substr($pharmacy['name'] ?? 'P',0,1)); ?>
    </div>

    <h2 class="text-2xl font-bold mt-3">
        <?php echo $pharmacy['pharmacyName'] ?? $pharmacy['name']; ?>
    </h2>

    <div class="bg-white text-black mt-4 p-4 rounded-xl max-w-md mx-auto text-left space-y-2">
        <p><strong>Email:</strong> <?php echo $pharmacy['email'] ?? 'N/A'; ?></p>
        <p><strong>Phone:</strong> <?php echo $pharmacy['phone'] ?? 'N/A'; ?></p>
        <p><strong>Address:</strong> <?php echo $pharmacy['pharmAddress'] ?? 'N/A'; ?></p>
    </div>

</div>

<!-- SEARCH -->
<div class="mb-6">
<input id="searchInput" onkeyup="filterMedicines()" 
class="w-full p-3 border rounded-lg" placeholder="Search medicine...">
</div>


<!-- MEDICINES GRID -->
<div class="grid md:grid-cols-3 gap-6">

<?php while($row = mysqli_fetch_assoc($result)): ?>

<div class="medicine-card group relative bg-white/90 backdrop-blur-md border border-gray-100 
rounded-2xl p-4 cursor-pointer shadow-sm hover:shadow-2xl transition-all duration-300
hover:-translate-y-1 hover:scale-[1.02] overflow-hidden"
data-name="<?php echo strtolower($row['name']); ?>"

onclick='openModal(
<?php echo json_encode($row["name"]); ?>,
<?php echo json_encode($row["details"]); ?>,
<?php echo json_encode($row["price"]); ?>,
<?php echo json_encode($row["availability"]); ?>,
<?php echo json_encode($row["stock"] ?? "N/A"); ?>
)'>

<!-- TOP SECTION -->
<div class="flex items-start justify-between gap-2">

  <div>
    <h3 class="font-bold text-[#49465b] text-lg group-hover:text-[#6b6785] transition">
      <?php echo $row['name']; ?>
    </h3>

    <p class="text-xs text-gray-400 mt-1 leading-snug">
      <?php echo substr($row['details'],0,60); ?>...
    </p>
  </div>

  <!-- STOCK WARNING (BLINK IF < 5) -->
  <?php $lowStock = ($row['stock'] ?? 0) < 5; ?>

  <span class="
    text-[10px] px-2 py-1 rounded-full font-medium
    <?php echo $lowStock ? 'bg-red-100 text-red-600 animate-pulse' : 'bg-blue-100 text-blue-600'; ?>
  ">
    Stock: <?php echo $row['stock'] ?? 'N/A'; ?>
  </span>

</div>

<!-- CATEGORY TAG -->
<div class="mt-3">
  <?php
    $name = strtolower($row['name']);

    if(strpos($name,'para') !== false || strpos($name,'pain') !== false){
        $cat = "Painkiller";
        $catColor = "bg-red-100 text-red-600";
    }
    elseif(strpos($name,'vit') !== false || strpos($name,'vitamin') !== false){
        $cat = "Vitamin";
        $catColor = "bg-yellow-100 text-yellow-600";
    }
    else{
        $cat = "Antibiotic";
        $catColor = "bg-green-100 text-green-600";
    }
  ?>

  <span class="text-[11px] px-3 py-1 rounded-full font-semibold <?php echo $catColor; ?>">
    <?php echo $cat; ?>
  </span>
</div>

<!-- PRICE + STATUS -->
<div class="mt-4 flex items-center justify-between">

  <div class="text-lg font-bold text-green-600">
    ৳ <?php echo $row['price']; ?>
  </div>

  <span class="
    px-3 py-1 text-[11px] rounded-full font-semibold
    <?php echo ($row['availability'] == 'Available') 
      ? 'bg-green-100 text-green-600' 
      : 'bg-red-100 text-red-500'; ?>
  ">
    <?php echo $row['availability']; ?>
  </span>

</div>

<!-- GLOW EFFECT -->
<div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition duration-300
bg-gradient-to-r from-[#49465b]/5 to-transparent pointer-events-none"></div>

</div>

<?php endwhile; ?>

</div>
<!-- MODERN MEDICINE MODAL -->
<div id="modal"
  class="fixed inset-0 hidden items-center justify-center z-50 bg-black/40 backdrop-blur-sm">

  <div class="w-[92%] md:w-[420px] rounded-2xl bg-white/90 backdrop-blur-xl shadow-2xl border border-white/40 overflow-hidden transform scale-95 opacity-0 transition-all duration-300"
       id="modalBox">

    <!-- TOP HEADER -->
    <div class="bg-gradient-to-r from-[#49465b] to-[#6b6785] p-4 text-white relative">

      <h2 id="mName" class="text-lg font-bold"></h2>

      <button onclick="closeModal()"
        class="absolute top-3 right-3 bg-white/20 hover:bg-white/30 rounded-full w-8 h-8 flex items-center justify-center">
        ✕
      </button>
    </div>

    <!-- BODY -->
    <div class="p-5 space-y-3">

      <p id="mDetails" class="text-gray-600 text-sm leading-relaxed"></p>

      <div class="flex items-center justify-between mt-3">

        <div class="text-green-600 font-bold text-lg">
          <span id="mPrice"></span>
        </div>

        <div id="mStock"
          class="text-xs px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-medium">
        </div>

      </div>

      <div id="mStatus"
        class="mt-2 text-xs font-semibold px-3 py-1 inline-block rounded-full">
      </div>

    </div>

    <!-- FOOTER BUTTON -->
    <div class="p-4 border-t bg-white/60 flex justify-end">
      <button onclick="closeModal()"
        class="px-4 py-2 rounded-lg bg-[#49465b] text-white hover:scale-105 transition">
        Close
      </button>
    </div>

  </div>
</div>
<!-- REVIEWS -->
<div class="mt-10 bg-white p-6 rounded-xl shadow">

<h2 class="text-xl font-bold mb-4 text-[#49465b]">Reviews</h2>

<!-- FORM -->
<input id="userName" class="w-full p-2 border rounded mb-3"
placeholder="Your Name" value="<?php echo $_SESSION['name'] ?? ''; ?>">

<div id="stars" class="mb-3 text-2xl cursor-pointer"></div>

<textarea id="comment" class="w-full p-3 border rounded mb-3"
placeholder="Write review..."></textarea>

<button onclick="submitReview()" class="bg-[#49465b] text-white px-5 py-2 rounded">
Submit Review
</button>

<!-- ALL REVIEWS -->
<h3 class="mt-6 font-bold text-[#49465b]">All Reviews</h3>

<div id="reviewsList" class="mt-4 space-y-2 max-h-[250px] overflow-y-auto pr-2">

<?php while($rev=mysqli_fetch_assoc($reviews)): ?>
<div class="bg-gray-50 p-2 rounded-lg border text-sm">
<b><?php echo $rev['userName']; ?></b>
<div class="text-yellow-400"><?php echo str_repeat("★",$rev['rating']); ?></div>
<p><?php echo $rev['comment']; ?></p>
<span class="text-xs text-gray-400"><?php echo timeAgo($rev['created_at']); ?></span>
</div>
<?php endwhile; ?>

</div>

</div>
</div>

<script>
const modal = document.getElementById("modal");
const mName = document.getElementById("mName");
const mDetails = document.getElementById("mDetails");
const mPrice = document.getElementById("mPrice");
const mStock = document.getElementById("mStock");
const mStatus = document.getElementById("mStatus");
/* SEARCH */
function filterMedicines(){
let val=document.getElementById("searchInput").value.toLowerCase();
document.querySelectorAll(".medicine-card").forEach(card=>{
card.style.display = card.dataset.name.includes(val) ? "block" : "none";
});
}

/* ================= MODAL ================= */
function openModal(name, details, price, status, stock) {
    mName.innerText = name;
    mDetails.innerText = details;
    mPrice.innerText = "৳ " + price;
    mStock.innerText = "Stock: " + stock;
    mStatus.innerText = "Status: " + status;

    // status color
    if(status === "Available"){
        mStatus.className = "mt-2 text-xs font-semibold px-3 py-1 inline-block rounded-full bg-green-100 text-green-600";
    } else {
        mStatus.className = "mt-2 text-xs font-semibold px-3 py-1 inline-block rounded-full bg-red-100 text-red-500";
    }

    modal.classList.remove("hidden");
    modal.classList.add("flex");

    setTimeout(() => {
        document.getElementById("modalBox").style.transform = "scale(1)";
        document.getElementById("modalBox").style.opacity = "1";
    }, 10);
}

function closeModal() {
    document.getElementById("modalBox").style.transform = "scale(0.95)";
    document.getElementById("modalBox").style.opacity = "0";

    setTimeout(() => {
        modal.classList.add("hidden");
        modal.classList.remove("flex");
    }, 200);
}

/* STAR */
let rating=0;
function renderStars(){
let html="";
for(let i=1;i<=5;i++){
html+=`<span onclick="setRating(${i})">${i<=rating?"★":"☆"}</span>`;
}
document.getElementById("stars").innerHTML=html;
}
renderStars();

function setRating(r){rating=r;renderStars();}

/* SUBMIT REVIEW */
function submitReview(){

let userName = document.getElementById("userName").value.trim();
let comment = document.getElementById("comment").value.trim();

if(!userName || !comment || rating===0){
alert("Fill all fields");
return;
}

let fd=new FormData();
fd.append("pharmacyId","<?php echo $pharmacyId; ?>"); // ✅ correct ID
fd.append("userName",userName);
fd.append("comment",comment);
fd.append("rating",rating);

fetch("add_review.php",{method:"POST",body:fd})
.then(res=>res.json())
.then(data=>{
if(data.status=="success"){
location.reload();
}else{
alert("Error saving review");
}
});
}

</script>

</body>
</html>