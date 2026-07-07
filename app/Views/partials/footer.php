    </main>
</div><!-- /.admin-layout -->

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
// Auto-dismiss flash messages after 6 seconds
document.querySelectorAll('.flash-message').forEach(function(el) {
    setTimeout(function() {
        el.style.transition = 'opacity 0.5s, transform 0.5s';
        el.style.opacity = '0';
        el.style.transform = 'translateY(-10px)';
        setTimeout(function() { el.remove(); }, 500);
    }, 6000);
});
</script>
</body>
</html>
