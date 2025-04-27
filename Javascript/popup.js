
const modal = document.getElementById("importModal");
const openBtn = document.getElementById("openModal");
const closeBtn = document.querySelector(".close-btn");

openBtn.onclick = () => modal.style.display = "block";
closeBtn.onclick = () => modal.style.display = "none";
