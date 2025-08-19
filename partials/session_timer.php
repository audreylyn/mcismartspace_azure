<script>
document.addEventListener('DOMContentLoaded', function() {
    const sessionTimeout = <?php echo $sessionManager->getSessionTimeout(); ?>;
    const warningTime = 30; // 5 minutes in seconds
    let sessionTimer;
    let warningTimer;

    function startSessionTimers() {
        // Clear existing timers
        clearTimeout(sessionTimer);
        clearTimeout(warningTimer);

        // Time until session expires
        const timeToExpire = sessionTimeout;

        // Time until warning should be shown
        const timeToWarning = timeToExpire - warningTime;

        // Set timer for session timeout warning
        if (timeToWarning > 0) {
            warningTimer = setTimeout(showSessionWarning, timeToWarning * 1000);
        }

        // Set timer for actual session timeout
        sessionTimer = setTimeout(logout, timeToExpire * 1000);
    }

    function showSessionWarning() {
        // You can use a modal or a simple alert
        if (confirm("Your session is about to expire. Do you want to extend it?")) {
            extendSession();
        }
    }

    function extendSession() {
        fetch('../auth/extend_session.php', {
            method: 'POST',
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Session extended');
                startSessionTimers(); // Restart timers
            } else {
                logout();
            }
        })
        .catch(() => {
            logout();
        });
    }

    function logout() {
        window.location.href = '../auth/logout.php';
    }

    // Start timers on page load
    startSessionTimers();

    // Optional: Reset timers on user activity
    document.addEventListener('click', startSessionTimers);
    document.addEventListener('keypress', startSessionTimers);
});
</script>
