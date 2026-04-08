<?php
session_start();
include "backend/db.php";

if(!isset($_SESSION['user_id'])){
    header("Location: join.php");
    exit();
}

$id = $_SESSION['user_id'];

/* ------------------- Fetch Current User ------------------- */
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$currentUser = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$currentUser){
    session_destroy();
    header("Location: join.php");
    exit();
}

/* ------------------- Dashboard Redirect ------------------- */
$dashboardPage = "dashboard.php";

switch($currentUser['role']){
    case 'patient': $dashboardPage = "patient_dashboard.php"; break;
    case 'doctor': $dashboardPage = "doctor_dashboard.php"; break;
    case 'hospital': $dashboardPage = "hospital_dashboard.php"; break;
    case 'pharmacy': $dashboardPage = "pharmacy_dashboard.php"; break;
    case 'transport': $dashboardPage = "transport_dashboard.php"; break;
    case 'admin': $dashboardPage = "admin_dashboard.php"; break;
}

/* ------------------- Role Data ------------------- */
$roleData = [];

switch($currentUser['role']){
    case 'patient':
        $stmt = $conn->prepare("SELECT * FROM patients WHERE userId = ?");
        break;
    case 'doctor':
        $stmt = $conn->prepare("SELECT * FROM doctors WHERE userId = ?");
        break;
    case 'hospital':
        $stmt = $conn->prepare("SELECT * FROM hospitals WHERE userId = ?");
        break;
    case 'pharmacy':
        $stmt = $conn->prepare("SELECT * FROM pharmacies WHERE userId = ?");
        break;
    case 'transport':
        $stmt = $conn->prepare("SELECT * FROM transports WHERE userId = ?");
        break;
    case 'admin':
        $stmt = $conn->prepare("SELECT * FROM admins WHERE userId = ?");
        break;
}

if(isset($stmt) && $stmt){
    $stmt->bind_param("s", $currentUser['id']); // ✅ IMPORTANT FIX
    $stmt->execute();
    $roleData = $stmt->get_result()->fetch_assoc() ?? [];
    $stmt->close();
}
/* ------------------- Save Profile ------------------- */
if(isset($_POST['save'])){
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $profilePic = $currentUser['profilePic'];

    /* Upload image */
    if(isset($_FILES['pic']) && $_FILES['pic']['name'] != ''){
        $ext = pathinfo($_FILES['pic']['name'], PATHINFO_EXTENSION);
        $newPic = "uploads/".time()."_".rand(100,999).".".$ext;

        if(!is_dir('uploads')) mkdir('uploads', 0755, true);

        if(move_uploaded_file($_FILES['pic']['tmp_name'], $newPic)){
            if($profilePic && file_exists($profilePic)) unlink($profilePic);
            $profilePic = $newPic;
        }
    }

   /* Update users table */
$address = $_POST['address'] ?? '';

$stmtUser = $conn->prepare("
    UPDATE users 
    SET name=?, phone=?, profilePic=?, address=? 
    WHERE id=?
");

$stmtUser->bind_param("ssssi", $name, $phone, $profilePic, $address, $id);
$stmtUser->execute();
$stmtUser->close();


    /* Role specific update */
   $userId = $currentUser['userId'];
$stmtRole = null;

switch($currentUser['role']){

    case 'patient':
        $bloodGroup = $_POST['bloodGroup'] ?? '';
        $healthIssues = $_POST['healthIssues'] ?? '';
        $allergies = $_POST['allergies'] ?? '';

        if($roleData){
            $stmtRole = $conn->prepare("UPDATE patients SET bloodGroup=?, healthIssues=?, address=?, allergies=? WHERE userId=?");
            $stmtRole->bind_param("sssss", $bloodGroup, $healthIssues, $address, $allergies, $userId);
        } else {
            $stmtRole = $conn->prepare("INSERT INTO patients (userId,bloodGroup,healthIssues,address,allergies) VALUES (?,?,?,?,?)");
            $stmtRole->bind_param("sssss", $userId, $bloodGroup, $healthIssues, $address, $allergies);
        }
        break;

    case 'doctor':
        $specialization = $_POST['specialization'] ?? '';
        $experience = $_POST['experience'] ?? '';
        $clinic = $_POST['clinic'] ?? '';

        if($roleData){
            $stmtRole = $conn->prepare("UPDATE doctors SET specialization=?, experience=?, clinic=? WHERE userId=?");
            $stmtRole->bind_param("ssss", $specialization, $experience, $clinic, $userId);
        } else {
            $stmtRole = $conn->prepare("INSERT INTO doctors (userId,specialization,experience,clinic) VALUES (?,?,?,?)");
            $stmtRole->bind_param("ssss", $userId, $specialization, $experience, $clinic);
        }
        break;

    case 'pharmacy':
        $pharmacyName = $_POST['pharmacyName'] ?? '';
        $drugLicense = $_POST['drugLicense'] ?? '';
        $pharmAddress = $_POST['pharmAddress'] ?? '';

        if($roleData){
            $stmtRole = $conn->prepare("UPDATE pharmacies SET pharmacyName=?, drugLicense=?, pharmAddress=? WHERE userId=?");
            $stmtRole->bind_param("ssss", $pharmacyName, $drugLicense, $pharmAddress, $userId);
        } else {
            $stmtRole = $conn->prepare("INSERT INTO pharmacies (userId,pharmacyName,drugLicense,pharmAddress) VALUES (?,?,?,?)");
            $stmtRole->bind_param("ssss", $userId, $pharmacyName, $drugLicense, $pharmAddress);
        }
        break;

    case 'admin':
        $department = $_POST['department'] ?? '';
        $permissions = $_POST['permissions'] ?? '';

        if($roleData){
            $stmtRole = $conn->prepare("UPDATE admins SET department=?, permissions=? WHERE userId=?");
            $stmtRole->bind_param("sss", $department, $permissions, $userId);
        } else {
            $stmtRole = $conn->prepare("INSERT INTO admins (userId, department, permissions) VALUES (?,?,?)");
            $stmtRole->bind_param("sss", $userId, $department, $permissions);
        }
        break;
}

    if($stmtRole){
    $stmtRole->execute();
    $stmtRole->close();
}

    echo "<script>alert('Profile updated successfully'); window.location='profile.php';</script>";
    exit();
}

/* ------------------- Delete Account ------------------- */
if(isset($_POST['delete'])){
    if($currentUser['profilePic'] && file_exists($currentUser['profilePic'])){
        unlink($currentUser['profilePic']);
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    session_destroy();

    echo "<script>alert('Account deleted'); window.location='join.php';</script>";
    exit();
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>QuickAid — Profile</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
/* CSS remains largely same; no major changes needed */
:root{
  --brand:#49465b; --muted:#6b7280; --bg:#f3f6f9; --card:#ffffff;
  --accent:#2563eb; --header-gradient: linear-gradient(90deg, #49465b, #6e6c80);
}
*{box-sizing:border-box;}
body{font-family:Inter,system-ui,Segoe UI,Roboto,Arial; background:var(--bg); margin:0; color:#0f172a;}
header{background:var(--header-gradient); color:white; padding:18px 20px; position:sticky; top:0; z-index:40; display:flex; align-items:center; justify-content:center; gap:12px;}
header .back{font-size:20px; cursor:pointer; position:absolute; left:20px;}
header h1{font-size:18px; margin:0; font-weight:600;}
.container{max-width:1100px; margin:26px auto; padding:0 18px;}
.grid{display:grid; grid-template-columns:320px 1fr; gap:22px;}
.card{background:var(--card); border-radius:12px; padding:18px; box-shadow:0 6px 18px rgba(2,6,23,0.06);}
.avatar{display:flex; align-items:center; gap:14px;}
.avatar img{width:86px; height:86px; border-radius:50%; object-fit:cover; border:3px solid #fff; box-shadow:0 6px 18px rgba(9,10,11,0.06);}
.avatar h2{margin:0; font-size:18px;}
.role-badge{display:inline-block; background:#eef2ff; color:#3730a3; padding:6px 10px; border-radius:999px; font-weight:600; font-size:12px;}
.meta{margin-top:12px; color:var(--muted); font-size:14px;}
.left-actions{display:flex; gap:10px; margin-top:16px; flex-wrap:wrap;}
.btn{background:var(--header-gradient); color:white; border:none; padding:10px 12px; border-radius:9px; cursor:pointer; font-weight:600; transition:0.3s;}
.btn:hover{opacity:0.85;}
.gradient-btn{background: var(--header-gradient); color: white;}
.gradient-btn:hover{opacity:0.85;}
.section{margin-bottom:18px;}
label{display:block; font-size:13px; margin-bottom:6px; color:var(--muted);}
input[type="text"], input[type="email"], input[type="tel"], textarea{width:100%; padding:10px 12px; border-radius:8px; border:1px solid #e6e9ef; font-size:14px;}
textarea{min-height:90px; resize:vertical;}
.grid-2{display:grid; grid-template-columns:1fr 1fr; gap:12px;}
@media(max-width:900px){.grid{grid-template-columns:1fr;} .avatar img{width:76px;height:76px;}}
.professional-summary h4{font-size:14px; color:#4b5563; margin-bottom:6px; border-bottom:1px solid #e5e7eb; padding-bottom:4px;}
.professional-summary .summary-sections{margin-top:14px;}
.professional-summary .kv{display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #f3f4f6; font-size:14px; color:#374151;}
.professional-summary .kv strong{color:#111827;}
.professional-summary .left-actions{margin-top:20px; gap:8px; display:flex; flex-wrap:wrap;}
.delete-card-inner{background:#fff; border:1px solid #e5e7eb; border-left:4px solid #b91c1c; border-radius:10px; padding:16px; box-shadow:0 4px 12px rgba(0,0,0,0.08);}
.delete-card-inner h4{margin:0 0 8px; color:#b91c1c; font-size:16px;}
.delete-card-inner p{margin:0 0 12px; font-size:14px; color:#374151;}
.flex-btns{display:flex; gap:10px;}
.flex-btns button{padding:8px 12px; border-radius:8px; cursor:pointer; border:none;}
.flex-btns #confirmDeleteInline{background:#b91c1c; color:white;}
.flex-btns #cancelDeleteInline{background:#e5e7eb; color:#1f2937;}
  </style>
</head>
<body>

<header>
  <div class="back" onclick="history.back()"><i class="bi bi-arrow-left"></i></div>
  <h1>Profile</h1>
</header>

<main class="container">
  <div class="grid">
    <!-- LEFT PANEL -->
    <aside class="card professional-summary">
      <div class="avatar">
        <img src="<?php echo ($currentUser['profilePic'] && file_exists($currentUser['profilePic'])) ? $currentUser['profilePic'] : 'https://via.placeholder.com/100'; ?>" alt="avatar">
        <div style="flex:1">
          <h2><?php echo htmlspecialchars($currentUser['name']); ?></h2>
          <div class="role-badge"><?php echo ucfirst($currentUser['role']); ?></div>
          <div class="meta"><?php echo htmlspecialchars($currentUser['email']); ?></div>
        </div>
      </div>

      <div class="summary-sections">
        <div class="section">
          <h4>Contact Information</h4>
          <div class="kv"><strong>Phone:</strong> <?php echo htmlspecialchars($currentUser['phone']); ?></div>
          <div class="kv"><strong>Email:</strong> <?php echo htmlspecialchars($currentUser['email']); ?></div>
          <div class="kv">
  <strong>Address:</strong>
  <span><?php echo htmlspecialchars($currentUser['address'] ?? '—'); ?></span>
</div>
        </div>

        <div class="section">
          <h4>Account Details</h4>
          <div class="kv"><strong>Status:</strong> Active</div>
          <div class="kv"><strong>Joined:</strong> <?php echo date('d M, Y', strtotime($currentUser['created_at'] ?? 'now')); ?></div>
          <div class="kv"><strong>Last Login:</strong> <?php echo date('d M, Y H:i', strtotime($currentUser['last_login'] ?? 'now')); ?></div>
        </div>
          
        <?php if($currentUser['role'] == 'patient'): ?>
        <div class="section">
          <h4>Health Details</h4>
          <div class="kv"><strong>Blood Group:</strong> <?php echo htmlspecialchars($roleData['bloodGroup'] ?? '—'); ?></div>
          <div class="kv"><strong>Health Issues:</strong> <?php echo htmlspecialchars($roleData['healthIssues'] ?? '—'); ?></div>
        </div>
        <?php endif; ?>
      </div>

      <div class="left-actions" style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:16px;">
    
    <?php if($currentUser['role'] != 'admin'): ?>
        <button class="btn" onclick="document.querySelector('form').scrollIntoView({behavior:'smooth'});">
            Edit Profile
        </button>
    <?php endif; ?>

    <button class="btn" onclick="window.location='<?php echo $dashboardPage; ?>'">
        Dashboard
    </button>

    <button class="btn gradient-btn" onclick="handleLogout()">Logout</button>

    <?php if($currentUser['role'] != 'admin'): ?>
        <button class="btn gradient-btn" onclick="document.getElementById('deleteCard').style.display='block';">
            Delete Account
        </button>
    <?php endif; ?>

</div>
      <div id="deleteCard" style="display:none; margin-top:12px;">
        <div class="delete-card-inner">
          <h4>Delete Account?</h4>
          <p>Are you sure? This cannot be undone.</p>
          <div class="flex-btns">
            <form method="POST" style="display:inline;"><button type="submit" name="delete">Yes, Delete</button></form>
            <button onclick="document.getElementById('deleteCard').style.display='none';">Cancel</button>
          </div>
        </div>
      </div>

    </aside>

    <!-- RIGHT PANEL -->
    <section>
      <form method="POST" enctype="multipart/form-data" class="card section">
        
        <h3>Profile Details</h3>
        <div class="grid-2">
          <div>
            <label>Full Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($currentUser['name']); ?>">
          </div>
          <div>
            <label>Role</label>
            <input type="text" value="<?php echo ucfirst($currentUser['role']); ?>" readonly>
          </div>
        </div>

        <div class="grid-2" style="margin-top:12px">
          <div>
            <label>Phone</label>
            <input type="tel" name="phone" value="<?php echo htmlspecialchars($currentUser['phone']); ?>">
          </div>
          <div>
            <label>Email</label>
            <input type="email" value="<?php echo htmlspecialchars($currentUser['email']); ?>" readonly>
          </div>
          <div style="margin-top:10px">
          <label>Address</label>
          <input type="text" name="address" 
            value="<?php echo htmlspecialchars($currentUser['address'] ?? ''); ?>">
        </div>
        </div>
          <!-- ✅ Admin fields -->
<?php if($currentUser['role']=='admin'): ?>
<div class="grid-2" style="margin-top:12px">
  <div>
    <label>Department</label>
    <input type="text" name="department" 
      value="<?php echo htmlspecialchars($roleData['department'] ?? ''); ?>">
  </div>
  <div>
    <label>Permissions</label>
    <input type="text" name="permissions" 
      value="<?php echo htmlspecialchars($roleData['permissions'] ?? ''); ?>">
  </div>
</div>
<?php endif; ?>
        <!-- Role-specific fields -->
        <?php if($currentUser['role']=='patient'): ?>
        <div class="grid-2" style="margin-top:12px">
          <div>
            <label>Blood Group</label>
            <input type="text" name="bloodGroup" value="<?php echo htmlspecialchars($roleData['bloodGroup'] ?? ''); ?>">
          </div>
          <div>
            <label>Known Allergies</label>
            <input type="text" name="allergies" value="<?php echo htmlspecialchars($roleData['allergies'] ?? ''); ?>">
          </div>
        </div>
        <div style="margin-top:10px">
          <label>Health Issues</label>
          <textarea name="healthIssues"><?php echo htmlspecialchars($roleData['healthIssues'] ?? ''); ?></textarea>
        </div>
        <?php elseif($currentUser['role']=='doctor'): ?>
        <div class="grid-2" style="margin-top:12px">
          <div>
            <label>Specialization</label>
            <input type="text" name="specialization" value="<?php echo htmlspecialchars($roleData['specialization'] ?? ''); ?>">
          </div>
          <div>
            <label>Years of Experience</label>
            <input type="text" name="experience" value="<?php echo htmlspecialchars($roleData['experience'] ?? ''); ?>">
          </div>
        </div>
        <div style="margin-top:10px">
          <label>Clinic/Hospital</label>
          <input type="text" name="clinic" value="<?php echo htmlspecialchars($roleData['clinic'] ?? ''); ?>">
        </div>
        <?php elseif($currentUser['role']=='pharmacy'): ?>
<div class="grid-2" style="margin-top:12px">
  <div>
    <label>Pharmacy Name</label>
    <input type="text" name="pharmacyName" 
      value="<?php echo htmlspecialchars($roleData['pharmacyName'] ?? ''); ?>">
  </div>
  <div>
    <label>Drug License</label>
    <input type="text" name="drugLicense" 
      value="<?php echo htmlspecialchars($roleData['drugLicense'] ?? ''); ?>">
  </div>
</div>

<div style="margin-top:10px">
  <label>Pharmacy Address</label>
  <input type="text" name="pharmAddress" 
    value="<?php echo htmlspecialchars($roleData['pharmAddress'] ?? ''); ?>">
</div>

        <?php endif; ?>
        

        <div style="margin-top:12px">
          <label>Profile Picture</label>
          <input type="file" name="pic" accept="image/*">
        </div>

        <div style="margin-top:12px">
          <button type="submit" name="save" class="btn">Save Profile</button>
        </div>
      </form>
    </section>
  </div>
</main>
<script>
function handleLogout(){
    // clear frontend auth
    localStorage.removeItem("isLoggedIn");
    localStorage.removeItem("userName");

    // redirect to logout.php (session destroy)
    window.location.href = "logout.php";
}
</script>
</body>
</html>
