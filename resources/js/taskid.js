$(document).ready(function () {
    var table = $('#kodebooking-table').DataTable({
        drawCallback: function () {
            var startDate = $('#start_date').val();
            var endDate = $('#end_date').val();
            var dataFound = false; // Flag untuk mengecek apakah ada data

            this.api().rows().every(function () {
                var data = this.data();
                var tanggalPeriksa = data.tanggal;

                if (startDate && endDate) {
                    if (tanggalPeriksa >= startDate && tanggalPeriksa <= endDate) {
                        $(this.node()).show();
                        dataFound = true; // Jika ada data, set flag ke true
                    } else {
                        $(this.node()).hide();
                    }
                } else {
                    $(this.node()).show();
                    dataFound = true; // Jika tidak ada filter, set flag ke true
                }
            });

            // Jika tidak ada data yang ditemukan
            if (!dataFound) {
                $('#taskid-body').html('<tr><td colspan="12">Tidak ada data yang ditemukan.</td></tr>');
            }
        },
    });
    // Custom filter for Task ID
    $.fn.dataTable.ext.search.push(
        function (settings, data, dataIndex) {
        var taskIDFilter = $('#taskid_filter').val().toLowerCase();
        var taskID = data[3].toLowerCase(); // Index 3 is the Task ID column

        if (taskIDFilter) {
            return taskID.includes(taskIDFilter);
        }

        return true;
        }
    );

    // Apply the filter
    $('#taskid_filter').keyup(function () {
        table.draw();
    });

    // Filter function
    $('#filter').click(function () {
        $('#loading').show(); // Tampilkan loading
        table.draw();
        $('#loading').hide(); // Sembunyikan loading setelah draw
    });

    // Reset function
    $('#reset').click(function () {
        $('#start_date').val('');
        $('#end_date').val('');
        table.draw();
    });
});
$('#download_excel').click(function () {
window.location.href = '/export-taskid';
    });

