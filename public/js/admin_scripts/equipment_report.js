$(document).ready(function() {
    // Initialize DataTable
    var table = $('#equipmentReportTable').DataTable({
        responsive: true,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search by equipment, room, student...",
            lengthMenu: "_MENU_",
            info: "Showing _START_ to _END_ of _TOTAL_ entries"
        },
        dom: 'rt<"bottom"ip><"clear">',
        pageLength: 10,
        ordering: true,
        columnDefs: [{
            targets: -1,
            orderable: false
        }]
    });

    // Auto-fade success messages after 3 seconds
    if ($('.alert-success').length > 0) {
        setTimeout(function() {
            $('.alert-success').fadeOut(1000, function() {
                $(this).remove();
            });
        }, 1000);
    }

    // Custom search handling
    $('#customSearch').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Handle show entries dropdown
    $('#entries-filter').on('change', function() {
        table.page.len($(this).val()).draw();
    });

    // Custom filtering functionality
    $('#status-filter').on('change', function() {
        table.column(6).search($(this).val()).draw();
    });

    // Location filter - filters the Location column
    $('#location-filter').on('change', function() {
        var searchTerm = $(this).val();
        table.column(2).search(searchTerm).draw();
    });

    // Date filter using custom function
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        var dateFilter = $('#date-filter').val();
        if (!dateFilter) {
            return true; // Show all rows if no date filter
        }

        var reportDate = new Date(data[5]); // Date column
        var today = new Date();
        var daysAgo = new Date();
        daysAgo.setDate(today.getDate() - parseInt(dateFilter));

        return reportDate >= daysAgo;
    });

    $('#date-filter').on('change', function() {
        table.draw();
    });

    // Reset all filters
    $('#reset-filters').on('click', function() {
        $('#status-filter').val('');
        $('#date-filter').val('7');
        $('#location-filter').val('');
        $('#entries-filter').val('10');
        $('#customSearch').val('');
        table.search('').columns().search('').page.len(10).draw();
    });

    // Apply initial filters
    $('#status-filter').trigger('change');
    $('#date-filter').trigger('change');

    // Add this CSS for reporter badges
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .reporter-badge {
                display: inline-block;
                font-size: 0.75rem;
                padding: 0.125rem 0.375rem;
                border-radius: 0.25rem;
                margin-left: 0.375rem;
                background-color: #e2e8f0;
                color: #475569;
            }
        `)
        .appendTo('head');
});

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
