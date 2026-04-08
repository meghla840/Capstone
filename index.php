<?php 
include "backend/db.php";
session_start();

/* Logout */
if(isset($_POST['logout'])){
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

/* Login status */
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['userName'] ?? '';


/* Fetch doctors with user data */
$topDoctors = [];

$query = "
SELECT d.*, u.name AS user_name, u.profilePic 
FROM doctors d
LEFT JOIN users u ON d.userId = u.userId
ORDER BY d.experienceYears DESC 
LIMIT 6
";

$result = mysqli_query($conn, $query);

if($result){
    while($row = mysqli_fetch_assoc($result)){
        $topDoctors[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="shortcut icon" href="images/WhatsApp Image 2025-08-02 at 12.37.45_fcd8b415.jpg" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,opsz,wght@0,18..144,300..900;1,18..144,300..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <title>QuickAid</title>
    <style>
/* ==== Copy your existing internal CSS here ==== */
    #top-rated-Specialists {
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* 3 cards per row */
    gap: 25px;
    width: 70%;
    margin: 0 auto;
  
    
}
.btn-view a {
    text-decoration: none;
    color: inherit;
}
 a {
    text-decoration: none;
    color: inherit;
}
@media (max-width: 992px) {
    #top-rated-Specialists {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 600px) {
    #top-rated-Specialists {
        grid-template-columns: repeat(1, 1fr);
    }
}

        .rated-doctor-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px;
            transition: transform 0.3s ease;
        }

        .rated-doctor-card:hover {
            transform: scale(1.03);
        }

        .rated-doctor-img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 10px;
        }

        .rated-doctor-info {
            margin-top: 10px;
            text-align: center;
        }

        .rated-doctor-name {
            font-size: 18px;
            font-weight: bold;
        }

        .rated-doctor-rating {
            color: #f1c40f;
            font-size: 16px;
        }

        .btn-view {
            background: #49465b;
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
        }
        .btn-view:hover {
            background: #68657b;
            transform: scale(1.05);
        }

        .search-btn-find_doctor_page{
            background-color: #49465b;
            color: #fff;
            font-size:18px;
            width:10%;
            padding:12px;
            border:none;
            border-radius:8px;
            font-weight:bold;
            cursor:pointer;
        }
        .hero .buttons a {
            text-decoration: none;   
            color: white;           
            display: inline-block;  
        }
        .default-avatar-large {
    width: 100%;
    height: 250px;
    border-radius: 10px;
    background: #eee;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 80px;
    color: #888;
}
    </style>
</head>
<body class="open-sans">
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
<div id="userSection">
<?php if($isLoggedIn): ?>
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
        <div class="logo">
            <img src="images/logo.png" alt="Logo" height="50px" width="auto">
        </div>

        <div class="menu-toggle" id="menu-toggle">☰</div>

        <ul class="ul" id="nav-links">
            <li><a href="index.php">Home</a></li>
            <li class="drpdwn">
                <a href="">Doctors</a>
                <div class="dropdown-content">
                    <a href="findDoctor.php">Find Doctors</a>
                    
                    <a href="all_doctors.php">Doctors List</a>
                   
                </div>
            </li>
            <li class="drpdwn"><a href="">Hospital</a>
                <div class="dropdown-content">
                    <a href="find_hospital.php">Find Hospital</a>
                    <a href="hospital_list.php">Hospital List</a>
                </div>
            </li>
            <li class="drpdwn"><a href="">Medicine Availability</a>
                <div class="dropdown-content">
                    <a href="medicine_nearby.php">Medical Store list</a>
                    
                    <a href="medicine_details.php">Medicine Details</a>
                </div>
            </li>
            <li class="drpdwn"><a href="">Health Forum</a>
                <div class="dropdown-content">
                    <a href="askQc.html">Ask a Question</a>
                    <a href="browse.html">Browse Discussions</a>
                </div>
            </li>
            <li class="drpdwn">
                <a href="">Health Support Services</a>
                <div class="dropdown-content">
                    <a href="articles.html">Latest Health News</a>
                    <a href="emergency_action_guide.html">Emergency Action Guide</a>
                    <a href="careTips.html">Care and Tips For Special Groups</a>
                    <a href="promise.html">Our Commitment & Care Promise</a>
                    <a href="get_in_touch.php">Get in Touch</a>
                </div>
            </li>
            <li>
                <button id="navbarEmergencyBtn" class="emergency-button">
                    <i class="bi bi-car-front-fill"></i> Emergency
                </button>
            </li>
        </ul>

        <!-- Emergency Modal -->
        <div id="emergencyModal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
            <div style="background:#fff; padding:25px 30px; border-radius:12px; text-align:center; max-width:400px; width:90%; box-shadow:0 5px 20px rgba(0,0,0,0.3);">
                <h2 style="margin-bottom:15px;">Emergency Call</h2>
                <p>Do you want to call the emergency number <strong>+880125460586</strong> to arrange an ambulance?</p>
                <div style="margin-top:20px; display:flex; justify-content:space-around;">
                    <button id="confirmCall" style="padding:10px 20px; border:none; border-radius:8px; font-weight:bold; cursor:pointer; background-color:#28a745; color:white;">Yes</button>
                    <button id="cancelCall" style="padding:10px 20px; border:none; border-radius:8px; font-weight:bold; cursor:pointer; background-color:#dc3545; color:white;">No</button>
                </div>
            </div>
        </div>
    </nav>
</header>

<main>
        <section class="hero">
  <div class="slide active" style="background-image: url('images/hospitallllll.png')">
    <h2 class="number">01.</h2>
    <h1>Emergency? <br><span>Get to Nearest Hospital</span></h1>
    <div class="buttons">
      <button class="btn"><a href="find_hospital.html">Locate Hospital</a></button>
      <button class="btn2"><a href="hospital_list.php">Hospital List</a></button>
    </div>
  </div>
  
  <div class="slide" style="background-image: url('images/Ambulaceeeeeeee.png')">
    <h2 class="number">02.</h2>
    <h1><span>Stay Calm</span> and Call for Help</h1>
    <div class="buttons">
      <button class="btn"><a href="ambulance.php">Arrange Ambulance</a></button>
      <button class="btn2">Call Emergency</button>
    </div>
  </div>
  
  <div class="slide" style="background-image: url('images/Pharmacyyyyyyyyyyyy.png')">
    <h2 class="number">03.</h2>
    <h1>Find Nearest <br><span>Medicine Facilities</span></h1>
    <div class="buttons">
      <button class="btn"><a href="medicine_nearby.php">Get Medicine Nearby</a></button>
      <button class="btn2"><a href="medicine_details.html">Order Online</a></button>
    </div>
  </div>
</section>
        <section class="Seacrh_Section">
            <div class="search">
                <h2 class="merriweather">Start Your Search</h2>
                <div class="form"><input type="text" name="" id="" placeholder="Seacrh doctors, hospital, medicine etc.">
                <button class="search-btn-find_doctor_page">Search</button>
                </div>
            </div>
            <div class="join">
                <img src="images/Lifesavers - Hand.png" alt="" height="100px">
                <h3>Your help can save lives. <br> It takes just a minute to make a difference</h3>
            </div>
        </section>
        <section class="details">
            <div class="contain c1">
            <div class="text">Make an <br><span class="s1">Appointment</span></div>
            <button class="bt" ><a href="available_doctors.html" style="text-decoration:none; color:white;">Who's Available?</a></button>
        </div>

            <div class="contain c2">
                <div class="text">Find Emergency Care <br><span class="s2">Near You </span></div>
                <button class="bt" id="availableDoctorsBtn">Search By Location</button>
            </div>

            <div class="contain c3">
                <div class="text">Use Alert only for<br><span class="s3"> emergencies</span></div>
                <button class="bt" id="trafficAlertBtn">Alert Traffic</button>
            </div>

            <div class="contain c4">
                <div class="text">Emergency Medical <br><span class="s4">Transport</span></div>
                <button class="bt" id="bookAmbulanceBtn"><a href="ambulance.php">Book Now</a></button>
            </div>
        </section>
       <section class="how-it-works">
            <h2 class="merriweather">How It Works</h2>
        <div class="steps">
            
            <div class="step">
            <span class="number">1</span>
            <p><strong>Access Emergency Care</strong><br>Connect instantly to hospitals and emergency responders.</p>
            
            </div>


            <div class="step">
            <span class="number">2</span>
            <p><strong>Get Emergency Help</strong><br>Connect to nearby emergency care without delay.</p>
            </div>

            <div class="step">
            <span class="number">3</span>
            <p><strong>Trigger Alerts</strong><br>Notify local traffic or responders during emergencies.</p>
            </div>
            <div id="confirmModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:9999;">
    
                    <div style="background:#fff; padding:25px; border-radius:10px; text-align:center; width:300px;">
                        <h3>Send Traffic Alert?</h3>
                        <p>This will notify nearby users.</p>

                        <button id="confirmYes" style="margin:10px; padding:8px 15px; background:green; color:#fff; border:none; border-radius:6px;">Yes</button>
                        <button id="confirmNo" style="margin:10px; padding:8px 15px; background:red; color:#fff; border:none; border-radius:6px;">No</button>
                    </div>

                </div>

            <div class="step">
            <span class="number">4</span>
            <p><strong>Book Medical Transport</strong><br>Call an ambulance or medical ride instantly.</p>
            </div>

        </div>
        </section>
        <section class="specialist">
    <p class="heighlightt">Meet our Professionals</p>
    <h1 class="merriweather"> Top Rated <span>Specialist</span></h1>

   <div id="top-rated-Specialists">

        <?php foreach($topDoctors as $doctor): ?>

            <div class="rated-doctor-card">
            
                <?php 
                $imagePath = !empty($doctor['profilePic']) && file_exists($doctor['profilePic']) 
                    ? $doctor['profilePic'] 
                    : null;
                ?>

                <?php if($imagePath): ?>
                    <img src="<?php echo $imagePath; ?>" class="rated-doctor-img">
                <?php else: ?>
                    <div class="default-avatar-large">
                        <i class="bi bi-person-circle"></i>
                    </div>
                <?php endif; ?>

                <div class="rated-doctor-info">

                    <h2 class="rated-doctor-name">
                        <?php echo htmlspecialchars($doctor['user_name'] ?? 'Unknown Doctor'); ?>
                    </h2>

                    <p><?php echo htmlspecialchars($doctor['specialization']); ?></p>
                    <p><b>Experience:</b> <?php echo htmlspecialchars($doctor['experienceYears']); ?> years</p>

                    <a href="doctor_details.php?id=<?php echo $doctor['userId']; ?>" class="btn-view">
                View Details
                    </a>

                </div>

            </div>

        <?php endforeach; ?>

</div>

    <div class="see">
        <a href="all_doctors.php" class="see-btn">See All Doctors</a>
    </div>
</section>
        <section class="articles">
            <p class="heighlightt">Stay updated with our latest health tips, guides, and medical insights</p>
        <h1 class="merriweather">Latest Health <span class="highlight">Articles</span></h1>
        <p>Explore our latest articles covering current health concerns, expert advice, <br>
            and wellness tips to help you stay informed and protected.</p>

    <div class="article_container">

    
            <div class="card">
            <div class="img-container">
                <img class="main-img" src="images/corona.jpeg" alt="Health Image">
               
            </div>
            <div class="content">
                <div class="category">Corona</div>
                <div class="title">How COVID-19 variants affect elderly immunity</div>
                <div class="date"><i class="bi bi-calendar-week"></i> August 5, 2025</div>
            </div>
            <div class="like">
                <div><i class="bi bi-heart"></i> 88</div>
                <div><i class="bi bi-chat"></i> 10</div>
            </div>
            </div>

            
            <div class="card">
                <div class="img-container">
                    <img class="main-img" src="images/dengue.jpeg" alt="Health Image">
                
                    </div>
                    <div class="content">
                        <div class="category">Dengue</div>
                        <div class="title">Dengue outbreak worsens during monsoon in Dhaka</div>
                        <div class="date"><i class="bi bi-calendar-week"></i> July 29, 2025</div>
                    </div>
                    <div class="like">
                    <div><i class="bi bi-heart"></i> 188</div>
                    <div><i class="bi bi-chat"></i> 90</div>
                </div>
            </div>

              
                <div class="card">
                <div class="img-container">
                    <img class="main-img" src="images/airpolution.jpeg" alt="Health Image">
                    
                </div>
                <div class="content">
                    <div class="category">Air Pollution</div>
                    <div class="title">Polluted air increases risk of stroke and lung diseases</div>
                    <div class="date"><i class="bi bi-calendar-week"></i> July 20, 2025</div>
                </div>
                <div class="like">
                <div><i class="bi bi-heart"></i> 88k+</div>
                <div><i class="bi bi-chat"></i> 1000+</div>
            </div>
                </div>

             
                <div class="card">
                <div class="img-container">
                    <img class="main-img" src="images/mental.jpeg" alt="Health Image">
    
                </div>
                <div class="content">
                    <div class="category">Mental Health</div>
                    <div class="title">Rising anxiety among youth post-pandemic</div>
                    <div class="date"><i class="bi bi-calendar-week"></i> August 1, 2025</div>
                </div>
                <div class="like">
                <div><i class="bi bi-heart"></i> 98</div>
                <div><i class="bi bi-chat"></i> 100</div>
            </div>
                </div>

              
                <div class="card">
                <div class="img-container">
                    <img class="main-img" src="images/monkeypox.jpeg" alt="Health Image">
                    
                </div>
                <div class="content">
                    <div class="category">Monkeypox</div>
                    <div class="title">Monkeypox alert issued in several districts</div>
                    <div class="date"><i class="bi bi-calendar-week"></i> July 25, 2025</div>
                </div>
                <div class="like">
                <div><i class="bi bi-heart"></i> 45</div>
                <div><i class="bi bi-chat"></i> 106</div>
            </div>
                </div>

               
                <div class="card">
                <div class="img-container">
                    <img class="main-img" src="images/heatstroke.jpeg" alt="Health Image">
                    
                </div>
                <div class="content">
                    <div class="category">Heatstroke</div>
                    <div class="title">Record heatwaves raise heatstroke cases globally</div>
                    <div class="date"><i class="bi bi-calendar-week"></i> August 3, 2025</div>
                </div>
                <div class="like">
                <div><i class="bi bi-heart"></i> 98</div>
                <div><i class="bi bi-chat"></i> 65</div>
            </div>
                </div>

            </div>
            <div class="see"><button><a href="articles.html" class="read-btn">Explore All</a>
            </button></div>
        </section>

        <section class="emrg">
            <div class="emergency-box">
            
        
            <div class="section">
            <img src="images/logo.png" width="150px" height="50px" alt="icon" class="icon">
            <div class="content">
                <span class="title">Emergency Call</span>
                <span class="value"> +880156815648</span>
            </div>
            </div>
            <div class="divider"></div>
            <div class="section">
            <img src="https://img.icons8.com/color/48/email.png" alt="icon" class="icon">
            <div class="content">
                <span class="title">24/7 Email Support</span>
                <span class="value">QuidAid@domain.com</span>
            </div>
            </div>

            </div>
        </section>

    </main>

<footer>
        <section>
            <div class="foot_contain">
                <div class="foot">
                <img src="images/logo.png" alt="" height="150px" width="180px">
                <p style="text-align: justify;">QuickAid provides fast, reliable, and user-friendly access to emergency <br>health services.  Whether it's locating medicine,  finding a doctor,<br> or alerting responders   we’re here when every second counts.</p>
                <ul class="info">
                    <li><a href=""><i class="bi bi-envelope-at"></i>  QuicAid@gmail.com</a></li>
                    <li><a href=""><i class="bi bi-twitter"></i> Tweet Us</a></li>
                    <li>
                        <a href=""><i class="bi bi-linkedin"></i>  Visit Our Linkedin Profile</a>
                    </li>
                </ul>
                <div class="icon">
                    <i class="bi bi-envelope-at"></i> 
                    <i class="bi bi-twitter"></i>
                    <i class="bi bi-linkedin"></i>
                    <i class="bi bi-facebook"></i>
                    <i class="bi bi-geo"></i>
                    <i class="bi bi-browser-chrome"></i>

                </div>
            </div>
            
            <div class="formm">
                 <h3 class="merriweather"> Something bothering you?</h3>
                <p>Submit your complaint below and we’ll get back to you shortly.</p>
                <form >
                    <textarea name="complaint" placeholder="Write your complaint here..." rows="5" required></textarea>
                <button class="send">Send</button>
                </form>
                
            </div>
            
            </div>
            <div class="copyright">
                <p>Copyright  &copy; 2025 QuicAid. All rights reserved.</p>
            </div>
        </section>
    </footer>

<script>
document.addEventListener("DOMContentLoaded", function () {

    // ==========================
    // EMERGENCY MODAL
    // ==========================
    const emergencyBtn = document.getElementById("navbarEmergencyBtn");
    const modal = document.getElementById("emergencyModal");
    const confirmBtn = document.getElementById("confirmCall");
    const cancelBtn = document.getElementById("cancelCall");
    const emergencyNumber = "+880125460586";

    if (emergencyBtn) {
        emergencyBtn.addEventListener("click", () => {
            modal.style.display = "flex";
        });
    }

    if (confirmBtn) {
        confirmBtn.addEventListener("click", () => {
            window.location.href = `tel:${emergencyNumber}`;
            modal.style.display = "none";
        });
    }

    if (cancelBtn) {
        cancelBtn.addEventListener("click", () => {
            modal.style.display = "none";
        });
    }

    window.addEventListener("click", (e) => {
        if (e.target === modal) {
            modal.style.display = "none";
        }
    });

});
// traffic alert
document.addEventListener("DOMContentLoaded", function () {

    const btn = document.getElementById("trafficAlertBtn");
    const modal = document.getElementById("confirmModal");

    const yesBtn = document.getElementById("confirmYes");
    const noBtn = document.getElementById("confirmNo");

    // Open modal
    if (btn) {
        btn.addEventListener("click", function () {
            modal.style.display = "flex";
        });
    }

    // Close modal (No button)
    if (noBtn) {
        noBtn.addEventListener("click", function () {
            modal.style.display = "none";
        });
    }

    // Confirm Yes → Send alert
    if (yesBtn) {
        yesBtn.addEventListener("click", function () {

            modal.style.display = "none";

            fetch("alert_traffic.php", {
                method: "POST",
                credentials: "include"
            })
            .then(res => res.text())   // ✅ changed from json() to text()
            .then(text => {

                console.log("RAW RESPONSE:", text); // ✅ debug

                let data;

                try {
                    data = JSON.parse(text); // convert manually
                } catch (e) {
                    console.error("JSON parse error:", e);
                    showToast("❌ Server response invalid", "error");
                    return;
                }

                if (data.status === "success") {
                    showToast("✅ " + data.message + " from " + data.location, "success");
                } else {
                    showToast("❌ " + data.message, "error");
                }

            })
            .catch((err) => {
                console.error(err);
                showToast("⚠️ Network error!", "error");
            });

        });
    }

});
function showToast(message, type = "success") {

    let toast = document.createElement("div");

    toast.innerText = message;

    toast.style.position = "fixed";
    toast.style.bottom = "20px";
    toast.style.right = "20px";
    toast.style.padding = "12px 18px";
    toast.style.borderRadius = "8px";
    toast.style.color = "#fff";
    toast.style.fontSize = "14px";
    toast.style.zIndex = "9999";
    toast.style.boxShadow = "0 4px 10px rgba(0,0,0,0.2)";

    if(type === "success"){
        toast.style.background = "#28a745";
    } else {
        toast.style.background = "#dc3545";
    }

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 3000);
}
/* ✅ GLOBAL FUNCTION (OUTSIDE) */
function goToDoctor(id) {
    window.location.href = `doctor_details.php?id=${id}`;
}
</script>

</body>
</html>