
<?php
session_start();
include "backend/db.php";

// ❌ যদি login না থাকে
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ user info fetch
$stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$name = $user['name'];

$success = "";

if(isset($_POST['sendMsg'])){
    $message = htmlspecialchars(trim($_POST['message']));

    if(!empty($message)){
        $stmt = $conn->prepare("INSERT INTO msg (user_id, name, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $name, $message);
        $stmt->execute();

        // 🔥 IMPORTANT: redirect after insert
        header("Location: ".$_SERVER['PHP_SELF']."?success=1");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Get in Touch - QuickAid</title>

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
body { background: #f7f7fc; color: #1f2937; }

/* Sticky Header */
.header { 
    position: sticky;
    top: 0;
    background: rgba(75, 75, 113, 0.85);
    color: white; 
    text-align: center; 
    padding: 15px 15px;
    z-index: 100;
    border-radius: 0 0 20px 20px;
    backdrop-filter: blur(6px);
}
.header h1 { font-size: 1.6rem; }
.header h1 span { color: #f7d774; }
.header p { opacity: 0.85; margin-top: 6px; font-weight: 400; font-size: 0.9rem; }

/* Back button */
.back-btn { 
    position: absolute; 
    left: 15px; 
    top: 15px; 
    background: transparent; 
    color: white; 
    border: none; 
    padding: 6px 12px; 
    border-radius: 8px; 
    cursor: pointer; 
    display: flex; 
    align-items: center; 
    gap: 5px;
    font-weight: 500;
    font-size: 0.85rem;
    transition: 0.3s;
}
.back-btn:hover { background: rgba(255,255,255,0.45); }

/* Contact cards */
.contact-grid { 
    margin: 25px auto; 
    display: grid; 
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); 
    gap: 20px; 
    max-width: 1200px;
}
.contact-card { 
    background: white; 
    padding: 25px 20px; 
    border-radius: 18px; 
    text-align: center; 
    box-shadow: 0 6px 18px rgba(0,0,0,0.08); 
    transition: all 0.3s ease;
    border-top: 4px solid #4b5563;
}
.contact-card:hover { 
    transform: translateY(-4px); 
    box-shadow: 0 10px 24px rgba(0,0,0,0.14); 
}
.contact-card i { font-size: 2.2rem; color: #4b5563; margin-bottom: 12px; }
.contact-card h3 { margin-bottom: 8px; font-weight: 600; font-size: 1.1rem; color: #111827; }
.contact-card p { font-size: 0.95rem; color: #4b5563; margin-bottom: 10px; }
.contact-card a { text-decoration: none; font-weight: 900; color: #081c38; transition: 0.3s; }
.contact-card a:hover { color: #2c2f3b; }
.highlight { color: #4b5563; font-weight: 700; }

/* Form */
.message-box { 
    background: white; 
    padding: 35px; 
    margin: 40px auto; 
    border-radius: 18px; 
    box-shadow: 0 6px 20px rgba(0,0,0,0.07); 
    max-width: 700px; 
}
.message-box h2 { margin-bottom: 20px; font-size: 1.75rem; font-weight: 600; color: #111827; text-align: center; }

.form-group { margin-bottom: 16px; }
input, textarea { 
    width: 100%; 
    padding: 13px; 
    border-radius: 10px; 
    border: 1px solid #dcdce7; 
    outline: none; 
    font-size: 0.95rem; 
    background-color: #f9fafb;
}
input:focus, textarea:focus { border-color: #4b5563; box-shadow: 0 0 6px rgba(75,85,99,0.2); }
textarea { height: 140px; resize: vertical; }

.btn { 
    width: 100%; 
    padding: 14px; 
    background: #4b4b71; 
    border: none; 
    color: white; 
    font-size: 1rem; 
    font-weight: 600;
    border-radius: 10px; 
    cursor: pointer; 
    transition: 0.3s;
}
.btn:hover { background: #3a3a5a; }

.success { 
    margin-top: 12px; 
    color: #0f9d58; 
    text-align: center; 
    font-weight: 600; 
    font-size: 0.95rem;
}

/* Footer sticky */
.footer { 
    text-align: center; 
    padding: 20px; 
    color: white; 
    font-size: 0.9rem; 
    background: #4b4b71; 
    position: sticky; 
    bottom: 0; 
    width: 100%;
}

/* Chat popup center */
.chat-popup { 
    display: none; 
    position: fixed; 
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    border: 1px solid #ccc; 
    border-radius: 12px; 
    width: 350px; 
    max-height: 450px;
    background: white; 
    box-shadow: 0 6px 18px rgba(0,0,0,0.12); 
    z-index: 1000; 
    flex-direction: column; 
}
.chat-header { 
    background: #4b5563; 
    color: white; 
    padding: 10px; 
    border-radius: 12px 12px 0 0; 
    font-weight: 600; 
    text-align: center; 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
}
.chat-header button { 
    background: #ef4444; 
    color: white; 
    border: none; 
    padding: 4px 8px; 
    border-radius: 6px; 
    cursor: pointer; 
    font-size: 1.2rem; 
    line-height: 1;
}
.chat-messages { flex: 1; padding: 10px; overflow-y: auto; font-size: 0.9rem; }
.chat-input { display: flex; padding: 8px; border-top: 1px solid #ddd; }
.chat-input input { flex: 1; padding: 6px 8px; border-radius: 6px; border: 1px solid #ccc; font-size: 0.9rem; }
.chat-input button { background: #4b5563; color: white; border: none; padding: 6px 10px; margin-left: 5px; border-radius: 6px; cursor: pointer; }

@media(max-width:480px){
    .header h1 { font-size: 1.6rem; }
    .message-box { padding: 25px 20px; }
    .contact-card i { font-size: 2rem; }
    .chat-popup { width: 90%; right: 5%; bottom: 50%; }
}
</style>
</head>
<body>

<header class="header">
    <button class="back-btn" onclick="window.history.back()"><i class="bi bi-arrow-left"></i> Back</button>
    <div class="container">
        <h1>Contact <span>QuickAid</span></h1>
        <p>Your health & emergency support partner — always ready to help.</p>
    </div>
</header>

<main class="container">

<section class="contact-grid">
    <div class="contact-card">
        <i class="bi bi-envelope-fill"></i>
        <h3>Email Us</h3>
        <p>We respond within <span class="highlight">24 hours</span>.</p>
        <a href="mailto:support@quickaid.com">support@quickaid.com</a>
    </div>
    <div class="contact-card">
        <i class="bi bi-telephone-fill"></i>
        <h3>Call Support</h3>
        <p>Emergency help <span class="highlight">24/7</span>.</p>
        <a href="tel:+880125460586">+880 1254-60586</a>
    </div>
    <div class="contact-card">
        <i class="bi bi-chat-dots-fill"></i>
        <h3>Live Chat</h3>
        <p>Chat with our support team or AI assistant.</p>
        <a href="#" id="openChat">Start Chat</a>
    </div>
</section>

<section class="message-box">
    <h2>Send us a message</h2>

    <form method="POST">
    <div class="form-group">
        <textarea name="message" placeholder="Write your message..." required><?= isset($success) ? '' : '' ?></textarea>
    </div>
    <button type="submit" name="sendMsg" class="btn">Send Message</button>
</form>

<?php if(!empty($success)): ?>
    <div class="success-box"><?= $success ?></div>
<?php endif; ?>
    
</section>
</main>

<footer class="footer">
    <p>&copy; 2025 QuickAid. All rights reserved.</p>
</footer>

<!-- Chat Popup -->
<div class="chat-popup" id="chatPopup">
    <div class="chat-header">
        <span>Live Support</span>
        <button id="closeChat">&times;</button>
    </div>
    <div class="chat-messages" id="chatMessages"></div>
    <div class="chat-input">
        <input type="text" id="chatInput" placeholder="Type a message..." />
        <button id="sendChat">Send</button>
    </div>
</div>

<!-- ONLY CHANGED PARTS: CHAT SIZE + SCRIPT FIX -->

<style>
    /* 🔥 BIGGER MODERN CHATBOX */
    .chat-popup {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 600px;
        /* BIGGER */
        height: 500px;
        /* BIGGER */
        background: white;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        z-index: 1000;
        flex-direction: column;
    }

    .chat-header {
        background: #4b5563;
        color: white;
        padding: 12px;
        border-radius: 16px 16px 0 0;
        display: flex;
        justify-content: space-between;
    }

   .chat-messages {
    flex: 1;
    padding: 15px;
    overflow-y: auto;
    font-size: 0.95rem;
    display: flex;
    flex-direction: column;
}

    .chat-input {
        display: flex;
        padding: 10px;
        border-top: 1px solid #ddd;
    }

    .chat-input input {
        flex: 1;
        padding: 10px;
        border-radius: 8px;
    }

    .chat-input button {
        padding: 10px 15px;
    }

    @media(max-width:600px) {
        .chat-popup {
            width: 95%;
            height: 80%;
        }
    }
</style>

<script>


    // ===== Chat Popup =====
    const chatPopup = document.getElementById("chatPopup");

    document.getElementById("openChat").addEventListener("click", function (e) {
        e.preventDefault();
        chatPopup.style.display = "flex";
    });

    document.getElementById("closeChat").addEventListener("click", function () {
        chatPopup.style.display = "none";
    });

    // ===== AI LOGIC =====
    function getAIReply(msg) {
        msg = msg.toLowerCase();

        if (msg.includes("hi") || msg.includes("hello")) {
            return "Hello 👋 I'm QuickAid AI. Tell me your symptoms.";
        }

        if (msg.includes("emergency")) {
            return "🚨 Call emergency immediately!";
        }

        const issues = [
            { keywords: ["fever"], reply: "🤒 Fever: rest + water. High? see doctor." },
            { keywords: ["cough"], reply: "🤧 Cough: fluids + rest." },
            { keywords: ["headache"], reply: "🧠 Headache: rest + hydration." },
            { keywords: ["burn"], reply: "🔥 Cool with water 20 mins." },
            { keywords: ["cut"], reply: "🩸 Clean + bandage." },
            { keywords: ["chest pain"], reply: "❤️ EMERGENCY! Go hospital NOW." },
            { keywords: ["breathing"], reply: "💨 Breathing issue = emergency." },
            { keywords: ["diarrhea"], reply: "💦 Drink ORS." },
            { keywords: ["vomit"], reply: "🤮 Hydrate slowly." },
            { keywords: ["weak"], reply: "😴 Rest + food." },
            { keywords: ["stress"], reply: "🧘 Relax + breathing." },
            { keywords: ["tooth"], reply: "🦷 See dentist." },
            { keywords: ["ear"], reply: "👂 Avoid touching, doctor if pain." },
            { keywords: ["eye"], reply: "👁️ Avoid strain." },
            { keywords: ["rash"], reply: "🩹 Skin irritation care needed." }
        ];

        for (let issue of issues) {
            for (let kw of issue.keywords) {
                if (msg.includes(kw)) return issue.reply;
            }
        }

        return "Describe more symptoms 🙂";
    }

    // ===== CHAT SEND =====
   document.getElementById("sendChat").addEventListener("click", async function () {
    const input = document.getElementById("chatInput");
    const msg = input.value.trim();
    if (!msg) return;

    const messagesDiv = document.getElementById("chatMessages");

    // ===== USER MESSAGE =====
    const userWrapper = document.createElement("div");
    userWrapper.style.display = "flex";
    userWrapper.style.justifyContent = "flex-end";
    userWrapper.style.marginBottom = "10px";

    const userMsg = document.createElement("div");
    userMsg.style.background = "#4b5563";
    userMsg.style.color = "white";
    userMsg.style.padding = "10px 14px";
    userMsg.style.borderRadius = "18px 18px 0 18px";
    userMsg.style.maxWidth = "70%";
    userMsg.innerText = msg;

    userWrapper.appendChild(userMsg);
    messagesDiv.appendChild(userWrapper);

    input.value = "";

    // ===== TYPING =====
    const typing = document.createElement("div");
    typing.innerText = "QuickAid AI is typing...";
    typing.style.fontSize = "0.8rem";
    typing.style.margin = "5px";
    messagesDiv.appendChild(typing);

    messagesDiv.scrollTop = messagesDiv.scrollHeight;

    try {
        const response = await fetch("chat.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ message: msg })
        });

        const data = await response.json();
        typing.remove();

        // ===== PARSE AI RESPONSE =====
        let aiData;

        try {
            aiData = JSON.parse(data.reply);
        } catch {
            aiData = {
                doctor: "Medicine",
                message: data.reply
            };
        }

        const doctorType = aiData.doctor || "Medicine";
        const aiMessage = aiData.message || data.reply;

        // ===== SHOW AI MESSAGE =====
        const aiWrapper = document.createElement("div");
        aiWrapper.style.display = "flex";
        aiWrapper.style.justifyContent = "flex-start";
        aiWrapper.style.marginBottom = "10px";

        const aiMsg = document.createElement("div");
        aiMsg.style.background = "#e5e7eb";
        aiMsg.style.color = "#111";
        aiMsg.style.padding = "10px 14px";
        aiMsg.style.borderRadius = "18px 18px 18px 0";
        aiMsg.style.maxWidth = "70%";
        aiMsg.innerText = aiMessage;

        aiWrapper.appendChild(aiMsg);
        messagesDiv.appendChild(aiWrapper);

        // ===== FETCH DOCTORS =====
        const res2 = await fetch(`get_doctors.php?type=${doctorType}`);
        const doctors = await res2.json();

        // ===== SHOW DOCTORS =====
        if (doctors.length > 0) {

            const doctorWrapper = document.createElement("div");
            doctorWrapper.style.margin = "10px 0";

            const title = document.createElement("div");
            title.innerText = "Recommended Doctors:";
            title.style.fontWeight = "bold";
            title.style.marginBottom = "5px";

            doctorWrapper.appendChild(title);

            doctors.forEach(doc => {
                const card = document.createElement("div");
                card.style.border = "1px solid #ddd";
                card.style.borderRadius = "10px";
                card.style.padding = "8px";
                card.style.marginBottom = "6px";
                card.style.background = "#f9fafb";

               card.innerHTML = `
    <div><b>👨‍⚕️ Dr. ${doc.name}</b></div>
    <div>Specialist: ${doc.specialization}</div>
    <div>🏥 Clinic: ${doc.clinic || "Not provided"}</div>
    <div>💰 Fees: ৳${doc.fees || "Not set"}</div>
    <div>🆔 BMDC: ${doc.bmdc}</div>
`;
                doctorWrapper.appendChild(card);
            });

            messagesDiv.appendChild(doctorWrapper);
        }

        messagesDiv.scrollTop = messagesDiv.scrollHeight;

    } catch (error) {
        typing.innerText = "⚠️ Error connecting to AI.";
    }
});
</script>

</body>
</html>