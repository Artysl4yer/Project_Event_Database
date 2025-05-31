$(document).ready(function() {
    // Format student ID input
    function formatStudentId(input) {
        // Remove any non-digit characters
        let value = input.value.replace(/\D/g, '');
        
        // Ensure maximum of 7 digits
        if (value.length > 7) {
            value = value.substr(0, 7);
        }
        
        // Format as XX-XXXXX
        if (value.length > 2) {
            value = value.substr(0, 2) + '-' + value.substr(2);
        }
        
        input.value = value;
    }

    // Apply formatting to student ID fields
    $('#student_id, #identifier').on('input', function() {
        formatStudentId(this);
    });

    // Toggle between login and registration forms
    $('.register-link').click(function(e) {
        e.preventDefault();
        $('.loginpage').removeClass('active').addClass('hidden');
        $('.registration-box').removeClass('hidden').addClass('active');
    });

    $('.back-btn').click(function(e) {
        e.preventDefault();
        $('.registration-box').removeClass('active').addClass('hidden');
        $('.loginpage').removeClass('hidden').addClass('active');
    });

    // Show/hide password
    $('.show-pass input').change(function() {
        var passwordField = $(this).closest('form').find('input[type="password"]');
        passwordField.attr('type', this.checked ? 'text' : 'password');
    });

    // Clear any existing messages
    function clearMessages() {
        $('#loginMessage, #registerMessage').hide().removeClass('error success').text('');
    }

    // Handle login form submission
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        clearMessages();
        
        // Validate form
        var identifier = $('#identifier').val().trim();
        var password = $('#login-password').val().trim();
        
        if (!identifier || !password) {
            $('#loginMessage')
                .addClass('error')
                .text('Please enter both Student ID/Email and password')
                .show();
            return;
        }
        
        // Show loading screen
        $('.loading-screen').css('display', 'flex').hide().fadeIn(300);
        
        // Prepare form data
        var formData = new FormData(this);
        formData.append('login', '1');
        
        // Send AJAX request
        $.ajax({
            type: 'POST',
            url: '../php/login_register.php',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                console.log('Login response:', response);
                
                if (response.success) {
                    $('#loginMessage')
                        .removeClass('error')
                        .addClass('success')
                        .text('Login successful! Redirecting...')
                        .show();
                        
                    setTimeout(function() {
                        $('.loading-screen').fadeOut(300, function() {
                            if (response.redirect) {
                                window.location.href = response.redirect;
                            } else {
                                $('#loginMessage')
                                    .removeClass('success')
                                    .addClass('error')
                                    .text('Error: No redirect URL provided')
                                    .show();
                            }
                        });
                    }, 1000);
                } else {
                    $('.loading-screen').fadeOut(300);
                    $('#loginMessage')
                        .removeClass('success')
                        .addClass('error')
                        .text(response.message || 'Login failed. Please try again.')
                        .show();
                }
            },
            error: function(xhr, status, error) {
                console.error('Login error:', {xhr: xhr, status: status, error: error});
                $('.loading-screen').fadeOut(300);
                
                var errorMessage = 'An error occurred. Please try again.';
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        errorMessage = response.message;
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                }
                
                $('#loginMessage')
                    .removeClass('success')
                    .addClass('error')
                    .text(errorMessage)
                    .show();
            }
        });
    });

    // Handle registration form submission
    $('#registrationForm').on('submit', function(e) {
        e.preventDefault();
        clearMessages();
        
        // Validate form
        var studentId = $('#student_id').val().trim();
        var email = $('#email').val().trim();
        var password = $('#reg-password').val().trim();
        
        if (!studentId || !email || !password) {
            $('#registerMessage')
                .addClass('error')
                .text('Please fill in all required fields')
                .show();
            return;
        }
        
        // Validate student ID format
        if (!/^\d{2}-\d{5}$/.test(studentId)) {
            $('#registerMessage')
                .addClass('error')
                .text('Please enter a valid Student ID in XX-XXXXX format')
                .show();
            return;
        }
        
        // Prepare form data
        var formData = new FormData(this);
        formData.append('register', '1');
        
        // Send AJAX request
        $.ajax({
            type: 'POST',
            url: '../php/login_register.php',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                console.log('Registration response:', response);
                
                if (response.success) {
                    $('#registerMessage')
                        .removeClass('error')
                        .addClass('success')
                        .text(response.message || 'Registration successful! Redirecting...')
                        .show();
                        
                    setTimeout(function() {
                        window.location.href = '1_Login.php';
                    }, 2000);
                } else {
                    $('#registerMessage')
                        .removeClass('success')
                        .addClass('error')
                        .text(response.message || 'Registration failed. Please try again.')
                        .show();
                }
            },
            error: function(xhr, status, error) {
                console.error('Registration error:', {xhr: xhr, status: status, error: error});
                
                var errorMessage = 'An error occurred. Please try again.';
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        errorMessage = response.message;
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                }
                
                $('#registerMessage')
                    .removeClass('success')
                    .addClass('error')
                    .text(errorMessage)
                    .show();
            }
        });
    });
});

// Show/hide password function
function showPass() {
    var x = document.getElementById("login-password");
    var y = document.getElementById("reg-password");
    if (x.type === "password") {
        x.type = "text";
        if (y) y.type = "text";
    } else {
        x.type = "password";
        if (y) y.type = "password";
    }
} 