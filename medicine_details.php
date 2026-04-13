<?php
include "backend/db.php";
session_start();

/* SEARCH */
$search = isset($_GET['search']) ? $_GET['search'] : "";

/* QUERY */
$sql = "SELECT * FROM pharmacy_medicines WHERE 1=1";

if(!empty($search)){
    $search = mysqli_real_escape_string($conn, $search);
    $sql .= " AND (name LIKE '%$search%' OR details LIKE '%$search%' OR price LIKE '%$search%')";
}

$result = mysqli_query($conn, $sql);

$medicines = [];
while($row = mysqli_fetch_assoc($result)){
    $medicines[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Medicine Details</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>

<style>
* {
  box-sizing: border-box;
  margin:0;
  padding:0;
}

body {
  font-family: Arial;
  background:#eaf0f3;
  display:flex;
  flex-direction:column;
  min-height:100vh;
}

/* HEADER */
.header {
  background:#49465b;
  color:#fff;
  padding:12px 20px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  position:sticky;
  top:0;
  z-index: 999;
}

.header a {
  color:#fff;
  text-decoration:none;
  font-weight:bold;
  background:#6c6a81;
  padding:6px 12px;
  border-radius:5px;
}

/* MAIN */
main {
  flex:1;
  max-width:1100px;
  margin:20px auto;
  padding:0 15px;
  width:100%;
}

/* SEARCH FULL WIDTH */
.search-box {
  width:100%;
  margin-bottom:15px;
}

.search-box form {
  width:100%;
}

.search-box input {
  width:100%;
  padding:12px;
  border-radius:8px;
  border:1px solid #ccc;
  font-size:14px;
}

/* COUNT */
.count-pill {
  background:#dcdde1;
  padding:6px 12px;
  border-radius:999px;
  font-size:12px;
  display:inline-block;
  margin-bottom:15px;
}

/* GRID */
.grid {
  display:grid;
  grid-template-columns:repeat(auto-fill, minmax(240px,1fr));
  gap:18px;
}

/* CARD */
.card {
  background:#fff;
  border-radius:16px;
  padding:16px;
  cursor:pointer;

  box-shadow:0 10px 25px rgba(0,0,0,0.08);
  border:1px solid rgba(0,0,0,0.05);

  transition:0.35s;
  transform: translateY(20px);
  opacity:0;
  animation:fadeUp 0.5s ease forwards;
}

.card:hover {
  transform:translateY(-10px) scale(1.02);
  box-shadow:0 20px 45px rgba(73,70,91,0.2);
}

/* TOP */
.card-top {
  display:flex;
  justify-content:space-between;
  align-items:center;
}

.card-title {
  font-size:16px;
  font-weight:700;
  color:#2f2c3d;
}

.price-chip {
  background:linear-gradient(135deg,#49465b,#6c6a81);
  color:#fff;
  padding:4px 10px;
  border-radius:999px;
  font-size:12px;
}

/* STATUS */
.status {
  margin-top:8px;
}

.in-stock {
  color:#16a34a;
  font-size:13px;
  animation:pulse 1.5s infinite;
}

.out-stock {
  color:#dc2626;
  font-size:13px;
}

/* INFO */
.info {
  margin-top:10px;
  font-size:13px;
  color:#555;
  display:flex;
  flex-direction:column;
  gap:4px;
}

.details {
  color:#777;
  font-size:12px;
}

/* BUTTON */
.view-btn {
  margin-top:12px;
  width:100%;
  padding:8px;
  border:none;
  border-radius:10px;

  background:linear-gradient(135deg,#49465b,#6c6a81);
  color:#fff;
  font-size:13px;

  transition:0.3s;
}

.view-btn:hover {
  transform:scale(1.05);
}

/* ANIMATION */
@keyframes fadeUp {
  to {
    opacity:1;
    transform:translateY(0);
  }
}

/* STAGGER */
.card:nth-child(1){animation-delay:0.05s}
.card:nth-child(2){animation-delay:0.1s}
.card:nth-child(3){animation-delay:0.15s}
.card:nth-child(4){animation-delay:0.2s}
.card:nth-child(5){animation-delay:0.25s}

/* PULSE */
@keyframes pulse {
  0% {opacity:1;}
  50% {opacity:0.5;}
  100% {opacity:1;}
}
/* MODAL */
/* BACKDROP */
.backdrop {
  position:fixed;
  inset:0;
  background:rgba(0,0,0,0.6);
  display:none;
  align-items:center;
  justify-content:center;

  backdrop-filter: blur(6px);
  animation: fadeIn 0.3s ease;
}

/* MODAL BOX */
.modal {
  background: rgba(255,255,255,0.85);
  backdrop-filter: blur(15px);

  padding:22px;
  border-radius:16px;
  width:90%;
  max-width:420px;

  box-shadow:0 20px 60px rgba(0,0,0,0.25);
  position:relative;

  animation: popUp 0.35s ease;
}

/* CLOSE ICON */
.close-x {
  position:absolute;
  top:10px;
  right:14px;
  font-size:20px;
  cursor:pointer;
  color:#555;
  transition:0.2s;
}

.close-x:hover {
  color:#000;
  transform: scale(1.2);
}

/* HEADER */
.modal-header {
  display:flex;
  justify-content:space-between;
  align-items:center;
}

.modal-header h2 {
  font-size:20px;
  color:#2f2c3d;
}

/* STATUS BADGE */
.status-badge {
  font-size:12px;
  padding:4px 10px;
  border-radius:20px;
  font-weight:600;
}

/* DETAILS */
.modal-details {
  margin-top:10px;
  font-size:14px;
  color:#555;
  line-height:1.5;
}

/* INFO GRID */
.modal-info {
  margin-top:15px;
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:10px;
}

.info-box {
  background:rgba(73,70,91,0.08);
  padding:10px;
  border-radius:10px;
  text-align:center;
}

.info-box span {
  font-size:12px;
  color:#666;
}

.info-box strong {
  display:block;
  margin-top:4px;
  font-size:15px;
  color:#2f2c3d;
}

/* BUTTON */
.btn-close {
  margin-top:16px;
  width:100%;
  padding:10px;
  border:none;
  border-radius:10px;

  background:linear-gradient(135deg,#49465b,#6c6a81);
  color:#fff;
  font-size:14px;

  cursor:pointer;
  transition:0.3s;
}

.btn-close:hover {
  transform:scale(1.05);
}

/* ANIMATIONS */
@keyframes fadeIn {
  from { opacity:0; }
  to { opacity:1; }
}

@keyframes popUp {
  from {
    transform: scale(0.8) translateY(20px);
    opacity:0;
  }
  to {
    transform: scale(1) translateY(0);
    opacity:1;
  }
}
/* FOOTER */
.footer {
  background:#49465b;
  color:#fff;
  text-align:center;
  padding:14px;
}
</style>
</head>

<body>

<!-- HEADER -->
<div class="header">
  <a href="javascript:history.back()">⬅ Back</a>
  <h3>Medicine Details</h3>
  <div></div>
</div>

<main>

  <!-- SEARCH -->
  <div class="search-box">
    <form method="GET">
      <input type="text" name="search" placeholder="Search medicine..." value="<?php echo $search; ?>">
    </form>
  </div>

  <div class="count-pill">
    <?php echo count($medicines); ?> medicines
  </div>

  <!-- GRID -->
  <div class="grid">
  <?php foreach($medicines as $m): ?>
    <div class="card" onclick='openModal(<?php echo json_encode($m); ?>)'>

      <!-- TOP -->
      <div class="card-top">
        <div class="card-title"><?php echo $m['name']; ?></div>
        <div class="price-chip">৳ <?php echo $m['price']; ?></div>
      </div>

      <!-- STATUS -->
      <div class="status">
        <span class="<?php echo strtolower($m['availability']) == 'available' ? 'in-stock' : 'out-stock'; ?>">
          ● <?php echo $m['availability']; ?>
        </span>
      </div>

      <!-- INFO -->
      <div class="info">
        <div>📦 Qty: <?php echo $m['quantity']; ?></div>
        <div class="details">
          <?php echo substr($m['details'],0,50); ?>...
        </div>
      </div>

      <!-- BUTTON -->
      <button class="view-btn">View Details</button>

    </div>
  <?php endforeach; ?>
</div>

</main>

<div id="modalBackdrop" class="backdrop">
  <div class="modal">

    <!-- CLOSE ICON -->
    <span class="close-x" onclick="closeModal()">×</span>

    <!-- TOP -->
    <div class="modal-header">
      <h2 id="modalName"></h2>
      <div id="modalStatus" class="status-badge"></div>
    </div>

    <!-- DETAILS -->
    <p id="modalDetails" class="modal-details"></p>

    <!-- INFO GRID -->
    <div class="modal-info">
      <div class="info-box">
        <span>💰 Price</span>
        <strong id="modalPrice"></strong>
      </div>

      <div class="info-box">
        <span>📦 Quantity</span>
        <strong id="modalQty"></strong>
      </div>
    </div>

    <!-- BUTTON -->
    <button class="btn-close" onclick="closeModal()">Close</button>

  </div>
</div>

<!-- FOOTER -->
<div class="footer">
  <p>© <?php echo date("Y"); ?> Medicine System</p>
</div>

<script>
function openModal(m){
    document.getElementById("modalName").innerText = m.name;
    document.getElementById("modalDetails").innerText = m.details;
    document.getElementById("modalPrice").innerText = "Tk " + m.price;
    document.getElementById("modalQty").innerText = m.quantity;
    document.getElementById("modalStatus").innerText = m.availability;

    document.getElementById("modalBackdrop").style.display = "flex";
}

function closeModal(){
    document.getElementById("modalBackdrop").style.display = "none";
}
</script>

</body>
</html>