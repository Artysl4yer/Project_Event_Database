// Modal functionality
function openParticipantModal(participantId = null) {
    const modal = document.getElementById('participantModal');
    const form = document.getElementById('participantForm');
    const title = document.getElementById('modalTitle');
    
    if (participantId) {
        title.textContent = 'Edit Student';
        document.getElementById('participant-number').value = participantId;
        
        const formData = new FormData();
        formData.append('action', 'get_participant');
        formData.append('id', participantId);

        fetch('7_StudentTable.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json().catch(() => {
                throw new Error('Invalid JSON response from server');
            });
        })
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Failed to fetch student data');
            }
            const student = data.data;
            document.getElementById('participant-id').value = student.ID || '';
            document.getElementById('participant-firstname').value = student.first_name || '';
            document.getElementById('participant-lastname').value = student.last_name || '';
            document.getElementById('participant-course').value = student.Course || '';
            document.getElementById('participant-section').value = student.Section?.slice(-1) || '';
            document.getElementById('participant-gender').value = student.Gender || '';
            document.getElementById('participant-age').value = student.Age || '';
            document.getElementById('participant-year').value = student.Year || '';
            document.getElementById('participant-dept').value = student.Dept || '';
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Failed to load student data'
            });
            closeParticipantModal();
        });
    } else {
        title.textContent = 'Add New Student';
        form.reset();
        document.getElementById('participant-number').value = '';
    }
    
    modal.style.display = 'block';
}

function closeParticipantModal() {
    const modal = document.getElementById('participantModal');
    modal.style.display = 'none';
    document.getElementById('participantForm').reset();
}

function openAddStudentModal() {
    const modal = document.getElementById('participantModal');
    const form = document.getElementById('participantForm');
    const title = document.getElementById('modalTitle');

    title.textContent = 'Add New Student';
    form.reset();
    document.getElementById('participant-number').value = '';
    modal.style.display = 'block';
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Close participant modal when clicking outside
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('participantModal');
        if (event.target === modal) {
            closeParticipantModal();
        }
    });

    // Form submission handler
    const participantForm = document.getElementById('participantForm');
    if (participantForm) {
        participantForm.addEventListener('submit', function(event) {
            event.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'save_participant');

            fetch('7_StudentTable.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json().catch(() => {
                    throw new Error('Invalid JSON response from server');
                });
            })
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Failed to save student');
                }
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    closeParticipantModal();
                    window.location.reload();
                });
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'An unexpected error occurred'
                });
            });
        });
    }
}); 