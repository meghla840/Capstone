<?php
session_start();

// Strong session check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Ensure session variables exist
$userId = $_SESSION['user_id'] ?? '';
$currentUserName = $_SESSION['username'] ?? '';
$userRole = $_SESSION['role'] ?? 'user';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Browse Discussions - QuickAid Forum</title>

    <link rel="stylesheet" href="styles/style.css" />
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        #qa-submit-comment:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
    </style>
</head>

<body class="open-sans">

<!-- Header (UNCHANGED DESIGN) -->
<header>
<div class="hold">
    <div class="estrip">
        <div class="scroll">
            <i class="bi bi-telephone"></i>Emergency Number: +880125460586 - Call Anytime!
        </div>
    </div>

    <div class="emergencyData">
        <ul style="display:flex; gap:5px; list-style:none; margin:0; padding:0;">
            <li><a href=""><i class="bi bi-facebook"></i></a></li>
            <li><a href=""><i class="bi bi-envelope"></i></a></li>
            <li><a href=""><i class="bi bi-google"></i></a></li>
            <li><a href=""><i class="bi bi-geo-alt"></i></a></li>
        </ul>

        <!-- KEEP DESIGN SAME -->
        <span id="userSection">
<?php if(isset($_SESSION['user_id'])): ?>
    <span>Hello, <?php echo $_SESSION['username']; ?></span>
    <a href="logout.php">Logout</a>
<?php else: ?>
    <a href="join.php"><button> + Join Now</button></a>
<?php endif; ?>
</span>
    </div>
</div>

<nav>
    <div class="logo">
        <img src="images/logo.png" alt="" height="100px" width="180px" />
    </div>

    <div class="menu-toggle" id="menu-toggle">☰</div>

    <ul class="ul" id="nav-links">
        <li><a href="index.php">Home</a></li>

        <li class="drpdwn">
            <a href="">Doctors</a>
            <div class="dropdown-content">
              <a href="findDoctor.html">Find Doctors</a>
              <a href="doctor_available_in_location.html">Doctors Available in Location</a>
              <a href="all_doctors.html">Doctors List</a>
              <a href="demo_appointment list.html">appointment List</a>
            </div>
        </li>

        <li class="drpdwn"><a href="">Hospital</a>
            <div class="dropdown-content">
                <a href="find_hospital.html">Find Hospital</a>
                <a href="nearby_hospitals.html">Check around me</a>
                <a href="hospital_list.html">Hospital List</a>
            </div>
        </li>

        <li class="drpdwn"><a href="">Medicine Availability</a>
            <div class="dropdown-content">
                <a href="medical_store_list.html">Medical Store list</a>
                <a href="medicine_nearby.html">Check Medicine Nearby</a>
                <a href="medicine_details.html">Medicine Details</a>
            </div>
        </li>

        <li class="drpdwn"><a href="">Health Forum</a>
            <div class="dropdown-content">
                <a href="askQc.php">Ask a Question</a>
                <a href="browse.php">Browse Discussions</a>
            </div>
        </li>

        <li class="drpdwn"><a href="">Health Support Services</a>
            <div class="dropdown-content">
                <a href="articles.html">Latest Health News</a>
                <a href="emergency_action_guide.html">Emergency Action Guide</a>
                <a href="careTips.html">Care and Tips</a>
                <a href="promise.html">Our Commitment</a>
                <a href="get_in_touch.html">Get in Touch</a>
            </div>
        </li>

        <li>
            <button id="navbarEmergencyBtn" class="emergency-button">
                <i class="bi bi-car-front-fill"></i> Emergency
            </button>
        </li>
    </ul>
</nav>
</header>

<!-- MAIN (UNCHANGED) -->
<main class="max-w-6xl p-6 mx-auto">
    <div class="p-4 bg-white shadow rounded-2xl">

        <div class="flex flex-wrap items-center justify-between mb-4">
            <div class="flex-1 tabs tabs-boxed" id="tabs">
                <a class="tab tab-lifted active" data-tab="my">My Posts</a>
                <a class="tab tab-lifted" data-tab="all">All Posts</a>
                <a class="tab tab-lifted" data-tab="doctor">Doctor Posts</a>
            </div>

            <div class="flex items-center gap-2">
                <select id="category-filter" class="px-3 py-1 text-sm border rounded-lg input input-bordered">
                    <option value="">All Categories</option>
                    <option value="psychotherapy">Psychotherapy</option>
                    <option value="eye-health">Eye Health</option>
                    <option value="mental-health">Mental Health</option>
                    <option value="random-question">Random Question</option>
                    <option value="fitness">Fitness</option>
                </select>
            </div>
        </div>

        <div class="mb-4">
            <a href="askQc.php" class="text-white btn bg-gradient-to-r from-purple-600 to-blue-500">+ New Post</a>
        </div>

        <div id="posts-container" class="space-y-4"></div>

        <div id="empty-state" class="hidden py-10 text-center text-slate-500">
            <p>No posts to show yet. Create the first post!</p>
        </div>

    </div>
</main>

<!-- FOOTER (UNCHANGED) -->
<footer>
    <section>
        <div class="flex items-center justify-between foot_contain">
            <div class="foot">
                <img src="images/logo.png" height="150px" width="180px" />
                <p>QuickAid provides fast, reliable health services...</p>
            </div>
        </div>
    </section>
</footer>

<!-- ✅ ONLY IMPORTANT FIX -->
<script>
window.LOGGED_IN_USER = {
    id: "<?php echo $userId; ?>",
    name: "<?php echo addslashes($currentUserName); ?>",
    role: "<?php echo $userRole; ?>"
};
</script>

<script src="jsFile/form2.js"></script>

</body>
</html>