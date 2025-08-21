<?php
// This file contains the footer HTML for registrar pages
?>
    </div>
    <script type="text/javascript" src="../public/js/admin_scripts/main.min.js"></script>
    <?php if (isset($additionalScripts)): ?>
    <?php echo $additionalScripts; ?>
    <?php endif; ?>
    <script>
        // Toggle dropdown menu for sidebar
        function toggleIcon(el) {
            el.classList.toggle("active");
            var icon = el.querySelector(".toggle-icon i");
            if (icon.classList.contains("mdi-plus")) {
                icon.classList.remove("mdi-plus");
                icon.classList.add("mdi-minus");
            } else {
                icon.classList.remove("mdi-minus");
                icon.classList.add("mdi-plus");
            }
            var submenu = el.nextElementSibling;
            if (submenu) {
                if (submenu.style.display === "block") {
                    submenu.style.display = "none";
                } else {
                    submenu.style.display = "block";
                }
            }
        }
    </script>
</body>
</html>
