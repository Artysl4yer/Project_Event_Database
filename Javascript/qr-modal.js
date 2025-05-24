// QR Code Modal functionality
class QRCodeModal {
    constructor() {
        this.modalId = 'qrModal';
        this.initialize();
    }

    initialize() {
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById(this.modalId);
            const closeBtn = modal.querySelector('.close-qr');
            
            if (closeBtn) {
                closeBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.closeModal();
                });
            }

            // Close QR modal when clicking outside
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.closeModal();
                }
            }, true);

            // Close QR modal when pressing Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && modal.style.display === 'block') {
                    e.preventDefault();
                    e.stopPropagation();
                    this.closeModal();
                }
            }, true);
        });
    }

    openModal(participantNumber, participantId) {
        const modal = document.getElementById(this.modalId);
        const qrcodeDiv = document.getElementById('qrcode');
        
        // Clear previous QR code
        qrcodeDiv.innerHTML = '';
        
        // Fetch student details
        fetch(`../php/get_student_details.php?student_id=${participantId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Create QR code data in the format:
                    // Name
                    // (Student ID)
                    // Course
                    const qrData = `${data.student.full_name}\n(${data.student.student_id})\n${data.student.course}`;
                    
                    // Generate new QR code
                    window.qrcode = new QRCode(qrcodeDiv, {
                        text: qrData,
                        width: 200,
                        height: 200,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.H
                    });
                    
                    // Show modal
                    modal.style.display = 'block';
                    modal.classList.add('active');
                } else {
                    alert('Error: Could not fetch student details');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error: Could not fetch student details');
            });
    }

    closeModal() {
        const modal = document.getElementById(this.modalId);
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('active');
        }
    }
}

// Initialize QR code modal
const qrCodeModal = new QRCodeModal();

// Expose functions globally
function generateQRCode(participantNumber, participantId) {
    qrCodeModal.openModal(participantNumber, participantId);
}

function downloadQRCode() {
    if (!window.qrcode) return;
    
    const canvas = document.querySelector("#qrcode canvas");
    if (!canvas) return;
    
    const link = document.createElement('a');
    link.href = canvas.toDataURL("image/png");
    link.download = `participant-qr-${Date.now()}.png`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
} 