const API_KEY = "e9f40be6ba7546179e706aec199637dc";

async function loadHealthNews() {
    const url = `https://newsapi.org/v2/top-headlines?category=health&country=us&apiKey=${API_KEY}`;

    const container = document.querySelector(".article_container");

    // Loading UI
    container.innerHTML = "<p style='text-align:center;'>Loading latest health news...</p>";

    try {
        const response = await fetch(url);
        const data = await response.json();

        container.innerHTML = "";

        data.articles.forEach(article => {

            const image = article.urlToImage ? article.urlToImage : "images/default.jpg";
            const date = new Date(article.publishedAt).toDateString();

            const card = `
<div class="card">
    <div class="img-container">
        <img class="main-img" src="${image}" alt="Health Image">
    </div>

    <div class="content">
        <div class="category">Health</div>
        <div class="title">${article.title}</div>
        <div class="date"><i class="bi bi-calendar-week"></i> ${date}</div>
    </div>

    <div class="card-actions">
        <div class="like-btn" onclick="toggleLike(this)">
            <i class="bi bi-heart"></i>
        </div>
        <a href="${article.url}" target="_blank" class="read-btn">Read More</a>
    </div>
</div>
`;

            container.innerHTML += card;
        });

    } catch (error) {
        container.innerHTML = "<p style='color:red; text-align:center;'>Failed to load news</p>";
        console.error(error);
    }




}
function toggleLike(element) {
    const icon = element.querySelector("i");

    if (icon.classList.contains("bi-heart")) {
        icon.classList.remove("bi-heart");
        icon.classList.add("bi-heart-fill");
        icon.style.color = "red";
    } else {
        icon.classList.remove("bi-heart-fill");
        icon.classList.add("bi-heart");
        icon.style.color = "";
    }
}

// Call function
loadHealthNews();