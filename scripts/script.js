// Helper utilities
const $ = (sel, ctx = document) => ctx.querySelector(sel);
const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

document.addEventListener("DOMContentLoaded", () => {

  /* -----------------------------------------
     MOBILE MENU
  ------------------------------------------*/
  const menuToggle = $("#menu-toggle");
  const navLinks = $("#nav-links");

  menuToggle?.addEventListener("click", (e) => {
    e.stopPropagation();
    const opened = navLinks.classList.toggle("open");
    menuToggle.setAttribute("aria-expanded", opened ? "true" : "false");
  });

  document.addEventListener("click", (e) => {
    if (!navLinks.contains(e.target) && navLinks.classList.contains("open")) {
      navLinks.classList.remove("open");
      menuToggle.setAttribute("aria-expanded", "false");
    }
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      navLinks.classList.remove("open");
      menuToggle?.setAttribute("aria-expanded", "false");
    }
  });

  /* -----------------------------------------
     DROPDOWNS
  ------------------------------------------*/
  $$(".drpdwn").forEach((li) => {
    const link = li.querySelector("a");
    const dd = li.querySelector(".dropdown-content");

    if (!dd) return;

    link.setAttribute("aria-haspopup", "true");

    link.addEventListener("click", (ev) => {
      ev.preventDefault();
      const opened = dd.classList.toggle("open");
      li.classList.toggle("open", opened);
      link.setAttribute("aria-expanded", opened ? "true" : "false");
    });

    document.addEventListener("click", (e) => {
      if (!li.contains(e.target)) {
        dd.classList.remove("open");
        li.classList.remove("open");
      }
    });
  });

window.addEventListener("DOMContentLoaded", () => {
    const userSection = document.getElementById("userSection");
    const savedUser = JSON.parse(localStorage.getItem("quickAidUser"));

    if(savedUser) {
        userSection.innerHTML = `
        <div style="position:relative;">
            <button id="profileBtn"><i class="bi bi-person-circle"></i> </button>
            <div id="profileDropdown" style="display:none; position:absolute; right:0; background:white; border:1px solid #ccc; border-radius:6px; margin-top:5px; min-width:150px; z-index:1000;">
                <a href="profile.html" id="goProfile">Profile</a>
                <a href="#" id="logoutBtn">Logout</a>
            </div>
        </div>
        `;

        const profileBtn = document.getElementById("profileBtn");
        const profileDropdown = document.getElementById("profileDropdown");
        const logoutBtn = document.getElementById("logoutBtn");

        profileBtn.addEventListener("click", () => {
            profileDropdown.style.display = profileDropdown.style.display === "block" ? "none" : "block";
        });

        logoutBtn.addEventListener("click", () => {
            localStorage.removeItem("quickAidUser");
            location.reload();
        });

        window.addEventListener("click", (e) => {
            if(!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)){
                profileDropdown.style.display = "none";
            }
        });
    }
});

// Emergency Modal functionality
const emergencyBtn = document.getElementById("navbarEmergencyBtn");
const modal = document.getElementById("emergencyModal");
const confirmBtn = document.getElementById("confirmCall");
const cancelBtn = document.getElementById("cancelCall");
const emergencyNumber = "+880125460586";

emergencyBtn.addEventListener("click", () => modal.style.display="flex");
confirmBtn.addEventListener("click", ()=>{ window.location.href=`tel:${emergencyNumber}`; modal.style.display="none"; });
cancelBtn.addEventListener("click", ()=>{ modal.style.display="none"; });
window.addEventListener("click", (e) => { if(e.target===modal) modal.style.display="none"; });

  /* -----------------------------------------
     HERO SLIDER
  ------------------------------------------*/
  const hero = $(".hero");
  if (hero) {
    const slides = $$(".hero .slide", hero);
    let current = 0;
    let intervalId = null;
    const AUTO_MS = 6000;

    const setActive = (i) => {
      slides.forEach((s, idx) => {
        s.classList.toggle("active", idx === i);
        s.setAttribute("aria-hidden", idx === i ? "false" : "true");
      });
      current = i;
    };

    const next = () => setActive((current + 1) % slides.length);
    const prev = () => setActive((current - 1 + slides.length) % slides.length);

    const start = () => { intervalId = setInterval(next, AUTO_MS); };
    const stop = () => { clearInterval(intervalId); };

    setActive(0);
    start();

    hero.addEventListener("mouseenter", stop);
    hero.addEventListener("mouseleave", start);
    hero.addEventListener("focusin", stop);
    hero.addEventListener("focusout", start);

    let xStart = 0, xEnd = 0;
    hero.addEventListener("touchstart", (e) => {
      stop();
      xStart = e.changedTouches[0].screenX;
    });
    hero.addEventListener("touchend", (e) => {
      xEnd = e.changedTouches[0].screenX;
      const diff = xEnd - xStart;
      if (diff > 40) prev();
      else if (diff < -40) next();
      start();
    });
  }

  /* -----------------------------------------
     SEARCH BOX
  ------------------------------------------*/
  const searchBtn = document.querySelector(".Seacrh_Section .form input[type='button']");
  const searchInput = document.querySelector(".Seacrh_Section .form input[type='text']");
  const locationSelect = $("#location");

  searchBtn?.addEventListener("click", () => {
    const q = (searchInput?.value || "").trim();
    const loc = locationSelect?.value || "";

    if (!q && !loc) {
      alert("Please type something or select a location.");
      return;
    }

    window.location.href =
      "search_results.html?query=" + encodeURIComponent(q + " " + loc);
  });

  searchInput?.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      searchBtn?.click();
    }
  });

  /* -----------------------------------------
     HOME PAGE BUTTON ACTIONS
  ------------------------------------------*/

  const go = (id, page) => {
    const btn = document.getElementById(id);
    if (!btn) return;
    btn.addEventListener("click", () => (window.location.href = page));
  };

  // Hero section
  go("locateHospitalBtn", "find_hospital.html");
  go("hospitalListBtn", "hospital_list.html");
  go("arrangeAmbulanceBtn", "ambulance.html");
  go("medicineNearbyBtn", "pharmacy.html");
  go("orderOnlineBtn", "order_medicine.html");

  // Feature boxes
  go("bookAppointmentBtn", "appointment.html");
  go("availableDoctorsBtn", "available-doctors.html");
  go("bookAmbulanceBtn", "ambulance.html");

  const callEmergency = $("#callEmergencyBtn");
  callEmergency?.addEventListener("click", () => {
    alert("Dialing emergency number...");
  });

  const trafficBtn = $("#trafficAlertBtn");
  trafficBtn?.addEventListener("click", () => {
    alert("Traffic alert submitted successfully!");
  });

  const simpleSearch = $("#searchBtn");
  simpleSearch?.addEventListener("click", () => {
    let v = document.querySelector(".search input[type='text']").value;
    if (v.trim() === "") return alert("Please type something to search.");
    window.location.href =
      "search_results.html?query=" + encodeURIComponent(v);
  });

  /* -----------------------------------------
     EMERGENCY BUTTON IN NAV
  ------------------------------------------*/
  const navEmergencyBtn = [...document.querySelectorAll("nav ul li button")]
    .find((btn) => btn.textContent.toLowerCase().includes("emergency"));

  if (navEmergencyBtn) {
    navEmergencyBtn.addEventListener("click", () => {
      const num = "+880125460586";
      if (confirm("Do you want to call the emergency number?")) {
        window.location.href = `tel:${num}`;
      }
    });
  }

  /* -----------------------------------------
     SCROLL TO DOCTORS SECTION
  ------------------------------------------*/
  $$(".see button").forEach((btn) => {
    btn.addEventListener("click", () => {
      const target = document.querySelector(".specialists_container");
      target
        ? target.scrollIntoView({ behavior: "smooth" })
        : window.scrollTo({ top: 0, behavior: "smooth" });
    });
  });

  /* -----------------------------------------
     COMPLAINT FORM
  ------------------------------------------*/
  const complaintForm = document.querySelector("footer form");
  if (complaintForm) {
    complaintForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const t = complaintForm.querySelector("textarea[name='complaint']");
      if (!t.value.trim()) return alert("Please write your complaint before submitting.");
      alert("Thank you! Your complaint has been received.");
      t.value = "";
    });
  }
});
