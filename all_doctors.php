<?php
include "backend/db.php";
session_start();

/* SEARCH */
$search = isset($_GET['search']) ? $_GET['search'] : "";

/* QUERY */
$sql = "
SELECT 
d.*,
u.name,
u.phone,
u.email,
u.profilePic
FROM doctors d
LEFT JOIN users u ON d.userId = u.userId
";

if(!empty($search)){
    $search = mysqli_real_escape_string($conn, $search);
    $sql .= " WHERE u.name LIKE '%$search%'";
}

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Doctor List</title>
    <style>
      
        body{
            font-family: Arial;
            background:#f4f6f9;
            margin:0;
        }

        .header{
            background: rgba(75,75,113,0.85);
            color:#fff;
            padding:15px 20px;
            display:flex;
            align-items:center;
            justify-content:space-between;
        }

        .header a{
            color:#fff;
            text-decoration:none;
            font-weight:bold;
            background:transparent;
            padding:8px 12px;
            border-radius:5px;
        }

        .container{
            width:90%;
            margin:auto;
        }

        /* FULL WIDTH SEARCH */
        .search-box{
            margin:20px 0;
            width:100%;
        }

        .search-box form{
            display:flex;
            gap:10px;
        }

        .search-box input{
            flex:1;
            padding:12px;
            border-radius:6px;
            border:1px solid #ccc;
            font-size:14px;
        }

        .search-box button{
            padding:12px 18px;
            border:none;
            background:#49465b;
            color:#fff;
            border-radius:6px;
            cursor:pointer;
        }

        .card-container{
            display:flex;
            flex-wrap:wrap;
            gap:20px;
            justify-content:center;
        }

        .card{
            background:#fff;
            padding:20px;
            width:280px;
            border-radius:12px;
            box-shadow:0 4px 12px rgba(0,0,0,0.1);
            text-align:center;
            transition:0.3s;
        }

        .card:hover{
            transform:translateY(-5px);
        }

        /* PROFILE IMAGE */
        .profile-img{
            width:90px;
            height:90px;
            border-radius:50%;
            object-fit:cover;
            margin-bottom:10px;
            border:2px solid #49465b;
        }

        /* DEFAULT ICON (SVG STYLE) */
        .default-icon{
    width:90px;
    height:90px;
    border-radius:50%;
    background:#a18cd1;
    display:flex;
    align-items:center;
    justify-content:center;
    margin:0 auto 10px;
    border:2px solid #007bff;
    box-shadow: inset 2px 2px 5px rgba(0,0,0,0.1),
                inset -2px -2px 5px rgba(255,255,255,0.7);
}

        .default-icon svg{
            width:45px;
            height:45px;
            fill:#7a7a7a;
        }

        .card h3{
            margin:5px 0;
        }

        .card p{
            margin:5px 0;
            font-size:14px;
            color:#555;
        }

        .btn{
            display:inline-block;
            margin-top:10px;
            padding:8px 12px;
            background:#49465b;
            color:#fff;
            text-decoration:none;
            border-radius:5px;
        }

        .btn:hover{
            background:#0056b3;
        }

        .footer{
            margin-top:40px;
            background: rgba(75,75,113,0.85);
            color:#fff;
            text-align:center;
            padding:15px;
        }
    </style>
</head>
<body>

<div class="header">
    <a href="javascript:history.back()">⬅ Back</a>
    <h2>Doctor List</h2>
    <div></div>
</div>

<div class="container">

    <!-- SEARCH FULL WIDTH -->
    <div class="search-box">
        <form method="GET">
            <input type="text" name="search" placeholder="Search doctor by name..." value="<?php echo $search; ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <div class="card-container">

                                <?php if(mysqli_num_rows($result) > 0): ?>
                                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                                        <div class="card">

                                            <!-- PROFILE IMAGE / ICON -->
                                                                <?php 
                        $imgPath = "uploads/" . $row['profilePic'];
                        $hasImage = !empty($row['profilePic']) && file_exists($imgPath);
                        ?>

                        <?php if($hasImage): ?>
                            <img src="<?= $imgPath ?>" class="profile-img">
                        <?php else: ?>
                            <div class="default-icon" style="
    background: linear-gradient(135deg, rgba(73,70,91,0.15), rgba(124,120,163,0.15));
">
    <svg viewBox="0 0 24 24">
        <path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-4.4 0-8 2.2-8 5v2h16v-2c0-2.8-3.6-5-8-5z"/>
    </svg>
</div>
                        <?php endif; ?>

                    <h3><?php echo $row['name']; ?></h3>
                    <p><strong>Specialization:</strong> <?php echo $row['specialization']; ?></p>
                    <p><strong>Experience:</strong> <?php echo $row['experienceYears']; ?> years</p>
                    <p><strong>Clinic:</strong> <?php echo $row['clinic']; ?></p>
                    <p><strong>Fees:</strong> <?php echo $row['consultationFees']; ?></p>

                    <a class="btn" href="doctor_details.php?id=<?php echo $row['userId']; ?>">
                        View Details
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center;">No doctors found</p>
        <?php endif; ?>

    </div>

</div>

<div class="footer">
    <p>© <?php echo date("Y"); ?> Doctor Appointment System</p>
</div>

</body>
</html>