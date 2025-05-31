// Handle archive operations
function archiveEvent(eventId) {
    Swal.fire({
        title: 'Archive Event',
        text: 'Are you sure you want to archive this event?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#17692d',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, archive it!'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('event_id', eventId);

            fetch('../php/archive_event.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        'Archived!',
                        'The event has been archived.',
                        'success'
                    ).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire(
                        'Error!',
                        data.message,
                        'error'
                    );
                }
            })
            .catch(error => {
                Swal.fire(
                    'Error!',
                    'Failed to archive event.',
                    'error'
                );
            });
        }
    });
}

function unarchiveEvent(eventId) {
    Swal.fire({
        title: 'Unarchive Event',
        text: 'Are you sure you want to unarchive this event?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#17692d',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, unarchive it!'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('unarchive', eventId);

            fetch('../php/unarchive_event.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        'Unarchived!',
                        'The event has been unarchived.',
                        'success'
                    ).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire(
                        'Error!',
                        data.message,
                        'error'
                    );
                }
            })
            .catch(error => {
                Swal.fire(
                    'Error!',
                    'Failed to unarchive event.',
                    'error'
                );
            });
        }
    });
}

function deleteArchive(eventId) {
    Swal.fire({
        title: 'Delete Archived Event',
        text: 'Are you sure you want to permanently delete this archived event? This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('event_id', eventId);

            fetch('../php/delete_archive.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        'Deleted!',
                        'The archived event has been deleted.',
                        'success'
                    ).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire(
                        'Error!',
                        data.message,
                        'error'
                    );
                }
            })
            .catch(error => {
                Swal.fire(
                    'Error!',
                    'Failed to delete archived event.',
                    'error'
                );
            });
        }
    });
}

function viewEventDetails(eventId) {
    fetch(`../php/get_event_details.php?event_id=${eventId}&type=archive`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const event = data.event;
                const modalContent = document.getElementById('eventDetails');
                
                const startDate = new Date(event.event_start);
                const endDate = new Date(event.event_end);
                
                modalContent.innerHTML = `
                    <div class="event-detail-item">
                        <strong>Title:</strong> ${event.event_title}
                    </div>
                    <div class="event-detail-item">
                        <strong>Event Code:</strong> ${event.event_code}
                    </div>
                    <div class="event-detail-item">
                        <strong>Location:</strong> ${event.event_location}
                    </div>
                    <div class="event-detail-item">
                        <strong>Start:</strong> ${startDate.toLocaleString()}
                    </div>
                    <div class="event-detail-item">
                        <strong>End:</strong> ${endDate.toLocaleString()}
                    </div>
                    <div class="event-detail-item">
                        <strong>Description:</strong> ${event.event_description}
                    </div>
                    <div class="event-detail-item">
                        <strong>Organization:</strong> ${event.organization}
                    </div>
                    <div class="event-detail-item">
                        <strong>Status:</strong> ${event.event_status}
                    </div>
                `;
                
                document.getElementById('viewEventModal').style.display = 'block';
            } else {
                Swal.fire(
                    'Error!',
                    'Failed to load event details.',
                    'error'
                );
            }
        })
        .catch(error => {
            Swal.fire(
                'Error!',
                'Failed to load event details.',
                'error'
            );
        });
}

function closeViewModal() {
    document.getElementById('viewEventModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('viewEventModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}

// Table sorting functionality
function sortTable(column, order) {
    const table = document.getElementById('archiveTable');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    rows.sort((a, b) => {
        let aValue = a.querySelector(`td[data-label='${column}']`).textContent;
        let bValue = b.querySelector(`td[data-label='${column}']`).textContent;

        if (column === 'Start' || column === 'End') {
            aValue = new Date(aValue).getTime();
            bValue = new Date(bValue).getTime();
        } else if (column === 'Number') {
            aValue = parseInt(aValue);
            bValue = parseInt(bValue);
        }

        if (order === 'asc') {
            return aValue > bValue ? 1 : -1;
        } else {
            return aValue < bValue ? 1 : -1;
        }
    });

    rows.forEach(row => tbody.appendChild(row));
}

// Filter functionality
function applyFilter(filter) {
    const rows = document.querySelectorAll('#archiveTable tbody tr');
    
    rows.forEach(row => {
        switch(filter) {
            case 'all':
                row.style.display = '';
                break;
            case 'title':
                sortTable('Title', 'asc');
                break;
            case 'date':
                sortTable('Start', 'desc');
                break;
            case 'status':
                const status = row.querySelector('td[data-label="Status"]').textContent.toLowerCase();
                row.style.display = status === 'active' ? '' : 'none';
                break;
        }
    });
}

// Dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    let activeDropdown = null;
    let isDropdownClicking = false;

    // Handle dropdown toggle clicks
    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const dropdownMenu = this.nextElementSibling;
            
            // If there's an active dropdown and it's not this one, hide it
            if (activeDropdown && activeDropdown !== dropdownMenu) {
                activeDropdown.classList.remove('show');
            }
            
            // Toggle current dropdown
            if (dropdownMenu) {
                dropdownMenu.classList.toggle('show');
                activeDropdown = dropdownMenu.classList.contains('show') ? dropdownMenu : null;
            }
        });
    });

    // Handle clicks on dropdown menu items
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
        menu.addEventListener('mousedown', function(e) {
            isDropdownClicking = true;
            e.stopPropagation();
        });

        menu.addEventListener('mouseup', function(e) {
            setTimeout(() => {
                isDropdownClicking = false;
            }, 100);
            e.stopPropagation();
        });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown-wrapper') && activeDropdown) {
            activeDropdown.classList.remove('show');
            activeDropdown = null;
        }
    });

    // Handle scroll events
    window.addEventListener('scroll', function() {
        if (activeDropdown) {
            activeDropdown.classList.remove('show');
            activeDropdown = null;
        }
    });
});

// Add these styles to ensure dropdowns are visible
const style = document.createElement('style');
style.textContent = `
    .dropdown-wrapper {
        position: relative;
    }

    .dropdown-toggle {
        background: none;
        border: none;
        padding: 5px 10px;
        cursor: pointer;
        color: #666;
    }

    .dropdown-toggle:hover {
        color: #333;
    }

    .dropdown-menu {
        display: none;
        position: absolute;
        right: 0;
        top: 100%;
        background: white;
        min-width: 160px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        border-radius: 4px;
        z-index: 1000;
        padding: 8px 0;
    }

    .dropdown-menu.show {
        display: block;
    }

    .dropdown-menu button {
        display: block;
        width: 100%;
        padding: 8px 16px;
        text-align: left;
        background: none;
        border: none;
        cursor: pointer;
        font-size: 14px;
        color: #333;
        transition: background-color 0.2s;
    }

    .dropdown-menu button:hover {
        background-color: #f5f5f5;
    }

    .dropdown-menu button i {
        margin-right: 8px;
        width: 16px;
    }
`;
document.head.appendChild(style); 