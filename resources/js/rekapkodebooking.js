document.getElementById('filter').addEventListener('click', function() {
    var filterDate = document.getElementById('filter_date').value;
    if (filterDate) {
        window.location.href = route('rekap_kodebooking') + '?filter_date=' + filterDate;
    }
});

document.getElementById('reset').addEventListener('click', function() {
    window.location.href = route('rekap_kodebooking');
});
