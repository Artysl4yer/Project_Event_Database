
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
            });
    } else {
        title.textContent = 'Add New Participant';
        form.reset();
        document.getElementById('participant-number').value = '';
    }
    
    modal.style.display = 'block';
}

function closeModal() {
    document.getElementById('participantModal').style.display = 'none';
}

window.addEventListener('click', function(event) {
    const modal = document.getElementById('participantModal');
    if (event.target === modal) {
        closeModal();
    }
});