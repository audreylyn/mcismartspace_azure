document.addEventListener('DOMContentLoaded', function () {
  // Define the fade-out timer for alerts (in milliseconds)
  const fadeTime = 1700; // 3 seconds
  const transitionTime = 1000; // 1 second transition time

  // Get all alert elements as a NodeList
  const alerts = document.querySelectorAll('.alert');

  // Apply fade-out to each alert
  alerts.forEach(function (alert) {
    if (alert) {
      setTimeout(function () {
        // Start the fade-out effect
        alert.classList.add('fade-out');

        // Remove the element after the transition completes
        setTimeout(function () {
          alert.style.display = 'none';
        }, transitionTime);
      }, fadeTime);
    }
  });
});
