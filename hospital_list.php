<?php
session_start();
include "backend/db.php";

/* ================= FETCH HOSPITALS ================= */
$result = mysqli_query($conn, "SELECT * FROM hospitals ORDER BY id DESC");

if(!$result){
    die("Query Failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hospital List</title>

<link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet"/>
<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

<link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700&display=swap" rel="stylesheet">

<style>
  html, body {
  height: 100%;
}
main {
  flex: 1;
}
body { font-family: 'Merriweather', serif; background:#eaf0f3; margin:0; padding:0; 
  animation: pageFade 0.6s ease;}

header { 
  position: sticky;
  top: 0;
  z-index: 9999;
  background: rgba(75,75,113,0.85);
  color:white;
  padding:1rem 2rem;
  display:flex;
  align-items:center;
  justify-content:space-between;
}
@keyframes pageFade {
  from { opacity: 0; }
  to { opacity: 1; }
}
header h1 { font-size:1.5rem; margin:0; }

.btn-back { background-color:transparent; color:white; padding:0.5rem 1rem; border-radius:5px; cursor:pointer; }
.btn-back:hover { background-color:#49465b; }

main { max-width:900px; margin:2rem auto; }
.hospital-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 18px;
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
.hospital-card {
  background: #fff;
  border-radius: 16px;
  overflow: hidden;
  cursor: pointer;

  box-shadow: 0 8px 20px rgba(0,0,0,0.08);
  border: 1px solid rgba(0,0,0,0.05);
  transform: translateY(20px);
  opacity: 0;
  animation: cardEntry 0.6s ease forwards;
  transition: all 0.35s ease;
  
}

.hospital-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 18px 40px rgba(0,0,0,0.15);
}

/* IMAGE TOP */
.hospital-card img {
  width: 100%;
  height: 180px;
  object-fit: cover;
  
   transition: transform 0.5s ease, filter 0.3s;
}

.hospital-card:hover img {
 transform: scale(1.08);
  filter: brightness(1.05);
}

/* INFO BELOW */
.hospital-info {
  padding: 14px 16px;
  display: flex;
  flex-direction: column;
  gap: 6px;
   text-align: center;
  align-items: center;
}


/* TITLE */
.hospital-info h2 {
  font-size: 1.1rem;
  font-weight: 700;
  color: #2f2c3d;
  transition: 0.3s;
}

/* TEXT */
.hospital-info div {
  font-size: 0.88rem;
  color: #555;
  display: flex;
  gap: 6px;
  align-items: center;
}

/* BED HIGHLIGHT */


/* ANIMATION DELAY */
/* stagger animation */
.hospital-card:nth-child(1){animation-delay:0.05s}
.hospital-card:nth-child(2){animation-delay:0.1s}
.hospital-card:nth-child(3){animation-delay:0.15s}
.hospital-card:nth-child(4){animation-delay:0.2s}
.hospital-card:nth-child(5){animation-delay:0.25s}
.hospital-card:nth-child(6){animation-delay:0.3s}

@keyframes cardEntry {
  to {
    transform: translateY(0);
    opacity: 1;
  }
}


/* HOVER MAGIC EFFECT */
.hospital-card::after {
  content: "";
  position: absolute;
  inset: 0;
  border-radius: 16px;
  background: radial-gradient(circle at top left, rgba(73,70,91,0.15), transparent);
  opacity: 0;
  transition: 0.4s;
}

.hospital-card:hover::after {
  opacity: 1;
}



.hospital-card:hover h2 {
  letter-spacing: 0.5px;
}

/* BED BADGE STYLE */
.beds {
  background: rgba(73,70,91,0.08);
  padding: 4px 10px;
  border-radius: 8px;
}
@keyframes fadeUp {
  from {
    opacity: 0;
    transform: translateY(15px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
</head>

<body>

<header>
  <button class="btn-back" onclick="goBack()">← Back</button>
  <h1>Hospital List</h1>
  <div></div>
</header>

<main>
 <div class="hospital-grid">

    <?php while($row = mysqli_fetch_assoc($result)): ?>

<?php 
$image = !empty($row['profilePic']) 
    ? $row['profilePic'] 
    : 'default.png';
?>

<div class="hospital-card" onclick="goToDetails(<?= $row['id'] ?>)">

  <!-- IMAGE TOP -->
  <img src="<?= $image ?>" onerror="this.src='default.png'">

  <!-- INFO CENTER -->
  <div class="hospital-info">

    <h2><?= htmlspecialchars($row['hospitalName']) ?></h2>

    <div>📍 <?= htmlspecialchars($row['location']) ?></div>

    <div>📞 <?= htmlspecialchars($row['hphone'] ?? 'Not set') ?></div>

    <div class="beds">
      🛏 Beds: <?= $row['availableBeds'] ?> / <?= $row['totalBeds'] ?>
    </div>

  </div>

</div>

<?php endwhile; ?>
  </div>
</main>

<footer style="text-align:center; padding:1rem; background:#49465b; color:white;">
  &copy; 2025 QuicAid. All rights reserved.
</footer>

<script>
function goBack(){
  window.history.back();
}

function goToDetails(id){
  // redirect with hospital user id (important for your existing details page)
  window.location.href = `hospital_details.php?id=${id}`;
}
</script>

</body>
</html>