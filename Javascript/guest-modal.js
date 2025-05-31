// Get the modal
const modal = document.getElementById('guestModal');

// Get the <span> element that closes the modal
const span = document.getElementsByClassName('close')[0];

// When the user clicks on <span> (x), close the modal
if (span) {
    span.onclick = function() {
        closeGuestModal();
    }
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        closeGuestModal();
    }
}

// Function to validate phone number
function validatePhoneNumber(phone) {
    const phoneRegex = /^09\d{9}$/;
    return phoneRegex.test(phone);
}

// Function to validate email
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Function to validate the form
function validateGuestForm() {
    const firstName = document.getElementById('guest-firstname').value.trim();
    const lastName = document.getElementById('guest-lastname').value.trim();
    const email = document.getElementById('guest-email').value.trim();
    const organization = document.getElementById('guest-organization').value.trim();
    const contact = document.getElementById('guest-contact').value.trim();

    if (!firstName || !lastName) {
        alert('Please enter both first and last name');
        return false;
    }

    if (!validateEmail(email)) {
        alert('Please enter a valid email address');
        return false;
    }

    if (!organization) {
        alert('Please enter the organization');
        return false;
    }

    if (!validatePhoneNumber(contact)) {
        alert('Please enter a valid 11-digit phone number starting with 09');
        return false;
    }

    return true;
}

// Add form validation before submission
const guestForm = document.getElementById('guestForm');
if (guestForm) {
    guestForm.addEventListener('submit', function(e) {
        if (!validateGuestForm()) {
            e.preventDefault();
        }
    });
}

// Function to handle delete guest
function deleteGuest(guestId) {
    if (confirm('Are you sure you want to delete this guest?')) {
        fetch('../php/delete_guest.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: guestId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Error deleting guest');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the guest');
        });
    }
} 