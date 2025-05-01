
const modal = document.getElementById("importModal");
const openBtn = document.getElementById("openModal");
const closeBtn = document.querySelector(".btn-close");

<<<<<<< HEAD
openBtn.onclick = () => modal.style.display = "block";
closeBtn.onclick = () => modal.style.display = "none";
=======


function openModal(){
    document.getElementById('importModal').style.display = "block";
}


function openRegistration(eventId) {
    document.getElementById('importRegistration').style.display = "block";

  }
  
  function closeModal() {
    document.getElementById('importModal').style.display = "none";
  }


  function togglePanel() {
    const panel = document.querySelector('.second-page');
    panel.classList.remove('active');

    // Force reflow to restart animation
    void panel.offsetWidth;

    // Add the class back to trigger animation
    panel.classList.add('active');
  }
>>>>>>> 59f980d (Version 1.13)
