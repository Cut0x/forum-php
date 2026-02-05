</main>
<?php
$footerText = 'Forum';
$footerLink = '';
$footerCategories = [];
$footerLinks = [];
if ($pdo) {
    $footerText = get_setting($pdo, 'footer_text', $footerText) ?? $footerText;
    $footerLink = get_setting($pdo, 'footer_link', '') ?? '';
    $footerCategories = $pdo->query('SELECT id, name FROM footer_categories ORDER BY sort_order, name')->fetchAll();
    $footerLinks = $pdo->query('SELECT id, category_id, label, url FROM footer_links ORDER BY sort_order, label')->fetchAll();
}
?>
<footer class="mt-auto app-footer border-top py-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="fw-semibold mb-2">
                    <?php if ($footerLink): ?>
                        <a class="text-decoration-none" href="<?php echo e($footerLink); ?>" target="_blank" rel="noopener"><?php echo e($footerText); ?></a>
                    <?php else: ?>
                        <?php echo e($footerText); ?>
                    <?php endif; ?>
                </div>
                <div class="text-muted small">Forum open-source.</div>
            </div>
            <div class="col-lg-8">
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
        </div>
        <div class="d-flex justify-content-between mt-4 small text-muted">
            <span>&copy; 2026</span>
            <span></span>
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
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
                        row.innerHTML = `<img src="${item.avatar || 'assets/default_user.jpg'}" class="rounded-circle" width="24" height="24" alt=""> <strong>@${item.username}</strong>`;
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
</script>
</body>
</html>
