<script>
    window.onload = function() {
        <?php
        if (isset($_SESSION['success_message'])) {
            echo 'showCustomAlert("' . addslashes($_SESSION['success_message']) . '", "success");';
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo 'showCustomAlert("' . addslashes($_SESSION['error_message']) . '", "error");';
            unset($_SESSION['error_message']);
        }
        ?>
    }

        // Show the modal if there are messages
    <?php if ($success_message): ?>
        showModal("Success", "<?php echo addslashes($success_message); ?>", "success");
    <?php endif; ?>

    <?php if ($error_message): ?>
        showModal("Error", `<?php echo str_replace('`', '\`', $error_message); ?>`, "error");
    <?php endif; ?>
</script>