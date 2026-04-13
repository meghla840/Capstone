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
    display:flex;
    flex-direction:column;
    gap:12px;
    padding-bottom:100px;
}

/* CARD FIX */
.card{
    width:100%;
    background:white;
    padding:16px;
    border-radius:14px;
    box-shadow:0 10px 25px rgba(0,0,0,0.08);
    cursor:pointer;
    transition:0.2s;
}

/* MODAL FIX (IMPORTANT) */


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

/* CARD */
.card{
    background: white;
    border-radius: 16px;
    padding: 14px;
    margin: 10px auto;
    width: 92%; /* left-right gap */
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    border: 1px solid rgba(0,0,0,0.05);
    transition: all 0.25s ease;
    cursor: pointer;
}

.card:hover{
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(0,0,0,0.15);
}

.card-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    font-weight:600;
    font-size:15px;
}

.card h3{
    margin:8px 0;
    font-size:16px;
}

.card p{
    color:#555;
    font-size:13px;
    line-height:1.4;
}

.meta{
    font-size:13px;
    color:#666;
    margin-top:10px;
}

.actions{
    margin-top:10px;
    display:flex;
    gap:8px;
}



/* DOCTOR */
.doctor-badge{
    background:#0ea5e9;
    color:white;
    padding:3px 8px;
    border-radius:20px;
    font-size:11px;
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
    background:rgba(0,0,0,0.65);
    backdrop-filter: blur(8px);
    z-index:9999;
    animation: fadeIn 0.2s ease;
}

.modal-content{
    width:600px;
    max-width:95%;
    margin:5% auto;
    background:#ffffff;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 20px 60px rgba(0,0,0,0.25);
    animation: popIn 0.25s ease;
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

/* COMMENTS */
.comments{
    margin-top:12px;
    max-height:260px;
    overflow:auto;
    padding-right:5px;
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


.actions{
    display:flex;
    gap:20px;
    margin-top:14px;
    align-items:center;
}

.icon-btn{
    display:flex;
    align-items:center;
    gap:6px;
    border:none;
    background:transparent;
    cursor:pointer;
    font-size:14px;
    color:#444;
    padding:6px 10px;
    border-radius:8px;
    transition:0.2s;
}

.icon-btn:hover{
    background:#f3f4f6;
}
.footer{
    width:100%;
    text-align:center;
    padding:12px;
    margin-top:30px;
    font-size:13px;
    background: linear-gradient(90deg,#49465b,#6e6c80);
    color: #fff;
    border-top:1px solid #eee;
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

    <div class="post-content">
        ${p.content}
    </div>

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