<?php
session_start();

// Destroy session
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logging out...</title>
</head>
<body>

<script>
    // Clear localStorage
    localStorage.removeItem("isLoggedIn");
    localStorage.removeItem("userName");

    // Redirect to home page
    window.location.href = "index.php";
</script>

</body>
</html>