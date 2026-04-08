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
  grid-template-columns:repeat(auto-fill, minmax(230px,1fr));
  gap:14px;
}

/* CARD */
.card {
  background:#fff;
  border-radius:12px;
  padding:14px;
  box-shadow:0 2px 6px rgba(0,0,0,0.1);
  cursor:pointer;
  transition:0.2s;
}

.card:hover {
  transform:translateY(-4px);
}

.card-header {
  display:flex;
  justify-content:space-between;
  align-items:center;
}

.card-title {
  font-size:16px;
  font-weight:bold;
}

.price-chip {
  background:#49465b;
  color:#fff;
  padding:4px 10px;
  border-radius:999px;
  font-size:12px;
}

.company-text {
  font-size:13px;
  color:#555;
  margin-top:6px;
}

/* MODAL */
.backdrop {
  position:fixed;
  inset:0;
  background:rgba(0,0,0,0.5);
  display:none;
  align-items:center;
  justify-content:center;
}

.modal {
  background:#fff;
  padding:18px;
  border-radius:10px;
  width:90%;
  max-width:420px;
}

.btn-close {
  margin-top:12px;
  background:#49465b;
  color:#fff;
  border:none;
  padding:8px 12px;
  border-radius:6px;
  cursor:pointer;
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

        <div class="card-header">
          <div class="card-title"><?php echo $m['name']; ?></div>
          <div class="price-chip">Tk <?php echo $m['price']; ?></div>
        </div>

        <p class="company-text">
          <strong>Status:</strong> <?php echo $m['availability']; ?>
        </p>

        <p class="company-text">
          <strong>Quantity:</strong> <?php echo $m['quantity']; ?>
        </p>

      </div>
    <?php endforeach; ?>
  </div>

</main>

<!-- MODAL -->
<div id="modalBackdrop" class="backdrop">
  <div class="modal">
    <h2 id="modalName"></h2>
    <p id="modalDetails"></p>
    <p><strong>Price:</strong> <span id="modalPrice"></span></p>
    <p><strong>Quantity:</strong> <span id="modalQty"></span></p>
    <p><strong>Status:</strong> <span id="modalStatus"></span></p>

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