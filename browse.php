<?php
session_start();
include "backend/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'user';
$action = $_GET['action'] ?? "";

/* =========================
   FEED
========================= */
if ($action == "get_feed") {

    header('Content-Type: application/json');

    $offset = intval($_GET['offset'] ?? 0);

    $res = mysqli_query($conn, "
        SELECT fp.*,
        fp.user_id,
        u.name,
        u.role,

        (SELECT COUNT(*) FROM post_likes WHERE post_id=fp.id) as likes,
        (SELECT COUNT(*) FROM post_comments WHERE post_id=fp.id) as comments

        FROM forum_posts fp
        JOIN users u ON fp.user_id=u.id
        ORDER BY fp.id DESC
        LIMIT 5 OFFSET $offset
    ");

    $data = [];
    while ($r = mysqli_fetch_assoc($res)) {
        $data[] = $r;
    }

    echo json_encode($data);
    exit();
}

/* =========================
   LIKE TOGGLE
========================= */
if ($action == "like") {

    $post_id = intval($_POST['post_id']);

    $check = mysqli_query($conn,"
        SELECT id FROM post_likes
        WHERE post_id=$post_id AND user_id=$user_id
    ");

    if (mysqli_num_rows($check) > 0) {

        mysqli_query($conn,"
            DELETE FROM post_likes
            WHERE post_id=$post_id AND user_id=$user_id
        ");

    } else {

        mysqli_query($conn,"
            INSERT INTO post_likes(post_id,user_id)
            VALUES($post_id,$user_id)
        ");
    }

    // return updated count
    $count = mysqli_fetch_assoc(mysqli_query($conn,"
        SELECT COUNT(*) as total FROM post_likes WHERE post_id=$post_id
    "));

    echo json_encode(["count"=>$count['total']]);
    exit();
}

/* =========================
   COMMENT
========================= */
if ($action == "comment") {

    $post_id = intval($_POST['post_id']);
    $comment = mysqli_real_escape_string($conn,$_POST['comment']);

    mysqli_query($conn,"
        INSERT INTO post_comments(post_id,user_id,comment)
        VALUES($post_id,$user_id,'$comment')
    ");

    echo json_encode(["status"=>"ok"]);
    exit();
}

/* =========================
   GET POST (MODAL)
========================= */
if ($action == "get_post") {

    header('Content-Type: application/json');

    $id = intval($_GET['id']);

    $post = mysqli_fetch_assoc(mysqli_query($conn,"
        SELECT fp.*, u.name, u.role,
        (SELECT COUNT(*) FROM post_likes WHERE post_id=fp.id) as like_count,
        (SELECT COUNT(*) FROM post_comments WHERE post_id=fp.id) as comment_count
        FROM forum_posts fp
        JOIN users u ON fp.user_id=u.id
        WHERE fp.id=$id
    "));

    $comments = [];
    $res = mysqli_query($conn,"
        SELECT c.*, u.name, u.role
        FROM post_comments c
        JOIN users u ON c.user_id=u.id
        WHERE c.post_id=$id
        ORDER BY c.created_at DESC
    ");

    while ($r = mysqli_fetch_assoc($res)) {
        $comments[] = $r;
    }

    echo json_encode([
        "post"=>$post,
        "comments"=>$comments
    ]);
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>QuickAid Forum</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
body{
    margin:0;
    font-family:system-ui;
    background:linear-gradient(180deg,#f4f6f9,#e9eef5);
    min-height:100vh;
    overflow-x:hidden;
}
.header{
    position: sticky;
    top: 0;
    z-index: 9999;
    background: linear-gradient(90deg,#49465b,#6e6c80);
    color: #fff;
    padding: 14px 18px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    box-shadow: 0 6px 18px rgba(0,0,0,0.2);
}
/* FEED AREA FULL HEIGHT SUPPORT */
#feed{
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    padding: 20px;

    align-content: start;   /* ⭐ IMPORTANT */
}

/* HEADER FIX (sticky full width) */
.topbar{
    position:sticky;
    top:0;
    z-index:1000;
    width:100%;
    background:#49465b;
    color:white;
    box-shadow:0 10px 25px rgba(0,0,0,0.2);
}

.topbar-inner{
    max-width:900px;
    margin:auto;
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:14px 16px;
}

.title{
    font-weight:600;
    letter-spacing:0.5px;
}

.nav-btn{
    background:rgba(255,255,255,0.15);
    border:none;
    color:white;
    padding:6px 12px;
    border-radius:8px;
    cursor:pointer;
}

/* CONTAINER */
.container{
    
    margin:auto;
   
}

.card{
    position: relative;
    min-height: 200px;

    background: linear-gradient(145deg, #ffffff, #f8fafc);
    border-radius: 20px;
    padding: 18px;

    overflow: hidden;

    border: 1px solid rgba(99,102,241,0.15);

    box-shadow:
        0 10px 30px rgba(0,0,0,0.06),
        0 1px 2px rgba(0,0,0,0.04);

    display: flex;
    flex-direction: column;
    justify-content: space-between;

    transition: all 0.35s cubic-bezier(.25,.8,.25,1);

    animation: floatIn 0.6s ease-out both;
}

/* 🌈 GRADIENT BORDER GLOW */
.card::before{
    content:"";
    position:absolute;
    top:0;
    left:0;
    height:2px;
    width:100%;
    background: linear-gradient(90deg,#6366f1,#06b6d4,#22c55e,#f59e0b);
    background-size: 300% 300%;
    animation: gradientMove 4s ease infinite;
}

/* 💫 SOFT LAYER GLOW */
.card::after{
    content:"";
    position:absolute;
    inset:0;
    background: radial-gradient(circle at top left, rgba(99,102,241,0.08), transparent 60%);
    opacity:0;
    transition:0.4s;

    pointer-events: none;  /* ⭐ THIS FIXES CLICK ISSUE */
}

.card:hover::after{
    opacity:1;
}

/* 🚀 HOVER LIFT (premium feel) */
.card:hover{
    transform: translateY(-10px) scale(1.02);
    box-shadow:
        0 20px 50px rgba(0,0,0,0.12),
        0 5px 15px rgba(0,0,0,0.08);
}

/* HEADER */
.card-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    font-size:13px;
    font-weight:600;
    color:#475569;
    margin-bottom:10px;
}

/* TITLE */
.card h3{
    font-size:17px;
    font-weight:800;
    color:#0f172a;
    margin:8px 0;
    letter-spacing:0.2px;
}

/* CONTENT */
.card p{
    font-size:13.5px;
    color:#64748b;
    line-height:1.7;

    max-height: 80px;
    overflow: hidden;
}

/* ✨ ENTRY ANIMATION (smooth + modern) */
@keyframes floatIn{
    0%{
        opacity:0;
        transform: translateY(20px) scale(0.97);
        filter: blur(6px);
    }
    100%{
        opacity:1;
        transform: translateY(0) scale(1);
        filter: blur(0);
    }
}

/* 🌊 GRADIENT MOVE */
@keyframes gradientMove{
    0%{background-position:0% 50%}
    50%{background-position:100% 50%}
    100%{background-position:0% 50%}
}

.meta{
    font-size:13px;
    color:#666;
    margin-top:10px;
}

/* 🧩 ACTIONS BAR (MODERN) */
.actions{
    margin-top: auto;
    padding-top: 12px;

    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;

    border-top: 1px solid rgba(148,163,184,0.25);

    position: relative;
}

/* subtle glow line */
.actions::before{
    content:"";
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:1px;
    background: linear-gradient(90deg, transparent, rgba(99,102,241,0.4), transparent);
}

/* 🎯 ICON BUTTONS (if needed upgrade feel) */
.icon-btn{
    display:flex;
    align-items:center;
    gap:6px;

    background: rgba(248,250,252,0.8);
    border: 1px solid rgba(226,232,240,0.8);

    padding:6px 12px;
    border-radius:999px;

    font-size:13px;
    color:#334155;

    cursor:pointer;

    transition: all 0.25s ease;
}

.icon-btn:hover{
    transform: translateY(-2px);
    background: white;
    border-color: rgba(99,102,241,0.3);
    box-shadow: 0 6px 16px rgba(0,0,0,0.06);
}

/* ❤️ like active state */
.icon-btn.liked{
    background: rgba(239,68,68,0.08);
    border-color: rgba(239,68,68,0.25);
    color:#ef4444;
}

/* 🩺 DOCTOR BADGE (premium pill style) */
.doctor-badge{
    display:inline-flex;
    align-items:center;
    gap:4px;

    background: linear-gradient(135deg,#0ea5e9,#2563eb);
    color:#fff;

    font-size:11px;
    font-weight:600;

    padding:4px 10px;
    border-radius:999px;

    box-shadow: 0 4px 10px rgba(14,165,233,0.25);

    position: relative;
    overflow: hidden;
}

/* subtle shine animation */
.doctor-badge::after{
    content:"";
    position:absolute;
    top:0;
    left:-60%;
    width:50%;
    height:100%;
    background: rgba(255,255,255,0.25);
    transform: skewX(-20deg);
    animation: badgeShine 3s infinite;
}

@keyframes badgeShine{
    0%{ left:-60%; }
    50%{ left:120%; }
    100%{ left:120%; }
}

/* BUTTONS */
.btn{
    padding:7px 10px;
    border:none;
    border-radius:8px;
    cursor:pointer;
    margin-right:6px;
}

.btn-like{
    background:#ff4d6d;
    color:white;
    border:none;
    padding:6px 10px;
    border-radius:8px;
}

.btn-comment{
    background:#49465b;
    color:white;
    border:none;
    padding:6px 10px;
    border-radius:8px;
}

/* MODAL */
/* ===== MODAL UPGRADED DESIGN ===== */
.modal{
    display:none;
    position:fixed;
    inset:0;
     max-height:90vh; 
    background:rgba(0,0,0,0.65);
    backdrop-filter: blur(8px);
    z-index:9999;
    animation: fadeIn 0.2s ease;
}

.modal-content{
    width:600px;
    max-width:95%;
    margin:5% auto;
    background:#fff;
    border-radius:18px;
    overflow:hidden;

    display:flex;
    flex-direction:column;

    max-height:90vh;
}

/* HEADER */
.modal-header{
    padding:16px 18px;
    background:linear-gradient(90deg,#49465b,#6e6c80);
    color:white;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.modal-header h2{
    font-size:18px;
    margin:0;
}

.modal-close{
    cursor:pointer;
    font-size:18px;
    padding:6px 10px;
    border-radius:8px;
    transition:0.2s;
}

.modal-close:hover{
    background:rgba(255,255,255,0.15);
}

/* BODY */
.modal-body{
    padding:18px;
    overflow-y:auto;   /* ⭐ scroll enable */
    flex:1;
}

.post-author{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:10px;
    font-size:13px;
    color:#666;
}

.post-content{
    font-size:15px;
    line-height:1.6;
    color:#222;
    margin-bottom:15px;
}

/* STATS */
.modal-stats{
    display:flex;
    gap:15px;
    font-size:13px;
    color:#555;
    padding:10px 0;
    border-top:1px solid #eee;
    border-bottom:1px solid #eee;
}

/* comments scroll fix */
.comments{
    margin-top:12px;
    max-height:100px;
    overflow-y:auto;
    padding-right:5px;
}

/* comment box always visible */
.comment-box{
    display:flex;
    gap:8px;
    margin-top:12px;

    background:white;
    padding-top:10px;
    border-top:1px solid #eee;

    position: relative;   /* ⭐ NOT sticky */
    z-index: 10;
}

.comment{
    background:#f8f9fb;
    padding:10px 12px;
    border-radius:12px;
    margin-bottom:8px;
}

.comment b{
    font-size:13px;
}

.comment p{
    margin:4px 0 0;
    font-size:13px;
    color:#333;
}

.comment span{
    font-size:11px;
    color:#888;
    margin-left:6px;
}

/* INPUT */
.comment-box{
    display:flex;
    gap:8px;
    margin-top:12px;
}

.comment-box input{
    flex:1;
    padding:10px;
    border-radius:10px;
    border:1px solid #ddd;
    outline:none;
}

.comment-box button{
    background:#49465b;
    color:white;
    border:none;
    padding:10px 14px;
    border-radius:10px;
    cursor:pointer;
}

@keyframes fadeIn{
    from{opacity:0}
    to{opacity:1}
}

@keyframes popIn{
    from{transform:scale(0.95); opacity:0}
    to{transform:scale(1); opacity:1}
}
input{
    width:100%;
    padding:10px;
    border-radius:10px;
    border:1px solid #ddd;
    margin-top:10px;
}

.send-btn{
    margin-top:10px;
    padding:8px 14px;
    background:#49465b;
    color:white;
    border:none;
    border-radius:10px;
    cursor:pointer;
}


.filters{
    display:flex;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
    margin:15px 0;
    padding:10px;
    background:white;
    border-radius:12px;
    box-shadow:0 4px 12px rgba(0,0,0,0.05);
}

/* filter buttons */
.filters button{
    padding:7px 14px;
    border:none;
    border-radius:20px;
    background:#f1f1f1;
    cursor:pointer;
    font-size:13px;
    transition:0.2s;
}

.filters button:hover{
    background:#e2e2e2;
}

.filters button.active{
    background:#49465b;
    color:white;
}

/* dropdown */
.filters{
    display:flex;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
    margin:15px 0;
    padding:12px;
    background:white;
    border-radius:14px;
    box-shadow:0 6px 18px rgba(0,0,0,0.06);
}

/* BUTTONS → pill style */
.filters button{
    padding:8px 14px;
    border:none;
    border-radius:25px;
    background:#f3f4f6;
    color:#333;
    font-size:13px;
    font-weight:500;
    cursor:pointer;
    transition:0.25s;
    position:relative;
}

/* hover effect */
.filters button:hover{
    background:#e5e7eb;
    transform:translateY(-1px);
}

/* active button glow */
.filters button.active{
    background:#49465b;
    color:white;
    box-shadow:0 6px 14px rgba(73,70,91,0.3);
}

/* dropdown modern look */
.filters select{
    margin-left:auto;
    padding:8px 12px;
    border-radius:12px;
    border:1px solid #ddd;
    background:white;
    font-size:13px;
    cursor:pointer;
    outline:none;
    transition:0.2s;
}

.filters select:hover{
    border-color:#49465b;
}

   .footer{
    width:100%;
    text-align:center;
    padding:12px;
    font-size:13px;

    background: linear-gradient(90deg,#49465b,#6e6c80);
    color: #fff;

    position: fixed;
    bottom: 0;
    left: 0;

    

    box-shadow: 0 -4px 12px rgba(0,0,0,0.15);
}
@media (max-width: 900px){
    #feed{
        grid-template-columns: repeat(2, 260px);
    }
}

@media (max-width: 500px){
    #feed{
        grid-template-columns: 1fr;
    }
}


.post-content{
    font-size:15px;
    line-height:1.6;
    color:#222;
    margin-bottom:10px;

    display:-webkit-box;
    -webkit-line-clamp:2;   /* ⭐ 2 line limit */
    -webkit-box-orient: vertical;
    overflow:hidden;
}

/* expanded হলে full show */
.post-content.expanded{
    -webkit-line-clamp: unset;
}
.see-more{
    font-size:13px;
    color:#2563eb;
    cursor:pointer;
    font-weight:600;
    margin-bottom:10px;
    display:inline-block;
}
</style>
</head>

<body>

<div class="container">


<div class="header" style="display:flex; align-items:center; justify-content:flex-start; gap:15px;">
    <a href="javascript:history.back()" style="color:#fff; text-decoration:none; font-size:18px; font-weight:bold;">← Back</a>
    <h2 style="margin:0; color:#fff; flex-grow:1; text-align:center;">Browse Your Confusions</h2>
</div>




<!-- FILTER BAR -->
<div class="filters" style="margin:15px 0;">
    <button class="active" onclick="setFilter('all',this)">All Posts</button>
    <button onclick="setFilter('your',this)">Your Posts</button>
    <button onclick="setFilter('doctor',this)">Doctor Posts</button>

    <select onchange="filterCategory(this.value)" style="margin-left:10px;padding:6px;">
        <option value="">All Category</option>
        <option value="mental-health">Mental</option>
        <option value="eye-health">Eye</option>
        <option value="psychotherapy">Psychotherapy</option>
        <option value="fitness">Fitness</option>
    </select>
</div>

<div id="feed"></div>
</div>

<!-- MODAL -->
<div class="modal" id="modal">
<div class="modal-content">

<div id="modalBody"></div>
</div>
</div>
<!-- SIMPLE FOOTER -->
<div class="footer">
    © <?php echo date("Y"); ?> QuickAid | All Rights Reserved
</div>
<script>

let offset=0;
let currentFilter="all";





/* =========================
   LOAD FEED
========================= */


async function loadFeed(){

    const res = await fetch(`browse.php?action=get_feed&offset=${offset}`);
    const data = await res.json();

    data.forEach(p => {

        let doctorBadge = p.role == "doctor"
        ? `<span class="doctor-badge">Doctor</span>` : "";

        document.getElementById("feed").innerHTML += `
        <div class="card"
            data-category="${p.category || ''}"
            data-user="${p.user_id}"
            onclick="openPost(${p.id})">

            <div class="card-header">
                <b>${p.name}</b>
                ${doctorBadge}
            </div>

            <h3>${p.title}</h3>
            <p>${p.content.substring(0,120)}...</p>

            <div class="actions">

                <button class="icon-btn like-btn" id="likebtn-${p.id}"
                    onclick="event.stopPropagation(); likePost(${p.id})">

                    <i class="fa-regular fa-heart"></i>
                    <span id="like-${p.id}">${p.likes}</span>
                </button>

                <button class="icon-btn"
                    onclick="event.stopPropagation(); openPost(${p.id})">

                    <i class="fa-regular fa-comment"></i>
                    <span>${p.comments}</span>
                </button>

            </div>

        </div>`;
    });

    offset += 5;
}
/* =========================
   FILTER BUTTONS
========================= */
function setFilter(type,btn){
currentFilter=type;

document.querySelectorAll(".filters button")
.forEach(b=>b.classList.remove("active"));
btn.classList.add("active");

applyFilter();
}

/* =========================
   CATEGORY FILTER
========================= */
function filterCategory(cat){
document.querySelectorAll(".card").forEach(c=>{
c.dataset.cat=cat;
});
applyFilter();
}

/* =========================
   APPLY FILTER LOGIC
========================= */
function applyFilter(){

document.querySelectorAll(".card").forEach(card=>{

let match=true;

let category = card.dataset.category;
let user = card.dataset.user;

// filter type
if(currentFilter=="doctor" && !card.innerText.includes("Doctor")){
match=false;
}

if(currentFilter=="your" && user != "<?php echo $user_id; ?>"){
match=false;
}

// category filter dropdown
let selectedCat = document.querySelector("select").value;

if(selectedCat && category != selectedCat){
match=false;
}

card.style.display = match ? "block" : "none";
});
}
/* =========================
   LIKE
========================= */
async function likePost(id){

const res = await fetch("browse.php?action=like",{
method:"POST",
body:new URLSearchParams({post_id:id})
});

const data = await res.json();

// update count
document.getElementById("like-"+id).innerText = data.count;

const btn = document.getElementById("likebtn-"+id);
const icon = btn.querySelector("i");

btn.classList.toggle("liked");

if(btn.classList.contains("liked")){
    icon.classList.remove("fa-regular");
    icon.classList.add("fa-solid");
}else{
    icon.classList.remove("fa-solid");
    icon.classList.add("fa-regular");
}

}
/* =========================
   OPEN POST
========================= */
function openPost(id){

fetch(`browse.php?action=get_post&id=${id}`)
.then(res => res.json())
.then(data => {

let p = data.post;

let html = `
<div class="modal-header">
    <h2>${p.title}</h2>
    <span onclick="closeModal()" class="modal-close">✖</span>
</div>

<div class="modal-body">

    <div class="post-author">
        <div>
            <b>${p.name}</b>
            <span>${p.role == "doctor" ? "🩺 Doctor" : "👤 User"}</span>
        </div>
        <div style="font-size:12px;color:#aaa;">
            Post ID #${p.id}
        </div>
    </div>

    <div class="post-content" id="content-${p.id}">
                ${p.content}
            </div>

            <span class="see-more" id="toggle-${p.id}"
            onclick="toggleContent(${p.id})">
                See more
            </span>

    <div class="modal-stats">
        <div>❤️ Likes: ${p.like_count}</div>
        <div>💬 Comments: ${p.comment_count}</div>
    </div>

    <h4 style="margin-top:15px;">Comments</h4>

    <div class="comments">
`;

data.comments.forEach(c => {
html += `
<div class="comment">
    <b>${c.name}</b>
    <span>${c.role == "doctor" ? "Doctor" : ""}</span>
    <p>${c.comment}</p>
</div>`;
});

html += `
    </div>

    <div class="comment-box">
        <input id="cmt" placeholder="Write a helpful comment...">
        <button onclick="comment(${id})">Send</button>
    </div>

</div>
`;

document.getElementById("modalBody").innerHTML = html;
document.getElementById("modal").style.display = "block";
setTimeout(() => {
    let el = document.getElementById("content-"+id);
    let btn = document.getElementById("toggle-"+id);

    if(el && btn){
        if(el.innerText.length < 120){
            btn.style.display = "none";
        }
    }
}, 50);
});
}
/* FIX CLOSE */
function closeModal(){
document.getElementById("modal").style.display="none";
}

/* CLICK OUTSIDE CLOSE */
document.getElementById("modal").addEventListener("click", function(e){
    if(e.target === this){
        closeModal();
    }
});

/* ESC CLOSE */
document.addEventListener("keydown", function(e){
    if(e.key === "Escape"){
        closeModal();
    }
});

/* COMMENT */
async function comment(id){

let c=document.getElementById("cmt").value;

await fetch("browse.php?action=comment",{
method:"POST",
body:new URLSearchParams({
post_id:id,
comment:c
})
});

openPost(id);
}



let loading = false;

window.addEventListener("scroll", () => {
    if (loading) return;

    if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 200) {
        loadMore();
    }
});


function toggleContent(id){

    let content = document.getElementById("content-"+id);
    let btn = document.getElementById("toggle-"+id);

    content.classList.toggle("expanded");

    if(content.classList.contains("expanded")){
        btn.innerText = "See less";
    }else{
        btn.innerText = "See more";
    }
}

function loadMore() {
    loading = true;
    loadFeed().then(() => {
        loading = false;
    });
}

window.onload=loadFeed;

</script>

</body>
</html>