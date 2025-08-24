// Room details modal functionality without AJAX
function showRoomDetailsModal(roomCard) {
  // Extract room data from the data attributes
  const roomCard$ = $(roomCard);
  const roomId = roomCard$.data('room-id');
  const roomName = roomCard$.data('room-name');
  const buildingName = roomCard$.data('building-name');
  const roomType = roomCard$.data('room-type');
  const capacity = roomCard$.data('capacity');
  const status = roomCard$.data('status');
  const statusText = roomCard$.data('status-text');
  const statusClass = roomCard$.data('status-class');
  const hasEquipment = roomCard$.data('has-equipment') === true;

  // Set status icon based on status
  let statusIcon = 'check';
  if (status === 'occupied') {
    statusIcon = 'warning';
  } else if (status === 'maintenance') {
    statusIcon = 'wrench';
  }

  // Load template
  let template = $('#roomDetailsTemplate').html();

  // Initial equipment list placeholder
  let equipmentList = '';
  if (hasEquipment) {
    equipmentList =
      '<div class="equipment-loading">' +
      '<i class="fa fa-spinner fa-spin"></i> Loading equipment details...' +
      '</div>';

    // Fetch equipment details
    $.ajax({
      url: 'get_equipment_details.php',
      type: 'GET',
      data: { room_id: roomId },
      dataType: 'json',
      success: function (response) {
        if (
          response.success &&
          response.equipment &&
          response.equipment.length > 0
        ) {
          let equipmentHtml = '';

          response.equipment.forEach(function (item) {
            const statusClass = 'status-' + item.status.toLowerCase();
            equipmentHtml += `
              <div class="equipment-item">
                <div class="name-status">
                <span class="equipment-name">${item.name}</span>
                <span class="equipment-status ${statusClass}">${
              item.status
            }</span>
                </div>
                <div class="equipment-details">
                ${
                  item.description
                    ? `<div class="equipment-description">${item.description}</div>`
                    : ''
                }
                </div>
              </div>
            `;
          });

          $('.equipment-list').html(equipmentHtml);
        } else {
          $('.equipment-list').html(
            '<div class="no-equipment">No detailed equipment information available.</div>'
          );
        }
      },
      error: function () {
        $('.equipment-list').html(
          '<div class="equipment-error">Error loading equipment details.</div>'
        );
      },
    });
  } else {
    equipmentList =
      '<div class="no-equipment">No equipment found for this room.</div>';
  }

  // Empty reserve button section
  let reserveButton = '';

  // Replace placeholders in template
  template = template
    .replace('{roomName}', roomName)
    .replace(/{buildingName}/g, buildingName)
    .replace('{roomType}', roomType)
    .replace('{capacity}', capacity)
    .replace(/{statusText}/g, statusText)
    .replace('{statusClass}', statusClass)
    .replace('{statusIcon}', statusIcon)
    .replace('{statusTooltip}', '')
    .replace('{equipmentList}', equipmentList)
    .replace('{reserveButton}', reserveButton);

  // Update modal content
  $('#roomDetailsContent').html(template);

  // Show modal
  $('#roomDetailsModal').modal('show');
}
