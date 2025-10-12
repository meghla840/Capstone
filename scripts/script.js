
document.getElementById("menu-toggle").addEventListener("click", function () {
  document.getElementById("nav-links").classList.toggle("active");
});


document.querySelectorAll(".drpdwn > a").forEach(function (dropdownLink) {
  dropdownLink.addEventListener("click", function (e) {
    if (window.innerWidth <= 768) {
      e.preventDefault(); 
      let dropdown = this.nextElementSibling;
      dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
    }
  });
});
