
    // Event Modal Functions
    function showEventDetails(eventData) {
        const modal = document.getElementById('eventModal');
        const poster = document.getElementById('modalEventPoster');
        const title = document.getElementById('modalEventTitle');
        const date = document.getElementById('modalEventDate');
        const location = document.getElementById('modalEventLocation');
        const org = document.getElementById('modalEventOrg');
        const status = document.getElementById('modalEventStatus');
        const description = document.getElementById('modalEventDescription');
        const viewParticipantsBtn = document.getElementById('viewParticipantsBtn');
        
        // Set event data
        poster.src = '../images-icon/plm_courtyard.png';
        title.textContent = eventData.event_title;
        
        const eventDate = new Date(eventData.date_start + ' ' + eventData.event_start);
        date.textContent = 'Date: ' + eventDate.toLocaleString();
        location.textContent = 'Location: ' + eventData.event_location;
        org.textContent = 'Organization: ' + eventData.organization;
        status.textContent = 'Status: ' + eventData.event_status;
        description.textContent = eventData.event_description;
        
        // Set up view participants button
        viewParticipantsBtn.onclick = function() {
            viewParticipants(eventData.number, eventData.event_title);
            closeEventModal();
        };
        
        // Show modal
        modal.style.display = 'block';
    }
    
    function closeEventModal() {
        document.getElementById('eventModal').style.display = 'none';
    }
    
    // Close modal when clicking X
    document.querySelector('.close-modal').onclick = closeEventModal;
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('eventModal');
        if (event.target == modal) {
            closeEventModal();
        }
        
        // Also handle other modals
        if (event.target.classList.contains('modal') || event.target.classList.contains('admin-modal')) {
            if (event.target.id === 'adminModal') {
                closeAdminModal();
            } else {
                event.target.classList.remove('show');
            }
        }
    };
    
    // Existing functions from your code
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM Content Loaded'); 
        const closeButtons = document.querySelectorAll('.close-admin, .close-participants');
        closeButtons.forEach(button => {
            button.onclick = function() {
                console.log('Close button clicked'); 
                const modal = this.closest('.modal, .admin-modal');
                if (modal) {
                    modal.classList.remove('show');
                    if (modal.id === 'adminModal') {
                        document.getElementById('adminPassword').disabled = false;
                        document.getElementById('adminError').textContent = '';
                    }
                }
            };
        });
    });

    function viewParticipants(eventId, eventTitle) {
        console.log('Viewing participants for event:', eventId, eventTitle);
        
        const modal = document.getElementById('participantsModal');
        const titleElement = document.getElementById('eventTitle');
        const participantsList = document.getElementById('participantsList');
        
        if (!eventId) {
            console.error('No event ID provided to viewParticipants');
            return;
        }
        
        titleElement.textContent = eventTitle;
        participantsList.innerHTML = '<tr><td colspan="9" class="loading">Loading participants...</td></tr>';
        modal.classList.add('show');

        // Add admin button
        const header = modal.querySelector('.header');
        const existingBtn = header.querySelector('.admin-add-btn');
        if (existingBtn) {
            existingBtn.remove();
        }
        const adminBtn = document.createElement('button');
        adminBtn.className = 'action-btn admin-add-btn';
        adminBtn.innerHTML = '<i class="fas fa-user-plus"></i> Admin Add';
        adminBtn.onclick = function() {
            showAdminModal(eventId);
        };
        header.appendChild(adminBtn);

        // Construct the URL properly
        const endpoint = `${window.location.origin}/Project_Event_Database/php/get_participants.php?event_id=${eventId}`;
        console.log('Fetching participants from:', endpoint);

        fetch(endpoint, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(async response => {
            console.log('Response status:', response.status);
            const responseText = await response.text();
            console.log('Raw response:', responseText);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}, response: ${responseText}`);
            }
            
            try {
                return JSON.parse(responseText);
            } catch (e) {
                console.error('JSON parse error:', e);
                throw new Error('Invalid JSON response from server');
            }
        })
        .then(data => {
            console.log('Participants data:', data);
            participantsList.innerHTML = '';
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            if (!Array.isArray(data) || data.length === 0) {
                participantsList.innerHTML = '<tr><td colspan="9" class="no-data">No participants registered yet</td></tr>';
            } else {
                data.forEach(participant => {
                    const row = document.createElement('tr');
                    const registrationDate = participant.registration_date ? 
                        new Date(participant.registration_date).toLocaleString() : 
                        'N/A';
                    row.innerHTML = `
                        <td>${participant.ID || ''}</td>
                        <td>${participant.Name || ''}</td>
                        <td>${participant.Course || ''}</td>
                        <td>${participant.Section || ''}</td>
                        <td>${participant.Gender || ''}</td>
                        <td>${participant.Age || ''}</td>
                        <td>${participant.Year || ''}</td>
                        <td>${participant.Dept || ''}</td>
                        <td>${registrationDate}</td>
                    `;
                    participantsList.appendChild(row);
                });
            }
            document.getElementById('totalParticipants').textContent = Array.isArray(data) ? data.length : 0;
        })
        .catch(error => {
            console.error('Error loading participants:', error);
            participantsList.innerHTML = `<tr><td colspan="9" class="error">Error loading participants: ${error.message}</td></tr>`;
        });
    }

    function showAdminModal(eventId) {
        console.log('Opening admin modal for event:', eventId);
        const modal = document.getElementById('adminModal');
        if (!modal) {
            console.error('Admin modal not found!');
            return;
        }
        document.getElementById('currentEventId').value = eventId;
        document.getElementById('participantForm').style.display = 'none';
        document.getElementById('adminPassword').value = '';
        document.getElementById('adminError').textContent = '';
        modal.classList.add('show');
    }

    function closeAdminModal() {
        const modal = document.getElementById('adminModal');
        const adminPasswordForm = document.getElementById('adminPasswordForm');
        const participantForm = document.getElementById('participantForm');

        adminPasswordForm.reset();
        participantForm.reset();
        
        document.getElementById('adminPassword').disabled = false;
        participantForm.style.display = 'none';
        document.getElementById('adminError').textContent = '';
        
        modal.classList.remove('show');
    }
