// admin.js

// Admin Modal Functions
function openAdminModal() {
    document.getElementById('adminModal').style.display = 'block';
}

function closeAdminModal() {
    document.getElementById('adminModal').style.display = 'none';
    document.getElementById('adminForm').reset();
    document.getElementById('adminMessage').innerHTML = '';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('adminModal');
    if (event.target == modal) {
        closeAdminModal();
    }
}

// Dropdown functionality has been moved to event-dropdown.js

// AJAX form submission
document.addEventListener('DOMContentLoaded', function () {
    const adminForm = document.getElementById('adminForm');
    const messageDiv = document.getElementById('adminMessage');

    adminForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                messageDiv.innerHTML = data.message;
                messageDiv.className = 'message success';
                setTimeout(() => {
                    closeAdminModal();
                    window.location.reload();
                }, 1500);
            } else {
                messageDiv.innerHTML = data.message;
                messageDiv.className = 'message error';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            messageDiv.innerHTML = 'An error occurred';
            messageDiv.className = 'message error';
        });
    });
});
