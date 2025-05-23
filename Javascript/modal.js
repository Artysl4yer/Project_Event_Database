// Modal functionality for participant management
function openParticipantModal(participantId = null) {
    const modal = document.getElementById('participantModal');
    const form = document.getElementById('participantForm');
    const title = document.getElementById('modalTitle');
    
    if (participantId) {
        title.textContent = 'Edit Participant';
        document.getElementById('participant-number').value = participantId;
        
        fetch(`7_StudentTable.php?action=get_participant&id=${participantId}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('participant-id').value = data.ID || '';
                document.getElementById('participant-name').value = data.Name || '';
                document.getElementById('participant-course').value = data.Course || '';
                document.getElementById('participant-section').value = data.Section || '';
                document.getElementById('participant-gender').value = data.Gender || '';
                document.getElementById('participant-age').value = data.Age || '';
                document.getElementById('participant-year').value = data.Year || '';
                document.getElementById('participant-dept').value = data.Dept || '';
            })
            .catch(error => {
                console.error('Error fetching participant data:', error);
            });
    } else {
        title.textContent = 'Add New Participant';
        form.reset();
        document.getElementById('participant-number').value = '';
    }
    
    modal.style.display = 'block';
}

function closeModal() {
    const modal = document.getElementById('participantModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('participantModal');
    if (event.target === modal) {
        closeModal();
    }
});

// Close modal when pressing Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});

// Initialize modal functionality when the document is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners for any close buttons in the modal
    const closeButtons = document.querySelectorAll('.close-modal, .btn-close');
    closeButtons.forEach(button => {
        button.addEventListener('click', closeModal);
    });

    // Prevent form submission from closing the modal
    const modalForm = document.getElementById('participantForm');
    if (modalForm) {
        modalForm.addEventListener('submit', function(event) {
            // The form will still submit normally, but we prevent the default behavior
            // that might interfere with our modal handling
            event.stopPropagation();
        });
    }
}); 