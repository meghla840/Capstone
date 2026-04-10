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

<!-- MEDICINES -->
<!-- MEDICINES GRID -->
<div class="grid md:grid-cols-3 gap-6">

<?php while($row = mysqli_fetch_assoc($result)): ?>

<div class="medicine-card bg-white p-4 rounded-xl shadow cursor-pointer"
data-name="<?php echo strtolower($row['name']); ?>"

onclick='openModal(
<?php echo json_encode($row["name"]); ?>,
<?php echo json_encode($row["details"]); ?>,
<?php echo json_encode($row["price"]); ?>,
<?php echo json_encode($row["availability"]); ?>,
<?php echo json_encode($row["stock"] ?? "N/A"); ?>
)'>

<h3 class="font-bold text-[#49465b]"><?php echo $row['name']; ?></h3>

<p class="text-sm text-gray-500"><?php echo $row['details']; ?></p>

<div class="flex justify-between mt-2">
<span class="text-green-600 font-semibold">৳ <?php echo $row['price']; ?></span>

<span class="text-xs bg-blue-100 text-blue-600 px-2 py-1 rounded">
Stock: <?php echo $row['stock'] ?? 'N/A'; ?>
</span>

</div>

</div>

<?php endwhile; ?>

</div>
<!-- MEDICINE MODAL -->
<div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">

  <div class="bg-white w-[90%] md:w-[400px] rounded-xl p-5 relative">

    <!-- CLOSE -->
    <button onclick="closeModal()" class="absolute top-2 right-3 text-xl font-bold">✖</button>

    <h2 id="mName" class="text-xl font-bold text-[#49465b]"></h2>

    <p id="mDetails" class="text-gray-600 mt-2"></p>

    <p id="mPrice" class="mt-3 font-semibold text-green-600"></p>

    <p id="mStock" class="mt-1 text-sm text-blue-600"></p>

    <p id="mStatus" class="mt-1 text-sm text-gray-500"></p>

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
function openModal(name,details,price,status,stock){
    mName.innerText = name;
    mDetails.innerText = details;
    mPrice.innerText = "৳ " + price;
    mStock.innerText = "Stock: " + stock;
    mStatus.innerText = "Status: " + status;

    modal.classList.remove("hidden");
    modal.classList.add("flex");
    setTimeout(()=> modal.classList.add("modal-show"),10);
}

function closeModal(){
    modal.classList.remove("modal-show");
    setTimeout(()=>{
        modal.classList.add("hidden");
        modal.classList.remove("flex");
    },200);
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