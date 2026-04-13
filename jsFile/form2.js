(function () {
    // ✅ Get logged-in user from PHP
    const CURRENT_USER = window.LOGGED_IN_USER || { id: '', name: 'User', role: 'user' };

    const Utils = {
        escapeHtml: (unsafe) => {
            if (!unsafe) return '';
            return String(unsafe)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        },

        fileToDataUrl: (file) => {
            if (!file) return null;
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = e => resolve(e.target.result);
                reader.onerror = reject;
                reader.readAsDataURL(file);
            });
        },

        timeAgo: (dateString) => {
            const date = new Date(dateString);
            const seconds = Math.floor((Date.now() - date.getTime()) / 1000);
            if (seconds < 60) return 'just now';
            const mins = Math.floor(seconds / 60);
            if (mins < 60) return `${mins}m`;
            const hrs = Math.floor(mins / 60);
            if (hrs < 24) return `${hrs}h`;
            return `${Math.floor(hrs / 24)}d`;
        }
    };

    const Toast = {
        wrap: null,
        init: () => {
            if (!Toast.wrap) {
                Toast.wrap = document.createElement('div');
                Object.assign(Toast.wrap.style, {
                    position: 'fixed',
                    top: '20px',
                    right: '20px',
                    zIndex: 9999
                });
                document.body.appendChild(Toast.wrap);
            }
        },
        show: (message, type = 'info') => {
            Toast.init();
            const div = document.createElement('div');
            div.textContent = message;
            div.style.background = type === 'error' ? '#ef4444' : '#22c55e';
            div.style.color = '#fff';
            div.style.padding = '10px';
            div.style.marginTop = '10px';
            div.style.borderRadius = '8px';
            Toast.wrap.appendChild(div);
            setTimeout(() => div.remove(), 3000);
        }
    };

    // =========================
    // RENDER POST CARD
    // =========================
    function renderPostCard(post) {
        const wrapper = document.createElement('div');
        wrapper.className = 'p-4 mb-4 bg-white border shadow rounded-2xl border-slate-100';

        // Check if doctor for avatar and badge
        const isDoctor = post.author.role.toLowerCase() === 'doctor';

        wrapper.innerHTML = `
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-slate-100 text-slate-700 font-bold">
                        ${isDoctor ? 'Dr' : (post.author.name || 'U').slice(0, 2)}
                    </div>
                    <div>
                        <div class="font-semibold">${Utils.escapeHtml(post.title)}</div>
                        <div class="text-xs text-slate-500">
                            ${Utils.escapeHtml(post.author.name)} · 
                            <span class="capitalize">${Utils.escapeHtml(post.category)}</span> · 
                            ${Utils.timeAgo(post.createdAt)}
                        </div>
                    </div>
                </div>
                ${isDoctor ? '<span class="badge badge-info">Doctor</span>' : ''}
            </div>

            <div class="mb-3 text-slate-700">
                <p class="whitespace-pre-wrap text-sm">${Utils.escapeHtml(post.content)}</p>
                ${post.image ? `<img src="${post.image}" class="object-cover w-full mt-3 rounded-md max-h-80" />` : ''}
            </div>

            <div class="flex gap-3 mt-3">
                <button class="like-btn text-sm">👍 Like (<span>0</span>)</button>
                <button class="share-btn text-sm">🔗 Share</button>
                <button class="comment-toggle text-sm">💬 Comments (${post.comments?.length || 0})</button>
            </div>

            <div class="comments hidden mt-3">
                <div class="comment-list mb-2 text-sm border-t pt-2"></div>
                <div class="flex gap-2">
                    <input type="text" class="comment-input border p-2 rounded w-full text-sm" placeholder="Write comment..." />
                    <button class="comment-btn px-3 bg-blue-500 text-white rounded">Post</button>
                </div>
            </div>
        `;

        // Like logic
        const likeBtn = wrapper.querySelector('.like-btn');
        likeBtn.addEventListener('click', () => {
            let count = parseInt(likeBtn.querySelector('span').textContent);
            likeBtn.querySelector('span').textContent = count + 1;
        });

        // Share logic
        wrapper.querySelector('.share-btn').addEventListener('click', () => {
            navigator.clipboard.writeText(window.location.href);
            Toast.show("Link copied!", "success");
        });

        // Comment Toggle
        const toggle = wrapper.querySelector('.comment-toggle');
        const commentBox = wrapper.querySelector('.comments');
        toggle.addEventListener('click', () => commentBox.classList.toggle('hidden'));

        // Load existing comments
        const list = wrapper.querySelector('.comment-list');
        if (post.comments) {
            post.comments.forEach(c => {
                const div = document.createElement('div');
                div.className = "mb-1";
                div.innerHTML = `<b>${Utils.escapeHtml(c.author)}</b>: ${Utils.escapeHtml(c.text)}`;
                list.appendChild(div);
            });
        }

        // Add new comment
        const input = wrapper.querySelector('.comment-input');
        const btn = wrapper.querySelector('.comment-btn');
        btn.addEventListener('click', () => {
            const text = input.value.trim();
            if (!text) return;
            const div = document.createElement('div');
            div.innerHTML = `<b>${Utils.escapeHtml(CURRENT_USER.name)}</b>: ${Utils.escapeHtml(text)}`;
            list.appendChild(div);
            input.value = "";
        });

        return wrapper;
    }

    // =========================
    // BROWSE PAGE LOGIC
    // =========================
    async function renderAllPosts(tab = 'all', categoryFilter = '') {
        const container = document.getElementById('posts-container');
        const empty = document.getElementById('empty-state');
        if (!container) return;

        try {
            const res = await fetch('get_posts.php');
            let posts = await res.json();

            // Filter by Tab
            if (tab === 'my') {
                posts = posts.filter(p => String(p.author.userId) === String(CURRENT_USER.id));
            } else if (tab === 'doctor') {
                posts = posts.filter(p => p.author.role.toLowerCase() === 'doctor');
            }

            // Filter by Category Dropdown
            if (categoryFilter && categoryFilter !== '') {
                posts = posts.filter(p => p.category === categoryFilter);
            }

            container.innerHTML = '';

            if (posts.length === 0) {
                empty?.classList.remove('hidden');
            } else {
                empty?.classList.add('hidden');
                posts.forEach(p => container.appendChild(renderPostCard(p)));
            }
        } catch (e) {
            console.error("Error loading posts:", e);
        }
    }

    function initBrowse() {
        const tabs = document.querySelectorAll('#tabs .tab');
        const categoryFilter = document.getElementById('category-filter');

        tabs.forEach(t => {
            t.addEventListener('click', () => {
                tabs.forEach(x => x.classList.remove('tab-active'));
                t.classList.add('tab-active');
                renderAllPosts(t.dataset.tab, categoryFilter?.value || '');
            });
        });

        if (categoryFilter) {
            categoryFilter.addEventListener('change', () => {
                const activeTab = document.querySelector('#tabs .tab-active')?.dataset.tab || 'all';
                renderAllPosts(activeTab, categoryFilter.value);
            });
        }

        renderAllPosts('all');
    }

    // =========================
    // CREATE POST PAGE LOGIC
    // =========================
    async function initAskPage() {
        const form = document.getElementById('create-post-form');
        const hiddenInput = document.getElementById('selected-category');
        const categoryOptions = document.getElementById('category-options');

        if (!form || !hiddenInput) return;

        // Handle Category Selection (Tags)
        if (categoryOptions) {
            categoryOptions.addEventListener('click', (e) => {
                const tag = e.target.closest('.category-tag');
                if (!tag) return;

                document.querySelectorAll('.category-tag').forEach(t => t.classList.remove('active-tag'));
                tag.classList.add('active-tag');
                hiddenInput.value = tag.dataset.value;
            });
        }

        form.addEventListener('submit', async e => {
            e.preventDefault();

            const title = document.getElementById('post-title').value.trim();
            const content = document.getElementById('post-content').value.trim();
            const category = hiddenInput.value;
            const imageFile = document.getElementById('post-image').files[0];

            if (!title || !content || !category) {
                return Toast.show("Please fill title, content, and select a category", "error");
            }

            const imageData = imageFile ? await Utils.fileToDataUrl(imageFile) : null;

            const payload = { title, content, category, image: imageData };

            try {
                const res = await fetch('create_post.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const result = await res.json();
                if (result.success) {
                    Toast.show("Post created successfully!", "success");
                    setTimeout(() => location.href = "browse.php", 1200);
                } else {
                    Toast.show(result.message || "Failed to create post", "error");
                }
            } catch (err) {
                Toast.show("Connection error", "error");
            }
        });
    }

    // Initialization
    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('posts-container')) initBrowse();
        if (document.getElementById('create-post-form')) initAskPage();
    });

})();