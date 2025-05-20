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
    // Dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize dropdown toggles
    document.addEventListener('click', function(e) {
        // Handle dropdown toggle
        if (e.target.closest('.dropdown-toggle')) {
            const toggle = e.target.closest('.dropdown-toggle');
            const menu = toggle.nextElementSibling;
            const isShowing = menu.classList.contains('show');
            
            // Close all other dropdowns first
            document.querySelectorAll('.dropdown-menu.show').forEach(openMenu => {
                if (openMenu !== menu) {
                    openMenu.classList.remove('show');
                }
            });
            
            // Toggle current dropdown
            menu.classList.toggle('show', !isShowing);
            e.stopPropagation();
        }
        // Close dropdowns when clicking outside
        else if (!e.target.closest('.dropdown-menu')) {
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });
});

// QR Code functionality
let qrcode = null;

function generateQRCode(participantNumber, participantId) {
    const modal = document.getElementById('qrModal');
    const qrcodeDiv = document.getElementById('qrcode');
    
    // Clear previous QR code
    qrcodeDiv.innerHTML = '';
    
    // Create data for participant QR code
    const qrData = JSON.stringify({
        type: 'participant',
        id: participantId,
        number: participantNumber
    });
    
    // Generate new QR code
    qrcode = new QRCode(qrcodeDiv, {
        text: qrData,
        width: 200,
        height: 200,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });
    
    // Show modal
    modal.classList.add('show');
    
    // Prevent body scroll when modal is open
    document.body.style.overflow = 'hidden';
}

function downloadQRCode() {
    if (!qrcode) return;
    
    const canvas = document.querySelector("#qrcode canvas");
    if (!canvas) return;
    
    const link = document.createElement('a');
    link.href = canvas.toDataURL("image/png");
    link.download = `participant-qr-${Date.now()}.png`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Close QR modal
document.querySelector('.close-qr').addEventListener('click', function() {
    const modal = document.getElementById('qrModal');
    modal.classList.remove('show');
    document.body.style.overflow = '';
});

// Close modal when clicking outside
document.getElementById('qrModal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.remove('show');
        document.body.style.overflow = '';
    }
});

});