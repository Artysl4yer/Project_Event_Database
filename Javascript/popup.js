// Open the modal for adding a new event
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
              document.querySelector('[name="code"]').value = data.number; // Store event ID for updating
              
          })
          .catch(error => console.error('Error fetching event details:', error));
  } else {
      document.getElementById('eventForm').reset();
      document.querySelector('[name="code"]').value = ''; // Clear event ID for new event
  }
  populateCodeField();
}

// Close the modal
function closeModal() {
  document.getElementById('importModal').style.display = "none";
}

// Open the registration modal (if needed)
function openRegistration(eventId) {
  document.getElementById('importRegistration').style.display = "block";
}

// Example of an event edit button on your page
function setupEditButton(eventId) {
  const editButton = document.querySelector(`#edit-btn-${eventId}`);
  editButton.addEventListener('click', function() {
      openModal(eventId); // Pass eventId to the modal
  });
}

// You can call this function to set up the edit buttons when the page loads
window.onload = function() {
  const editButtons = document.querySelectorAll('.edit-btn');
  editButtons.forEach(button => {
      const eventId = button.dataset.eventId;
      setupEditButton(eventId);
  });
};

