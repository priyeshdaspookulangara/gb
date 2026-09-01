<?php
// layouts/footer.php
?>
        </div> <!-- content-page -->
    </div> <!-- main-wrapper -->

    <script>
        // Sidebar Toggle & Mobile Overlay Management
        const toggleBtn = document.getElementById('sidebar-toggle');
        const sidebarCloseBtn = document.getElementById('sidebar-close');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');

        function openSidebar() {
            if (sidebar) sidebar.classList.add('open');
            if (overlay) overlay.classList.add('active');
            document.body.classList.add('sidebar-active');
        }

        function closeSidebar() {
            if (sidebar) sidebar.classList.remove('open');
            if (overlay) overlay.classList.remove('active');
            document.body.classList.remove('sidebar-active');
        }

        if (toggleBtn) {
            toggleBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                if (window.innerWidth <= 992) {
                    if (sidebar && sidebar.classList.contains('open')) {
                        closeSidebar();
                    } else {
                        openSidebar();
                    }
                } else {
                    document.body.classList.toggle('sidebar-collapsed');
                }
            });
        }

        if (sidebarCloseBtn) {
            sidebarCloseBtn.addEventListener('click', closeSidebar);
        }

        if (overlay) {
            overlay.addEventListener('click', closeSidebar);
        }

        // Close mobile sidebar on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && sidebar && sidebar.classList.contains('open')) {
                closeSidebar();
            }
        });

        // Close mobile sidebar when clicking menu links on mobile screens
        const sidebarLinks = document.querySelectorAll('.sidebar-menu a');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 992) {
                    closeSidebar();
                }
            });
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth > 992) {
                closeSidebar();
            }
        });

        // Live Time
        function updateTime() {
            const now = new Date();
            const liveTime = document.getElementById('live-time');
            if (liveTime) {
                liveTime.innerText = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }
        }
        setInterval(updateTime, 1000);
        updateTime();
    </script>
</body>
</html>
