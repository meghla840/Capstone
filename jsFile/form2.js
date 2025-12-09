(function () {
    const STORAGE_KEY = 'quickaid_forum_posts_v1';
    const CURRENT_USER = { id: 'me', name: 'You (Demo)', role: 'user' };

    // --- Storage ---
    function loadPosts() {
        try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]'); }
        catch { return []; }
    }
    function savePosts(posts) { localStorage.setItem(STORAGE_KEY, JSON.stringify(posts)); }

    // --- Helpers ---
    function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return String(unsafe).replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;').replaceAll("'", '&#039;');
    }
    function fileToDataUrl(file) {
        if (!file) return null;
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = e => resolve(e.target.result);
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }
    function timeAgo(date) {
        const seconds = Math.floor((Date.now() - date.getTime()) / 1000);
        if (seconds < 60) return 'just now';
        const mins = Math.floor(seconds / 60);
        if (mins < 60) return `${mins}m`;
        const hrs = Math.floor(mins / 60);
        if (hrs < 24) return `${hrs}h`;
        return `${Math.floor(hrs / 24)}d`;
    }

    // --- Render Post Card ---
    function renderPostCard(post) {
        const wrapper = document.createElement('div');
        wrapper.className = 'p-4 mb-4 bg-white shadow rounded-2xl';

        // Header
        const header = document.createElement('div');
        header.className = 'flex items-center justify-between mb-3';

        const left = document.createElement('div');
        left.className = 'flex items-center gap-3';
        const avatar = document.createElement('div');
        avatar.className = 'flex items-center justify-center w-10 h-10 rounded-full bg-slate-100 text-slate-700';
        avatar.textContent = post.author.role === 'doctor' ? 'Dr' : (post.author.name || 'U').slice(0, 2);

        const meta = document.createElement('div');
        meta.innerHTML = `<div class="font-semibold">${escapeHtml(post.title)}</div>
            <div class="text-xs text-slate-500">${escapeHtml(post.author.name)} · ${escapeHtml(post.category)} · ${timeAgo(new Date(post.createdAt))}</div>`;

        left.appendChild(avatar); left.appendChild(meta);

        const right = document.createElement('div');
        if (post.author.role === 'doctor') {
            const badge = document.createElement('span');
            badge.className = 'badge badge-info';
            badge.textContent = 'Doctor';
            right.appendChild(badge);
        }

        header.appendChild(left); header.appendChild(right);

        // Body
        const body = document.createElement('div');
        body.className = 'mb-3 text-slate-700';
        body.innerHTML = `<p class="whitespace-pre-wrap">${escapeHtml(post.content)}</p>`;
        if (post.image) {
            const img = document.createElement('img');
            img.src = post.image;
            img.alt = post.title;
            img.className = 'object-cover w-full mt-3 rounded-md max-h-80';
            body.appendChild(img);
        }

        // Actions (Like, Share, Comment)
        const actions = document.createElement('div');
        actions.className = 'flex gap-3 mt-3';

        // Like button
        const likeBtn = document.createElement('button');
        likeBtn.textContent = `👍 ${post.likes}`;
        likeBtn.addEventListener('click', () => {
            if (!post.likedBy.includes(CURRENT_USER.id)) {
                post.likes++;
                post.likedBy.push(CURRENT_USER.id);
                savePosts(loadPosts().map(p => p.id === post.id ? post : p));
                likeBtn.textContent = `👍 ${post.likes}`;
            } else {
                alert('You already liked this post.');
            }
        });

        // Share button
        const shareBtn = document.createElement('button');
        shareBtn.textContent = `🔗 Share`;
        shareBtn.addEventListener('click', () => {
            navigator.clipboard.writeText(window.location.href);
            alert('Post link copied!');
        });

        // Comment button
        const commentBtn = document.createElement('button');
        commentBtn.textContent = `💬 Comments (${post.comments.length})`;
        commentBtn.addEventListener('click', () => {
            const commentText = prompt('Enter your comment:');
            if (commentText) {
                post.comments.push({ id: Date.now(), author: CURRENT_USER, text: commentText });
                savePosts(loadPosts().map(p => p.id === post.id ? post : p));
                commentBtn.textContent = `💬 Comments (${post.comments.length})`;
                renderAllPosts(currentTab, document.getElementById('category-filter')?.value || '', document.getElementById('search-input')?.value || '');
            }
        });

        actions.append(likeBtn, shareBtn, commentBtn);

        wrapper.append(header, body, actions);
        return wrapper;
    }

    // --- Render all posts ---
    let currentTab = 'all';
    function renderAllPosts(tab = 'all', categoryFilter = '', searchQuery = '') {
        currentTab = tab;
        const container = document.getElementById('posts-container');
        const empty = document.getElementById('empty-state');
        if (!container) return;
        container.innerHTML = '';

        let posts = loadPosts();

        // Tab filter
        if (tab === 'my') posts = posts.filter(p => p.author?.id === CURRENT_USER.id);
        else if (tab === 'doctor') posts = posts.filter(p => p.author?.role === 'doctor');

        // Category filter
        if (categoryFilter) posts = posts.filter(p => p.category.toLowerCase() === categoryFilter.toLowerCase());

        // Search filter
        if (searchQuery) {
            posts = posts.filter(p =>
                p.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
                p.content.toLowerCase().includes(searchQuery.toLowerCase())
            );
        }

        if (!posts.length && empty) empty.classList.remove('hidden');
        else {
            if (empty) empty.classList.add('hidden');
            posts.forEach(p => container.appendChild(renderPostCard(p)));
        }

        populateCategoryDropdown();
    }

    // --- Tabs, Search, Category ---
    function attachTabs() {
        document.querySelectorAll('#tabs .tab').forEach(t => {
            t.addEventListener('click', () => {
                document.querySelectorAll('#tabs .tab').forEach(x => x.classList.remove('active'));
                t.classList.add('active');
                const category = document.getElementById('category-filter')?.value || '';
                const search = document.getElementById('search-input')?.value || '';
                renderAllPosts(t.dataset.tab, category, search);
            });
        });
    }

    function populateCategoryDropdown() {
        const categorySelect = document.getElementById('category-filter');
        if (!categorySelect) return;
        const posts = loadPosts();
        const categories = [...new Set(posts.map(p => p.category))];
        const currentValue = categorySelect.value || '';
        categorySelect.innerHTML = `<option value="">All Categories</option>` +
            categories.map(c => `<option value="${c}">${c}</option>`).join('');
        if (currentValue) categorySelect.value = currentValue;
    }

    function attachCategoryFilter() {
        const categorySelect = document.getElementById('category-filter');
        if (!categorySelect) return;
        categorySelect.addEventListener('change', () => {
            const search = document.getElementById('search-input')?.value || '';
            renderAllPosts(currentTab, categorySelect.value, search);
        });
    }

    function attachSearch() {
        const searchInput = document.getElementById('search-input');
        const searchBtn = document.getElementById('search-btn');
        function filterPosts() {
            const category = document.getElementById('category-filter')?.value || '';
            const query = searchInput.value.trim();
            renderAllPosts(currentTab, category, query);
        }
        searchBtn?.addEventListener('click', filterPosts);
        searchInput?.addEventListener('keypress', e => { if (e.key === 'Enter') filterPosts(); });
    }

    // --- Menu toggle ---
    function attachMenuToggle() {
        const menuToggle = document.getElementById('menu-toggle');
        if (menuToggle) menuToggle.addEventListener('click', () => {
            const nav = document.getElementById('nav-links');
            if (nav) nav.classList.toggle('active');
        });
    }

    // --- Ask page ---
    async function initAskPage() {
        const form = document.getElementById('create-post-form');
        if (!form) return;

        const imageInput = document.getElementById('post-image');
        const imagePreviewContainer = document.getElementById('image-preview-container');
        const imagePreview = document.getElementById('image-preview');

        imageInput.addEventListener('change', async () => {
            const file = imageInput.files[0];
            if (!file) { imagePreviewContainer.classList.add('hidden'); return; }
            imagePreview.src = await fileToDataUrl(file);
            imagePreviewContainer.classList.remove('hidden');
        });

        const categoryTags = document.querySelectorAll('.category-tag');
        const hiddenCategory = document.getElementById('selected-category');
        categoryTags.forEach(tag => {
            tag.addEventListener('click', () => {
                categoryTags.forEach(t => t.classList.remove('bg-teal-400', 'text-white'));
                tag.classList.add('bg-teal-400', 'text-white');
                hiddenCategory.value = tag.dataset.value;
            });
        });

        form.addEventListener('submit', async e => {
            e.preventDefault();
            const title = document.getElementById('post-title').value.trim();
            const content = document.getElementById('post-content').value.trim();
            const authorType = document.getElementById('post-author-type').value;
            const category = hiddenCategory.value;
            const file = imageInput.files[0];

            if (!title || !content || !category) return alert('Please fill title, content, and select a category.');

            const dataUrl = await fileToDataUrl(file);
            const posts = loadPosts();
            posts.unshift({
                id: Date.now() + '-' + Math.floor(Math.random() * 9999),
                title, content, image: dataUrl || null, category,
                author: authorType === 'doctor' ? { id: 'doctor-demo', name: 'Dr. Demo', role: 'doctor' } : { ...CURRENT_USER },
                likes: 0, likedBy: [], comments: [], createdAt: new Date().toISOString(), shares: 0
            });
            savePosts(posts);

            // Reset form
            form.reset();
            hiddenCategory.value = '';
            categoryTags.forEach(t => t.classList.remove('bg-teal-400', 'text-white'));
            imagePreviewContainer.classList.add('hidden');
            document.getElementById('create-post-modal').checked = false;

            // Render with current filters
            const categoryFilter = document.getElementById('category-filter')?.value || '';
            const searchQuery = document.getElementById('search-input')?.value || '';
            renderAllPosts(currentTab, categoryFilter, searchQuery);
        });
    }

    // --- Init ---
    function initBrowse() { attachTabs(); attachMenuToggle(); attachSearch(); attachCategoryFilter(); renderAllPosts('all'); }
    function initAsk() { initAskPage(); attachMenuToggle(); }

    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('create-post-form')) initAsk();
        if (document.getElementById('posts-container')) initBrowse();
    });

    window.forum = { initAsk, initBrowse, loadPosts };
})();
