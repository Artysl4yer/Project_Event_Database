 $(document).ready(function() {
                $('.register-link').click(function(e) {
                    e.preventDefault();
                    console.log("Register link clicked");
                    $('.loginpage').removeClass('active').addClass('hidden');
                    $('.registration-box').removeClass('hidden').addClass('active');
                });

                $('.back-btn').click(function(e) {
                    e.preventDefault();
                    $('.registration-box').removeClass('active').addClass('hidden');
                    $('.loginpage').removeClass('hidden').addClass('active');
                });

                $('#registrationForm').on('submit', function(e) {
                    e.preventDefault();
                    console.log('Registration form submitted');
                    
                    var formData = {
                        name: $('#reg-name').val(),
                        organization: $('#reg-organization').val(),
                        username: $('#reg-username').val(),
                        password: $('#reg-password').val(),
                        confirm_password: $('#reg-confirm-password').val()
                    };

                    console.log('Form data:', formData);

                    $.ajax({
                        type: 'POST',
                        url: '../php/register.php',
                        data: formData,
                        dataType: 'json',
                        success: function(response) {
                            console.log('Registration response:', response);
                            if (response.success) {
                                $('#registerMessage').html(response.message).removeClass('error').addClass('success');
                                $('#registrationForm')[0].reset();
                                setTimeout(function() {
                                    $('.registration-box').removeClass('active').addClass('hidden');
                                    $('.loginpage').removeClass('hidden').addClass('active');
                                    $('#loginMessage').html('Registration successful! Please login with your new account.').removeClass('error').addClass('success');
                                }, 2000);
                            } else {
                                $('#registerMessage').html(response.message).removeClass('success').addClass('error');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Registration error:', error);
                            $('#registerMessage').html('An error occured').addClass('error');
                        }
                    });
                });

                $('#loginForm').on('submit', function(e) {
                    e.preventDefault();
                    console.log('Login form submitted');
                    $.ajax({
                        type: 'POST',
                        url: '../php/login.php',
                        data: $(this).serialize(),
                        dataType: 'json',
                        success: function(response) {
                            console.log('Login response:', response);
                            if (response.success) {
                                window.location.href = '4_Event.php';
                            } else {
                                $('#loginMessage').html(response.message).removeClass('success').addClass('error');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Login error:', error);
                            $('#loginMessage').html('Invalid credentials').addClass('error');
                        }
                    });
                });
            });