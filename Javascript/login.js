document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    const cancelBtn = document.querySelector('.cancel-btn');

    // Login form submission
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value.trim();

        // Simple validation
        if (!email || !password) {
            alert('Please fill in all fields');
            return;
        }

        // Here you would typically send data to server for authentication
        // For now, we'll simulate a successful login
        simulateLogin(email, password);
    });

    // Cancel button functionality
    cancelBtn.addEventListener('click', function() {
        document.getElementById('email').value = '';
        document.getElementById('password').value = '';
    });

    // Simulate login function (replace with actual API call)
    function simulateLogin(email, password) {
        // Show loading state
        loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
        loginBtn.disabled = true;

        // Simulate API call delay
        setTimeout(() => {
            // Check for a test credential (in real app, remove this)
            if (email === 'admin@plp.edu.ph' && password === 'plp123') {
                // Store user session (in a real app, you'd get this from server)
                sessionStorage.setItem('isLoggedIn', 'true');
                sessionStorage.setItem('userEmail', email);
                
                // Redirect to event page
                window.location.href = 'event.php';
            } else {
                // For demo purposes, we'll allow any non-empty login
                sessionStorage.setItem('isLoggedIn', 'true');
                sessionStorage.setItem('userEmail', email);
                window.location.href = 'event.php';
                
                // In a real app, you would show an error:
                // alert('Invalid credentials');
                // loginBtn.innerHTML = 'Log in';
                // loginBtn.disabled = false;
            }
        }, 1500);
    }

    // Check if user is already logged in
    if (sessionStorage.getItem('isLoggedIn') === 'true') {
        window.location.href = 'event.php';
    }
});