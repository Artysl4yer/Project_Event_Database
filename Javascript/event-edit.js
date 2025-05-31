// Add Event Modal Functions
function openAddModal() {
    const modal = document.getElementById('addEventModal');
    if (!modal) {
        console.error('Add modal not found');
        return;
    }
    
    modal.style.display = 'block';
    generateNewEventCode();
}

function closeAddModal() {
    const modal = document.getElementById('addEventModal');
    if (modal) {
        modal.style.display = 'none';
        document.getElementById('addEventForm').reset();
        document.getElementById('newPreviewImg').src = '../images-icon/plm_courtyard.png';
        document.getElementById('newImageName').textContent = 'No file chosen';
    }
}

// Edit Event Modal Functions
function openEditModal(eventId) {
    const modal = document.getElementById('editEventModal');
    if (!modal) {
        console.error('Edit modal not found');
        return;
    }

    modal.style.display = 'block';
    
    // Set the event ID in the hidden input
    const eventIdInput = document.getElementById('editEventId');
    if (eventIdInput) {
        eventIdInput.value = eventId;
    }

    // Fetch event details from the server
    fetch(`../php/get_event.php?id=${eventId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(response => {
            if (!response.success || !response.data) {
                throw new Error('No data received from server');
            }

            const data = response.data;

            // Update form fields with event data
            const updateField = (id, value) => {
                const element = document.getElementById(id);
                if (element) {
                    element.value = value || '';
                }
            };

            // Basic fields
            updateField('editEventTitle', data.event_title);
            updateField('editEventVenue', data.event_location);
            updateField('editEventDescription', data.event_description);
            updateField('editCodeField', data.event_code);
            updateField('editEventDuration', data.duration);

            // Handle organization field
            const orgSelect = document.getElementById('editOrganization');
            const customOrgInput = document.getElementById('editCustomOrg');
            const orgValue = data.organization || '';
            
            if (orgSelect && customOrgInput) {
                const matchingOption = Array.from(orgSelect.options).find(option => option.value === orgValue);
                
                if (matchingOption) {
                    orgSelect.value = orgValue;
                    customOrgInput.style.display = 'none';
                    customOrgInput.required = false;
                    orgSelect.name = 'organization';
                    customOrgInput.name = 'custom_organization';
                } else {
                    orgSelect.value = 'others';
                    customOrgInput.value = orgValue;
                    customOrgInput.style.display = 'block';
                    customOrgInput.required = true;
                    orgSelect.name = '';
                    customOrgInput.name = 'organization';
                }
            }

            // Handle date and time
            if (data.event_start) {
                const startDate = new Date(data.event_start);
                updateField('editEventDate', startDate.toISOString().split('T')[0]);
                updateField('editEventTime', startDate.toTimeString().slice(0, 5));
            }

            // Handle registration deadline
            if (data.registration_deadline) {
                const deadline = new Date(data.registration_deadline);
                updateField('editRegistrationDeadline', deadline.toISOString().slice(0, 16));
            }

            // Handle image preview
            const previewImg = document.getElementById('editPreviewImg');
            const imageNameElement = document.getElementById('editImageName');
            
            if (data.event_image && previewImg && imageNameElement) {
                previewImg.src = data.event_image;
                const imageName = data.event_image.split('/').pop();
                imageNameElement.textContent = imageName || 'Current image';
            }
        })
        .catch(error => {
            console.error('Error fetching event details:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load event details. Please try again.',
                customClass: { container: 'swal-on-top-custom-modal' }
            });
            closeEditModal();
        });
}

function closeEditModal() {
    const modal = document.getElementById('editEventModal');
    if (modal) {
        modal.style.display = 'none';
        document.getElementById('editEventForm').reset();
        document.getElementById('editPreviewImg').src = '../images-icon/plm_courtyard.png';
        document.getElementById('editImageName').textContent = 'No file chosen';
    }
}

// Utility function to check for valid date string
function isValidDateString(dateStr) {
    if (!dateStr || dateStr === '0000-00-00' || dateStr === '0000-00-00 00:00:00') return false;
    const d = new Date(dateStr);
    return !isNaN(d.getTime());
}

// Load event data for editing
async function loadEventData(eventId) {
    try {
        const response = await fetch(`../php/get_event_details.php?id=${eventId}`);
        if (!response.ok) {
            throw new Error('Failed to load event data');
        }
        
        const data = await response.json();
        console.log('Event data loaded:', data); // Debug log
        
        // Populate edit form fields
        document.getElementById('editEventId').value = data.number;
        document.getElementById('editEventTitle').value = data.event_title || '';
        document.getElementById('editEventVenue').value = data.event_location || '';
        document.getElementById('editOrganization').value = data.organization || '';
        
        // Handle date and time
        if (isValidDateString(data.date_start)) {
            const startDateTime = new Date(data.date_start);
            document.getElementById('editEventDate').value = startDateTime.toISOString().split('T')[0];
            document.getElementById('editEventTime').value = startDateTime.toTimeString().slice(0, 5);
        } else {
            document.getElementById('editEventDate').value = '';
            document.getElementById('editEventTime').value = '';
        }
        document.getElementById('editEventDuration').value = data.event_duration || '';
        if (isValidDateString(data.registration_deadline)) {
            const deadline = new Date(data.registration_deadline);
            document.getElementById('editRegistrationDeadline').value = deadline.toISOString().slice(0, 16);
        } else {
            document.getElementById('editRegistrationDeadline').value = '';
        }
        document.getElementById('editCodeField').value = data.event_code || '';
        document.getElementById('editEventDescription').value = data.event_description || '';
        document.getElementById('editRegistrationStatus').value = data.registration_status || 'open'; // Default to 'open' if not set
        
        // Handle image preview
        const previewImg = document.getElementById('editPreviewImg');
        if (data.file) {
            previewImg.src = `../uploads/events/${data.file}`;
            document.getElementById('editImageName').textContent = data.file;
        } else {
            previewImg.src = '../images-icon/plm_courtyard.png';
            document.getElementById('editImageName').textContent = 'No file chosen';
        }
        
    } catch (error) {
        console.error('Error loading event data:', error);
        alert('Error loading event data. Please try again.');
    }
}

// Form submission handlers
async function handleEditFormSubmit(e) {
    e.preventDefault();
    
    // Validate the form before submission
    if (!validateEventDateTime(e.target)) {
        return;
    }
    
    try {
        const formData = new FormData(e.target);
        const response = await fetch(e.target.action, {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const result = await response.json();
        if (result.success) {
            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Event Updated!',
                    text: result.message || 'Event details have been successfully updated.',
                    timer: 3500,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(() => {
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                });
            }, 500);
            closeEditModal();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: result.message || 'Could not update the event. Please check the details.',
                customClass: {
                    container: 'swal-on-top-custom-modal'
                }
            });
        }
    } catch (error) {
        console.error('Error updating event:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error Updating Event!',
            text: error.message || 'An unexpected error occurred.',
            customClass: {
                container: 'swal-on-top-custom-modal'
            }
        });
    }
}

async function handleAddFormSubmit(e) {
    console.log('Add form submission handler called');
    e.preventDefault();
    
    // Validate the form before submission
    if (!validateEventDateTime(e.target)) {
        console.log('Validation failed');
        return;
    }
    
    try {
        const form = e.target;
        const formData = new FormData(form);
        
        // Log form data for debugging
        console.log('Submitting form with data:');
        for (let [key, value] of formData.entries()) {
            console.log(`${key}: ${value}`);
        }

        const response = await fetch(form.action, {
            method: 'POST',
            body: formData
        });

        console.log('Response status:', response.status);
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Non-JSON response:', text);
            throw new Error('Server returned non-JSON response');
        }

        const result = await response.json();
        console.log('Response data:', result);

        if (!response.ok) {
            throw new Error(result.message || 'Network response was not ok');
        }

        if (result.success) {
            console.log('Event created successfully:', result);
            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Event Created!',
                    text: result.message || 'New event has been successfully created.',
                    timer: 3500,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(() => {
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                });
            }, 500);
            closeAddModal();
        } else {
            console.error('Server returned success: false', result);
            throw new Error(result.message || 'Failed to create event');
        }
    } catch (error) {
        console.error('Error creating event:', error);
        let errorMessage = 'An unexpected error occurred. Please try again.';
        if (error.message) {
            if (error.message.includes('non-JSON response')) {
                errorMessage = 'Server error: Invalid response format.';
            } else if (error.message.includes('internal server error')) {
                errorMessage = 'Server error: An internal issue occurred.';
            } else {
                errorMessage = error.message;
            }
        }
        Swal.fire({
            icon: 'error',
            title: 'Error Creating Event!',
            text: errorMessage,
            customClass: {
                container: 'swal-on-top-custom-modal'
            }
        });
    }
}

// Image preview handlers
function handleEditImageSelect(input) {
    const preview = document.getElementById('editPreviewImg');
    const fileName = document.getElementById('editImageName');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
        fileName.textContent = input.files[0].name;
    }
}

function handleNewImageSelect(input) {
    const preview = document.getElementById('newPreviewImg');
    const fileName = document.getElementById('newImageName');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
        fileName.textContent = input.files[0].name;
    }
}

// Generate event code
function generateNewEventCode() {
    const codeField = document.getElementById('newCodeField');
    if (codeField) {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        const timestamp = Date.now().toString().slice(-4);
        let code = '';
        for (let i = 0; i < 8; i++) {
            code += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        codeField.value = code + timestamp;
    }
}

// Validation function
function validateEventDateTime(form) {
    // 1. Get all required fields
    const dateField = form.querySelector('#newEventDate') || form.querySelector('#editEventDate');
    const timeField = form.querySelector('#newEventTime') || form.querySelector('#editEventTime');
    const durationField = form.querySelector('#newEventDuration') || form.querySelector('#editEventDuration');
    const registrationDeadlineField = form.querySelector('#newRegistrationDeadline') || form.querySelector('#editRegistrationDeadline');

    // Basic field validation - just check if fields are filled
    if (!dateField?.value || !timeField?.value || !durationField?.value || !registrationDeadlineField?.value) {
        Swal.fire({
            icon: 'error',
            title: 'Missing Information',
            text: 'Please fill in all date and time fields.',
            customClass: { container: 'swal-on-top-custom-modal' }
        });
        return false;
    }

    // Only validate that duration is a positive number
    const duration = parseInt(durationField.value);
    if (isNaN(duration) || duration < 1) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Duration',
            text: 'Event duration must be at least 1 hour.',
            customClass: { container: 'swal-on-top-custom-modal' }
        });
        return false;
    }

    // All validations passed
    return true;
}

// Handle organization change
function handleOrgChange(select, prefix) {
    const customInput = document.getElementById(prefix + 'CustomOrg');
    if (!customInput) return;

    if (select.value === 'others') {
        customInput.style.display = 'block';
        customInput.required = true;
        select.name = '';
        customInput.name = 'organization';
        customInput.value = ''; // Clear any previous value
        customInput.focus(); // Auto-focus the input
    } else {
        customInput.style.display = 'none';
        customInput.required = false;
        select.name = 'organization';
        customInput.name = 'custom_organization';
        customInput.value = ''; // Clear the value when switching back
    }
}

// Add event listeners for organization select fields
document.addEventListener('DOMContentLoaded', function() {
    // For Add Event form
    const newOrgSelect = document.getElementById('newOrganization');
    if (newOrgSelect) {
        newOrgSelect.addEventListener('change', function() {
            handleOrgChange(this, 'new');
        });
    }

    // For Edit Event form
    const editOrgSelect = document.getElementById('editOrganization');
    if (editOrgSelect) {
        editOrgSelect.addEventListener('change', function() {
            handleOrgChange(this, 'edit');
        });
    }

    // Add Event button handler
    const addEventBtn = document.getElementById('addEventBtn');
    if (addEventBtn) {
        addEventBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            openAddModal();
        });
    }

    // Edit Event button handlers
    document.querySelectorAll('.dropdown-edit-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const eventId = this.dataset.eventId;
            openEditModal(eventId);
        });
    });

    // Form submission handlers
    const addForm = document.getElementById('addEventForm');
    if (addForm) {
        addForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            console.log('Form submission started');
            
            // First validate the date/time
            if (!validateEventDateTime(this)) {
                console.log('Date/time validation failed');
                return false;
            }
            
            console.log('Validation passed, preparing to submit form');

            try {
                const formData = new FormData(this);
                
                // Log form data for debugging
                console.log('Form data being submitted:');
                for (let [key, value] of formData.entries()) {
                    console.log(`${key}: ${value}`);
                }

                // Log the form action URL
                console.log('Submitting to URL:', this.action);

                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData
                });

                console.log('Response status:', response.status);
                
                // Log raw response for debugging
                const rawResponse = await response.text();
                console.log('Raw response:', rawResponse);

                // Try to parse as JSON
                let result;
                try {
                    result = JSON.parse(rawResponse);
                } catch (parseError) {
                    console.error('Failed to parse response as JSON:', parseError);
                    throw new Error('Server returned invalid JSON response');
                }

                if (result.success) {
                    console.log('Event created successfully:', result);
                    setTimeout(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Event Created!',
                            text: result.message || 'New event has been successfully created.',
                            timer: 3500,
                            timerProgressBar: true,
                            showConfirmButton: false
                        }).then(() => {
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        });
                    }, 500);
                    closeAddModal();
                } else {
                    console.error('Server returned success: false', result);
                    throw new Error(result.message || 'Failed to create event');
                }
            } catch (error) {
                console.error('Error creating event:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error Creating Event',
                    text: error.message || 'Could not create the event. Please try again.',
                    customClass: { container: 'swal-on-top-custom-modal' }
                });
            }
        });
        console.log('Add form handler attached');
    }

    // Edit Event button handlers
    document.querySelectorAll('.dropdown-edit-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const eventId = this.dataset.eventId;
            openEditModal(eventId);
        });
    });

    // Form submission handlers for edit form
    const editForm = document.getElementById('editEventForm');
    if (editForm) {
        editForm.addEventListener('submit', handleEditFormSubmit);
        console.log('Edit form handler attached');
    }

    // Close modals when clicking outside
    window.addEventListener('click', function(e) {
        const addModal = document.getElementById('addEventModal');
        const editModal = document.getElementById('editEventModal');
        
        if (e.target === addModal) {
            closeAddModal();
        }
        if (e.target === editModal) {
            closeEditModal();
        }
    });

    // Close modals when pressing Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAddModal();
            closeEditModal();
        }
    });

    // Initialize form validation
    const forms = document.querySelectorAll('#addEventForm, #editEventForm');
    forms.forEach(form => {
        if (form) {
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.addEventListener('click', function(e) {
                    if (!validateEventDateTime(form)) {
                        e.preventDefault();
                        return false;
                    }
                });
            }
        }
    });
}); 