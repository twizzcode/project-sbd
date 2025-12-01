    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="container mx-auto px-4 py-6">
            <div class="text-center text-gray-600 text-sm">
                <p>&copy; <?= date('Y') ?> VetClinic. All rights reserved.</p>
                <p class="mt-2">
                    <i class="fas fa-phone mr-2"></i>Emergency Hotline: +62 123-456-7890
                </p>
            </div>
        </div>
    </footer>

    <!-- jQuery - Minimal setup -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
    // Simple smooth scroll for owner portal
    $(document).ready(function() {
        $('a[href^="#"]').on('click', function(e) {
            e.preventDefault();
            const target = $(this.getAttribute('href'));
            if(target.length) {
                $('html, body').animate({
                    scrollTop: target.offset().top - 80
                }, 500);
            }
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert-auto-dismiss').fadeOut('slow');
        }, 5000);

        // Confirm logout
        $('a[href*="logout"]').on('click', function(e) {
            if (!confirm('Are you sure you want to logout?')) {
                e.preventDefault();
            }
        });
    });
    </script>
</body>
</html>
