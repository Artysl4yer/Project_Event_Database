// Event Modal Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Get modal elements
    const modal = document.getElementById('eventModal');
    const addEventBtn = document.querySelector('.btn-import');
    const closeBtn = document.querySelector('.close-modal');
    const closeModalBtn = document.querySelector('.btn-close');

    // Function to open modal
    window.openModal = function() {
        if (modal) {
            modal.style.display = 'block';
            modal.classList.add('active');
            generateEventCode();
        }
    };

    // Function to close modal
    window.closeModal = function() {
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('active');
        }
    };

    // Event listeners
    if (addEventBtn) {
        addEventBtn.addEventListener('click', openModal);
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }

    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeModal);
    }

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    // Close modal when pressing Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && modal.style.display === 'block') {
            closeModal();
        }
    });

    // Generate event code
    function generateEventCode() {
        const codeField = document.getElementById('codeField');
        if (codeField) {
            const timestamp = Date.now().toString().slice(-4);
            const randomStr = Math.random().toString(36).substring(2, 6).toUpperCase();
            codeField.value = `${randomStr}${timestamp}`;
        }
    }
}); 