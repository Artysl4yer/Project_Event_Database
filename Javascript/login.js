function showForm(formId) {
    console.log('Showing form:', formId);
    
    // Get both forms
    const loginForm = document.querySelector('.loginpage');
    const registerForm = document.querySelector('.registration-box');
    
    if (!loginForm || !registerForm) {
        console.error('Forms not found:', { loginForm, registerForm });
        return;
    }
    
    if (formId === 'register-form') {
        console.log('Switching to registration form');
        loginForm.classList.remove('active');
        registerForm.classList.add('active');
    } else {
        console.log('Switching to login form');
        registerForm.classList.remove('active');
        loginForm.classList.add('active');
    }
    
    // Log the state after changes
    console.log('Login form active:', loginForm.classList.contains('active'));
    console.log('Register form active:', registerForm.classList.contains('active'));
    console.log('Login form visibility:', window.getComputedStyle(loginForm).visibility);
    console.log('Register form visibility:', window.getComputedStyle(registerForm).visibility);
}

function showPass(event) {
    // Get the checkbox that was clicked
    const checkbox = event.target;
    
    // Find the closest form to determine which password field to toggle
    const form = checkbox.closest('form');
    const passwordInput = form.querySelector('input[type="password"], input[type="text"][id="password"], input[type="text"][id="reg-password"]');
    
    if (passwordInput) {
        passwordInput.type = checkbox.checked ? 'text' : 'password';
    }
}

function formatInput(input) {
    if (input.id === 'student_id' || input.id === 'identifier') {
        // Remove any non-digit characters
        let value = input.value.replace(/\D/g, '');
        
        // Format as XX-XXXXXX if it's a student ID
        if (value.length > 2) {
            value = value.substring(0, 2) + '-' + value.substring(2);
        }
        
        input.value = value;
    }
}


// Handle form submissions
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const registrationForm = document.getElementById('registrationForm');

    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('../php/login_register.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(data => {
                // Check if the response contains an error message
                if (data.includes('Invalid Student ID/Email or password')) {
                    document.getElementById('loginMessage').innerHTML = 'Invalid Student ID/Email or password';
                    document.getElementById('loginMessage').className = 'message error';
                    return;
                }
                
                // If login was successful, the PHP script will have set the session
                // and we should be redirected to 4_Event.php
                window.location.href = '4_Event.php';
            })
            .catch(error => {
                console.error('Login error:', error);
                document.getElementById('loginMessage').innerHTML = 'Login failed. Please try again.';
                document.getElementById('loginMessage').className = 'message error';
            });
        });
    }

    if (registrationForm) {
        registrationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!validateIdentifier(this)) return;

            const formData = new FormData(this);
            
            fetch('../php/login_register.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(() => {
                // The PHP script will handle the redirect
                window.location.reload();
            })
            .catch(error => {
                document.getElementById('registerMessage').innerHTML = 'Registration failed. Please try again.';
                document.getElementById('registerMessage').className = 'message error';
            });
        });
    }

    // Add click event listeners to all show password checkboxes
    const showPassCheckboxes = document.querySelectorAll('.show-pass input[type="checkbox"]');
    showPassCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('click', showPass);
    });
});