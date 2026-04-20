document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Handling Likes and Dislikes on Posts
    const reactionButtons = document.querySelectorAll('.btn-like, .btn-dislike');
    
    reactionButtons.forEach(button => {
        button.addEventListener('click', async (e) => {
            const article = button.closest('.post-card');
            const postId = article.dataset.postId;
            const type = button.dataset.type; // 'Like' or 'Dislike'

            try {
                const response = await fetch('api/react.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ post_id: postId, type: type })
                });

                const data = await response.json();
                
                if (data.success) {
                    // Update likes and dislikes count on the UI
                    const likesCountSpan = article.querySelector('.likes-count');
                    const dislikesCountSpan = article.querySelector('.dislikes-count');
                    if (likesCountSpan) likesCountSpan.textContent = data.likes;
                    if (dislikesCountSpan) dislikesCountSpan.textContent = data.dislikes;
                    
                    // Toggle active state locally for UX
                    const isLike = type === 'Like';
                    const otherButtonClass = isLike ? '.btn-dislike' : '.btn-like';
                    const otherButton = article.querySelector(otherButtonClass);
                    
                    if (button.classList.contains('active-like') || button.classList.contains('active-dislike')) {
                        button.classList.remove('active-like', 'active-dislike');
                    } else {
                        button.classList.add(isLike ? 'active-like' : 'active-dislike');
                        if (otherButton) otherButton.classList.remove('active-like', 'active-dislike');
                    }
                }
            } catch (err) {
                console.error("Erreur lors de la réaction:", err);
            }
        });
    });

    // 2. Handling Reactions on Replies
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-like-reply, .btn-dislike-reply');
        if (!btn) return;
        
        const replyDiv = btn.closest('.reply');
        const replyId = replyDiv.dataset.replyId;
        const type = btn.dataset.type; // 'Like' or 'Dislike'
        
        try {
            const response = await fetch('api/react_reply.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ reply_id: replyId, type: type })
            });

            const data = await response.json();
            if (data.success) {
                // Update counts
                const likeCountSpan = replyDiv.querySelector('.reply-likes-count');
                const dislikeCountSpan = replyDiv.querySelector('.reply-dislikes-count');
                if (likeCountSpan) likeCountSpan.textContent = data.likes;
                if (dislikeCountSpan) dislikeCountSpan.textContent = data.dislikes;

                // Toggle active states
                const isLike = type === 'Like';
                const otherButtonClass = isLike ? '.btn-dislike-reply' : '.btn-like-reply';
                const otherButton = replyDiv.querySelector(otherButtonClass);
                
                if (btn.classList.contains('active-like') || btn.classList.contains('active-dislike')) {
                    btn.classList.remove('active-like', 'active-dislike');
                } else {
                    btn.classList.add(isLike ? 'active-like' : 'active-dislike');
                    if (otherButton) otherButton.classList.remove('active-like', 'active-dislike');
                }
            }
        } catch (err) {
            console.error(err);
        }
    });

    // 3. Handling Reply to a Reply (Sub-replies)
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-sub-reply');
        if (!btn) return;

        const replyDiv = btn.closest('.reply');
        const replyId = replyDiv.dataset.replyId;
        const authorName = replyDiv.querySelector('.reply-author').textContent;
        const postCard = replyDiv.closest('.post-card');
        const inputArea = postCard.querySelector('.reply-input-area');
        const inputField = inputArea.querySelector('.reply-input');
        
        // Set the parent ID so the backend knows it's a nested reply
        inputArea.dataset.parentId = replyId;
        
        // Pre-fill the input with @username
        inputField.value = `@${authorName} `;
        inputField.focus();
    });

    // Image previews for reply
    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('reply-image-input')) {
            const file = e.target.files[0];
            if (file) {
                const wrapper = e.target.closest('.reply-input-wrapper');
                let preview = wrapper.querySelector('.reply-img-preview');
                if (!preview) {
                    preview = document.createElement('span');
                    preview.className = 'reply-img-preview';
                    preview.style = "font-size: 0.8rem; color: #10b981; margin-right: 0.5rem;";
                    wrapper.insertBefore(preview, wrapper.querySelector('.btn-send-reply'));
                }
                preview.textContent = '📸 ' + file.name;
            }
        }
    });

    // 4. Handling New Replies with Images
    document.addEventListener('click', async (e) => {
        const sendBtn = e.target.closest('.btn-send-reply');
        if (!sendBtn) return;
        
        const article = sendBtn.closest('.post-card');
        const inputArea = article.querySelector('.reply-input-area');
        const postId = article.dataset.postId;
        const parentId = inputArea.dataset.parentId || 0;
        const inputField = article.querySelector('.reply-input');
        const fileInput = article.querySelector('.reply-image-input');
        
        const commentaire = inputField.value.trim();
        const file = fileInput.files[0];

        if (!commentaire && !file) return;

        sendBtn.disabled = true;

        try {
            const formData = new FormData();
            formData.append('post_id', postId);
            formData.append('parent_reply_id', parentId);
            formData.append('commentaire', commentaire);
            if (file) {
                formData.append('image', file);
            }

            const response = await fetch('api/reply.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                const repliesList = article.querySelector('.replies-list');
                const marginLeftStyle = data.parent_reply_id ? 'margin-left: 2.5rem; border-left: 2px solid #e5e7eb; padding-left: 1rem;' : '';
                const imgHtml = data.image_url ? `<img src="${data.image_url}" style="max-width: 200px; border-radius: 8px; margin-top: 0.5rem;">` : '';
                
                const newReplyHTML = `
                    <div class="reply" data-reply-id="${data.id_reply}" style="${marginLeftStyle} animation: fadeIn 0.3s ease-in-out;">
                        <div class="avatar reply-avatar" style="background:${data.color_bg}; color:${data.color_text};">${data.initials}</div>
                        <div style="flex:1;">
                            <div class="reply-content-box" data-raw-content="${data.commentaire}">
                                <div class="reply-author">${data.author_name}</div>
                                <div class="reply-text">${data.commentaire}</div>
                                ${imgHtml}
                            </div>
                            <div class="reply-actions">
                                <button class="reply-action btn-like-reply" data-type="Like">J'aime (<span class="reply-likes-count">0</span>)</button>
                                <button class="reply-action btn-dislike-reply" data-type="Dislike">Je n'aime pas (<span class="reply-dislikes-count">0</span>)</button>
                                <button class="reply-action btn-sub-reply">Répondre</button>
                                <button class="reply-action btn-edit-reply" style="color: #6b7280;">Modifier</button>
                                <span style="font-size:0.75rem; color:#9ca3af; margin-left: auto;">${data.date}</span>
                            </div>
                        </div>
                    </div>
                `;
                
                repliesList.insertAdjacentHTML('beforeend', newReplyHTML);
                inputField.value = '';
                fileInput.value = '';
                const preview = inputArea.querySelector('.reply-img-preview');
                if(preview) preview.remove();
                inputArea.dataset.parentId = 0; // Reset parent
            }
        } catch (err) {
            console.error("Erreur lors de l'ajout de la réponse:", err);
        } finally {
            sendBtn.disabled = false;
        }
    });

    const replyInputs = document.querySelectorAll('.reply-input');
    replyInputs.forEach(input => {
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const sendBtn = input.closest('.reply-input-wrapper').querySelector('.btn-send-reply');
                sendBtn.click();
            }
        });
    });

    // 5. Handling New Post with Image
    const submitPostBtn = document.getElementById('btn-submit-post');
    const newPostContent = document.getElementById('new-post-content');
    const postImageInput = document.getElementById('post-image-input');
    const postImageName = document.getElementById('post-image-name');

    if (postImageInput) {
        postImageInput.addEventListener('change', (e) => {
            if (e.target.files[0]) {
                postImageName.textContent = '📸 ' + e.target.files[0].name;
            } else {
                postImageName.textContent = '';
            }
        });
    }

    if (submitPostBtn && newPostContent) {
        submitPostBtn.addEventListener('click', async () => {
            const contenu = newPostContent.value.trim();
            const file = postImageInput ? postImageInput.files[0] : null;
            
            if (!contenu && !file) return;

            const originalText = submitPostBtn.textContent;
            submitPostBtn.textContent = 'Publication...';
            submitPostBtn.disabled = true;

            try {
                const formData = new FormData();
                formData.append('contenu', contenu);
                if (file) formData.append('image', file);

                const response = await fetch('api/post.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Erreur lors de la publication');
                    submitPostBtn.textContent = originalText;
                    submitPostBtn.disabled = false;
                }
            } catch (err) {
                console.error("Erreur lors de la publication:", err);
                submitPostBtn.textContent = originalText;
                submitPostBtn.disabled = false;
            }
        });
    }

    // 6. Inline Editing for Posts
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-edit-post');
        if (!btn) return;
        
        const article = btn.closest('.post-card');
        const contentBox = article.querySelector('.post-content');
        const textElement = contentBox.querySelector('.post-text');
        
        // Prevent multiple edit boxes
        if (contentBox.querySelector('.edit-post-form')) return;

        const rawContent = contentBox.dataset.rawContent;
        
        textElement.style.display = 'none';
        
        const formHtml = `
            <div class="edit-post-form" style="margin-top: 0.5rem;">
                <textarea class="edit-post-input" style="width:100%; min-height:80px; padding:0.5rem; border:1px solid #d1d5db; border-radius:8px; font-family:inherit;">${rawContent}</textarea>
                <div style="margin-top:0.5rem; display:flex; gap:0.5rem;">
                    <button class="btn-submit btn-save-post" style="padding:0.4rem 1rem; font-size:0.8rem;">Enregistrer</button>
                    <button class="btn-cancel-post" style="padding:0.4rem 1rem; font-size:0.8rem; background:white; color:#166534; border:1px solid #166534; border-radius:999px; cursor:pointer;">Annuler</button>
                </div>
            </div>
        `;
        contentBox.insertAdjacentHTML('beforeend', formHtml);
        
        const formDiv = contentBox.querySelector('.edit-post-form');
        
        formDiv.querySelector('.btn-cancel-post').addEventListener('click', () => {
            formDiv.remove();
            textElement.style.display = 'block';
        });

        formDiv.querySelector('.btn-save-post').addEventListener('click', async () => {
            const newContent = formDiv.querySelector('.edit-post-input').value.trim();
            if (!newContent) return;
            
            try {
                const response = await fetch('api/edit_post.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_post: article.dataset.postId, contenu: newContent })
                });
                const data = await response.json();
                if (data.success) {
                    textElement.innerHTML = data.contenu;
                    contentBox.dataset.rawContent = newContent;
                    formDiv.remove();
                    textElement.style.display = 'block';
                } else {
                    alert(data.message);
                }
            } catch (err) {
                console.error(err);
            }
        });
    });

    // 7. Inline Editing for Replies
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-edit-reply');
        if (!btn) return;
        
        const replyDiv = btn.closest('.reply');
        const contentBox = replyDiv.querySelector('.reply-content-box');
        const textElement = contentBox.querySelector('.reply-text');
        
        if (contentBox.querySelector('.edit-reply-form')) return;

        const rawContent = contentBox.dataset.rawContent;
        
        textElement.style.display = 'none';
        
        const formHtml = `
            <div class="edit-reply-form" style="margin-top: 0.5rem; width:100%;">
                <textarea class="edit-reply-input" style="width:100%; min-height:60px; padding:0.5rem; border:1px solid #d1d5db; border-radius:8px; font-family:inherit; font-size:0.9rem;">${rawContent}</textarea>
                <div style="margin-top:0.3rem; display:flex; gap:0.5rem;">
                    <button class="btn-submit btn-save-reply" style="padding:0.3rem 0.8rem; font-size:0.75rem;">Enregistrer</button>
                    <button class="btn-cancel-reply" style="padding:0.3rem 0.8rem; font-size:0.75rem; background:white; color:#166534; border:1px solid #166534; border-radius:999px; cursor:pointer;">Annuler</button>
                </div>
            </div>
        `;
        contentBox.insertAdjacentHTML('beforeend', formHtml);
        
        const formDiv = contentBox.querySelector('.edit-reply-form');
        
        formDiv.querySelector('.btn-cancel-reply').addEventListener('click', () => {
            formDiv.remove();
            textElement.style.display = 'block';
        });

        formDiv.querySelector('.btn-save-reply').addEventListener('click', async () => {
            const newContent = formDiv.querySelector('.edit-reply-input').value.trim();
            if (!newContent) return;
            
            try {
                const response = await fetch('api/edit_reply.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_reply: replyDiv.dataset.replyId, commentaire: newContent })
                });
                const data = await response.json();
                if (data.success) {
                    textElement.innerHTML = data.commentaire;
                    contentBox.dataset.rawContent = newContent;
                    formDiv.remove();
                    textElement.style.display = 'block';
                } else {
                    alert(data.message);
                }
            } catch (err) {
                console.error(err);
            }
        });
    });

    // 8. Delete Post
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-delete-post');
        if (!btn) return;
        
        const article = btn.closest('.post-card');
        const postId = article.dataset.postId;
        
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette publication ?')) return;
        
        try {
            const response = await fetch('api/delete_post.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_post: postId })
            });
            const data = await response.json();
            if (data.success) {
                article.remove();
            } else {
                alert(data.message || 'Erreur lors de la suppression');
            }
        } catch (err) {
            console.error(err);
        }
    });

    // 9. Delete Reply
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-delete-reply');
        if (!btn) return;
        
        const replyDiv = btn.closest('.reply');
        const replyId = replyDiv.dataset.replyId;
        
        if (!confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?')) return;
        
        try {
            const response = await fetch('api/delete_reply.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_reply: replyId })
            });
            const data = await response.json();
            if (data.success) {
                replyDiv.remove();
            } else {
                alert(data.message || 'Erreur lors de la suppression');
            }
        } catch (err) {
            console.error(err);
        }
    });

});
