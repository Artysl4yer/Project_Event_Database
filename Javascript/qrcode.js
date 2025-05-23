let qrcode = null;

// Generate code for new events
function generateCode(length = 12) {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    const timestamp = Date.now().toString().slice(-4);
    let code = '';
    for (let i = 0; i < length - 4; i++) {
        code += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return code + timestamp;
}

// QR Code specific functions
function generateQRCode(eventId, eventCode) {
    const modal = document.getElementById('qrModal');
    if (!modal) return;

    modal.style.display = 'block';
    modal.classList.add('show');

    const qrcodeContainer = document.getElementById('qrcode');
    qrcodeContainer.innerHTML = ''; // Clear previous QR code

    // Generate QR code with event registration URL
    const registrationUrl = `${window.location.origin}/pages/register_participant.php?event=${eventId}&code=${eventCode}`;
    qrcode = new QRCode(qrcodeContainer, {
        text: registrationUrl,
        width: 256,
        height: 256
    });
}

function downloadQRCode() {
    const canvas = document.querySelector('#qrcode canvas');
    if (!canvas) return;

    const link = document.createElement('a');
    link.download = 'event-qr-code.png';
    link.href = canvas.toDataURL();
    link.click();
}

// Close QR modal when clicking outside
window.addEventListener('click', function(event) {
    const qrModal = document.getElementById('qrModal');
    if (event.target === qrModal) {
        qrModal.classList.remove('show');
        qrModal.style.display = 'none';
    }
});

// Close QR modal when clicking close button
document.addEventListener('DOMContentLoaded', function() {
    const closeButton = document.querySelector('.close-qr');
    if (closeButton) {
        closeButton.addEventListener('click', function() {
            const qrModal = document.getElementById('qrModal');
            qrModal.classList.remove('show');
            qrModal.style.display = 'none';
        });
    }
});