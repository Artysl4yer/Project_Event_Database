function toggleDropdown(number) {
  const dropdown = document.getElementById('dropdown' + number);
  if (!dropdown) return;

  // Hide all other dropdowns first
  document.querySelectorAll('.dropdown-menu').forEach(menu => {
    if (menu !== dropdown) {
      menu.style.display = 'none';
    }
  });

  // Toggle current dropdown
  if (dropdown.style.display === 'block') {
    dropdown.style.display = 'none';
  } else {
    dropdown.style.display = 'block';
  }
}

// Close all dropdowns if click outside dropdown-wrapper
document.addEventListener('click', function(event) {
  if (!event.target.closest('.dropdown-wrapper')) {
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
      menu.style.display = 'none';
    });
  }
});

// Opens modal to edit event with eventId
function editEvent(number) {
  closeAllDropdowns();
  openModal(number); // Your existing function to open modal and fill form
}

// Confirm deletion placeholder
function deleteEvent(number) {
  closeAllDropdowns();
  if (confirm('Are you sure you want to delete event #' + number + '?')) {
    // Add your delete logic here, e.g. AJAX call
    console.log('Delete event ID:', number);
  }
}

function closeAllDropdowns() {
  document.querySelectorAll('.dropdown-menu').forEach(menu => {
    menu.style.display = 'none';
  });
}
