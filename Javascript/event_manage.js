// event_manage.js
// Global functions for event management
function loadEventData(eventId) {
    console.log('loadEventData called with eventId:', eventId);
    fetch(`../php/get_event.php?id=${eventId}`)
        .then(response => response.json())
        .then(data => {
            console.log('AJAX result:', data);
            if (data.success) {
                // Populate form with event data
                document.getElementById('eventTitle').value = data.event.event_title;
                document.getElementById('eventDescription').value = data.event.event_description;
                document.getElementById('eventVenue').value = data.event.event_location;
                document.getElementById('organization').value = data.event.organization;
                document.getElementById('eventDate').value = data.event.date_start ? data.event.date_start.split(' ')[0] : '';
                document.getElementById('eventTime').value = data.event.event_start ? data.event.event_start.split(' ')[1] : '';
                document.getElementById('eventDuration').value = data.event.event_duration || '';
                document.getElementById('registrationDeadline').value = data.event.registration_deadline ? new Date(data.event.registration_deadline).toISOString().slice(0,16) : '';
                document.getElementById('codeField').value = data.event.event_code;

                // Set image preview
                const previewImg = document.getElementById('previewImg');
                if (data.event.file) {
                    previewImg.src = '../uploads/events/' + data.event.file;
                } else {
                    previewImg.src = '../images-icon/plm_courtyard.png';
                }
                document.getElementById('imageName').textContent = 'No file chosen';

                // Add hidden input for event ID
                let eventIdInput = document.getElementById('event_id');
                if (!eventIdInput) {
                    eventIdInput = document.createElement('input');
                    eventIdInput.type = 'hidden';
                    eventIdInput.id = 'event_id';
                    eventIdInput.name = 'event_id';
                    document.getElementById('eventForm').appendChild(eventIdInput);
                }
                eventIdInput.value = eventId;
                
                // Change form action and submit button text
                document.getElementById('eventForm').action = '../php/update_event.php';
                document.querySelector('.btn-submit').textContent = 'Update Event';
                
                // Open the modal
                if (typeof openModal === 'function') {
                    console.log('Calling openModal');
                    openModal();
                } else {
                    console.error('openModal is not defined');
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Error loading event data'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error loading event data'
            });
        });
}

function archiveEvent(eventId) {
    Swal.fire({
        title: 'Archive Event',
        text: 'Are you sure you want to archive this event?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, archive it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../php/archive_event.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `event_id=${eventId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to archive event'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while archiving the event'
                });
            });
        }
    });
}

// Dropdown functionality has been moved to event-dropdown.js

// Form submission handler
document.getElementById('eventForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const isUpdate = this.action.includes('update_event.php');
    
    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                closeModal();
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'An error occurred'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while processing your request'
        });
    });
});

// Initialize event table
document.addEventListener('DOMContentLoaded', function() {
    // Commented out TableManager usage to prevent errors
    // const eventTable = new TableManager('eventTable', {
    //     filterColumn: 8,  // Status column
    //     nameColumn: 1,    // Title column
    //     courseColumn: 7   // Organization column
    // });

    // Show/hide status filter
    const statusFilterBtn = document.querySelector('[data-filter="status"]');
    const statusFilter = document.getElementById('statusFilter');
    
    if (statusFilterBtn && statusFilter) {
        // Set initial state based on URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const currentFilter = urlParams.get('filter');
        const currentStatus = urlParams.get('status');
        
        if (currentFilter === 'status') {
            statusFilterBtn.classList.add('active');
            statusFilter.style.display = 'inline-block';
            if (currentStatus) {
                statusFilter.value = currentStatus;
            }
        }

        statusFilterBtn.addEventListener('click', function() {
            const isVisible = statusFilter.style.display === 'inline-block';
            statusFilter.style.display = isVisible ? 'none' : 'inline-block';
            
            if (!isVisible) {
                statusFilter.value = 'all';
                const params = new URLSearchParams(window.location.search);
                params.set('filter', 'status');
                params.set('status', 'all');
                window.location.href = `${window.location.pathname}?${params.toString()}`;
            }
        });

        statusFilter.addEventListener('change', function() {
            const params = new URLSearchParams(window.location.search);
            params.set('filter', 'status');
            params.set('status', this.value);
            window.location.href = `${window.location.pathname}?${params.toString()}`;
        });
    }
});

window.loadEventData = loadEventData; 