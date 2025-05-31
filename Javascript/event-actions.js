document.addEventListener('DOMContentLoaded', function() {
    // Handle dropdown menu actions
    document.querySelectorAll('.dropdown-menu button[data-action]').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const action = this.dataset.action;
            const eventId = this.dataset.eventId;
            
            switch(action) {
                case 'edit':
                    window.location.href = `6_NewEvent.php?edit=${eventId}`;
                    break;
                case 'attendance':
                    window.location.href = `11_Attendance.php?event=${eventId}`;
                    break;
                case 'qr':
                    const eventCode = this.dataset.eventCode;
                    generateQRCode(eventId, eventCode);
                    break;
            }
        });
    });

    // Handle delete forms
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this event?')) {
                this.submit();
            }
        });
    });
}); 