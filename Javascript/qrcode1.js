 // Modal Functions
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

// QR Code Functions
let qrcode = null;

function generateQRCode(participantNumber, participantId) {
    const modal = document.getElementById('qrModal');
    const qrcodeDiv = document.getElementById('qrcode');
    
    qrcodeDiv.innerHTML = '';
    
    const participantData = {
        type: 'participant',
        id: participantId,
        number: participantNumber,
        system: 'PLP Event System',
        timestamp: new Date().toISOString()
    };
    
    qrcode = new QRCode(qrcodeDiv, {
        text: JSON.stringify(participantData),
        width: 256,
        height: 256,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });
    
    modal.classList.add('show');
}

function downloadQRCode() {
    if (!qrcode) return;
    
    const canvas = document.querySelector("#qrcode canvas");
    const image = canvas.toDataURL("image/png");
    const link = document.createElement('a');
    link.href = image;
    link.download = `participant-qr-${new Date().getTime()}.png`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    // Toggle dropdown on click
    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const menu = this.nextElementSibling;
            
            // Close all other dropdowns
            document.querySelectorAll('.dropdown-menu').forEach(m => {
                if (m !== menu) m.classList.remove('show');
            });
            
            // Toggle current dropdown
            menu.classList.toggle('show');
        });
    });
    
    // Close dropdown when clicking elsewhere
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.classList.remove('show');
        });
    });

    // Close modals when clicking their close buttons
    document.querySelector('.close-qr')?.addEventListener('click', function() {
        document.getElementById('qrModal').classList.remove('show');
    });
});

// Close modals when clicking outside
window.addEventListener('click', function(event) {
    if (event.target == document.getElementById('participantModal')) {
        closeModal();
    }
    if (event.target == document.getElementById('qrModal')) {
        document.getElementById('qrModal').classList.remove('show');
    }
});