                </div>
            </main>
        </div>
    </div>

    <!-- jQuery - Required for DataTables -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- DataTables - For table pagination and search -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    
    <!-- Sweet Alert - For confirmation dialogs -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <?php if (isset($use_chart) && $use_chart === true): ?>
    <!-- Chart.js for dashboard graphics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/assets/js/charts.js"></script>
    <?php endif; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mobile menu toggle
        const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
        if (mobileSidebarToggle) {
            mobileSidebarToggle.addEventListener('click', function(event) {
                event.stopPropagation();
                const sidebar = document.getElementById('sidebar');
                sidebar.classList.toggle('show');
            });
        }

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggleButton = document.getElementById('mobileSidebarToggle');
            if (sidebar && toggleButton && !sidebar.contains(event.target) && !toggleButton.contains(event.target)) {
                if (window.innerWidth < 1024) {
                    sidebar.classList.remove('show');
                }
            }
        });

        // Notification toggle
        const notificationButton = document.getElementById('notificationButton');
        if (notificationButton) {
            notificationButton.addEventListener('click', function(event) {
                event.stopPropagation();
                const notificationDropdown = document.getElementById('notificationDropdown');
                const userMenu = document.getElementById('userMenu');
                if (notificationDropdown) {
                    notificationDropdown.classList.toggle('hidden');
                }
                if (userMenu) {
                    userMenu.classList.add('hidden');
                }
            });
        }

        // User menu toggle
        const userMenuButton = document.getElementById('userMenuButton');
        if (userMenuButton) {
            userMenuButton.addEventListener('click', function(event) {
                event.stopPropagation();
                const userMenu = document.getElementById('userMenu');
                const notificationDropdown = document.getElementById('notificationDropdown');
                if (userMenu) {
                    userMenu.classList.toggle('hidden');
                }
                if (notificationDropdown) {
                    notificationDropdown.classList.add('hidden');
                }
            });
        }

        // Close menus when clicking outside
        document.addEventListener('click', function(event) {
            const userMenuButton = document.getElementById('userMenuButton');
            const userMenu = document.getElementById('userMenu');
            const notificationButton = document.getElementById('notificationButton');
            const notificationDropdown = document.getElementById('notificationDropdown');
            
            if (userMenu && !event.target.closest('#userMenuButton')) {
                userMenu.classList.add('hidden');
            }
            if (notificationDropdown && !event.target.closest('#notificationButton')) {
                notificationDropdown.classList.add('hidden');
            }
        });

        // Initialize DataTables if present
        if (typeof $ !== 'undefined' && $.fn.DataTable) {
            $(document).ready(function() {
                if ($('.datatable').length) {
                    $('.datatable').DataTable({
                        language: {
                            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                        }
                    });
                }
            });
        }
    });
    </script>
</body>
</html>