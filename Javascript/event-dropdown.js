document.addEventListener('DOMContentLoaded', function() {
    // Initialize all dropdowns
    initializeDropdowns();

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        const target = e.target;
        const dropdownWrapper = target.closest('.dropdown-wrapper');
        const isToggleClick = target.closest('.dropdown-toggle');

        if (!dropdownWrapper && !isToggleClick) {
            closeAllDropdowns();
        }
    });

    // Close dropdowns on scroll or resize
    window.addEventListener('scroll', closeAllDropdowns);
    window.addEventListener('resize', closeAllDropdowns);
});

function initializeDropdowns() {
    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Get the dropdown menu
            const dropdownMenu = this.nextElementSibling;
            if (!dropdownMenu) return;

            // Check if this dropdown is already open
            const isOpen = dropdownMenu.classList.contains('show');
            
            // Close all other dropdowns first
            closeAllDropdowns();
            
            // Toggle current dropdown (only open if it was closed)
            if (!isOpen) {
                dropdownMenu.classList.add('show');
                // Position the dropdown
                positionDropdown(dropdownMenu);
            }
        });
    });

    // Initialize all dropdown buttons
    initializeDropdownButtons();
}

function closeAllDropdowns() {
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
        menu.classList.remove('show');
    });
}

function positionDropdown(dropdown) {
    if (!dropdown) return;
    
    const rect = dropdown.getBoundingClientRect();
    const parentRect = dropdown.parentElement.getBoundingClientRect();
    const windowWidth = window.innerWidth;
    const windowHeight = window.innerHeight;
    
    // Reset position
    dropdown.style.left = '';
    dropdown.style.right = '';
    dropdown.style.top = '';
    dropdown.style.bottom = '';
    
    // Check if dropdown goes off the right edge of the screen
    if (rect.right > windowWidth) {
        dropdown.style.right = '0';
        dropdown.style.left = 'auto';
    }
    
    // Check if dropdown goes off the bottom of the screen
    if (rect.bottom > windowHeight) {
        const spaceAbove = parentRect.top;
        const spaceBelow = windowHeight - parentRect.bottom;
        
        if (spaceAbove > spaceBelow) {
            // Position above the toggle button
            dropdown.style.bottom = '100%';
            dropdown.style.top = 'auto';
            dropdown.style.marginBottom = '8px';
            dropdown.style.marginTop = '0';
        } else {
            // Keep below but adjust height
            const maxHeight = windowHeight - parentRect.bottom - 20;
            dropdown.style.maxHeight = maxHeight + 'px';
            dropdown.style.overflowY = 'auto';
        }
    }
}

function initializeDropdownButtons() {
    // Edit button
    document.querySelectorAll('.dropdown-edit-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const eventId = this.dataset.eventId;
            if (eventId) {
                if (typeof openEditModal === 'function') {
                    openEditModal(eventId);
                }
            }
        });
    });

    // Attendance button
    document.querySelectorAll('.dropdown-attendance-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const eventId = this.dataset.eventId;
            if (eventId) {
                window.location.href = `11_Attendance.php?event=${eventId}`;
            }
        });
    });

    // QR code button
    document.querySelectorAll('.dropdown-qr-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const eventCode = this.dataset.eventCode;
            if (typeof generateQRCode === 'function' && eventCode) {
                generateQRCode(eventCode);
            }
        });
    });

    // View participants button
    document.querySelectorAll('.dropdown-view-participants-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const eventId = this.dataset.eventId;
            if (eventId) {
                window.location.href = `view_attendance.php?event=${eventId}`;
            }
        });
    });

    // Unarchive button
    document.querySelectorAll('.dropdown-unarchive-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const eventId = this.dataset.eventId;
            if (eventId && confirm('Are you sure you want to unarchive this event?')) {
                fetch('../php/unarchive_event.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'event_id=' + eventId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Event unarchived successfully');
                        window.location.reload();
                    } else {
                        alert('Error unarchiving event: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while unarchiving the event');
                });
            }
        });
    });

    // Delete button
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const eventId = this.dataset.eventId;
            if (eventId) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            deleteEvent(eventId);
                        }
                    });
                } else if (confirm('Are you sure you want to delete this event?')) {
                    deleteEvent(eventId);
                }
            }
        });
    });
}

function deleteEvent(eventId) {
    fetch('../php/delete_event.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'delete_id=' + eventId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (typeof Swal !== 'undefined') {
                Swal.fire(
                    'Deleted!',
                    'Your event has been deleted.',
                    'success'
                ).then(() => {
                    window.location.reload();
                });
            } else {
                alert('Event deleted successfully');
                window.location.reload();
            }
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire(
                    'Error!',
                    data.message || 'Could not delete the event.',
                    'error'
                );
            } else {
                alert('Error deleting event: ' + (data.message || 'Unknown error'));
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof Swal !== 'undefined') {
            Swal.fire(
                'Error!',
                'An error occurred while deleting the event.',
                'error'
            );
        } else {
            alert('An error occurred while deleting the event');
        }
    });
} 