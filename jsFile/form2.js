(function () {
    const STORAGE_KEY = 'quickaid_forum_posts_v1';
    const CURRENT_USER = { id: 'me', name: 'You (Demo)', role: 'user' };

    // Load posts from localStorage
    function loadPosts() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            return raw ? JSON.parse(raw) : [];
        } catch (e) { return []; }
    }

    // Save posts to localStorage
    function savePosts(posts) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(posts));
    }

    // Escape HTML
    function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return String(unsafe)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }
    
    function highlightMatch(text, query) {
        if (!query) return escapeHtml(text);
        const regex = new RegExp(`(${query})`, 'gi');
        return escapeHtml(text).replace(regex, '<span class="bg-yellow-200">$1</span>');
    }

    // File to data URL
    async function fileToDataUrl(file) {
        if (!file) return null;
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = e => resolve(e.target.result);
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }

    // Initialize Ask Page
    async function initAskPage() {
        const form = document.getElementById('create-post-form');
        if (!form) return;

        const imageInput = document.getElementById('post-image');
        const imagePreviewContainer = document.getElementById('image-preview-container');
        const imagePreview = document.getElementById('image-preview');

        // Image preview
        imageInput.addEventListener('change', async () => {
            const file = imageInput.files[0];
            if (!file) {
                imagePreviewContainer.classList.add('hidden');
                return;
            }
            const url = await fileToDataUrl(file);
            imagePreview.src = url;
            imagePreviewContainer.classList.remove('hidden');
        });

        // Category selection
        const categoryTags = document.querySelectorAll('.category-tag');
        const hiddenCategory = document.getElementById('selected-category');

        categoryTags.forEach(tag => {
            tag.addEventListener('click', () => {
                categoryTags.forEach(t => t.classList.remove('bg-teal-400', 'text-white'));
                tag.classList.add('bg-teal-400', 'text-white');
                hiddenCategory.value = tag.dataset.value;
            });
        });

        // Submit form
        form.addEventListener('submit', async e => {
            e.preventDefault();
            const title = document.getElementById('post-title').value.trim();
            const content = document.getElementById('post-content').value.trim();
            const authorType = document.getElementById('post-author-type').value;
            const category = hiddenCategory.value;
            const file = imageInput.files[0];

            if (!title || !content || !category) {
                return alert('Please fill title, content, and select a category.');
            }

            const dataUrl = await fileToDataUrl(file);
            const posts = loadPosts();

            const post = {
                id: Date.now() + '-' + Math.floor(Math.random() * 9999),
                title,
                content,
                image: dataUrl || null,
                category,
                author: authorType === 'doctor'
                    ? { id: 'doctor-demo', name: 'Dr. Demo', role: 'doctor' }
                    : { ...CURRENT_USER },
                likes: 0,
                likedBy: [],
                comments: [],
                createdAt: new Date().toISOString(),
                shares: 0
            };

            posts.unshift(post);
            savePosts(posts);

            // Reset modal
            form.reset();
            hiddenCategory.value = '';
            categoryTags.forEach(t => t.classList.remove('bg-teal-400', 'text-white'));
            imagePreviewContainer.classList.add('hidden');
            const modalCheckbox = document.getElementById('create-post-modal');
            if (modalCheckbox) modalCheckbox.checked = false;

            renderAllPosts(currentTab); // render without reload
        });
    }

    // Render post card
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
        left.appendChild(avatar);
        left.appendChild(meta);

        const right = document.createElement('div');
        if (post.author.role === 'doctor') {
            const badge = document.createElement('span');
            badge.className = 'badge badge-info';
            badge.textContent = 'Doctor';
            right.appendChild(badge);
        }
        header.appendChild(left);
        header.appendChild(right);

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

        // Actions
        const actions = document.createElement('div');
        actions.className = 'flex items-center justify-between gap-2 mt-3';
        const leftActions = document.createElement('div');
        leftActions.className = 'flex items-center gap-3';

        const likeBtn = document.createElement('button');
        likeBtn.className = 'gap-2 btn btn-ghost btn-sm';
        likeBtn.innerHTML = `<i class="bi bi-hand-thumbs-up"></i> <span class="like-count">${post.likes}</span>`;
        likeBtn.addEventListener('click', () => toggleLike(post.id, likeBtn));

        const commentBtn = document.createElement('button');
        commentBtn.className = 'gap-2 btn btn-ghost btn-sm';
        commentBtn.innerHTML = `<i class="bi bi-chat-left-text"></i> <span>${post.comments.length}</span>`;
        commentBtn.addEventListener('click', () => {
            const commentSection = wrapper.querySelector('.comments-section');
            commentSection.classList.toggle('hidden');
        });

        const shareBtn = document.createElement('button');
        shareBtn.className = 'gap-2 btn btn-ghost btn-sm';
        shareBtn.innerHTML = `<i class="bi bi-share"></i> <span>${post.shares}</span>`;
        shareBtn.addEventListener('click', () => sharePost(post.id));

        leftActions.append(likeBtn, commentBtn, shareBtn);

        const rightActions = document.createElement('div');
        rightActions.className = 'text-xs text-slate-500';
        rightActions.textContent = new Date(post.createdAt).toLocaleString();

        actions.append(leftActions, rightActions);

        // Comments section
        const commentsSection = document.createElement('div');
        commentsSection.className = 'hidden mt-3 comments-section';
        const commentsList = document.createElement('div');
        commentsList.className = 'space-y-2';
        post.comments.forEach(c => {
            const cdiv = document.createElement('div');
            cdiv.className = 'p-2 text-xs rounded-md bg-slate-50';
            cdiv.innerHTML = `<strong>${escapeHtml(c.by)}</strong> · ${escapeHtml(c.text)}`;
            commentsList.appendChild(cdiv);
        });

        // Add comment input
        const inputWrapper = document.createElement('div');
        inputWrapper.className = 'flex gap-2 mt-2';
        const input = document.createElement('input');
        input.type = 'text';
        input.placeholder = 'Write a comment...';
        input.className = 'flex-1 input input-sm input-bordered';
        const submitBtn = document.createElement('button');
        submitBtn.className = 'btn btn-sm';
        submitBtn.textContent = 'Post';
        submitBtn.addEventListener('click', () => {
            const text = input.value.trim();
            if (!text) return;
            addComment(post.id, text);
        });
        inputWrapper.append(input, submitBtn);

        commentsSection.append(commentsList, inputWrapper);

        wrapper.append(header, body, actions, commentsSection);

        return wrapper;
    }

    // Time ago
    function timeAgo(date) {
        const seconds = Math.floor((Date.now() - date.getTime()) / 1000);
        if (seconds < 60) return 'just now';
        const mins = Math.floor(seconds / 60);
        if (mins < 60) return `${mins}m`;
        const hrs = Math.floor(mins / 60);
        if (hrs < 24) return `${hrs}h`;
        return `${Math.floor(hrs / 24)}d`;
    }

    // Add comment
    function addComment(postId, text) {
        const posts = loadPosts();
        const post = posts.find(p => p.id === postId);
        if (!post) return;
        post.comments.push({ by: CURRENT_USER.name, text, at: new Date().toISOString() });
        savePosts(posts);
        renderAllPosts(currentTab);
    }

    // Toggle like
    function toggleLike(postId, btn) {
        const posts = loadPosts();
        const p = posts.find(x => x.id === postId);
        if (!p) return;
        const idx = p.likedBy.indexOf(CURRENT_USER.id);
        if (idx === -1) p.likedBy.push(CURRENT_USER.id);
        else p.likedBy.splice(idx, 1);
        p.likes = p.likedBy.length;
        savePosts(posts);
        btn.querySelector('.like-count').textContent = p.likes;
    }

    // Share post
    function sharePost(postId) {
        const posts = loadPosts();
        const original = posts.find(p => p.id === postId);
        if (!original) return;
        const copy = JSON.parse(JSON.stringify(original));
        copy.id = Date.now() + '-sh-' + Math.floor(Math.random() * 9999);
        copy.author = { ...CURRENT_USER };
        copy.shares = 0;
        copy.createdAt = new Date().toISOString();
        posts.unshift(copy);
        original.shares += 1;
        savePosts(posts);
        renderAllPosts(currentTab);
    }

    // Render all posts
    let currentTab = 'all';
    function renderAllPosts(tab = 'all') {
        currentTab = tab;
        const container = document.getElementById('posts-container');
        const empty = document.getElementById('empty-state');
        if (!container) return;
        container.innerHTML = '';

        const posts = loadPosts();
        let filtered = posts;
        if (tab === 'my') filtered = posts.filter(p => p.author?.id === CURRENT_USER.id);
        else if (tab === 'doctor') filtered = posts.filter(p => p.author?.role === 'doctor');

        if (!filtered.length && empty) empty.classList.remove('hidden');
        else {
            if (empty) empty.classList.add('hidden');
            filtered.forEach(p => container.appendChild(renderPostCard(p)));
        }
    }

    // Tabs
    function attachTabs() {
        document.querySelectorAll('#tabs .tab').forEach(t => {
            t.addEventListener('click', () => {
                document.querySelectorAll('#tabs .tab').forEach(x => x.classList.remove('active'));
                t.classList.add('active');
                renderAllPosts(t.dataset.tab);
            });
        });
    }

    // Search posts by category
 
    function attachSearch() {
        const searchInput = document.getElementById('search-input');
        const searchBtn = document.getElementById('search-btn');
        if (!searchInput || !searchBtn) return;

        const filterPosts = () => {
            const query = searchInput.value.trim().toLowerCase();
            const posts = loadPosts();
            let filtered;

            if (!query) filtered = posts.filter(p => {
                if (currentTab === 'my') return p.author?.id === CURRENT_USER.id;
                if (currentTab === 'doctor') return p.author?.role === 'doctor';
                return true;
            });
            else filtered = posts.filter(p =>
                p.category?.toLowerCase().includes(query) &&
                (currentTab === 'my' ? p.author?.id === CURRENT_USER.id :
                    currentTab === 'doctor' ? p.author?.role === 'doctor' : true)
            );

            const container = document.getElementById('posts-container');
            const empty = document.getElementById('empty-state');
            container.innerHTML = '';
            if (!filtered.length && empty) empty.classList.remove('hidden');
            else {
                if (empty) empty.classList.add('hidden');
                filtered.forEach(p => container.appendChild(renderPostCard(p)));
            }
        };

        searchBtn.addEventListener('click', filterPosts);
        searchInput.addEventListener('keyup', filterPosts); // live filtering as you type
    }


    // Menu toggle for mobile
    function attachMenuToggle() {
        const menuToggle = document.getElementById('menu-toggle');
        if (menuToggle) menuToggle.addEventListener('click', () => {
            const nav = document.getElementById('nav-links');
            if (nav) nav.classList.toggle('active');
        });
    }

    function initBrowse() {
        attachTabs();
        attachMenuToggle();
        attachSearch();
        renderAllPosts('all');
    }

    function initAsk() {
        initAskPage();
        attachMenuToggle();
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('create-post-form')) initAsk();
        if (document.getElementById('posts-container')) initBrowse();
    });

    window.forum = { initAsk, initBrowse, loadPosts };
})();
