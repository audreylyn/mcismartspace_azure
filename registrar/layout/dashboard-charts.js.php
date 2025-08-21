<?php
// Dashboard Charts JavaScript

// We need the dashboard data to be passed into this file for it to work
if (!isset($room_status_counts) || !isset($equipment_status) || !isset($room_types) || !isset($rooms_by_department)) {
    echo "<!-- Required dashboard data variables not available -->";
    return;
}
?>
<script>
// Initialize charts once the DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Room Status Chart
    const roomStatusLabels = ['Available', 'Occupied', 'Maintenance'];
    const roomStatusData = [
        <?php echo $room_status_counts['available'] ?? 0; ?>,
        <?php echo $room_status_counts['occupied'] ?? 0; ?>,
        <?php echo $room_status_counts['maintenance'] ?? 0; ?>
    ];
    const roomStatusColors = [
        '#2e7d32', // available - green
        '#f9a825', // occupied - gold
        '#c62828'  // maintenance - red
    ];

    const roomStatusChart = new Chart(
        document.getElementById('roomStatusChart'),
        {
            type: 'doughnut',
            data: {
                labels: roomStatusLabels,
                datasets: [{
                    data: roomStatusData,
                    backgroundColor: roomStatusColors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        display: false
                    }
                }
            }
        }
    );

    // Equipment Status Chart
    const equipmentStatusLabels = ['Working', 'Needs Repair', 'Maintenance', 'Missing'];
    const equipmentStatusData = [
        <?php echo $equipment_status['working'] ?? 0; ?>,
        <?php echo $equipment_status['needs_repair'] ?? 0; ?>,
        <?php echo $equipment_status['maintenance'] ?? 0; ?>,
        <?php echo $equipment_status['missing'] ?? 0; ?>
    ];
    const equipmentStatusColors = [
        '#2e7d32', // working - green
        '#f9a825', // needs_repair - gold
        '#0277bd', // maintenance - blue
        '#c62828'  // missing - red
    ];

    const equipmentStatusChart = new Chart(
        document.getElementById('equipmentStatusChart'),
        {
            type: 'doughnut',
            data: {
                labels: equipmentStatusLabels,
                datasets: [{
                    data: equipmentStatusData,
                    backgroundColor: equipmentStatusColors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        display: false
                    }
                }
            }
        }
    );

    // Room Types Chart
    <?php 
    $type_labels = [];
    $type_data = [];
    foreach ($room_types as $type => $count) {
        $type_labels[] = '"' . $type . '"';
        $type_data[] = $count;
    }
    ?>
    
    const roomTypeLabels = [<?php echo implode(',', $type_labels); ?>];
    const roomTypeData = [<?php echo implode(',', $type_data); ?>];
    const roomTypeColors = [
        '#1e5631',   // primary
        '#d4af37',   // secondary
        '#0f2d1a',   // accent
        '#2e7d32',   // success
        '#c62828',   // danger
        '#f9a825',   // warning
        '#0277bd',   // info
        '#6c757d',   // muted
        '#9c27b0',   // purple
        '#e91e63'    // pink
    ];

    const roomTypesChart = new Chart(
        document.getElementById('roomTypesChart'),
        {
            type: 'bar',
            data: {
                labels: roomTypeLabels,
                datasets: [{
                    label: 'Room Count',
                    data: roomTypeData,
                    backgroundColor: roomTypeColors.slice(0, roomTypeLabels.length),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        }
    );

    // Rooms by Department Chart
    <?php 
    $dept_labels = [];
    $dept_data = [];
    foreach ($rooms_by_department as $dept => $count) {
        $dept_labels[] = '"' . $dept . '"';
        $dept_data[] = $count;
    }
    ?>
    
    const deptLabels = [<?php echo implode(',', $dept_labels); ?>];
    const deptData = [<?php echo implode(',', $dept_data); ?>];
    const deptColors = roomTypeColors.slice(0, deptLabels.length);

    const roomsByDepartmentChart = new Chart(
        document.getElementById('roomsByDepartmentChart'),
        {
            type: 'bar',
            data: {
                labels: deptLabels,
                datasets: [{
                    label: 'Room Count',
                    data: deptData,
                    backgroundColor: deptColors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        }
    );
});
</script>
