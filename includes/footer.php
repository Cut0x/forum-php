</main>
<?php
$footerText = 'Forum PHP';
$footerLink = '';
$footerCategories = [];
$footerLinks = [];
$emotesData = [];
$baseUrl = function_exists('app_base_url') ? app_base_url() : '';
$pdoReady = isset($pdo) && $pdo;
if ($pdoReady) {
    $footerText = get_setting($pdo, 'footer_text', $footerText) ?? $footerText;
    $footerLink = get_setting($pdo, 'footer_link', '') ?? '';
    $footerCategories = $pdo->query('SELECT id, name FROM footer_categories ORDER BY sort_order, name')->fetchAll();
    $footerLinks = $pdo->query('SELECT id, category_id, label, url FROM footer_links ORDER BY sort_order, label')->fetchAll();
    try {
        $emotesData = $pdo->query('SELECT name, file, title FROM emotes WHERE is_enabled = 1 ORDER BY name')->fetchAll();
    } catch (Throwable $e) {
        $emotesData = [];
    }
}
$emotesJson = json_encode($emotesData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE);
if ($emotesJson === false) {
    $emotesJson = '[]';
}
$emotesData = [];
if ($pdo) {
    try {
        $emotesData = $pdo->query('SELECT name, file, title FROM emotes WHERE is_enabled = 1 ORDER BY name')->fetchAll();
    } catch (Throwable $e) {
        $emotesData = [];
    }
}
?>
<footer class="mt-auto app-footer py-5">
    <div class="container">
        <div class="footer-slab">
            <div class="footer-grid">
                <div>
                    <div class="footer-brand mb-2">
                        <?php if ($footerLink): ?>
                            <a class="text-decoration-none" href="<?php echo e($footerLink); ?>" target="_blank" rel="noopener"><?php echo e($footerText); ?></a>
                        <?php else: ?>
                            <?php echo e($footerText); ?>
                        <?php endif; ?>
                    </div>
                <div class="footer-tagline small">Communautés, discussions et ressources.</div>
                </div>
                <div class="row g-3 footer-links">
                    <?php foreach ($footerCategories as $cat): ?>
                        <div class="col-6 col-md-3">
                            <div class="footer-title mb-2"><?php echo e($cat['name']); ?></div>
                            <?php foreach ($footerLinks as $link): ?>
                                <?php if ((int) $link['category_id'] === (int) $cat['id']): ?>
                                    <a class="d-block text-decoration-none mb-1" href="<?php echo e($link['url']); ?>" target="_blank" rel="noopener"><?php echo e($link['label']); ?></a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="d-flex justify-content-between mt-4 small text-muted">
                <span>&copy; <?php echo date('Y'); ?></span>
                <span><a class="text-decoration-none" href="sitemap.php">Sitemap</a></span>
            </div>
        </div>
    </div>
</footer>
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-0">
                <img class="image-modal-img" src="" alt="image">
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    window.__EMOTES = <?php echo $emotesJson; ?>;
    window.__BASE_URL = <?php echo json_encode($baseUrl, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE); ?>;
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map((tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl));

    function initMentions(textarea) {
        const box = document.createElement('div');
        box.className = 'mention-box d-none';
        document.body.appendChild(box);

        let current = { start: -1, query: '' };

        function hide() {
            box.classList.add('d-none');
            box.innerHTML = '';
        }

        function position() {
            const rect = textarea.getBoundingClientRect();
            box.style.left = rect.left + window.scrollX + 'px';
            box.style.top = rect.bottom + window.scrollY + 'px';
        }

        function search(query) {
            if (query.length < 2) {
                hide();
                return;
            }
            fetch('mention.php?q=' + encodeURIComponent(query))
                .then(r => r.json())
                .then(items => {
                    if (!items.length) {
                        hide();
                        return;
                    }
                    box.innerHTML = '';
                    items.forEach(item => {
                        const row = document.createElement('div');
                        row.className = 'mention-item d-flex align-items-center gap-2';
                        const display = item.name || item.username;
                        row.innerHTML = `<img src="${item.avatar || 'assets/default_user.jpg'}" class="rounded-circle" width="24" height="24" alt=""> <strong>${display}</strong> <span class="text-muted">@${item.username}</span>`;
                        row.addEventListener('click', () => {
                            const value = textarea.value;
                            const before = value.slice(0, current.start);
                            const after = value.slice(textarea.selectionStart);
                            textarea.value = before + '@' + item.username + ' ' + after;
                            textarea.focus();
                            hide();
                        });
                        box.appendChild(row);
                    });
                    position();
                    box.classList.remove('d-none');
                })
                .catch(hide);
        }

        textarea.addEventListener('input', () => {
            const pos = textarea.selectionStart;
            const value = textarea.value.slice(0, pos);
            const match = value.match(/(^|\s)@([a-zA-Z0-9_]{0,30})$/);
            if (!match) {
                hide();
                return;
            }
            current.start = pos - match[2].length - 1;
            current.query = match[2];
            search(current.query);
        });

        textarea.addEventListener('blur', () => setTimeout(hide, 150));
    }

    document.querySelectorAll('[data-mentions="1"]').forEach(initMentions);

    let emoteCache = Array.isArray(window.__EMOTES) && window.__EMOTES.length ? window.__EMOTES : null;
    function fetchEmotes() {
        if (emoteCache && emoteCache.length > 0) {
            return Promise.resolve(emoteCache);
        }
        return fetch('emotes.php')
            .then(r => r.json())
            .then(items => {
                const list = Array.isArray(items) ? items : [];
                if (list.length > 0) {
                    emoteCache = list;
                } else {
                    emoteCache = null;
                }
                return list;
            })
            .catch(() => []);
    }

    function insertAtCursor(textarea, text) {
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const value = textarea.value;
        textarea.value = value.slice(0, start) + text + value.slice(end);
        textarea.selectionStart = textarea.selectionEnd = start + text.length;
        textarea.focus();
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function initEmotes(textarea) {
        const toolbar = document.createElement('div');
        toolbar.className = 'emote-toolbar';
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-sm btn-outline-secondary';
        button.textContent = 'Émotes';
        const panel = document.createElement('div');
        panel.className = 'emote-panel d-none';
        toolbar.appendChild(button);
        toolbar.appendChild(panel);

        textarea.parentNode.insertBefore(toolbar, textarea);

        let opened = false;
        function closePanel() {
            panel.classList.add('d-none');
            opened = false;
        }

        button.addEventListener('click', () => {
            if (opened) {
                closePanel();
                return;
            }
            fetchEmotes().then(items => {
                panel.innerHTML = '';
                if (!items.length) {
                    const empty = document.createElement('div');
                    empty.className = 'text-muted small';
                    empty.textContent = 'Aucune émote disponible.';
                    panel.appendChild(empty);
                } else {
                    items.forEach(item => {
                        const name = item.name || '';
                        const file = item.file || '';
                        if (!name || !file) {
                            return;
                        }
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'emote-item';
                        btn.title = ':' + name + ':';
                        btn.setAttribute('aria-label', ':' + name + ':');
                        btn.innerHTML = `<img class="emote" src="assets/emotes/${file}" alt=":${name}:">`;
                        btn.addEventListener('click', () => {
                            insertAtCursor(textarea, ':' + name + ': ');
                        });
                        panel.appendChild(btn);
                    });
                }
                panel.classList.remove('d-none');
                opened = true;
            });
        });

        document.addEventListener('click', (e) => {
            if (!opened) return;
            if (toolbar.contains(e.target)) return;
            closePanel();
        });
    }

    document.querySelectorAll('[data-emotes="1"]').forEach(initEmotes);

    function initImages(textarea) {
        let toolbar = textarea.previousElementSibling;
        while (toolbar && !toolbar.classList.contains('emote-toolbar')) {
            toolbar = toolbar.previousElementSibling;
        }
        if (!toolbar) {
            toolbar = document.createElement('div');
            toolbar.className = 'emote-toolbar';
            textarea.parentNode.insertBefore(toolbar, textarea);
        }

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-sm btn-outline-secondary';
        button.textContent = 'Image';
        const status = document.createElement('span');
        status.className = 'text-muted small';
        status.textContent = '';
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*';
        input.className = 'd-none';

        toolbar.appendChild(button);
        toolbar.appendChild(status);
        toolbar.appendChild(input);

        button.addEventListener('click', () => input.click());
        input.addEventListener('change', () => {
            const file = input.files && input.files[0];
            if (!file) return;
            status.textContent = 'Upload...';
            const body = new FormData();
            body.append('image', file);
            const uploadUrl = window.__BASE_URL ? (window.__BASE_URL + '/upload-image.php') : 'upload-image.php';
            fetch(uploadUrl, { method: 'POST', body })
                .then(r => r.json())
                .then(data => {
                    if (!data || !data.url) {
                        throw new Error('upload_failed');
                    }
                    insertAtCursor(textarea, `![](${data.url})`);
                    status.textContent = 'OK';
                    setTimeout(() => { status.textContent = ''; }, 1200);
                })
                .catch(() => {
                    status.textContent = 'Erreur';
                    setTimeout(() => { status.textContent = ''; }, 1500);
                })
                .finally(() => {
                    input.value = '';
                });
        });
    }

    document.querySelectorAll('[data-images="1"]').forEach(initImages);

    const imageModalEl = document.getElementById('imagePreviewModal');
    const imageModalImg = imageModalEl ? imageModalEl.querySelector('img') : null;
    if (imageModalEl && imageModalImg) {
        const imageModal = new bootstrap.Modal(imageModalEl);
        document.addEventListener('click', (e) => {
            const img = e.target.closest('img.post-image');
            if (!img) return;
            if (!img.closest('.content') && !img.closest('.preview-box')) return;
            imageModalImg.src = img.src;
            imageModal.show();
        });
    }
</script>
</body>
</html>
