/**
 * reNaI3es — client JS (≤150 рядків)
 * Мобільне меню, kanban drag-and-drop, автоприховування flash, підтвердження дій.
 */

// === Мобільне меню ===
(function() {
    var toggle = document.getElementById('mobileToggle');
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebarOverlay');
    if (!toggle || !sidebar) return;

    function closeMenu() {
        sidebar.classList.remove('open');
        if (overlay) overlay.classList.remove('open');
    }

    toggle.addEventListener('click', function() {
        sidebar.classList.toggle('open');
        if (overlay) overlay.classList.toggle('open');
    });

    if (overlay) overlay.addEventListener('click', closeMenu);

    // Закрити при кліку на посилання
    sidebar.querySelectorAll('.sidebar-link').forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) closeMenu();
        });
    });
})();

// === Kanban drag-and-drop ===
(function() {
    var board = document.querySelector('.kanban-board');
    if (!board) return;
    var dragged = null;

    board.querySelectorAll('.kanban-card').forEach(function(card) {
        card.setAttribute('draggable', 'true');
        card.addEventListener('dragstart', function(e) {
            dragged = card;
            card.classList.add('dragging');
            e.dataTransfer.setData('text/plain', card.dataset.id || '');
        });
        card.addEventListener('dragend', function() {
            card.classList.remove('dragging');
            dragged = null;
        });
    });

    board.querySelectorAll('.kanban-column').forEach(function(col) {
        col.addEventListener('dragover', function(e) { e.preventDefault(); });
        col.addEventListener('drop', function(e) {
            e.preventDefault();
            if (!dragged) return;
            var list = col.querySelector('.kanban-list') || col;
            list.appendChild(dragged);
            var taskId = dragged.dataset.id;
            var newStatus = col.dataset.status;
            if (taskId && newStatus) {
                var csrf = document.querySelector('meta[name="csrf-token"]');
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = dragged.dataset.statusUrl || ('/tasks/' + taskId + '/status');
                form.innerHTML = '<input type="hidden" name="_csrf" value="' + (csrf ? csrf.content : '') + '">' +
                    '<input type="hidden" name="status" value="' + newStatus + '">';
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
})();

// === Автоприховування flash ===
document.querySelectorAll('.flash').forEach(function(el) {
    setTimeout(function() {
        el.style.transition = 'opacity 300ms';
        el.style.opacity = '0';
        setTimeout(function() { el.remove(); }, 300);
    }, 5000);
});

// === Підтвердження небезпечних дій ===
document.querySelectorAll('[data-confirm]').forEach(function(el) {
    el.addEventListener('click', function(e) {
        if (!confirm(el.dataset.confirm || 'Ви впевнені?')) e.preventDefault();
    });
});
