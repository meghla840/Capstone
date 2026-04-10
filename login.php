<?php
session_start();
include "backend/db.php";

// 🔹 Redirect already logged-in users
if(isset($_SESSION['isLoggedIn']) && $_SESSION['isLoggedIn'] === true){
    header("Location: index.php");
    exit();
}

$message = "";

if(isset($_POST['login'])){

    $loginId = strtolower(trim($_POST['loginId']));
    $password = trim($_POST['password']);
    $loginId = mysqli_real_escape_string($conn,$loginId);

    $sql = "SELECT * FROM users WHERE email='$loginId' OR phone='$loginId' LIMIT 1";
    $result = mysqli_query($conn,$sql);

    if($result && mysqli_num_rows($result) > 0){

        $user = mysqli_fetch_assoc($result);

        // ✅ BLOCK CHECK
        if($user['status'] == 'blocked'){
            $message = "❌ Your account is blocked by admin!";
        }
        else if(password_verify($password,$user['password'])){

            session_regenerate_id(true);

            $_SESSION['isLoggedIn'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            header("Location: index.php");
            exit();

        } else {
            $message = "❌ Wrong Password!";
        }

    } else {
        $message = "❌ User not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login – QuickAid</title>

<link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<style>
body { font-family: 'Merriweather', serif; background: #eaf0f3; margin: 0; padding: 0; }

header {
    background: #49465b;
    color: white;
    padding: 1rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    position: sticky;
    top: 0;
}

header i {
    font-size: 1.4rem;
    cursor: pointer;
    position: absolute;
    left: 20px;
}

header h1 {
    margin: 0;
    font-size: 1.4rem;
    font-weight: 600;
    text-align: center;
    width: 100%;
}

.card {
    max-width: 500px;
    margin: 2rem auto;
    background: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 6px 15px rgba(0,0,0,0.15);
    text-align: center;
}

h2 {
    font-size: 1.8rem;
    color: #49465b;
    margin-bottom: 1.5rem;
}

input {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-family: 'Merriweather', serif;
    font-size: 1rem;
}

button {
    background: #49465b;
    color: white;
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 8px;
    font-size: 1.1rem;
    cursor: pointer;
    font-weight: 600;
    margin-top: 10px;
}

button:hover {
    background: #6c6a81;
}

.signup-link {
    text-align: center;
    margin-top: 1rem;
}

.signup-link a {
    color: #49465b;
    font-weight: 700;
    text-decoration: none;
}

.signup-link a:hover {
    text-decoration: underline;
}

.message {
    margin-top: 10px;
    font-weight: 600;
}

.error { color: #e74c3c; }
.success { color: #27ae60; }

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
    color:#666;
}

.toggle-password:hover{
    color:#000;
}
</style>
</head>

<body>

<header>
    <i class="bi bi-arrow-left" onclick="history.back()"></i>
    <h1>Login</h1>
</header>

<div class="card">
    <h2>Welcome Back</h2>

    <form action="" method="POST">
        <input type="text" name="loginId" placeholder="Email or Phone" required>

        <div class="password-box">
            <input type="password" id="password" name="password" placeholder="Password" required>

            <span class="toggle-password" onclick="togglePassword()">
                <i id="eyeIcon" class="bi bi-eye"></i>
            </span>
        </div>

        <button type="submit" name="login">Login</button>

        <?php if($message): ?>
        <div class="message <?php echo (strpos($message,'❌')!==false)?'error':'success'; ?>">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <div class="signup-link">
            Don't have an account? <a href="join.php">Join Now</a>
        </div>
    </form>
</div>

<script>
function togglePassword(){
    let pass = document.getElementById("password");
    let icon = document.getElementById("eyeIcon");

    if(pass.type === "password"){
        pass.type = "text";
        icon.classList.remove("bi-eye");
        icon.classList.add("bi-eye-slash");
    }else{
        pass.type = "password";
        icon.classList.remove("bi-eye-slash");
        icon.classList.add("bi-eye");
    }
}
</script>

</body>
</html>