document.addEventListener("DOMContentLoaded", () => {
    const userSection = document.getElementById("userSection");

    if (!userSection) return;

    // Check user login from localStorage
    let savedUser = null;
    try {
        savedUser = JSON.parse(localStorage.getItem("quickAidUser"));
    } catch (e) { console.error(e); }

    function renderUser() {
        if (savedUser && savedUser.username) {
            // Logged in → show profile
            userSection.innerHTML = `
                <a href="profile.html">
                    <button style="padding:6px 12px; border-radius:6px; background:#49465b; color:white; border:none; cursor:pointer;">
                        <i class="bi bi-person-circle"></i> ${savedUser.username}
                    </button>
                </a>
            `;
        } else {
            // Not logged in → show join
            userSection.innerHTML = `
                <a href="join.html"><button style="padding:6px 12px; border-radius:6px; background:#49465b; color:white; border:none; cursor:pointer;">
                    + Join Now
                </button></a>
            `;
        }
    }

    renderUser();

    // Optional: Update on logout/login dynamically
    window.addEventListener("storage", (e) => {
        if (e.key === "quickAidUser") {
            try { savedUser = JSON.parse(e.newValue); } catch { savedUser = null; }
            renderUser();
        }
    });
});
