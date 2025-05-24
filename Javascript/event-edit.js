// Event editing functionality
class EventEditor {
    constructor(options = {}) {
        this.isEditing = options.isEditing || false;
        this.modalId = options.modalId || 'eventModal';
        this.codeFieldId = options.codeFieldId || 'codeField';
        this.redirectUrl = options.redirectUrl || '6_NewEvent.php';
        this.initialize();
    }

    initialize() {
        // Initialize modal functionality
        this.setupModal();
        
        // Initialize code generation for new events
        if (!this.isEditing) {
            this.generateEventCode();
        }

        // Add event listeners for form submission
        const form = document.getElementById('eventForm');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleFormSubmit(form);
            });
        }
    }

    setupModal() {
        const modal = document.getElementById(this.modalId);
        if (!modal) return;

        // Close modal when clicking outside
        window.onclick = (event) => {
            if (event.target === modal) {
                this.closeModal();
            }
        };

        // Close modal when pressing Escape key
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                this.closeModal();
            }
        });

        // Add event listeners for close buttons
        const closeButtons = modal.querySelectorAll('.close-modal, .btn-close');
        closeButtons.forEach(button => {
            button.addEventListener('click', () => this.closeModal());
        });
    }

    closeModal() {
        const modal = document.getElementById(this.modalId);
        if (modal) {
            modal.classList.remove('active');
            modal.style.display = 'none';
            if (this.isEditing) {
                window.location.href = this.redirectUrl;
            }
        }
    }

    generateEventCode() {
        const codeField = document.getElementById(this.codeFieldId);
        if (codeField && !codeField.value) {
            codeField.value = this.generateCode(12);
        }
    }

    generateCode(length = 12) {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        const timestamp = Date.now().toString().slice(-4);
        let code = '';
        for (let i = 0; i < length - 4; i++) {
            code += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return code + timestamp;
    }

    openModal(eventId = null) {
        const modal = document.getElementById(this.modalId);
        if (!modal) return;

        modal.style.display = 'block';
        modal.classList.add('active');

        if (eventId) {
            this.fetchEventDetails(eventId);
        } else {
            this.resetForm();
            this.generateEventCode();
        }
    }

    async fetchEventDetails(eventId) {
        try {
            const response = await fetch(`../php/get_event_details.php?id=${eventId}`);
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();
            
            // Populate form fields
            const form = document.getElementById('eventForm');
            if (!form) return;

            form.querySelector('[name="event-title"]').value = data.event_title || '';
            form.querySelector('[name="event-location"]').value = data.event_location || '';
            
            // Handle date and time
            const startDateTime = new Date(data.date_start);
            const endDateTime = new Date(data.date_end);
            
            form.querySelector('[name="event-date-start"]').value = startDateTime.toISOString().split('T')[0];
            form.querySelector('[name="event-time-start"]').value = startDateTime.toTimeString().slice(0, 5);
            form.querySelector('[name="event-date-end"]').value = endDateTime.toISOString().split('T')[0];
            form.querySelector('[name="event-time-end"]').value = endDateTime.toTimeString().slice(0, 5);
            
            form.querySelector('[name="event-orgs"]').value = data.organization || '';
            form.querySelector('[name="event-description"]').value = data.event_description || '';
            
            if (form.querySelector('[name="event-status"]')) {
                form.querySelector('[name="event-status"]').value = data.event_status || 'Ongoing';
            }
            
            if (form.querySelector('[name="code"]')) {
                form.querySelector('[name="code"]').value = data.event_code || '';
            }
        } catch (error) {
            console.error('Error fetching event details:', error);
            alert('Error loading event details. Please try again.');
        }
    }

    resetForm() {
        const form = document.getElementById('eventForm');
        if (form) {
            form.reset();
            const codeField = document.getElementById(this.codeFieldId);
            if (codeField) {
                codeField.value = '';
            }
        }
    }

    async handleFormSubmit(form) {
        try {
            // Get form data
            const formData = new FormData(form);
            
            // Get selected organizations
            const orgSelect = form.querySelector('[name="event-orgs"]');
            if (!orgSelect || !orgSelect.value) {
                alert('Please select an organization');
                return;
            }

            // Log form data
            console.log('Submitting form data:');
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            const response = await fetch(form.action, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const result = await response.json();
            
            if (result.status === 'success') {
                alert('Event created successfully!');
                window.location.reload();
            } else {
                alert('Error: ' + (result.message || 'Failed to create event'));
            }
        } catch (error) {
            console.error('Error submitting form:', error);
            alert('Error: Failed to create event. Please try again.');
        }
    }
}

// Initialize event editor when the document is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Get isEditing value from PHP
    const isEditing = window.isEditing || false;
    
    // Initialize event editor
    window.eventEditor = new EventEditor({
        isEditing: isEditing,
        modalId: 'eventModal',
        codeFieldId: 'codeField',
        redirectUrl: '6_NewEvent.php'
    });

    // Expose modal functions globally
    window.openModal = function(eventId = null) {
        window.eventEditor.openModal(eventId);
    };

    window.closeModal = function() {
        window.eventEditor.closeModal();
    };
});

function toggleOtherOrg() {
    const otherCheckbox = document.getElementById('other');
    const otherInput = document.getElementById('other-org');
    
    if (otherCheckbox.checked) {
        otherInput.style.display = 'block';
        otherInput.required = true;
    } else {
        otherInput.style.display = 'none';
        otherInput.required = false;
        otherInput.value = '';
    }
}

// Function to combine selected organizations
function combineOrganizations() {
    const checkboxes = document.querySelectorAll('input[name="organizations[]"]');
    const otherInput = document.getElementById('other-org');
    const combinedInput = document.getElementById('combined-orgs');
    let selectedOrgs = [];

    checkboxes.forEach(checkbox => {
        if (checkbox.checked) {
            if (checkbox.value === 'other' && otherInput.value.trim()) {
                selectedOrgs.push(otherInput.value.trim());
            } else if (checkbox.value !== 'other') {
                selectedOrgs.push(checkbox.value);
            }
        }
    });

    combinedInput.value = selectedOrgs.join(', ');
    return selectedOrgs.length > 0;
}

// Add event listeners when document is loaded
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('eventForm');
    const checkboxes = document.querySelectorAll('input[name="organizations[]"]');
    const otherInput = document.getElementById('other-org');

    // Add change event listeners to all checkboxes and the other input
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', combineOrganizations);
    });

    otherInput.addEventListener('input', combineOrganizations);

    // If editing, pre-populate the checkboxes
    if (window.isEditing) {
        const existingOrgs = document.querySelector('input[name="event-orgs"]').value;
        const orgArray = existingOrgs.split(', ');
        
        orgArray.forEach(org => {
            const checkbox = Array.from(checkboxes).find(cb => cb.value === org);
            if (checkbox) {
                checkbox.checked = true;
            } else {
                // If organization is not in predefined list, check "Other" and fill input
                const otherCheckbox = document.getElementById('other');
                otherCheckbox.checked = true;
                otherInput.style.display = 'block';
                otherInput.value = org;
            }
        });
        
        combineOrganizations();
    }

    // Add form submit handler
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        window.eventEditor.handleFormSubmit(this);
    });
}); 