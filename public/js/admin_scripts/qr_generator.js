document.addEventListener('DOMContentLoaded', function() {
    const qrForm = document.getElementById('qr-form');
    const equipmentSelect = document.getElementById('equipment');
    const customId = document.getElementById('custom-id');
    const customName = document.getElementById('custom-name');
    const customRoom = document.getElementById('custom-room');
    const customBuilding = document.getElementById('custom-building');
    const qrInfo = document.getElementById('qr-info');
    const downloadBtn = document.getElementById('downloadBtn');
    // Get toggle for teacher/student mode
    const roleToggle = document.getElementById('role-toggle');

    let qrCode = null;

    // Add event listener to populate form fields when dropdown selection changes
    equipmentSelect.addEventListener('change', function() {
        if (this.value) {
            // Parse the JSON data from the selected option
            const equipmentData = JSON.parse(this.value);

            // Fill the custom fields with the selected equipment's data
            customId.value = equipmentData.id;
            customName.value = equipmentData.name;
            customRoom.value = equipmentData.room;
            customBuilding.value = equipmentData.building;
        } else {
            // Clear the fields if "Select Equipment" is chosen
            customId.value = '';
            customName.value = '';
            customRoom.value = '';
            customBuilding.value = '';
        }
    });

    qrForm.addEventListener('submit', function(e) {
        e.preventDefault();

        let equipmentData;

        if (equipmentSelect.value) {
            equipmentData = JSON.parse(equipmentSelect.value);
        } else if (customId.value && customName.value) {
            equipmentData = {
                id: customId.value,
                name: customName.value,
                room: customRoom.value || 'Unknown',
                building: customBuilding.value || 'Unknown'
            };
        } else {
            alert('Please select equipment or enter custom details');
            return;
        }

        // Create the QR code content - URL with query parameters
        const baseUrl = window.location.origin + '/mcmod/redirect-equipment-report.php';
        const qrContent = `${baseUrl}?id=${encodeURIComponent(equipmentData.id)}&name=${encodeURIComponent(equipmentData.name)}&room=${encodeURIComponent(equipmentData.room)}&building=${encodeURIComponent(equipmentData.building)}`;

        // Display equipment info
        qrInfo.innerHTML = `
            <p><strong>${equipmentData.name}</strong></p>
            <p class="equipment-id">ID: ${equipmentData.id}</p>
            <p class="location">Location: ${equipmentData.room}, ${equipmentData.building}</p>
        `;

        // Generate QR code
        if (qrCode !== null) {
            qrCode.clear();
            qrCode.makeCode(qrContent);
        } else {
            qrCode = new QRCode(document.getElementById("qrcode"), {
                text: qrContent,
                width: 200,
                height: 200,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        }

        // Show download button and set up download functionality
        downloadBtn.style.display = 'block';
        downloadBtn.onclick = function() {
            const canvas = document.querySelector('#qrcode canvas');
            if (canvas) {
                const dataURL = canvas.toDataURL('image/png');
                const a = document.createElement('a');
                a.href = dataURL;
                a.download = `QR_${equipmentData.name}_${equipmentData.id}.png`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            }
        };
    });
});
