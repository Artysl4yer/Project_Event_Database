// QR Code functionality
let qrcodeInstance = null;

function generateQRCode(participantNumber, participantId) {
    const modal = document.getElementById('qrModal');
    const qrcodeDiv = document.getElementById('qrcode');
    
    qrcodeDiv.innerHTML = '';
    
    const qrData = JSON.stringify({
        type: 'participant',
        id: participantId,
        number: participantNumber
    });
    
    qrcodeInstance = new QRCode(qrcodeDiv, {
        text: qrData,
        width: 200,
        height: 200,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });
    
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function downloadQRCode() {
    if (!qrcodeInstance) return;
    
    const canvas = document.querySelector("#qrcode canvas");
    if (!canvas) return;
    
    const link = document.createElement('a');
    link.href = canvas.toDataURL("image/png");
    link.download = `participant-qr-${Date.now()}.png`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Event Listeners for QR Modal
document.addEventListener('DOMContentLoaded', function() {
    // Close QR modal
    document.querySelector('.close-qr')?.addEventListener('click', function() {
        const modal = document.getElementById('qrModal');
        modal.classList.remove('show');
        document.body.style.overflow = '';
    });

    // Close modal when clicking outside
    document.getElementById('qrModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('show');
            document.body.style.overflow = '';
        }
    });
}); 