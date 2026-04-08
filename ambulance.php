<?php
include "backend/db.php";

/* ================= FETCH VEHICLES ================= */
$query = "SELECT * FROM transport_vehicles WHERE status='available'";
$result = mysqli_query($conn, $query);

$vehicles = [];

while($row = mysqli_fetch_assoc($result)){
    $vehicles[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Emergency Transport - QuickAid</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
body{
    margin:0;
    background:#f4f6fb;
    font-family:'Poppins', sans-serif;
}

/* ================= HEADER (FULL STICK TOP) ================= */
header{
    position:fixed;
    top:36px; /* emergency bar এর নিচে */
    left:0;
    width:100%;
    z-index:1000;
    background:linear-gradient(90deg,#3f3f6b,#5c5ca8);
    color:white;
    padding:15px 20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 4px 12px rgba(0,0,0,0.2);
}

header h2{
    margin:0;
    font-size:20px;
}

header button{
    background:transparent;
    color:white;
    border:none;
    padding:8px 14px;
    border-radius:6px;
    cursor:pointer;
    font-weight:500;
}

header button:hover{
    background: #834364;;
}


/* push content below fixed header */
.container{
    max-width:1200px;
    margin:140px auto 40px; /* emergency bar + header space */
    padding:20px;
}

/* TITLE */
h1{
    text-align:center;
    margin-bottom:30px;
    color:#333;
}

/* ================= CARDS ================= */
.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));
    gap:25px;
}

/* ✅ SQUARE PROFESSIONAL CARD */

    .card{
        background:#e6e6ff;
        width:100%;
        max-width:290px;

        padding:15px;
        
        border-radius:6px;
        /* ✅ Mixed color border (navy + purple + maroon feel via gradient) */
        border-left:6px solid transparent;
        background-image: linear-gradient(#e6e6ff, #e6e6ff),
                        linear-gradient(180deg, #1a1f3a, #4b2a6b, #5a1a2f);
        background-origin: border-box;
        background-clip: padding-box, border-box;

        /* fallback solid border color */
        border-right:1px solid #cfcfff;
        border-top:1px solid #cfcfff;
        border-bottom:1px solid #cfcfff;

        /* ✅ Shadow using similar mixed tone */
        box-shadow: -4px 6px 12px rgba(26, 31, 58, 0.25);
        cursor:pointer;
        transition:0.25s;

        display:flex;
        flex-direction:column;
        justify-content:center;
    }

.card:hover{
    transform:translateY(-4px);
    box-shadow:0 10px 18px rgba(0,0,0,0.12);
}



.card h3{
    margin:0 0 10px;
    color:#2f2f5f;
}

.card p{
    margin:5px 0;
    font-size:14px;
    color:#444;
}
.cards{
    display:grid;
    grid-template-columns:repeat(3, 1fr); /* ✅ 3 per row */
    gap:20px;
}
/* ================= POPUP ================= */
.popup-bg{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.5);
    display:none;
    justify-content:center;
    align-items:center;
}

.popup{
    width:90%;
    max-width:420px;
    background:white;
    padding:25px;
    border-radius:10px;
    text-align:center;
    box-shadow:0 10px 25px rgba(0,0,0,0.2);
}

.popup input, .popup textarea{
    width:100%;
    padding:10px;
    margin:8px 0;
    border-radius:6px;
    border:1px solid #ccc;
}

.popup button{
    width:48%;
    padding:10px;
    border:none;
    border-radius:6px;
    color:white;
    cursor:pointer;
    font-weight:500;
}

.confirm-btn{ background:#28a745; }
.cancel-btn{ background:#dc3545; }

/* ================= SUCCESS ================= */
#successMsg{
    position:fixed;
    top:20px;
    right:20px;
    background:#28a745;
    color:white;
    padding:12px 18px;
    border-radius:8px;
    display:none;
    box-shadow:0 4px 10px rgba(0,0,0,0.2);
}

#toast{
    position:fixed;
    top:20px;
    right:20px;
    padding:12px 18px;
    border-radius:8px;
    color:#fff;
    font-weight:500;
    display:none;
    z-index:9999;
    box-shadow:0 4px 10px rgba(0,0,0,0.2);
    opacity:0;
    transition:opacity 0.5s ease;
}

#toast.success{
    background:#28a745;
}

#toast.error{
    background:#28a745;
}

/* ================= FOOTER ================= */
footer{
    margin-top:60px;
    background:#3f3f6b;
    color:white;
    text-align:center;
    padding:15px;
}

@media (max-width: 992px){
    .cards{
        grid-template-columns:repeat(2, 1fr);
    }
}

@media (max-width: 600px){
    .cards{
        grid-template-columns:repeat(1, 1fr);
    }
}
.emergency-bar{
    position:fixed;
    top:0;
    left:0;
    width:100%;
    background:#1a1f3a;
    overflow:hidden;
    z-index:1100;
    height:35px;
    display:flex;
    align-items:center;
}

.emergency-text{
    white-space:nowrap;
    color:#fff;
    font-weight:500;
    padding-left:100%;
    animation: scrollText 12s linear infinite;
}

/* left → right → repeat */
@keyframes scrollText{
    0%{
        transform: translateX(0);
        opacity:0;
    }
    10%{
        opacity:1;
    }
    90%{
        opacity:1;
    }
    100%{
        transform: translateX(-100%);
        opacity:0;
    }
}
.emergency-text span{
    cursor:pointer;
    text-decoration:underline;
    margin:0 5px;
}

.emergency-text span:hover{
    color:#ffcc00;
}
</style>
</head>

<body>
<div class="emergency-bar">
    <div class="emergency-text">
        🚨 Emergency: <span onclick="callNumber('999')">999</span> | 
        Ambulance: <span onclick="callNumber('01706583458')">01706583458</span> | 
        Fire Service: <span onclick="callNumber('161')">161</span> | 
        Police: <span onclick="callNumber('999')">999</span>
    </div>
</div>
<header>
    <button onclick="goBack()">← Back</button>
    <h2>🚑 Emergency Transport</h2>
    <div></div>
</header>

<div class="container">

    <h1>Available Transport</h1>

    <div id="vehicleSection" class="cards"></div>

</div>

<!-- POPUP -->
<div class="popup-bg" id="popupBg">
    <div class="popup">
        <h3 id="popupName"></h3>
        <div id="popupDetails"></div>

        <button class="confirm-btn" onclick="showBookingPopup()">Confirm</button>
        <button class="cancel-btn" onclick="closePopup()">Cancel</button>
    </div>
</div>

<!-- BOOKING POPUP -->
<div class="popup-bg" id="bookingPopupBg">
    <div class="popup">
        <h3>Confirm Booking</h3>

        <input type="text" id="userName" placeholder="Your Name">
        <textarea id="userAddress" placeholder="Your Address"></textarea>
        <input type="text" id="userPhone" placeholder="Your Phone Number">

        <button class="confirm-btn" onclick="finalBook()">Confirm Booking</button>
        <button class="cancel-btn" onclick="closeBookingPopup()">Cancel</button>
    </div>
</div>


<div id="toast"></div>

<footer>
    <p>© 2026 QuickAid | Emergency Transport System</p>
</footer>

<script>
    function callNumber(number){
    const confirmCall = confirm("Do you want to call " + number + "?");

    if(confirmCall){
        window.location.href = "tel:" + number;
    }
}
    function showToast(message, type="success"){
    const toast = document.getElementById("toast");

    toast.className = "";
    toast.classList.add(type);
    toast.innerText = message;

    toast.style.display = "block";

    setTimeout(() => {
        toast.style.opacity = "1";
    }, 50);

    setTimeout(() => {
        toast.style.opacity = "0";
        setTimeout(() => {
            toast.style.display = "none";
        }, 500);
    }, 2500);
}
const vehicles = <?php echo json_encode($vehicles); ?>;

let selectedVehicle = null;

/* LOAD VEHICLES */
function loadVehicles(){

    let html = "";

    vehicles.forEach(v => {

        html += `
            <div class="card" onclick="openPopup(${v.id})">
                <h3>${v.name}</h3>
                <p><b>Type:</b> ${v.vehicleType}</p>
                <p><b>Driver:</b> ${v.driverName || 'N/A'}</p>
                <p><b>Capacity:</b> ${v.capacity || 'N/A'}</p>
            </div>
        `;
    });

    document.getElementById("vehicleSection").innerHTML = html || "<p>No vehicles available</p>";
}

loadVehicles();

/* POPUP */
function openPopup(id){
    selectedVehicle = vehicles.find(v => v.id == id);

    document.getElementById("popupName").innerText = selectedVehicle.name;

    document.getElementById("popupDetails").innerHTML = `
        <p><b>Type:</b> ${selectedVehicle.vehicleType}</p>
        <p><b>Driver:</b> ${selectedVehicle.driverName}</p>
        <p><b>Phone:</b> ${selectedVehicle.driverPhone}</p>
        <p><b>Capacity:</b> ${selectedVehicle.capacity}</p>
    `;

    document.getElementById("popupBg").style.display = "flex";
}

function closePopup(){
    document.getElementById("popupBg").style.display = "none";
}

function showBookingPopup(){
    document.getElementById("bookingPopupBg").style.display = "flex";
}

function closeBookingPopup(){
    document.getElementById("bookingPopupBg").style.display = "none";
}

/* BOOKING */
function finalBook(){

    const name = document.getElementById("userName").value;
    const address = document.getElementById("userAddress").value;
    const phone = document.getElementById("userPhone").value;

    if(name === "" || address === "" || phone === ""){
        alert("Please fill all fields");
        return;
    }

    const formData = new FormData();
    formData.append("name", name);
    formData.append("address", address);
    formData.append("phone", phone); // ✅ added
    formData.append("vehicle", selectedVehicle.name);
    formData.append("driver_phone", selectedVehicle.driverPhone);

    fetch("book_ambulance.php", {
    method: "POST",
    body: formData
        })
        .then(res => res.text())
        .then(data => {

            console.log("RAW RESPONSE:", data); // debugging

            data = data.trim();

            if(data === "success"){
    closeBookingPopup();
    closePopup();
    showToast("Booking Successful ✅", "success");
} else {
    showToast("Booking Successful ✅ : " + data, "error");
}
            })
            .catch(err => {
                console.error(err);
                alert("Server error");
            });
}
/* SUCCESS */
function showSuccessMsg(){
    const msg = document.getElementById("successMsg");
    msg.style.display = "block";

    setTimeout(() => {
        msg.style.display = "none";
    }, 2500);
}

function goBack(){
    window.history.back();
}
</script>

</body>
</html>