function toggleIcon(element) {
    const icon = element.querySelector('.toggle-icon i');
    if (icon.classList.contains('mdi-plus')) {
        icon.classList.remove('mdi-plus');
        icon.classList.add('mdi-minus');
    } else {
        icon.classList.remove('mdi-minus');
        icon.classList.add('mdi-plus');
    }
}

function showCustomAlert(message, type = 'success') {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `custom-alert ${type}`;

    // Add message and close button
    alertDiv.innerHTML = `
${message}
`;

    // Add to document
    document.body.appendChild(alertDiv);

    // Show alert
    alertDiv.style.display = 'block';

    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentElement) {
            alertDiv.style.opacity = '0';
            alertDiv.style.transform = 'translateX(100%)';
            setTimeout(() => alertDiv.remove(), 300);
        }
    }, 5000);
}

