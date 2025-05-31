document.addEventListener('DOMContentLoaded', function() {
    let activeDropdown = null;
    let isDropdownClicking = false;

    // Handle dropdown toggle clicks
    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const dropdownMenu = this.nextElementSibling;
            
            // If there's an active dropdown and it's not this one, hide it
            if (activeDropdown && activeDropdown !== dropdownMenu) {
                activeDropdown.classList.remove('show');
            }
            
            // Toggle current dropdown
            if (dropdownMenu) {
                const isVisible = dropdownMenu.classList.contains('show');
                if (isVisible) {
                    dropdownMenu.classList.remove('show');
                    activeDropdown = null;
                } else {
                    dropdownMenu.classList.add('show');
                    activeDropdown = dropdownMenu;
                }
            }
        });
    });

    // Handle clicks on dropdown menu items
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
        menu.addEventListener('mousedown', function(e) {
            isDropdownClicking = true;
            e.stopPropagation();
        });

        menu.addEventListener('mouseup', function(e) {
            setTimeout(() => {
                isDropdownClicking = false;
            }, 100);
            e.stopPropagation();
        });

        menu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });

    // Handle clicks on dropdown menu buttons
    document.querySelectorAll('.dropdown-menu button').forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            // Only close the dropdown for delete action
            if (!this.classList.contains('edit-btn') && !this.classList.contains('qr-btn')) {
                if (activeDropdown) {
                    setTimeout(() => {
                        activeDropdown.classList.remove('show');
                        activeDropdown = null;
                    }, 100);
                }
            }
        });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!isDropdownClicking && !e.target.closest('.dropdown-wrapper') && activeDropdown) {
            activeDropdown.classList.remove('show');
            activeDropdown = null;
        }
    });

    // Handle scroll events with debounce
    let scrollTimeout;
    window.addEventListener('scroll', function() {
        if (scrollTimeout) {
            clearTimeout(scrollTimeout);
        }
        scrollTimeout = setTimeout(function() {
            if (!isDropdownClicking && activeDropdown) {
                activeDropdown.classList.remove('show');
                activeDropdown = null;
            }
        }, 150);
    });

    // Position dropdown menus
    function positionDropdown(dropdownMenu) {
        if (!dropdownMenu) return;

        const rect = dropdownMenu.getBoundingClientRect();
        const viewportHeight = window.innerHeight;

        // Reset position
        dropdownMenu.style.transform = '';

        // If dropdown would go below viewport, show it above the toggle
        if (rect.bottom > viewportHeight) {
            dropdownMenu.style.transform = 'translateY(-100%)';
        }
    }

    // Apply positioning to all dropdowns when shown
    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const dropdownMenu = this.nextElementSibling;
            if (dropdownMenu && dropdownMenu.classList.contains('show')) {
                positionDropdown(dropdownMenu);
            }
        });
    });

    // File upload handling
    const fileInput = document.getElementById('fileInput');
    const fileNameSpan = document.getElementById('fileName');
    const uploadForm = document.getElementById('importFrm').querySelector('form');

    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            fileNameSpan.textContent = file.name;
            handleFileUpload(file);
        } else {
            fileNameSpan.textContent = 'No file chosen';
        }
    });

    function handleFileUpload(file) {
        // Create form data
        const formData = new FormData();
        formData.append('file', file);

        // Show loading state
        const loadingDiv = document.createElement('div');
        loadingDiv.id = 'uploadLoading';
        loadingDiv.innerHTML = 'Uploading and processing file...';
        loadingDiv.style.cssText = 'position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); ' +
                                 'background: rgba(0,0,0,0.8); color: white; padding: 20px; border-radius: 5px; z-index: 1000;';
        document.body.appendChild(loadingDiv);

        // Send request
        fetch('../php/importData.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Remove loading state
            document.getElementById('uploadLoading').remove();

            // Create alert div
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${data.success ? 'success' : 'danger'}`;
            
            if (data.success) {
                alertDiv.textContent = data.message;
            } else {
                let errorMessage = data.message + '\n';
                if (data.errors && data.errors.length > 0) {
                    errorMessage += '\nErrors:\n' + data.errors.join('\n');
                }
                alertDiv.textContent = errorMessage;
            }

            // Style the alert
            alertDiv.style.cssText = 'position: fixed; top: 20px; left: 50%; transform: translateX(-50%); ' +
                                   'z-index: 1000; width: auto; max-width: 80%; white-space: pre-line;';

            // Add alert to page
            document.body.appendChild(alertDiv);

            // Remove alert after 5 seconds
            setTimeout(() => {
                alertDiv.remove();
                if (data.success) {
                    window.location.reload(); // Reload page to show new data
                }
            }, 5000);
        })
        .catch(error => {
            // Remove loading state
            if (document.getElementById('uploadLoading')) {
                document.getElementById('uploadLoading').remove();
            }

            // Show error
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger';
            alertDiv.textContent = 'Error uploading file: ' + error.message;
            alertDiv.style.cssText = 'position: fixed; top: 20px; left: 50%; transform: translateX(-50%); ' +
                                   'z-index: 1000; width: auto; max-width: 80%;';
            document.body.appendChild(alertDiv);

            // Remove error after 5 seconds
            setTimeout(() => alertDiv.remove(), 5000);
        });
    }

    // Handle edit student
    window.editStudent = function(studentDataStr) {
        try {
            const studentData = typeof studentDataStr === 'string' ? JSON.parse(studentDataStr) : studentDataStr;
            const modal = document.getElementById('participantModal');
            if (!modal) return;

            // Populate the modal with student data
            document.getElementById('modalTitle').textContent = 'Edit Student';
            
            // Set form values
            const setFieldValue = (fieldId, value) => {
                const field = document.getElementById(fieldId);
                if (field) field.value = value || '';
            };

            setFieldValue('participant-number', studentData.number);
            setFieldValue('participant-id', studentData.ID);
            setFieldValue('participant-firstname', studentData.first_name);
            setFieldValue('participant-lastname', studentData.last_name);
            setFieldValue('participant-course', studentData.Course);
            setFieldValue('participant-gender', studentData.Gender);
            setFieldValue('participant-age', studentData.Age);
            setFieldValue('participant-year', studentData.Year);
            setFieldValue('participant-dept', studentData.Dept);

            // Extract section letter from the full section
            if (studentData.Section) {
                const sectionLetter = studentData.Section.slice(-1);
                setFieldValue('participant-section', sectionLetter);
            }

            // Show the modal
            modal.style.display = 'block';

            // Add event listener for form submission
            const form = document.getElementById('participantForm');
            if (form) {
                form.onsubmit = function(e) {
                    e.preventDefault();
                    
                    // Show confirmation dialog
                    Swal.fire({
                        title: 'Update Student',
                        text: 'Are you sure you want to update this student\'s information?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#17692d',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, update student',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const formData = new FormData(form);
                            formData.append('action', 'save_participant');

                            // Send AJAX request
                            fetch('7_StudentTable.php', {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        title: 'Success!',
                                        text: data.message,
                                        icon: 'success',
                                        confirmButtonColor: '#17692d'
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: data.message,
                                        icon: 'error',
                                        confirmButtonColor: '#d33'
                                    });
                                }
                            })
                            .catch(() => {
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'An error occurred while saving. Please try again.',
                                    icon: 'error',
                                    confirmButtonColor: '#d33'
                                });
                            });
                        }
                    });
                };
            }
        } catch (error) {
            Swal.fire({
                title: 'Error!',
                text: 'An error occurred while opening the edit form. Please try again.',
                icon: 'error',
                confirmButtonColor: '#d33'
            });
        }
    };

    // Handle delete student
    window.deleteStudent = function(studentId, studentName) {
        Swal.fire({
            title: 'Delete Student',
            text: `Are you sure you want to delete ${studentName}? This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete student',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'delete_participant');
                formData.append('delete_id', studentId);

                fetch('7_StudentTable.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonColor: '#17692d'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message,
                            icon: 'error',
                            confirmButtonColor: '#d33'
                        });
                    }
                })
                .catch(() => {
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred while deleting. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#d33'
                    });
                });
            }
        });
    };

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('participantModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    };

    // Close modal when clicking the close button
    document.querySelector('.close').onclick = function() {
        document.getElementById('participantModal').style.display = 'none';
    };

    // Reset form when opening add new student
    window.openAddStudentModal = function() {
        try {
            const modal = document.getElementById('participantModal');
            if (!modal) {
                console.error('Modal element not found');
                return;
            }

            // Reset form and title
            document.getElementById('modalTitle').textContent = 'Add New Student';
            const form = document.getElementById('participantForm');
            if (!form) {
                console.error('Form element not found');
                return;
            }

            form.reset();
            document.getElementById('participant-number').value = '';
            modal.style.display = 'block';

            // Add event listener for form submission
            form.onsubmit = function(e) {
                e.preventDefault();
                
                const formData = new FormData(form);
                formData.append('action', 'save_participant');

                // Send AJAX request
                fetch('7_StudentTable.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: data.message || 'Student added successfully.',
                            icon: 'success',
                            confirmButtonColor: '#17692d'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        throw new Error(data.message || 'Failed to add student');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: error.message || 'An error occurred while saving. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#d33'
                    });
                });
            };
        } catch (error) {
            console.error('Error in openAddStudentModal:', error);
            Swal.fire({
                title: 'Error!',
                text: 'An error occurred while opening the form. Please try again.',
                icon: 'error',
                confirmButtonColor: '#d33'
            });
        }
    };

    // Show success/error messages using SweetAlert2
    // Check for URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.has('success')) {
        Swal.fire({
            title: 'Success!',
            text: 'Student information has been updated successfully.',
            icon: 'success',
            confirmButtonColor: '#17692d'
        });
        // Clean URL
        window.history.replaceState({}, document.title, window.location.pathname);
    } else if (urlParams.has('deleted')) {
        Swal.fire({
            title: 'Deleted!',
            text: 'Student has been deleted successfully.',
            icon: 'success',
            confirmButtonColor: '#17692d'
        });
        // Clean URL
        window.history.replaceState({}, document.title, window.location.pathname);
    } else if (urlParams.has('error')) {
        Swal.fire({
            title: 'Error!',
            text: 'Something went wrong. Please try again.',
            icon: 'error',
            confirmButtonColor: '#d33'
        });
        // Clean URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }
}); 