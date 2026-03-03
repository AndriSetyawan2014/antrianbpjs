let lastTaskID = 0; // Menyimpan Task ID terakhir yang dikirim

document.getElementById('antrianForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Mencegah halaman refresh

    const taskID = parseInt(document.getElementById('taskID').value);
    
    // Validasi urutan Task ID
    if (!isValidTaskID(taskID)) {
        showMessage('Task ID tidak valid atau melanggar urutan!', 'alert alert-danger');
        return;
    }

    // Kirim data ke API
    const data = { taskID };

    axios.post('http://localhost:8000/api/add-antrian', data)
        .then(response => {
            showMessage('Task ID ' + taskID + ' berhasil dikirim!', 'alert alert-success');
            lastTaskID = taskID; // Update Task ID terakhir
            document.getElementById('antrianForm').reset();
        })
        .catch(error => {
            showMessage('Terjadi kesalahan: ' + error.response.data.message, 'alert alert-danger');
        });
});

// Fungsi untuk memvalidasi urutan Task ID
function isValidTaskID(taskID) {
    if (taskID === 99) return true; // Task ID 99 selalu valid untuk batal

    if (taskID > lastTaskID + 1) return false; // Tidak boleh melangkahi

    return true; // Valid
}

function showMessage(message, className) {
    const messageDiv = document.getElementById('message');
    messageDiv.textContent = message;
    messageDiv.className = className + ' alert alert-dismissible fade show';
    messageDiv.innerHTML += '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
}