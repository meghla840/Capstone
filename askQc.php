<?php
session_start();
include "backend/db.php";

if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$currentUserName = $_SESSION['username'];
$userRole = $_SESSION['role'] ?? 'user';

$message = "";

/* ================= POST HANDLER ================= */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = mysqli_real_escape_string($conn, $_POST['title'] ?? '');
    $content = mysqli_real_escape_string($conn, $_POST['content'] ?? '');
    $category = mysqli_real_escape_string($conn, $_POST['category'] ?? '');

    $imageName = "";

    /* -------- IMAGE UPLOAD -------- */
    if (!empty($_FILES['image']['name'])) {
        $imageName = time() . "_" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $imageName);
    }

    $sql = "INSERT INTO forum_posts (user_id, title, content, category, image)
            VALUES ('$userId', '$title', '$content', '$category', '$imageName')";
if (mysqli_query($conn, $sql)) {
    header("Location: askQc.php?success=1");
    $message = "Post created successfully!";
    exit();
} else {
    $message = "Error: " . mysqli_error($conn);
}
}

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
        
.category-tag{
    padding:12px;
    border-radius:12px;
    background:#f1f5f9;
    cursor:pointer;
    text-align:center;
    transition:0.25s;
    border:1px solid #e5e7eb;
    font-weight:500;
}

.category-tag:hover{
    background:#49465b;
    color:white;
    transform:translateY(-2px);
}

.active-tag{
    background:#49465b !important;
    color:white !important;
}
@keyframes fadeUp {
    from {opacity:0; transform:translateY(25px);}
    to {opacity:1; transform:translateY(0);}
}

.modal {
    background: rgba(0,0,0,0.45);
    backdrop-filter: blur(6px);
}

.modern-modal {
    width: 100%;
    max-width: 520px;
    border-radius: 20px;
    background: #ffffff;
    padding: 28px;
    box-shadow: 0 25px 70px rgba(0,0,0,0.25);
    animation: fadeUp 0.4s ease;
}

/* HEADER */
.modal-header {
    text-align: center;
    margin-bottom: 18px;
}

.modal-header .icon {
    width: 60px;
    height: 60px;
    margin: auto;
    border-radius: 50%;
    background: linear-gradient(135deg,#49465b,#6e6c80);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    color: white;
}

.modal-header h2 {
    margin-top: 10px;
    font-size: 20px;
    font-weight: 700;
    color: #222;
}

.modal-header p {
    font-size: 13px;
    color: #777;
}

/* FORM */
.modal-form {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.input-group label {
    font-size: 13px;
    font-weight: 600;
    color: #444;
    margin-bottom: 5px;
    display: block;
}

.input-group input,
.input-group textarea {
    width: 100%;
    padding: 12px 14px;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    outline: none;
    transition: 0.2s;
    font-size: 14px;
    background: #fff;
}

.input-group input:focus,
.input-group textarea:focus {
    border-color: #49465b;
    box-shadow: 0 0 0 3px rgba(73,70,91,0.15);
}

/* CATEGORY */
.category-grid {
    display: grid;
    grid-template-columns: repeat(2,1fr);
    gap: 10px;
}

.category-tag {
    padding: 10px;
    border-radius: 12px;
    text-align: center;
    cursor: pointer;
    border: 1px solid #e5e7eb;
    font-size: 13px;
    transition: 0.25s;
    background: #f9fafb;
}

.category-tag:hover {
    transform: translateY(-2px);
    border-color: #49465b;
    color: #49465b;
}

.active-tag {
    background: #49465b !important;
    color: #fff !important;
}

/* BUTTONS */
.modal-actions {
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
}

.btn-cancel {
    padding: 10px 14px;
    border-radius: 10px;
    background: #f3f4f6;
    cursor: pointer;
    font-size: 14px;
}

.btn-submit {
    padding: 10px 18px;
    border-radius: 12px;
    border: none;
    cursor: pointer;
    background: linear-gradient(135deg,#49465b,#6e6c80);
    color: white;
    font-weight: 600;
    transition: 0.3s;
}

.btn-submit:hover {
    transform: scale(1.05);
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
   <header style="
    position:sticky;
    top:0;
    z-index:999;
    background:#49465b;
    color:white;
    box-shadow:0 6px 18px rgba(0,0,0,0.15);
">

<div style="
    max-width:1100px;
    margin:auto;
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:12px 16px;
">

    <!-- LEFT: Back Button -->
    <button onclick="history.back()" style="
        background:rgba(255,255,255,0.15);
        border:none;
        color:white;
        padding:6px 12px;
        border-radius:8px;
        cursor:pointer;
        transition:0.3s;
    ">
        ⬅ Back
    </button>

    <!-- CENTER: Page Title -->
    <div style="
        font-size:16px;
        font-weight:600;
        letter-spacing:0.5px;
    ">
        Ask a Question
    </div>

    <!-- RIGHT: Empty balance -->
    <div style="width:70px;"></div>

</div>

</header>
<main style="
    min-height:100vh;
    background:linear-gradient(180deg,#f3f6f9,#e9eef5);
    padding:50px 20px;
">

<div style="
    max-width:900px;
    margin:auto;
    background:white;
    border-radius:22px;
    padding:40px;
    box-shadow:0 25px 60px rgba(0,0,0,0.12);
    position:relative;
    overflow:hidden;
    animation:fadeUp 0.7s ease;
">

    <!-- decorative shape -->
    <div style="
        position:absolute;
        top:-50px;
        right:-50px;
        width:140px;
        height:140px;
        background:#49465b;
        opacity:0.08;
        border-radius:50%;
    "></div>

    <div style="
        position:absolute;
        bottom:-60px;
        left:-60px;
        width:180px;
        height:180px;
        background:#6e6c80;
        opacity:0.08;
        border-radius:50%;
    "></div>

    <!-- Content -->
    <div style="text-align:center; position:relative; z-index:1;">

        <div style="
            width:70px;
            height:70px;
            margin:auto;
            border-radius:50%;
            background:linear-gradient(135deg,#49465b,#6e6c80);
            display:flex;
            align-items:center;
            justify-content:center;
            color:white;
            font-size:28px;
        ">
            🩺
        </div>

        <h1 style="margin-top:15px; color:#2b2b2b;">
            Ask a Health Question
        </h1>

        <p style="color:#777; margin-top:8px;">
            Get instant help from doctors & community experts
        </p>

        <label for="create-post-modal" style="
            display:inline-block;
            margin-top:25px;
            padding:12px 22px;
            background:linear-gradient(135deg,#49465b,#6e6c80);
            color:white;
            border-radius:12px;
            cursor:pointer;
            box-shadow:0 12px 30px rgba(0,0,0,0.15);
            transition:0.3s;
        ">
            + Create Post
        </label>

    </div>

</div>

</main>

    <input type="checkbox" id="create-post-modal" class="modal-toggle" />
    <div class="modal">
  <div class="modal-box modern-modal">

    <!-- Header -->
    <div class="modal-header">
      <div class="icon">🩺</div>
      <h2>Create Health Post</h2>
      <p>Share your concern with doctors & community experts</p>
    </div>

    <!-- FORM -->
    <form method="POST" enctype="multipart/form-data" class="modal-form">

      <div class="input-group">
        <label>Post Title</label>
        <input type="text" name="title" required placeholder="Enter your title">
      </div>

      <div class="input-group">
        <label>Description</label>
        <textarea name="content" rows="4" required placeholder="Describe your health problem..."></textarea>
      </div>

      <div class="input-group">
        <label>Upload Image (optional)</label>
        <input type="file" name="image">
      </div>

      <input type="hidden" name="category" id="selected-category">

      <!-- CATEGORY -->
      <div class="category-grid">
        <div class="category-tag" data-value="psychotherapy">Psychotherapy</div>
        <div class="category-tag" data-value="eye-health">Eye Health</div>
        <div class="category-tag" data-value="mental-health">Mental Health</div>
        <div class="category-tag" data-value="fitness">Fitness</div>
      </div>

      <!-- BUTTONS -->
      <div class="modal-actions">
        <label for="create-post-modal" class="btn-cancel">Cancel</label>
        <button type="submit" class="btn-submit">🚀 Publish Post</button>
      </div>

    </form>

  </div>
</div>
    <footer style="
    background:#1f2937;
    color:#bbb;
    text-align:center;
    padding:18px;
    font-size:13px;
">
    © 2026 QuickAid • All rights reserved
</footer>

    <script>
document.querySelectorAll(".category-tag").forEach(tag => {
    tag.addEventListener("click", function () {
        document.querySelectorAll(".category-tag").forEach(t => t.classList.remove("active-tag"));
        this.classList.add("active-tag");

        document.getElementById("selected-category").value = this.dataset.value;
    });
});
</script>

</body>

</html>