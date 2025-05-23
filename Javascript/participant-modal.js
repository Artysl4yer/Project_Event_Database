// Participant modal functionality
class ParticipantModal {
    constructor() {
        this.modalId = 'participantModal';
        this.formId = 'participantForm';
        this.titleId = 'modalTitle';
        this.isInitialized = false;
        this.initialize();
    }

    initialize() {
        if (this.isInitialized) return;
        
        // Add event listeners for close buttons
        const initModal = () => {
            console.log('Initializing participant modal...');
            const modal = document.getElementById(this.modalId);
            if (!modal) {
                console.error('Modal element not found:', this.modalId);
                return;
            }

            // Remove any existing event listeners
            const newModal = modal.cloneNode(true);
            modal.parentNode.replaceChild(newModal, modal);
            
            const closeButtons = newModal.querySelectorAll('.close-modal, .btn-close');
            console.log('Found close buttons:', closeButtons.length);
            
            closeButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    console.log('Close button clicked');
                    e.preventDefault();
                    e.stopPropagation();
                    this.closeModal();
                    return false;
                }, true);
            });

            // Close modal when clicking outside
            const handleOutsideClick = (event) => {
                if (event.target === newModal) {
                    console.log('Clicked outside modal');
                    event.preventDefault();
                    event.stopPropagation();
                    this.closeModal();
                    return false;
                }
            };
            newModal.addEventListener('click', handleOutsideClick, true);

            // Close modal when pressing Escape key
            const handleEscapeKey = (event) => {
                if (event.key === 'Escape' && newModal.style.display === 'block') {
                    console.log('Escape key pressed');
                    event.preventDefault();
                    event.stopPropagation();
                    this.closeModal();
                    return false;
                }
            };
            document.addEventListener('keydown', handleEscapeKey, true);

            // Prevent form submission from closing the modal
            const form = newModal.querySelector(`#${this.formId}`);
            if (form) {
                form.addEventListener('submit', (event) => {
                    event.stopPropagation();
                }, true);
            }

            this.isInitialized = true;
            console.log('Modal initialization complete');
        };

        // Initialize immediately if DOM is already loaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initModal);
        } else {
            initModal();
        }
    }

    openModal(participantId = null) {
        console.log('Opening modal for participant:', participantId);
        const modal = document.getElementById(this.modalId);
        const form = document.getElementById(this.formId);
        const title = document.getElementById(this.titleId);
        
        if (!modal || !form || !title) {
            console.error('Required elements not found:', { modal: !!modal, form: !!form, title: !!title });
            return;
        }

        if (participantId) {
            title.textContent = 'Edit Participant';
            document.getElementById('participant-number').value = participantId;
            
            fetch(`7_StudentTable.php?action=get_participant&id=${participantId}`)
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        document.getElementById('participant-id').value = data.ID || '';
                        document.getElementById('participant-firstname').value = data.first_name || '';
                        document.getElementById('participant-lastname').value = data.last_name || '';
                        document.getElementById('participant-course').value = data.Course || '';
                        document.getElementById('participant-section').value = data.Section || '';
                        document.getElementById('participant-gender').value = data.Gender || '';
                        document.getElementById('participant-age').value = data.Age || '';
                        document.getElementById('participant-year').value = data.Year || '';
                        document.getElementById('participant-dept').value = data.Dept || '';
                    }
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
        modal.classList.add('active');
        console.log('Modal opened');
    }

    closeModal() {
        console.log('closeModal called');
        const modal = document.getElementById(this.modalId);
        console.log('Modal element:', modal);
        if (modal) {
            console.log('Setting modal display to none');
            modal.style.display = 'none';
            modal.classList.remove('active');
            console.log('Modal closed');
        } else {
            console.error('Modal element not found during close');
        }
    }
}

// Initialize the modal when the document is loaded
let participantModalInstance = null;

function initializeParticipantModal() {
    if (!participantModalInstance) {
        console.log('Creating new participant modal instance');
        participantModalInstance = new ParticipantModal();
        window.participantModal = participantModalInstance;
    }
}

// Initialize immediately if DOM is already loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeParticipantModal);
} else {
    initializeParticipantModal();
}

// Expose the openModal function globally
function openParticipantModal(participantId = null) {
    console.log('Global openParticipantModal called');
    if (window.participantModal) {
        window.participantModal.openModal(participantId);
    } else {
        console.error('Participant modal not initialized');
    }
}

// Expose the closeModal function globally
function closeModal() {
    console.log('Global closeModal called');
    if (window.participantModal) {
        window.participantModal.closeModal();
    } else {
        console.error('Participant modal not initialized');
    }
} 