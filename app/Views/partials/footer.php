    </main>
</div><!-- /.admin-layout -->

<?php if (empty($_hideTourButton)): ?>
<button class="tour-trigger-btn" id="tourTriggerBtn" onclick="startPageTour()" title="Guide interactif">
    <i class="fas fa-question"></i>
</button>
<?php endif; ?>

<script>
function toggleSidebar() {
    document.getElementById('adminSidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('active');
}
function closeSidebar() {
    document.getElementById('adminSidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('active');
}
function toggleDarkMode() {
    document.documentElement.classList.toggle('dark-mode');
    const isDark = document.documentElement.classList.contains('dark-mode');
    localStorage.setItem('darkMode', isDark);
    const icon = document.getElementById('darkModeIcon');
    if (icon) icon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
}
if (document.documentElement.classList.contains('dark-mode')) {
    const icon = document.getElementById('darkModeIcon');
    if (icon) icon.className = 'fas fa-sun';
}
</script>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.js.iife.js"></script>
<script src="<?= APP_URL ?>/assets/js/tour.js"></script>
</body>
</html>
