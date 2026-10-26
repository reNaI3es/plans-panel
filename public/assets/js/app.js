/**
 * reNaI3es — клієнтський JS (≤150 рядків)
 * three.js hero (lazy), tilt-ефект карточок, kanban drag-and-drop, плавний скролл, a11y.
 */

// === three.js hero (лише якщо WebGL доступний і не мобільний) ===
(function initHero() {
    if (window.innerWidth < 768) return;
    if (!window.WebGLRenderingContext) return;
    const canvas = document.getElementById('hero-canvas');
    if (!canvas) return;
    const script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js';
    script.onload = function() {
        if (typeof THREE === 'undefined') return;
        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(75, canvas.clientWidth / canvas.clientHeight, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true });
        renderer.setSize(canvas.clientWidth, canvas.clientHeight);
        camera.position.z = 5;
        const geo = new THREE.IcosahedronGeometry(2, 1);
        const mat = new THREE.MeshBasicMaterial({ color: 0x4a9eff, wireframe: true, transparent: true, opacity: 0.3 });
        const mesh = new THREE.Mesh(geo, mat);
        scene.add(mesh);
        let frame = 0;
        function animate() {
            frame = requestAnimationFrame(animate);
            mesh.rotation.x += 0.002;
            mesh.rotation.y += 0.003;
            renderer.render(scene, camera);
        }
        animate();
        window.addEventListener('resize', function() {
            camera.aspect = canvas.clientWidth / canvas.clientHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(canvas.clientWidth, canvas.clientHeight);
        });
    };
    document.head.appendChild(script);
})();

// === Tilt-ефект карточок (≤6°) ===
document.querySelectorAll('[data-tilt]').forEach(function(el) {
    el.addEventListener('mousemove', function(e) {
        const rect = el.getBoundingClientRect();
        const x = (e.clientX - rect.left) / rect.width - 0.5;
        const y = (e.clientY - rect.top) / rect.height - 0.5;
        el.style.transform = 'perspective(600px) rotateY(' + (x * 6) + 'deg) rotateX(' + (-y * 6) + 'deg)';
    });
    el.addEventListener('mouseleave', function() {
        el.style.transform = '';
    });
});

// === Kanban drag-and-drop ===
(function initKanban() {
    const board = document.querySelector('.kanban-board');
    if (!board) return;
    let dragged = null;
    board.querySelectorAll('.kanban-card').forEach(function(card) {
        card.setAttribute('draggable', 'true');
        card.addEventListener('dragstart', function(e) {
            dragged = card;
            card.classList.add('dragging');
            e.dataTransfer.setData('text/plain', card.dataset.id);
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
            const list = col.querySelector('.kanban-list');
            if (list) list.appendChild(dragged);
            // AJAX-запит для оновлення статусу на сервері
            const taskId = dragged.dataset.id;
            const newStatus = col.dataset.status;
            if (taskId && newStatus) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = dragged.dataset.statusUrl || ('/tasks/' + taskId + '/status');
                const csrf = document.querySelector('meta[name="csrf-token"]');
                form.innerHTML = '<input type="hidden" name="_csrf" value="' + (csrf ? csrf.content : '') + '">' +
                    '<input type="hidden" name="status" value="' + newStatus + '">';
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
})();

// === Автоприховування flash-повідомлень ===
document.querySelectorAll('.flash').forEach(function(el) {
    setTimeout(function() { el.style.opacity = '0'; setTimeout(function() { el.remove(); }, 300); }, 5000);
});

// === Підтвердження небезпечних дій ===
document.querySelectorAll('[data-confirm]').forEach(function(el) {
    el.addEventListener('click', function(e) {
        if (!confirm(el.dataset.confirm)) e.preventDefault();
    });
});
