$(document).ready(function() {
    var table = $('#kodebooking-table').DataTable({
        // Pengaturan DataTables lainnya
        drawCallback: function () {
            var startDate = $('#start_date').val();
            var endDate = $('#end_date').val();
            var dataFound = false; // Flag untuk mengecek apakah ada data yang ditemukan

            this.api().rows().every(function () {
                var data = this.data();
                var tanggalPeriksa = data.tanggalperiksa;

                // Pastikan tanggal dalam format yang sesuai (YYYY-MM-DD)
                if (startDate && endDate) {
                    if (tanggalPeriksa >= startDate && tanggalPeriksa <= endDate) {
                        $(this.node()).show();
                        dataFound = true; // Data ditemukan dalam rentang tanggal
                    } else {
                        $(this.node()).hide();
                    }
                } else {
                    $(this.node()).show(); // Tampilkan semua data jika tidak ada filter tanggal
                    dataFound = true;
                }
            });

            // Jika tidak ada data yang ditemukan, tampilkan pesan
            if (!dataFound) {
                $('#data_kodebooking tbody').html('<tr><td colspan="7" class="text-center">Tidak ada data yang ditemukan.</td></tr>');
            }
        }
    });

    // Filter function
    $('#filter').click(function () {
        $('#loading').show(); // Tampilkan loading
        table.draw();  // Redraw table dengan filter baru
        $('#loading').hide(); // Sembunyikan loading setelah draw selesai
    });

    // Reset function
    $('#reset').click(function () {
        $('#start_date').val('');
        $('#end_date').val('');
        table.draw(); // Redraw table setelah reset filter
    });

    // Cek apakah ada parameter "message" di URL
    var urlParams = new URLSearchParams(window.location.search);
    var messageFilter = urlParams.get('message');

    if (messageFilter) {
        table.columns(4).search(messageFilter).draw(); // Kolom ke-4 adalah kolom 'message'
    }

    // Export function
    document.getElementById('export').addEventListener('click', function() {
        window.location.href = '/export_kodebooking'; // Mengarahkan ke halaman export
    });
});
