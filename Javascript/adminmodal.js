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

    // Dropdown functionality
    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            const menu = this.nextElementSibling;

            // Close all other dropdowns
            document.querySelectorAll('.dropdown-menu').forEach(m => {
                if (m !== menu) m.classList.remove('show');
            });

            // Toggle current dropdown
            menu.classList.toggle('show');
        });
    });

    // Close dropdown when clicking elsewhere
    document.addEventListener('click', function () {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.classList.remove('show');
        });
    });
});
