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
    <title>Ask a Question - QuickAid Forum</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style2.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="shortcut icon" href="images/WhatsApp Image 2025-08-02 at 12.37.45_fcd8b415.jpg" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Merriweather:ital,opsz,wght@0,18..144,300..900;1,18..144,300..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="styles/style.css" />
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <style>
        /* Logic for highlighting selected category */
        .category-tag.active-tag {
            background-color: #2dd4bf !important;
            color: white !important;
        }
    </style>

    <script>
        // Syncing JavaScript with the PHP Session
        const LOGGED_IN_USER = {
            id: "<?php echo $_SESSION['user_id']; ?>",
            name: "<?php echo addslashes($currentUserName); ?>",
            role: "<?php echo $_SESSION['role'] ?? 'user'; ?>"
        };
    </script>
</head>

<body class="open-sans">
    <header>
        <div class="hold">
            <div class="estrip">
                <div class="scroll"><i class="bi bi-telephone"></i>Emergency Number: +880125460586 - Call Anytime!</div>
            </div>
            <div class="emergencyData">
                <ul style="display:flex; gap:5px; list-style:none; margin:0; padding:0;">
                    <li><a href=""><i class="bi bi-facebook"></i></a></li>
                    <li><a href=""><i class="bi bi-envelope"></i></a></li>
                    <li><a href=""><i class="bi bi-google"></i></a></li>
                    <li><a href=""><i class="bi bi-geo-alt"></i></a></li>
                </ul>
              <div id="userSection">
<?php if(isset($_SESSION['user_id'])): ?>
    <a href="profile.php" class="profile-btn">
        <i class="bi bi-person-circle" style="font-size:22px;"></i>
    </a>
<?php else: ?>
    <a href="join.php" class="join-btn">+ Join Now</a>
<?php endif; ?>
</div>
            </div>
        </div>

        <nav>
            <div class="logo"><img src="images/logo.png" alt="" height="100px" width="180px" /></div>
            <div class="menu-toggle" id="menu-toggle">☰</div>
            <ul class="ul" id="nav-links">
                <li><a href="index.php">Home</a></li>
                <li class="drpdwn">
                    <a href="">Doctors</a>
                    <div class="dropdown-content">
                        <a href="findDoctor.php">Find Doctors</a>
                        <a href="doctor_available_in_location.html">Doctors Available in Location</a>
                        <a href="all_doctors.php">Doctors List</a>
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
                        <a href="careTips.html">Care and Tips For Special Groups</a>
                        <a href="promise.html">Our Commitment & Care Promise</a>
                        <a href="get_in_touch.html">Get in Touch</a>
                    </div>
                </li>
                <li><button id="navbarEmergencyBtn" class="emergency-button"><i class="bi bi-car-front-fill"></i>
                        Emergency</button></li>
            </ul>
        </nav>
    </header>

    <main class="bg-[#eaf0f3] pb-10 p-10">
        <section class="bg-[#bdd4e3] pl-28 pr-28 pb-4">
            <section class="py-10">
                <div class="max-w-3xl mx-auto bg-[#f7f6f9] p-6 md:p-8 rounded-2xl shadow-lg text-gray-600">
                    <h2 class="mb-3 text-2xl font-semibold">Ask a Health Question</h2>
                    <p class="mb-5 text-gray-400">Share your symptoms or concerns. Doctors and community members will
                        respond to help you.</p>
                    <label for="create-post-modal"
                        class="inline-flex items-center gap-2 px-4 py-3 rounded-lg cursor-pointer bg-gradient-to-r from-blue-500 to-purple-600">
                        <i class="text-white bi bi-plus-lg"></i><span class="font-semibold text-white">Create
                            Post</span>
                    </label>
                </div>
            </section>
        </section>
    </main>

    <input type="checkbox" id="create-post-modal" class="modal-toggle" />
    <div class="modal">
        <div class="w-full max-w-2xl p-8 bg-white border border-gray-200 shadow-xl modal-box rounded-2xl">
            <div class="mb-5 text-center">
                <h3 class="text-xl font-semibold text-gray-800">Create a New Post</h3>
                <p class="mt-1 text-xs text-gray-500">Share something with the community</p>
            </div>

            <form id="create-post-form" class="p-6 space-y-5 border border-gray-200 shadow-sm bg-gray-50 rounded-xl">
                <div class="flex flex-col gap-1">
                    <label class="text-lg font-medium text-gray-700">Post Title</label>
                    <input type="text" id="post-title" required
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-400"
                        placeholder="Enter a short title" />
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-lg font-medium text-gray-700">Post Content</label>
                    <textarea id="post-content" rows="4" required
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-400"
                        placeholder="Write your post..."></textarea>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-lg font-medium text-gray-700">Attach Image (optional)</label>
                    <input type="file" id="post-image" accept="image/*"
                        class="w-full px-2 py-1 text-sm border border-gray-300 rounded-lg" />
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-lg font-medium text-gray-700">Select Category</label>
                    <div class="flex flex-wrap gap-2 mt-1" id="category-options">
                        <div data-value="psychotherapy"
                            class="category-tag px-4 py-1.5 text-sm bg-gray-100 rounded-full cursor-pointer hover:bg-teal-300 transition-all">
                            Psychotherapy</div>
                        <div data-value="eye-health"
                            class="category-tag px-4 py-1.5 text-sm bg-gray-100 rounded-full cursor-pointer hover:bg-teal-300 transition-all">
                            Eye Health</div>
                        <div data-value="mental-health"
                            class="category-tag px-4 py-1.5 text-sm bg-gray-100 rounded-full cursor-pointer hover:bg-teal-300 transition-all">
                            Mental Health</div>
                        <div data-value="random-question"
                            class="category-tag px-4 py-1.5 text-sm bg-gray-100 rounded-full cursor-pointer hover:bg-teal-300 transition-all">
                            Random Question</div>
                        <div data-value="fitness"
                            class="category-tag px-4 py-1.5 text-sm bg-gray-100 rounded-full cursor-pointer hover:bg-teal-300 transition-all">
                            Fitness</div>
                    </div>
                    <input type="hidden" id="selected-category" required />
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-lg font-medium text-gray-700">Post As</label>
                    <select id="post-author-type" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                        <option value="user">Community User</option>
                        <?php if($_SESSION['role'] == 'doctor') echo '<option value="doctor">Doctor</option>'; ?>
                    </select>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <label for="create-post-modal"
                        class="px-4 py-2 text-sm bg-gray-200 rounded-lg cursor-pointer hover:bg-gray-300">Cancel</label>
                    <button type="submit"
                        class="px-5 py-2 text-sm text-white transition-all bg-teal-500 rounded-lg hover:bg-teal-600">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <section>
            <div class="flex items-center justify-between foot_contain">
                <div class="foot">
                    <img src="images/logo.png" alt="" height="150px" width="180px" />
                    <p style="text-align: justify">QuickAid provides fast, reliable, and user-friendly access to
                        emergency <br />health services...</p>
                    <ul class="pt-5 info">
                        <li class="flex items-center h-10"><a href=""><i class="bi bi-envelope-at"></i>
                                QuicAid@gmail.com</a></li>
                        <li class="flex items-center h-10"><a href=""><i class="bi bi-twitter"></i> Tweet Us</a></li>
                        <li class="flex items-center h-10"><a href=""><i class="bi bi-linkedin"></i> Visit Our Linkedin
                                Profile</a></li>
                    </ul>
                </div>
                <div class="formm">
                    <h3 class="merriweather">Something bothering you?</h3>
                    <form class="flex flex-col">
                        <textarea name="complaint" placeholder="Write your complaint here..." rows="5" required
                            class="mt-3 mb-3 bg-white"></textarea>
                        <button class="send">Send</button>
                    </form>
                </div>
            </div>
        </section>
    </footer>

    <script src="jsFile/form2.js"></script>

</body>

</html>