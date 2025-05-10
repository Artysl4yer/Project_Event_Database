document.addEventListener("DOMContentLoaded", function () {
const loginLink = document.querySelector(".action-link[href='#']");
const universityInfo = document.querySelector(".university-info");
const loginBox = document.querySelector(".login-box");
const backButton = document.querySelector(".back-btn");

    // Initialize: show university-info
universityInfo.classList.add("active");
loginBox.classList.remove("active");

loginLink.addEventListener("click", function (e) {
    e.preventDefault();
    universityInfo.classList.remove("active");
    loginBox.classList.add("active");
});

backButton.addEventListener("click", function () {
    loginBox.classList.remove("active");
    universityInfo.classList.add("active");
});
});