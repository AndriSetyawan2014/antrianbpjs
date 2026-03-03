document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('filter').addEventListener('click', function() {
        var filterDate = document.getElementById('filter_date').value;
        if (filterDate) {
            window.location.href = filterDateUrl + '?filter_date=' + filterDate;
        }
    });

    document.getElementById('reset').addEventListener('click', function() {
        window.location.href = filterDateUrl;
    });

    $('#data_taskid').DataTable({
        "paging": true,
        "lengthChange": false,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
    });
});
