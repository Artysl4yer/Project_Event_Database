



function openModal(){
    document.getElementById('importModal').style.display = "block";
}


function openRegistration(eventId) {
    document.getElementById('importRegistration').style.display = "block";

  }
  
  function closeModal() {
    document.getElementById('importModal').style.display = "none";
  }

<<<<<<< HEAD

  function togglePanel() {
    const panel = document.querySelector('.second-page');
    panel.classList.remove('active');

    // Force reflow to restart animation
    void panel.offsetWidth;

    // Add the class back to trigger animation
    panel.classList.add('active');
  }
=======
>>>>>>> 6c4bf48 (Version 1.13)
