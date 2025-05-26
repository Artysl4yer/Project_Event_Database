let qrcode = null;
        
function openModal() {
    document.getElementById('eventModal').classList.add('active');
}
    
window.onclick = function(event) {
    const modal = document.getElementById('eventModal');
    const qrModal = document.getElementById('qrModal');
    
    if (event.target == modal) {
        closeModal();
    }
    if (event.target == qrModal) {
        qrModal.classList.remove('show');
    }
}

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


 // QR Code Generation
function generateQRCode(eventCode) {
    const modal = document.getElementById('qrModal');
    const container = document.getElementById('qrcode-container');
    const qrcodeDiv = document.getElementById('qrcode');
    qrcodeDiv.innerHTML = '';
    
    // Get the event ID from the data attribute
    const eventId = document.querySelector('[data-event-id]').dataset.eventId;
    
    // Create a registration code format that includes both event ID and code
    const qrData = `REG:${eventId}:${eventCode}`;
    
    qrcode = new QRCode(qrcodeDiv, {
        text: qrData,
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
    link.download = 'event-qr-code.png';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

document.querySelector('.close-qr').onclick = function() {
    document.getElementById('qrModal').classList.remove('show');
}