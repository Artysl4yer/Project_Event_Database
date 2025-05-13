document.addEventListener("DOMContentLoaded", function () {
    const loginLink = document.querySelector(".login-link");
    const registerLink = document.querySelector(".register-link");
    
    if (!loginLink || !registerLink) {
        console.error("Login or Register link not found in DOM.");
        return; // Optional: stop the script if links are missing
    }

    const universityInfo = document.querySelector(".university-info");
    const loginBox = document.querySelector(".login-box");
    const registrationBox = document.querySelector(".registration-box");

    const backButtons = document.querySelectorAll(".back-btn");

    // Initial view
    universityInfo.classList.add("active");
    loginBox.classList.remove("active");
    registrationBox.classList.remove("active");

    // Show login box
    loginLink.addEventListener("click", function (e) {
    e.preventDefault();
    console.log("Login link clicked");
    loginBox.classList.add("active");
    registrationBox.classList.remove("active");
});

    // Show registration box
registerLink.addEventListener("click", function (e) {
    e.preventDefault();
    console.log("Register link clicked");
    registrationBox.classList.add("active");
    loginBox.classList.remove("active");
});



    // Go back to university info from either form
    backButtons.forEach(button => {
        button.addEventListener("click", function () {
            loginBox.classList.remove("active");
            registrationBox.classList.remove("active");
            universityInfo.classList.add("active");
        });
    });
});
