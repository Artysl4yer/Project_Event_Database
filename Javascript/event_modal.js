// Event Modal and Form Handling
document.addEventListener('DOMContentLoaded', function() {
    // Set initial event code
    const codeField = document.getElementById('codeField');
    if (codeField) {
        codeField.value = CodeGenerator.generateEventCode();
    }

    // Handle form submission
    const form = document.getElementById('eventForm');
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            try {
                const formData = new FormData(this);
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData
                });

                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    // Handle JSON response
                    const result = await response.json();
                    if (result.error) {
                        alert(result.message || 'Error saving event');
                    } else {
                        // Redirect on success without showing error
                        window.location.href = '6_NewEvent.php?success=true';
                        return;
                    }
                } else {
                    // Handle regular form response
                    if (response.ok) {
                        window.location.href = '6_NewEvent.php?success=true';
                        return;
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error saving event. Please try again.');
            }
        });
    }

    // Modal functions
    window.openModal = function() {
        document.getElementById('eventModal').style.display = 'block';
        // Reset form and generate new code when opening modal
        if (!window.isEditing) {
            form.reset();
            if (codeField) {
                codeField.value = CodeGenerator.generateEventCode();
            }
        }
    };

    window.closeModal = function() {
        document.getElementById('eventModal').style.display = 'none';
    };

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('eventModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    };

    // Show success message if present in URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success') === 'true') {
        alert('Event saved successfully!');
        // Remove the success parameter from URL
        window.history.replaceState({}, document.title, '6_NewEvent.php');
    }
});

// File upload handling
function handleFileSelect(input) {
    const fileName = input.files[0]?.name || 'No file chosen';
    document.getElementById('fileName').textContent = fileName;
    
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.border = 'none';
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        previewImg.src = '../images-icon/plm_courtyard.png';
        preview.style.border = '2px dashed #ddd';
    }
} 