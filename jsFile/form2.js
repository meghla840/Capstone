(function () {
    const STORAGE_KEY = 'quickaid_forum_posts_v1';
    const CURRENT_USER = { id: 'me', name: 'You (Demo)', role: 'user' };

    // --- Storage ---
    const Storage = {
        load: () => {
            try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]'); }
            catch { return []; }
        },
        save: (posts) => localStorage.setItem(STORAGE_KEY, JSON.stringify(posts))
    };

    // --- Utilities ---
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
        timeAgo: (date) => {
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
            Toast.wrap.id = 'qa-toast-wrap';
            Object.assign(Toast.wrap.style, {
                position: 'fixed',
                top: '20px',
                right: '20px',
                zIndex: 9999,
                display: 'flex',
                flexDirection: 'column',
                gap: '12px',
                pointerEvents: 'none'
            });
            document.body.appendChild(Toast.wrap);
        }
    },
    show: (message, variant = 'info', duration = 3000) => {
        Toast.init();

        const colors = {
            success: '#22c55e',
            error: '#ef4444',
            warn: '#facc15',
            info: '#3b82f6'
        };

        const toast = document.createElement('div');
        toast.textContent = message;

        Object.assign(toast.style, {
            minWidth: '220px',
            maxWidth: '320px',
            padding: '14px 18px',
            borderRadius: '14px',
            fontSize: '14px',
            fontWeight: '500',
            color: '#fff',
            background: colors[variant] || colors.info,
            boxShadow: '0 8px 28px rgba(0,0,0,0.18)',
            transform: 'translateX(120%) scale(0.8)',
            opacity: '0',
            pointerEvents: 'auto',
            transition: 'transform 0.5s cubic-bezier(0.2, 1, 0.3, 1), opacity 0.35s ease'
        });

        Toast.wrap.appendChild(toast);

        // Slide-in with bounce effect
        requestAnimationFrame(() => {
            toast.style.transform = 'translateX(0) scale(1.05)';
            toast.style.opacity = '1';
            setTimeout(() => toast.style.transform = 'translateX(0) scale(1)', 200); // settle
        });

        // Remove toast after duration with floating fade-out
        setTimeout(() => {
            toast.style.transition = 'transform 0.7s ease, opacity 0.7s ease';
            toast.style.transform = 'translateY(-20px) scale(0.9)';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 700);
        }, duration);

        // Click to dismiss early
        toast.addEventListener('click', () => {
            toast.style.transition = 'transform 0.5s ease, opacity 0.5s ease';
            toast.style.transform = 'translateY(-20px) scale(0.9)';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 500);
        });
    }
};

    // --- Comment Modal ---
    let commentModal = null;
    let activeCommentPostId = null;

    function ensureCommentModal() {
        if (commentModal) return commentModal;

        commentModal = document.createElement('div');
        commentModal.id = 'qa-comment-modal';
        Object.assign(commentModal.style, { position: 'fixed', inset: 0, display: 'none', zIndex: 10000 });

        commentModal.innerHTML = `
            <div class="qa-modal-backdrop" style="position:absolute; inset:0; background:rgba(0,0,0,0.45); backdrop-filter: blur(4px); transition: opacity .25s ease;"></div>
            <div class="qa-modal-root" style="position:relative; max-width:780px; margin:60px auto; background:#fff; border-radius:20px; overflow:hidden; box-shadow:0 12px 40px rgba(0,0,0,0.15); transform:translateY(20px); opacity:0; transition: all .25s ease;">
                <div style="padding:18px 22px; border-bottom:1px solid #f1f5f9; background:#f8fafc; display:flex; align-items:center; justify-content:space-between;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <strong id="qa-modal-title" style="font-size:17px;color:#0f172a;"></strong>
                        <span id="qa-modal-sub" style="color:#64748b;font-size:13px;"></span>
                    </div>
                    <button id="qa-modal-close" style="background:none;border:none;font-size:20px;color:#475569;cursor:pointer;">✕</button>
                </div>
                <div id="qa-modal-body" style="max-height:420px; overflow-y:auto; padding:20px; background:#fff; scrollbar-width: thin;"></div>
                <div style="padding:16px 20px; border-top:1px solid #f1f5f9; background:#f8fafc; display:flex; gap:12px;">
                    <textarea id="qa-new-comment" placeholder="Write a comment..." style="flex:1; resize:none; padding:12px 14px; border-radius:12px; border:1px solid #e2e8f0; font-size:14px; background:#fff;"></textarea>
                    <button id="qa-submit-comment" style="
    padding: 12px 22px;
    border: none;
    border-radius: 12px;
    background: linear-gradient(90deg, #4f46e5, #6366f1);
    color: #fff;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    box-shadow: 0 6px 16px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
">
    Post
</button>

                </div>
            </div>
        `;

        document.body.appendChild(commentModal);

        const closeModal = () => {
            commentModal.style.display = 'none';
            activeCommentPostId = null;
            document.body.style.overflow = '';
        };

        commentModal.querySelector('#qa-modal-close').addEventListener('click', closeModal);
        commentModal.querySelector('.qa-modal-backdrop').addEventListener('click', closeModal);

        return commentModal;
    }

    function showCommentModal(postId) {
        const modal = ensureCommentModal();
        activeCommentPostId = postId;

        const posts = Storage.load();
        const post = posts.find(p => p.id === postId);
        if (!post) return Toast.show('Post not found', 'error');

        const box = modal.querySelector('.qa-modal-root');
        requestAnimationFrame(() => { box.style.opacity = '1'; box.style.transform = 'translateY(0)'; });

        // header
        modal.querySelector('#qa-modal-title').textContent = post.title || 'Comments';
        modal.querySelector('#qa-modal-sub').textContent = ` · ${post.comments?.length || 0} comments`;

        // body
        const body = modal.querySelector('#qa-modal-body');
        body.innerHTML = '';
        const comments = (post.comments || []).sort((a, b) => new Date(a.createdAt) - new Date(b.createdAt));
        if (!comments.length) {
            const empty = document.createElement('div');
            empty.style.color = '#6b7280';
            empty.style.padding = '18px 6px';
            empty.textContent = 'No comments yet. Be the first to comment.';
            body.appendChild(empty);
        } else {
            comments.forEach(c => {
                const row = document.createElement('div');
                row.style.padding = '10px 6px';
                row.style.borderBottom = '1px solid rgba(15,23,42,0.04)';
                row.innerHTML = `
                    <div style="display:flex;gap:10px;align-items:flex-start;">
                        <div style="width:36px;height:36px;border-radius:50%;background:#f3f4f6;display:flex;align-items:center;justify-content:center;color:#374151;font-weight:600;">
                            ${Utils.escapeHtml(c.author?.name?.slice(0,2) || 'U')}
                        </div>
                        <div style="flex:1;">
                            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                <div style="font-weight:600;color:#111827;font-size:14px;">${Utils.escapeHtml(c.author?.name || 'Anonymous')}</div>
                                <div style="color:#6b7280;font-size:12px;">${Utils.timeAgo(new Date(c.createdAt || Date.now()))}</div>
                            </div>
                            <div style="margin-top:6px;color:#374151;font-size:14px;white-space:pre-wrap;">${Utils.escapeHtml(c.text)}</div>
                        </div>
                    </div>
                `;
                body.appendChild(row);
            });
        }

        // new comment
        const textarea = modal.querySelector('#qa-new-comment');
        const submitBtn = modal.querySelector('#qa-submit-comment');
        textarea.value = '';
        textarea.focus();

        submitBtn.replaceWith(submitBtn.cloneNode(true));
        modal.querySelector('#qa-submit-comment').addEventListener('click', () => {
            const text = textarea.value.trim();
            if (!text) return Toast.show('Comment cannot be empty', 'warn');

            const postsNow = Storage.load();
            const postNow = postsNow.find(p => p.id === activeCommentPostId);
            if (!postNow) return Toast.show('Post not found', 'error');

            postNow.comments = postNow.comments || [];
            postNow.comments.push({
                id: Date.now() + '-' + Math.floor(Math.random() * 9999),
                author: { ...CURRENT_USER },
                text,
                createdAt: new Date().toISOString()
            });

            Storage.save(postsNow);
            modal.querySelector('#qa-modal-sub').textContent = ` · ${postNow.comments.length} comments`;

            // append new comment instantly
            const row = document.createElement('div');
            row.style.padding = '10px 6px';
            row.style.borderBottom = '1px solid rgba(15,23,42,0.04)';
            row.innerHTML = `
                <div style="display:flex;gap:10px;align-items:flex-start;">
                    <div style="width:36px;height:36px;border-radius:50%;background:#f3f4f6;display:flex;align-items:center;justify-content:center;color:#374151;font-weight:600;">
                        ${Utils.escapeHtml(CURRENT_USER.name.slice(0,2))}
                    </div>
                    <div style="flex:1;">
                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                            <div style="font-weight:600;color:#111827;font-size:14px;">${Utils.escapeHtml(CURRENT_USER.name)}</div>
                            <div style="color:#6b7280;font-size:12px;">${Utils.timeAgo(new Date())}</div>
                        </div>
                        <div style="margin-top:6px;color:#374151;font-size:14px;white-space:pre-wrap;">${Utils.escapeHtml(text)}</div>
                    </div>
                </div>
            `;
            if (body.children.length === 1 && body.children[0].textContent.includes('No comments yet')) body.innerHTML = '';
            body.appendChild(row);
            textarea.value = '';
            Toast.show('Comment posted', 'success');
            updateCommentCountOnCard(postNow.id, postNow.comments.length);
        });

        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        body.scrollTop = body.scrollHeight;
    }

    function updateCommentCountOnCard(postId, count) {
        document.querySelectorAll('#posts-container button[data-post-id][data-role="comment"]').forEach(btn => {
            if (btn.dataset.postId === String(postId)) btn.textContent = `💬 Comments (${count})`;
        });
    }

    // --- Render Posts ---
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
        avatar.textContent = post.author.role === 'doctor' ? 'Dr' : (post.author.name || 'U').slice(0,2);

        const meta = document.createElement('div');
        meta.innerHTML = `<div class="font-semibold">${Utils.escapeHtml(post.title)}</div>
                          <div class="text-xs text-slate-500">${Utils.escapeHtml(post.author.name)} · ${Utils.escapeHtml(post.category)} · ${Utils.timeAgo(new Date(post.createdAt))}</div>`;

        left.append(avatar, meta);

        const right = document.createElement('div');
        if (post.author.role === 'doctor') {
            const badge = document.createElement('span');
            badge.className = 'badge badge-info';
            badge.textContent = 'Doctor';
            right.appendChild(badge);
        }

        header.append(left, right);

        // Body
        const body = document.createElement('div');
        body.className = 'mb-3 text-slate-700';
        body.innerHTML = `<p class="whitespace-pre-wrap">${Utils.escapeHtml(post.content)}</p>`;
        if (post.image) {
            const img = document.createElement('img');
            img.src = post.image;
            img.alt = post.title;
            img.className = 'object-cover w-full mt-3 rounded-md max-h-80';
            body.appendChild(img);
        }

        // Actions
        const actions = document.createElement('div');
        actions.className = 'flex gap-3 mt-3';

        const likeBtn = document.createElement('button');
        likeBtn.className = 'qa-action-btn';
        likeBtn.textContent = `👍 ${post.likes || 0}`;
        likeBtn.addEventListener('click', () => {
            post.likedBy = post.likedBy || [];
            if (!post.likedBy.includes(CURRENT_USER.id)) {
                post.likes = (post.likes || 0) + 1;
                post.likedBy.push(CURRENT_USER.id);
                Storage.save(Storage.load().map(p => p.id === post.id ? post : p));
                likeBtn.textContent = `👍 ${post.likes}`;
                Toast.show('You liked the post', 'success');
            } else Toast.show('You already liked this post.', 'info');
        });

        const shareBtn = document.createElement('button');
        shareBtn.className = 'qa-action-btn';
        shareBtn.textContent = '🔗 Share';
        shareBtn.addEventListener('click', () => {
            navigator.clipboard?.writeText(window.location.href)
                .then(() => Toast.show('Post link copied!', 'success'))
                .catch(() => Toast.show('Could not copy link', 'error'));
        });

        const commentBtn = document.createElement('button');
        commentBtn.className = 'qa-action-btn';
        commentBtn.dataset.postId = post.id;
        commentBtn.dataset.role = 'comment';
        commentBtn.textContent = `💬 Comments (${(post.comments?.length) || 0})`;
        commentBtn.addEventListener('click', () => showCommentModal(post.id));

        actions.append(likeBtn, shareBtn, commentBtn);
        wrapper.append(header, body, actions);

        return wrapper;
    }

    function renderAllPosts(tab = 'all', categoryFilter = '', searchQuery = '') {
        const container = document.getElementById('posts-container');
        const empty = document.getElementById('empty-state');
        if (!container) return;
        container.innerHTML = '';

        let posts = Storage.load();

        if (tab === 'my') posts = posts.filter(p => p.author?.id === CURRENT_USER.id);
        if (tab === 'doctor') posts = posts.filter(p => p.author?.role === 'doctor');

        if (categoryFilter) posts = posts.filter(p => (p.category || '').toLowerCase() === categoryFilter.toLowerCase());
        if (searchQuery) {
            posts = posts.filter(p =>
                (p.title || '').toLowerCase().includes(searchQuery.toLowerCase()) ||
                (p.content || '').toLowerCase().includes(searchQuery.toLowerCase())
            );
        }

        if (!posts.length && empty) empty.classList.remove('hidden');
        else {
            if (empty) empty.classList.add('hidden');
            posts.forEach(p => container.appendChild(renderPostCard(p)));
        }

        populateCategoryDropdown();
    }

    function populateCategoryDropdown() {
        const categorySelect = document.getElementById('category-filter');
        if (!categorySelect) return;
        const posts = Storage.load();
        const categories = [...new Set(posts.map(p => p.category).filter(Boolean))];
        const currentValue = categorySelect.value || '';
        categorySelect.innerHTML = `<option value="">All Categories</option>` +
            categories.map(c => `<option value="${c}">${c}</option>`).join('');
        if (currentValue) categorySelect.value = currentValue;
    }

    // --- Tabs, Search, Filters ---
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
        const filterPosts = () => {
            const category = document.getElementById('category-filter')?.value || '';
            renderAllPosts(currentTab, category, (searchInput?.value || '').trim());
        };
        searchBtn?.addEventListener('click', filterPosts);
        searchInput?.addEventListener('keypress', e => { if (e.key === 'Enter') filterPosts(); });
    }

    function attachMenuToggle() {
        const menuToggle = document.getElementById('menu-toggle');
        if (!menuToggle) return;
        menuToggle.addEventListener('click', () => {
            const nav = document.getElementById('nav-links');
            nav?.classList.toggle('active');
        });
    }

    // --- Ask Page (Create Post) ---
    async function initAskPage() {
        const form = document.getElementById('create-post-form');
        if (!form) return;

        const imageInput = document.getElementById('post-image');
        const imagePreviewContainer = document.getElementById('image-preview-container');
        const imagePreview = document.getElementById('image-preview');
        const hiddenCategory = document.getElementById('selected-category');

        imageInput.addEventListener('change', async () => {
            const file = imageInput.files[0];
            if (!file) { imagePreviewContainer?.classList.add('hidden'); return; }
            imagePreview.src = await Utils.fileToDataUrl(file);
            imagePreviewContainer?.classList.remove('hidden');
        });

        document.querySelectorAll('.category-tag').forEach(tag => {
            tag.addEventListener('click', () => {
                document.querySelectorAll('.category-tag').forEach(t => t.classList.remove('bg-teal-400', 'text-white'));
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

            if (!title || !content || !category) return Toast.show('Please fill title, content, and select a category.', 'warn');

            const dataUrl = await Utils.fileToDataUrl(file);
            const posts = Storage.load();
            posts.unshift({
                id: Date.now() + '-' + Math.floor(Math.random() * 9999),
                title, content, image: dataUrl || null, category,
                author: authorType === 'doctor' ? { id: 'doctor-demo', name: 'Dr. Demo', role: 'doctor' } : { ...CURRENT_USER },
                likes: 0, likedBy: [], comments: [], createdAt: new Date().toISOString(), shares: 0
            });
            Storage.save(posts);

            form.reset();
            hiddenCategory.value = '';
            document.querySelectorAll('.category-tag').forEach(t => t.classList.remove('bg-teal-400', 'text-white'));
            imagePreviewContainer?.classList.add('hidden');
            document.getElementById('create-post-modal')?.checked && (document.getElementById('create-post-modal').checked = false);

            Toast.show('Post created', 'success');

            renderAllPosts(currentTab, document.getElementById('category-filter')?.value || '', document.getElementById('search-input')?.value || '');
        });
    }

    // --- Init ---
    let currentTab = 'all';
    function initBrowse() { attachTabs(); attachMenuToggle(); attachSearch(); attachCategoryFilter(); renderAllPosts('all'); }
    function initAsk() { initAskPage(); attachMenuToggle(); }

    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('create-post-form')) initAsk();
        if (document.getElementById('posts-container')) initBrowse();
    });

    window.forum = { initAsk, initBrowse, loadPosts: Storage.load };
})();
