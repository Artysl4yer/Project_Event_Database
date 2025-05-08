document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');

    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value.trim();

        if (!email || !password) {
            showPopup('Please enter both email and password');
            return;
        }

        verifyCredentials(email, password);
    });

    function verifyCredentials(email, password) {
        // Hardcoded admin credentials
        const adminEmail = "admin@plp.edu.ph";
        const adminPassword = "admin2023";

        if (email === adminEmail && password === adminPassword) {
            // Directly redirect to event page
            window.location.href = "4_Event.php";
        } else {
            showPopup('Invalid credentials. Please try again.');
        }
    }

    function showPopup(message) {
        // Use your custom popup method here
        alert(message); // Replace with your own popup logic if needed
    }

    // Password visibility toggle function
    window.togglePassword = function() {
        const passwordField = document.getElementById('password');
        const toggleIcon = document.getElementById('togglePassword');

        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordField.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    };
});
