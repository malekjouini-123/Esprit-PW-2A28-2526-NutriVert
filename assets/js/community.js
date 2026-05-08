document.addEventListener('DOMContentLoaded', () => {
    
    // Custom Modal logic
    const confirmModal = document.getElementById('confirmModal');
    const modalConfirmBtn = document.getElementById('modalConfirm');
    const modalCancelBtn = document.getElementById('modalCancel');
    let onConfirmCallback = null;

    function showCustomConfirm(callback) {
        onConfirmCallback = callback;
        if (confirmModal) confirmModal.classList.add('show');
    }

    if (modalCancelBtn) {
        modalCancelBtn.addEventListener('click', () => {
            if (confirmModal) confirmModal.classList.remove('show');
            onConfirmCallback = null;
        });
    }

    if (modalConfirmBtn) {
        modalConfirmBtn.addEventListener('click', () => {
            if (onConfirmCallback) onConfirmCallback();
            if (confirmModal) confirmModal.classList.remove('show');
            onConfirmCallback = null;
        });
    }

    if (confirmModal) {
        confirmModal.addEventListener('click', (e) => {
            if (e.target === confirmModal) {
                confirmModal.classList.remove('show');
                onConfirmCallback = null;
            }
        });
    }

    function showSuccessModal(msg) {
        const sm = document.getElementById('successModal');
        const smt = document.getElementById('successModalText');
        if (sm && smt) {
            smt.textContent = msg;
            sm.classList.add('show');
        }
    }

    function showToast(msg) {
        if (!msg) return;
        const t = document.getElementById('toast');
        if (!t) return;
        t.textContent = msg;
        t.classList.add('show');
        clearTimeout(t._to);
        t._to = setTimeout(() => t.classList.remove('show'), 3200);
    }

    /**
     * Filtre les mots inappropriés côté client.
     */
    function filterProfanity(text) {
        if (!text) return text;
        const badWords = [
            'fuck', 'shit', 'asshole', 'bitch', 'bastard', 'crap', 'damn', 'piss', 'dick', 'pussy', 'cock', 'faggot', 'nigger', 'slut', 'bad word',
            'merde', 'connard', 'connasse', 'salope', 'enculé', 'pute', 'bordel', 'con', 'chier', 'salaud', 'abruti', 'nique', 'teub', 'bite', 'cul', 'mauvais mot', 'mauvaise mot'
        ];
        
        let filteredText = text;
        badWords.forEach(word => {
            const replacement = '*'.repeat(word.length);
            const regex = new RegExp('\\b' + word + '\\b', 'gi');
            filteredText = filteredText.replace(regex, replacement);
        });
        return filteredText;
    }

    // 1. Handling Reactions on Posts
    document.addEventListener('click', async (e) => {
        // Popover choice (Like or Dislike)
        const choice = e.target.closest('.btn-react-choice');
        if (choice) {
            const type = choice.dataset.type;
            const mainBtn = choice.closest('.reaction-wrapper').querySelector('.btn-react-main');
            handleReaction(mainBtn, type);
            return;
        }
        // Main button click (always toggles Like)
        const btn = e.target.closest('.btn-react-main');
        if (!btn) return;
        if (btn.classList.contains('btn-react-main-reply')) return;
        handleReaction(btn, 'Like');
    });

    async function handleReaction(button, type) {
        const article = button.closest('.post-card');
        const postId = article.dataset.postId;

        try {
            const response = await fetch('api/react.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ post_id: postId, type: type })
            });

            const data = await response.json();
            if (data.success) {
                const mainBtn  = article.querySelector('.btn-react-main');
                const icon     = mainBtn.querySelector('i');
                const reactTxt = mainBtn.querySelector('.react-text');

                mainBtn.classList.remove('active-like', 'active-dislike');

                if (data.user_reaction === 'Like') {
                    mainBtn.classList.add('active-like');
                    icon.className = 'fas fa-thumbs-up';
                    if (reactTxt) reactTxt.textContent = "J'aime";
                } else if (data.user_reaction === 'Dislike') {
                    mainBtn.classList.add('active-dislike');
                    icon.className = 'fas fa-thumbs-down';
                    if (reactTxt) reactTxt.textContent = "Je n'aime pas";
                } else {
                    icon.className = 'far fa-thumbs-up';
                    if (reactTxt) reactTxt.textContent = "J'aime";
                }

                // Update stat bar
                const statLikes    = article.querySelector('.post-stat-likes');
                const statDislikes = article.querySelector('.post-stat-dislikes');
                if (statLikes)    statLikes.textContent    = data.likes;
                if (statDislikes) statDislikes.textContent = data.dislikes;
            }
        } catch (err) {
            console.error("Erreur lors de la réaction:", err);
        }
    }

    // 2. Handling Reactions on Replies
    document.addEventListener('click', async (e) => {
        // Popover choice
        const choice = e.target.closest('.btn-react-choice-reply');
        if (choice) {
            const type = choice.dataset.type;
            const mainBtn = choice.closest('.reaction-wrapper-reply').querySelector('.btn-react-main-reply');
            handleReplyReaction(mainBtn, type);
            return;
        }
        // Main button click (toggles Like)
        const btn = e.target.closest('.btn-react-main-reply');
        if (!btn) return;
        handleReplyReaction(btn, 'Like');
    });

    async function handleReplyReaction(btn, type) {
        const replyDiv = btn.closest('.reply');
        const replyId = replyDiv.dataset.replyId;
        
        try {
            const response = await fetch('api/react_reply.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ reply_id: replyId, type: type })
            });

            const data = await response.json();
            if (data.success) {
                const mainBtn  = replyDiv.querySelector('.btn-react-main-reply');
                const icon     = mainBtn.querySelector('i');
                const reactTxt = mainBtn.querySelector('.react-text');
                const countBadge = mainBtn.querySelector('.react-count');

                mainBtn.classList.remove('active-like', 'active-dislike');

                if (data.user_reaction === 'Like') {
                    mainBtn.classList.add('active-like');
                    icon.className = 'fas fa-thumbs-up';
                    if (reactTxt) reactTxt.textContent = "J'aime";
                    if (countBadge) countBadge.textContent = data.likes > 0 ? data.likes : '';
                } else if (data.user_reaction === 'Dislike') {
                    mainBtn.classList.add('active-dislike');
                    icon.className = 'fas fa-thumbs-down';
                    if (reactTxt) reactTxt.textContent = "Je n'aime pas";
                    if (countBadge) countBadge.textContent = data.dislikes > 0 ? data.dislikes : '';
                } else {
                    icon.className = 'far fa-thumbs-up';
                    if (reactTxt) reactTxt.textContent = "J'aime";
                    // If no reaction, show like count if > 0, otherwise empty
                    if (countBadge) countBadge.textContent = data.likes > 0 ? data.likes : '';
                }
            }
        } catch (err) {
            console.error(err);
        }
    }

    // Dropdown close when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.post-options-container')) {
            document.querySelectorAll('.post-dropdown').forEach(dropdown => {
                dropdown.style.display = 'none';
            });
        }
    });

    // Option: Copier le lien
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-copy-link');
        if (!btn) return;
        
        const article = btn.closest('.post-card');
        const postId = article.dataset.postId;
        const link = window.location.origin + window.location.pathname + '#post-' + postId;
        
        navigator.clipboard.writeText(link).then(() => {
            showToast('Lien copié dans le presse-papiers');
        }).catch(err => {
            console.error('Erreur lors de la copie', err);
        });
        
        btn.closest('.post-dropdown').style.display = 'none';
    });

    // Option: Reposter
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-repost') || e.target.closest('.btn-repost-post');
        if (!btn) return;
        
        const article = btn.closest('.post-card');
        const postId = article.dataset.postId;
        
        // Visual feedback
        const originalContent = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>...';

        try {
            const formData = new FormData();
            formData.append('post_id', postId);

            const response = await fetch('api/repost.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            if (data.success) {
                showToast('Publication republiée !');
                // Reload to see the new post
                setTimeout(() => location.reload(), 1200);
            } else {
                showToast(data.message || 'Erreur lors du repost');
                btn.disabled = false;
                btn.innerHTML = originalContent;
            }
        } catch (err) {
            console.error('Erreur Repost:', err);
            showToast('Erreur lors de la republication');
            btn.disabled = false;
            btn.innerHTML = originalContent;
        }

        if (btn.closest('.post-dropdown')) {
            btn.closest('.post-dropdown').style.display = 'none';
        }
    });

    // Option: Répondre au post (focus input)
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-reply-post');
        if (!btn) return;

        const article = btn.closest('.post-card');
        const replyInput = article.querySelector('.reply-input');
        if (replyInput) {
            replyInput.focus();
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
        
        const commentaireRaw = inputField.value.trim();
        const commentaire = filterProfanity(commentaireRaw);
        const file = fileInput.files[0];

        // Contrôle de saisie
        if (!commentaire && !file) {
            showToast('Veuillez écrire un commentaire ou ajouter une image.');
            return;
        }
        
        if (commentaire && commentaire.length < 2) {
            showToast('Votre commentaire est trop court (min 2 caractères).');
            return;
        }
        
        if (commentaire && commentaire.length > 1000) {
            showToast('Votre commentaire est trop long (max 1000 caractères).');
            return;
        }

        // Visual feedback: update the input field if it was censored
        if (commentaire !== commentaireRaw) {
            inputField.value = commentaire;
        }

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
                                <div class="reaction-wrapper-reply">
                                    <button class="reply-action btn-react-main-reply" data-type="Like">
                                        <i class="far fa-thumbs-up"></i>
                                        <span class="react-text">J'aime</span>
                                        <span class="react-count"></span>
                                    </button>
                                    <div class="reaction-popover-reply">
                                        <button class="react-emoji btn-react-choice-reply" data-type="Like">
                                            <span class="emoji">&#128077;</span>
                                            <span class="react-label">J'aime</span>
                                        </button>
                                        <button class="react-emoji btn-react-choice-reply" data-type="Dislike">
                                            <span class="emoji">&#128078;</span>
                                            <span class="react-label">Je n'aime pas</span>
                                        </button>
                                    </div>
                                </div>
                                <button class="reply-action btn-sub-reply">Répondre</button>
                                <button class="reply-action btn-edit-reply" style="color: #6b7280;">Modifier</button>
                                <button class="reply-action btn-delete-reply" style="color: #ef4444;">Supprimer</button>
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

    // Emoji Popover Logic
    const emojiPickerBtn = document.getElementById('btn-emoji-picker');
    const emojiPopover = document.getElementById('emoji-popover');
    const mainTextarea = document.getElementById('new-post-content');

    if (emojiPickerBtn && emojiPopover) {
        emojiPickerBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            emojiPopover.classList.toggle('show');
        });

        document.addEventListener('click', (e) => {
            if (!emojiPopover.contains(e.target) && e.target !== emojiPickerBtn) {
                emojiPopover.classList.remove('show');
            }
        });

        emojiPopover.addEventListener('click', (e) => {
            const item = e.target.closest('.emoji-item');
            if (!item) return;

            const emoji = item.dataset.emoji;
            const iconId = item.dataset.id;
            
            if (mainTextarea) {
                const start = mainTextarea.selectionStart;
                const end = mainTextarea.selectionEnd;
                const text = mainTextarea.value;
                const before = text.substring(0, start);
                const after = text.substring(end);
                
                mainTextarea.value = before + emoji + after;
                
                // Put cursor after inserted emoji
                mainTextarea.selectionStart = mainTextarea.selectionEnd = start + emoji.length;
                mainTextarea.focus();
            }

            // Optional: Still sync with hidden select for DB categorization
            if (hiddenIconSelect) {
                let opt = hiddenIconSelect.querySelector(`option[value="${iconId}"]`);
                if (!opt) {
                    opt = document.createElement('option');
                    opt.value = iconId;
                    hiddenIconSelect.appendChild(opt);
                }
                opt.selected = true;
            }
        });
    }

    function clearIconPicker() {
        if (emojiPopover) emojiPopover.classList.remove('show');
        if (hiddenIconSelect) hiddenIconSelect.innerHTML = '';
    }

    // Emoji Picker for Replies
    document.addEventListener('click', (e) => {
        const toggleBtn = e.target.closest('.btn-reply-emoji-toggle');
        if (toggleBtn) {
            e.stopPropagation();
            const popover = toggleBtn.nextElementSibling;
            // Close other reply popovers
            document.querySelectorAll('.emoji-popover-reply').forEach(p => {
                if (p !== popover) p.classList.remove('show');
            });
            popover.classList.toggle('show');
            return;
        }

        // Close reply popovers when clicking outside
        if (!e.target.closest('.emoji-popover-reply')) {
            document.querySelectorAll('.emoji-popover-reply').forEach(p => p.classList.remove('show'));
        }

        // Emoji selection in replies
        const emojiItem = e.target.closest('.emoji-item-reply');
        if (emojiItem) {
            const emoji = emojiItem.dataset.emoji;
            const inputWrapper = emojiItem.closest('.reply-input-wrapper');
            const input = inputWrapper.querySelector('.reply-input');
            
            if (input) {
                const start = input.selectionStart;
                const end = input.selectionEnd;
                const text = input.value;
                input.value = text.substring(0, start) + emoji + text.substring(end);
                input.selectionStart = input.selectionEnd = start + emoji.length;
                input.focus();
            }
            emojiItem.closest('.emoji-popover-reply').classList.remove('show');
        }
    });

    if (submitPostBtn && newPostContent) {
        submitPostBtn.addEventListener('click', async () => {
            const contenuRaw = newPostContent.value.trim();
            const contenu = filterProfanity(contenuRaw);
            const file = postImageInput ? postImageInput.files[0] : null;
            const titleInput = document.getElementById('new-post-title');
            const typeInput = document.getElementById('new-post-type');
            const titreRaw = titleInput ? titleInput.value.trim() : '';
            const titre = filterProfanity(titreRaw);
            const typePost = typeInput ? typeInput.value : 'Article';
            
            // Visual feedback
            if (contenu !== contenuRaw) newPostContent.value = contenu;
            if (titre !== titreRaw && titleInput) titleInput.value = titre;
            
            // Contrôle de saisie
            let errorMessage = '';
            let targetErrorSpan = null;
            
            const titleErrorSpan = document.getElementById('new-post-title-error');
            const contentErrorSpan = document.getElementById('new-post-content-error');
            
            // Reset errors
            if (titleErrorSpan) { titleErrorSpan.style.display = 'none'; titleErrorSpan.textContent = ''; }
            if (contentErrorSpan) { contentErrorSpan.style.display = 'none'; contentErrorSpan.textContent = ''; }

            if (!titre) {
                errorMessage = 'Le titre est obligatoire.';
                targetErrorSpan = titleErrorSpan;
            } else if (titre.length < 5) {
                errorMessage = 'Le titre est trop court (minimum 5 caractères).';
                targetErrorSpan = titleErrorSpan;
            } else if (titre.length > 100) {
                errorMessage = 'Le titre est trop long (maximum 100 caractères).';
                targetErrorSpan = titleErrorSpan;
            } else if (!contenu && !file) {
                errorMessage = 'Veuillez écrire un message ou ajouter une photo.';
                targetErrorSpan = contentErrorSpan;
            } else if (contenu && contenu.length < 5) {
                errorMessage = 'Votre message est trop court (minimum 5 caractères).';
                targetErrorSpan = contentErrorSpan;
            } else if (contenu && contenu.length > 2000) {
                errorMessage = 'Votre message est trop long (maximum 2000 caractères).';
                targetErrorSpan = contentErrorSpan;
            }

            if (errorMessage) {
                if (targetErrorSpan) {
                    targetErrorSpan.textContent = errorMessage;
                    targetErrorSpan.style.display = 'block';
                    
                    const targetInput = (targetErrorSpan === titleErrorSpan) ? titleInput : newPostContent;
                    targetInput.style.border = '1px solid #ef4444';
                    targetInput.style.backgroundColor = '#fef2f2';
                    
                    setTimeout(() => {
                        targetErrorSpan.style.display = 'none';
                        targetInput.style.border = (targetInput === titleInput) ? 'none' : 'none';
                        targetInput.style.backgroundColor = (targetInput === titleInput) ? '#f3f4f6' : '#f3f4f6';
                    }, 4000);
                }
                
                showToast(errorMessage);
                return;
            }

            const originalText = submitPostBtn.textContent;
            submitPostBtn.textContent = 'Publication...';
            submitPostBtn.disabled = true;

            try {
                const formData = new FormData();
                if (titre) formData.append('titre', titre);
                formData.append('type_post', typePost);
                const iconInput = document.getElementById('new-post-icon');
                if (iconInput) {
                    Array.from(iconInput.selectedOptions).forEach(option => {
                        formData.append('icon_id[]', option.value);
                    });
                }
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
                    showToast(data.message || 'Erreur lors de la publication');
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
                    showToast(data.message);
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
                    showToast(data.message);
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
        
        showCustomConfirm(async () => {
            try {
                const response = await fetch('api/delete_post.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_post: postId })
                });
                const data = await response.json();
                if (data.success) {
                    article.remove();
                    showSuccessModal('La publication a été supprimée avec succès.');
                } else {
                    showToast(data.message || 'Erreur lors de la suppression');
                }
            } catch (err) {
                console.error(err);
            }
        });
    });

    // 9. Delete Reply
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-delete-reply');
        if (!btn) return;
        
        const replyDiv = btn.closest('.reply');
        const replyId = replyDiv.dataset.replyId;
        
        showCustomConfirm(async () => {
            try {
                const response = await fetch('api/delete_reply.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_reply: replyId })
                });
                const data = await response.json();
                if (data.success) {
                    replyDiv.remove();
                    showSuccessModal('Le commentaire a été supprimé avec succès.');
                } else {
                    showToast(data.message || 'Erreur lors de la suppression');
                }
            } catch (err) {
                console.error(err);
            }
        });
    });

    // 10. Back to Top Logic
    const backToTopBtn = document.getElementById('back-to-top');
    
    if (backToTopBtn) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 400) {
                backToTopBtn.classList.add('show');
            } else {
                backToTopBtn.classList.remove('show');
            }
        });

        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // 11. User Tagging Autocomplete
    if (typeof allUsers !== 'undefined' && Array.isArray(allUsers)) {
        const autocompleteDiv = document.createElement('div');
        autocompleteDiv.className = 'tag-autocomplete';
        document.body.appendChild(autocompleteDiv);
        
        let currentTarget = null;
        let currentMatchStart = -1;
        let currentMatchEnd = -1;

        function closeAutocomplete() {
            autocompleteDiv.classList.remove('show');
            currentTarget = null;
        }

        document.addEventListener('input', (e) => {
            if (e.target.tagName !== 'TEXTAREA' && e.target.tagName !== 'INPUT') return;
            
            // Ne pas autoriser les tags dans les champs de titre
            if (e.target.id === 'new-post-title' || e.target.id === 'quick_titre' || e.target.id === 'titre') return;
            
            const val = e.target.value;
            const cursorStart = e.target.selectionStart;
            
            const textBeforeCursor = val.substring(0, cursorStart);
            const match = textBeforeCursor.match(/(?:[\s\n]|^)(@[^@\n]*)$/);
            
            if (match) {
                const query = match[1].substring(1).toLowerCase();
                const filteredUsers = allUsers.filter(u => u.nom_utilisateur.toLowerCase().includes(query));
                
                if (filteredUsers.length > 0) {
                    currentTarget = e.target;
                    currentMatchStart = match.index + (textBeforeCursor[match.index] === ' ' || textBeforeCursor[match.index] === '\n' ? 1 : 0);
                    currentMatchEnd = cursorStart;
                    
                    autocompleteDiv.innerHTML = '';
                    filteredUsers.slice(0, 6).forEach(u => {
                        const item = document.createElement('div');
                        item.className = 'tag-item';
                        item.innerHTML = `<i class="fas fa-user-circle" style="color:var(--green-mid)"></i> ${u.nom_utilisateur}`;
                        item.addEventListener('mousedown', (ev) => {
                            ev.preventDefault(); // Empêcher la perte de focus du textarea
                            const fullVal = currentTarget.value;
                            const newVal = fullVal.substring(0, currentMatchStart) + `@${u.nom_utilisateur} ` + fullVal.substring(currentMatchEnd);
                            currentTarget.value = newVal;
                            closeAutocomplete();
                            currentTarget.focus();
                        });
                        autocompleteDiv.appendChild(item);
                    });
                    
                    const rect = e.target.getBoundingClientRect();
                    autocompleteDiv.style.left = `${rect.left + window.scrollX}px`;
                    // Positionnement sous le curseur n'est pas trivial, on le met sous le textarea
                    autocompleteDiv.style.top = `${rect.bottom + window.scrollY + 2}px`;
                    autocompleteDiv.classList.add('show');
                } else {
                    closeAutocomplete();
                }
            } else {
                closeAutocomplete();
            }
        });

        document.addEventListener('click', (e) => {
            if (!autocompleteDiv.contains(e.target)) {
                closeAutocomplete();
            }
        });
        
        // Cacher si on perd le focus ou appuie sur escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && autocompleteDiv.classList.contains('show')) {
                closeAutocomplete();
            }
        });
    }

});
