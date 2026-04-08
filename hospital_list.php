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
body { font-family: 'Merriweather', serif; background:#eaf0f3; margin:0; padding:0; }

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

header h1 { font-size:1.5rem; margin:0; }

.btn-back { background-color:transparent; color:white; padding:0.5rem 1rem; border-radius:5px; cursor:pointer; }
.btn-back:hover { background-color:#49465b; }

main { max-width:900px; margin:2rem auto; }

.hospital-card {
  background:white;
  padding:1rem;
  border-radius:10px;
  display:flex;
  gap:1rem;
  align-items:center;
  cursor:pointer;
  box-shadow:0 2px 6px rgba(0,0,0,0.15);
  transition: transform 0.2s;
}

.hospital-card:hover { transform: scale(1.03); }

.hospital-card img { width:80px; height:80px; }

.hospital-info { flex:1; }

.hospital-info h2 { font-weight:600; margin-bottom:0.5rem; }

.hospital-info div { display:flex; align-items:center; gap:0.5rem; margin-bottom:0.3rem; }
</style>
</head>

<body>

<header>
  <button class="btn-back" onclick="goBack()">← Back</button>
  <h1>Hospital List</h1>
  <div></div>
</header>

<main>
  <div class="flex flex-col gap-4">

    <?php while($row = mysqli_fetch_assoc($result)): ?>

      <div class="hospital-card"
           onclick="goToDetails(<?= $row['id'] ?>)">

        <img src="images/icons8-hospital-100.png" alt="Hospital">

        <div class="hospital-info">
          <h2><?= htmlspecialchars($row['hospitalName']) ?></h2>

          <div>📍 <?= htmlspecialchars($row['location']) ?></div>

          <div>📞 <?= htmlspecialchars($row['phone'] ?? 'Not set') ?></div>

          <div>🛏 Beds: <?= $row['availableBeds'] ?> / <?= $row['totalBeds'] ?></div>
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