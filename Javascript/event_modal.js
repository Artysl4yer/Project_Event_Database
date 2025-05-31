// Add a simple CodeGenerator to prevent errors
window.CodeGenerator = {
    generateEventCode: function(length = 8) {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let code = '';
        for (let i = 0; i < length; i++) {
            code += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return code;
    }
};

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
                        // Redirect without success parameter
                        window.location.href = '6_NewEvent.php';
                        return;
                    }
                } else {
                    // Handle regular form response
                    if (response.ok) {
                        window.location.href = '6_NewEvent.php';
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
        console.log('openModal called');
        const modal = document.getElementById('eventModal');
        console.log('eventModal element:', modal);
        modal.style.display = 'block';
        // Reset form and generate new code when opening modal
        if (!window.isEditing) {
            const form = document.getElementById('eventForm');
            const codeField = document.getElementById('codeField');
            if (form) form.reset();
            if (codeField && typeof CodeGenerator !== 'undefined') {
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

// Image preview handling
function handleImageSelect(input) {
    const preview = document.getElementById('previewImg');
    const imageName = document.getElementById('imageName');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
        }
        
        reader.readAsDataURL(file);
        imageName.textContent = file.name;
    } else {
        preview.src = '../images-icon/plm_courtyard.png';
        imageName.textContent = 'No file chosen';
    }
} 