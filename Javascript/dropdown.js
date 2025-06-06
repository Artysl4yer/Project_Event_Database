// This file is deprecated. All dropdown functionality has been moved to event-dropdown.js

document.addEventListener('DOMContentLoaded', function() {
    // Handle dropdown toggles
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent default
            e.stopPropagation(); // Stop bubbling
            
            // Close all other dropdowns first
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                if (menu !== this.nextElementSibling) {
                    menu.classList.remove('show');
                }
            });
            
            // Toggle this dropdown
            const dropdownMenu = this.nextElementSibling;
            if (dropdownMenu) {
                dropdownMenu.classList.toggle('show');
            }
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        // Don't close if clicking inside a dropdown menu
        if (!e.target.closest('.dropdown-menu') && !e.target.matches('.dropdown-toggle')) {
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });

    // Optional: close dropdowns on scroll or resize
    window.addEventListener('scroll', closeAllDropdowns);
    window.addEventListener('resize', closeAllDropdowns);

    function closeAllDropdowns() {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.style.display = 'none';
        });
    }
});

function handleDropdownClick(event) {
    if (event.target.classList.contains('dropdown-toggle')) {
        event.preventDefault();
        event.stopPropagation();
        toggleDropdown(event.target.getAttribute('data-dropdown-number'));
        return;
    }

    if (event.target.closest('.dropdown-menu button')) {
        event.stopPropagation();
        return;
    }

    if (!event.target.closest('.dropdown-wrapper')) {
        closeAllDropdowns();
    }
}

function toggleDropdown(number) {
    const dropdown = document.getElementById('dropdown' + number);
    if (!dropdown) return;

    closeAllDropdowns();
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    positionDropdown(dropdown);
}

function positionDropdown(dropdown) {
    const rect = dropdown.getBoundingClientRect();
    if (rect.right > window.innerWidth) {
        dropdown.style.left = 'auto';
        dropdown.style.right = '0';
    }
}

function editEvent(number) {
    closeAllDropdowns();
    openModal(number);
}

function deleteEvent(number) {
    closeAllDropdowns();
    if (confirm(`Are you sure you want to delete event #${number}?`)) {
        fetch(`../php/delete_event.php?id=${number}`, { method: 'DELETE' })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelector(`tr[data-event-id="${number}"]`).remove();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Delete failed');
        });
    }
}

function openModal(eventId = null) {
    const modal = document.getElementById('importModal');
    modal.style.display = "block";

    if (eventId) {
        fetch(`get_event_details.php?id=${eventId}`)
        .then(response => response.json())
        .then(data => {
            document.querySelector('[name="event-title"]').value = data.event_title;
            document.querySelector('[name="event-location"]').value = data.event_location;
            document.querySelector('[name="event-date-start"]').value = data.date_start.split(' ')[0];
            document.querySelector('[name="event-time-start"]').value = data.date_start.split(' ')[1];
            document.querySelector('[name="event-date-end"]').value = data.date_end.split(' ')[0];
            document.querySelector('[name="event-time-end"]').value = data.date_end.split(' ')[1];
            document.querySelector('[name="event-orgs"]').value = data.organization;
            document.querySelector('[name="event-description"]').value = data.event_description;
            document.querySelector('[name="code"]').value = data.number;
        })
        .catch(error => console.error('Error fetching event details:', error));
    } else {
        document.getElementById('eventForm').reset();
        document.querySelector('[name="code"]').value = '';
    }
    populateCodeField();
}

function closeImportModal() {
    document.getElementById('importModal').style.display = "none";
}

function openRegistration(eventId) {
    document.getElementById('importRegistration').style.display = "block";
}

function setupEditButtons() {
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            openModal(button.dataset.eventId);
        });
    });
}

function populateCodeField() {
    const codeField = document.getElementById('codeField');
    if (codeField && !codeField.value) {
        codeField.value = generateCode(12);
    }
}

function generateCode(length = 12) {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let code = '';
    for (let i = 0; i < length; i++) {
        code += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return code;
}

window.addEventListener('resize', function() {
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
        if (menu.style.display === 'block') {
            positionDropdown(menu);
        }
    });
});

document.addEventListener('click', function(event) {
    const modal = document.getElementById('importModal');
    if (event.target === modal) closeImportModal();
});
    