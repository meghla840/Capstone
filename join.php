<?php

$host = "localhost";
$user = "root";
$pass = "";
$db   = "quickaid";

$conn = mysqli_connect($host,$user,$pass,$db);

$message = "";

if(isset($_POST['signup'])){

    $name = mysqli_real_escape_string($conn,$_POST['name']);
    $phone = mysqli_real_escape_string($conn,$_POST['phone']);
    $email = mysqli_real_escape_string($conn,strtolower(trim($_POST['email'])));
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // ✅ role validation
    $allowedRoles = ['admin','patient','doctor','hospital','pharmacy','transport'];
    if(!in_array($role,$allowedRoles)){
        die("Invalid role");
    }

    // ✅ ADMIN LIMIT (IMPORTANT)
    if($role == "admin"){
        $adminCheck = mysqli_query($conn, "SELECT * FROM users WHERE role='admin'");
        if(mysqli_num_rows($adminCheck) > 0){
            $message = "⚠ Admin already exists";
            exit();
        }
    }


// optional fields
$bloodGroup = $_POST['bloodGroup'] ?? "";
$healthIssues = $_POST['healthIssues'] ?? "";
$address = $_POST['address'] ?? "";

$specialization = $_POST['specialization'] ?? "";
$bmdc = $_POST['bmdc'] ?? "";
$clinic = $_POST['clinic'] ?? "";

$hospitalName = $_POST['hospitalName'] ?? "";
$licenseH = $_POST['licenseH'] ?? "";
$hphone = $_POST['hphone'] ?? "";

$pharmacyName = $_POST['pharmacyName'] ?? "";
$drugLicense = $_POST['drugLicense'] ?? "";
$pharmAddress = $_POST['pharmAddress'] ?? "";

$driverName = $_POST['driverName'] ?? "";
$vehicleType = $_POST['vehicleType'] ?? "";
$numberPlate = $_POST['numberPlate'] ?? "";
$drivingLicense = $_POST['drivingLicense'] ?? "";
$serviceArea = $_POST['serviceArea'] ?? "";
$availableTill = $_POST['availableTill'] ?? "";

// ✅ check email OR phone (FIX)
$check = mysqli_query($conn,"SELECT * FROM users WHERE email='$email' OR phone='$phone'");

if(mysqli_num_rows($check)>0){

    $message = "⚠ Email or Phone already exists";

}else{

    // ✅ better unique id (FIX)
    $userId = "user_" . uniqid();

    // ✅ TRANSACTION START (VERY IMPORTANT)
    mysqli_begin_transaction($conn);

    try{

        // 1. insert users
        $sqlUser = "INSERT INTO users (userId,name,phone,email,password,role)
        VALUES ('$userId','$name','$phone','$email','$password','$role')";

        mysqli_query($conn,$sqlUser);

        // 2. role based insert

        if($role == "patient"){

            mysqli_query($conn,"INSERT INTO patients (userId,bloodGroup,healthIssues,address)
            VALUES ('$userId','$bloodGroup','$healthIssues','$address')");

        }

        elseif($role == "doctor"){

            mysqli_query($conn,"INSERT INTO doctors (userId,specialization,bmdc,clinic)
            VALUES ('$userId','$specialization','$bmdc','$clinic')");

        }

        elseif($role == "hospital"){

            mysqli_query($conn,"INSERT INTO hospitals (userId,hospitalName,licenseH,hphone)
            VALUES ('$userId','$hospitalName','$licenseH','$hphone')");

        }

        elseif($role == "pharmacy"){

            mysqli_query($conn,"INSERT INTO pharmacies (userId,pharmacyName,drugLicense,pharmAddress)
            VALUES ('$userId','$pharmacyName','$drugLicense','$pharmAddress')");

        }

        elseif($role == "transport"){

            mysqli_query($conn,"INSERT INTO transports 
            (userId,driverName,vehicleType,numberPlate,drivingLicense,serviceArea,availableTill)
            VALUES 
            ('$userId','$driverName','$vehicleType','$numberPlate','$drivingLicense','$serviceArea','$availableTill')");

        }

       
        mysqli_commit($conn);

        $message = "✅ Account created successfully";

        header("refresh:1; url=index.html");

    }catch(Exception $e){

       
        mysqli_rollback($conn);

        $message = "❌ Something went wrong";
    }

}

}


?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Join – QuickAid</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>

:root{
--brand:#49465b;
--header-gradient: linear-gradient(90deg, #49465b, #6e6c80);
--bg:#f3f6f9;
}

body{
font-family:'Inter',sans-serif;
background:var(--bg);
margin:0;
}

header{
background:var(--header-gradient);
color:white;
padding:15px;
text-align:center;
}

.card{
max-width:650px;
margin:40px auto;
background:white;
padding:30px;
border-radius:12px;
box-shadow:0 8px 20px rgba(0,0,0,0.15);
}

input,select{
width:100%;
padding:12px;
margin:8px 0;
border:1px solid #ddd;
border-radius:6px;
}

button{
width:100%;
padding:14px;
border:none;
background:var(--header-gradient);
color:white;
font-size:16px;
border-radius:8px;
cursor:pointer;
}

.hidden{
display:none;
}

.msg{
text-align:center;
font-weight:600;
margin-bottom:15px;
}
.login-link{
text-align:center;
margin-top:20px;
font-size:15px;
color:#555;
}

.login-link a{
color:#49465b;
font-weight:600;
text-decoration:none;
border-bottom:2px solid transparent;
transition:0.3s;
}

.login-link a:hover{
border-color:#49465b;
}
.password-box{
position:relative;
}

.password-box input{
width:100%;
padding:12px;
margin:8px 0;
border:1px solid #ddd;
border-radius:6px;
}

.toggle-password{
position:absolute;
right:15px;
top:50%;
transform:translateY(-50%);
cursor:pointer;
font-size:18px;
color:#777;
}

.toggle-password:hover{
color:#000;
}
</style>
</head>

<body>

<header>
<h2>Create Account</h2>
</header>

<div class="card">

<?php if($message!=""){ ?>
<div class="msg"><?php echo $message; ?></div>
<?php } ?>

<form method="POST">

<input type="text" name="name" placeholder="Full Name" required>

<input type="text" name="phone" placeholder="Phone Number" required>

<input type="email" name="email" placeholder="Email" required>

<div class="password-box">
<input type="password" id="password" name="password" placeholder="Password" required>
<span class="toggle-password" onclick="togglePassword()">
<i id="eyeIcon" class="bi bi-eye"></i>
</span>
</div>

<label>Select Role</label>

<select name="role" id="role" onchange="showRoleFields()" required>
<?php
$adminCheck = mysqli_query($conn, "SELECT * FROM users WHERE role='admin'");
$adminExists = mysqli_num_rows($adminCheck) > 0;
?>
<option value="">Choose</option>
<?php if(!$adminExists){ ?>
        <option value="admin">Admin</option>
    <?php } ?>
<option value="patient">Patient</option>
<option value="doctor">Doctor</option>
<option value="hospital">Hospital</option>
<option value="pharmacy">Pharmacy</option>
<option value="transport">Transport</option>

</select>

<div id="patientFields" class="hidden">

<select name="bloodGroup">
<option value="">Blood Group</option>
<option>A+</option>
<option>B+</option>
<option>O+</option>
<option>AB+</option>
</select>

<input type="text" name="healthIssues" placeholder="Health Issues">

<input type="text" name="address" placeholder="Address">

</div>

<div id="doctorFields" class="hidden">

<input type="text" name="specialization" placeholder="Specialization">

<input type="text" name="bmdc" placeholder="BMDC">

<input type="text" name="clinic" placeholder="Clinic Address">

</div>

<div id="hospitalFields" class="hidden">

<input type="text" name="hospitalName" placeholder="Hospital Name">

<input type="text" name="licenseH" placeholder="License">

<input type="text" name="hphone" placeholder="Emergency Phone">

</div>

<div id="pharmacyFields" class="hidden">

<input type="text" name="pharmacyName" placeholder="Pharmacy Name">

<input type="text" name="drugLicense" placeholder="Drug License">

<input type="text" name="pharmAddress" placeholder="Address">

</div>

<div id="transportFields" class="hidden">

<input type="text" name="driverName" placeholder="Driver Name">

<input type="text" name="vehicleType" placeholder="Vehicle Type">

<input type="text" name="numberPlate" placeholder="Number Plate">

<input type="text" name="drivingLicense" placeholder="Driving License">

<input type="text" name="serviceArea" placeholder="Service Area">

<input type="text" name="availableTill" placeholder="Available Till">

</div>

<button type="submit" name="signup">Sign Up</button>

</form>
<div class="login-link">
Already have an account? 
<a href="login.php">Login here</a>
</div>
</div>

<script>

function showRoleFields(){

let role=document.getElementById("role").value;

["patient","doctor","hospital","pharmacy","transport"].forEach(r=>{

document.getElementById(r+"Fields").classList.add("hidden");

});

if(role){

document.getElementById(role+"Fields").classList.remove("hidden");

}

}
function togglePassword(){

let pass = document.getElementById("password");

if(pass.type === "password"){
pass.type = "text";
}else{
pass.type = "password";
}

}

</script>

</body>
</html>