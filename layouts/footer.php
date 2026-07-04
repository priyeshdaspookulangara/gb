<?php
// layouts/footer.php
?>
        </div> <!-- content-page -->
    </div> <!-- main-wrapper -->

    <script>
        // Sidebar Toggle
        const toggleBtn = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar');
        if(toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('open');
            });
        }

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
