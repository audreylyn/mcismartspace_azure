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
          // Group equipment by name
          const equipmentGroups = {};
          response.equipment.forEach(function(item) {
            if (!equipmentGroups[item.name]) {
              equipmentGroups[item.name] = {
                name: item.name,
                description: item.description,
                statuses: {}
              };
            }
            equipmentGroups[item.name].statuses[item.status] = {
              status: item.status,
              quantity: parseInt(item.quantity),
              description: item.description
            };
          });

          // Generate HTML for grouped equipment
          let equipmentHtml = '';
          for (const equipmentName in equipmentGroups) {
            const equipment = equipmentGroups[equipmentName];
            equipmentHtml += `
              <div class="equipment-item">
                <div class="equipment-header">
                  <span class="equipment-name">${equipment.name}</span>
                </div>`;
            
            // Show status breakdown
            for (const status in equipment.statuses) {
              const statusInfo = equipment.statuses[status];
              const statusClass = 'status-' + status.toLowerCase().replace(/ /g, '-');
              const displayStatus = status.replace(/_/g, ' ');
              
              equipmentHtml += `
                <div class="equipment-status-item">
                  <span class="status-indicator ${statusClass}">${displayStatus}</span>
                  <span class="quantity">(${statusInfo.quantity})</span>`;
                  
              // Add warning icon for non-working equipment
              if (status !== 'working') {
                equipmentHtml += `<span class="warning-icon" title="Requires attention"><i class="fa fa-exclamation-triangle"></i></span>`;
              }
              
              equipmentHtml += `</div>`;
            }
            
            // Add description if available
            if (equipment.description) {
              equipmentHtml += `<div class="equipment-description">${equipment.description}</div>`;
            }
            
            equipmentHtml += `</div>`;
          }

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
