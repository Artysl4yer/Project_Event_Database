function openParticipantModal(participantId = null) {
    const modal = document.getElementById('participantModal');
    const form = document.getElementById('participantForm');
    const title = document.getElementById('modalTitle');
    
    if (participantId) {
        title.textContent = 'Edit Participant';
        document.getElementById('participant-number').value = participantId;
        
        fetch(`7_StudentTable.php?action=get_participant&id=${participantId}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('participant-id').value = data.ID || '';
                document.getElementById('participant-name').value = data.Name || '';
                document.getElementById('participant-course').value = data.Course || '';
                document.getElementById('participant-section').value = data.Section || '';
                document.getElementById('participant-gender').value = data.Gender || '';
                document.getElementById('participant-age').value = data.Age || '';
                document.getElementById('participant-year').value = data.Year || '';
                document.getElementById('participant-dept').value = data.Dept || '';
            });
    } else {
        title.textContent = 'Add New Participant';
        form.reset();
        document.getElementById('participant-number').value = '';
    }
    
    modal.style.display = 'block';
}

function closeModal() {
    document.getElementById('participantModal').style.display = 'none';
}

window.addEventListener('click', function(event) {
    const modal = document.getElementById('participantModal');
    if (event.target === modal) {
        closeModal();
    }
});

// QR Code functionality
let qrcode = null;

function generateQRCode(participantNumber, participantId) {
    const modal = document.getElementById('qrModal');
    const qrcodeDiv = document.getElementById('qrcode');
    
    // Clear previous QR code
    qrcodeDiv.innerHTML = '';
    
    // Create data for participant QR code
    const qrData = JSON.stringify({
        type: 'participant',
        id: participantId,
        number: participantNumber
    });
    
    // Generate new QR code
    qrcode = new QRCode(qrcodeDiv, {
        text: qrData,
        width: 200,
        height: 200,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });
    
    // Show modal
    modal.classList.add('show');
    
    // Prevent body scroll when modal is open
    document.body.style.overflow = 'hidden';
}

function downloadQRCode() {
    if (!qrcode) return;
    
    const canvas = document.querySelector("#qrcode canvas");
    if (!canvas) return;
    
    const link = document.createElement('a');
    link.href = canvas.toDataURL("image/png");
    link.download = `participant-qr-${Date.now()}.png`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Close QR modal
document.querySelector('.close-qr').addEventListener('click', function() {
    const modal = document.getElementById('qrModal');
    modal.classList.remove('show');
    document.body.style.overflow = '';
});

// Close modal when clicking outside
document.getElementById('qrModal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.remove('show');
        document.body.style.overflow = '';
    }
});

document.addEventListener('DOMContentLoaded', function() {
    initializeStudentTable();
    initializeDropdowns();
});

function initializeStudentTable() {
    const table = document.getElementById('studentTable');
    if (!table) return;

    const tbody = table.querySelector('tbody');
    const searchInput = document.getElementById('search');
    const courseFilter = document.getElementById('courseFilter');
    const courseFilterBtn = document.getElementById('courseFilterBtn');

    // Initialize sorting
    const headers = table.querySelectorAll('th[data-sort]');
    headers.forEach(header => {
        header.style.cursor = 'pointer';
        header.addEventListener('click', () => handleSort(header));
    });

    // Initialize course filter
    if (courseFilter) {
        courseFilter.addEventListener('change', function() {
            const selectedCourse = this.value;
            filterByCourse(selectedCourse);
            
            // Update button state
            if (courseFilterBtn) {
                if (selectedCourse !== 'all') {
                    courseFilterBtn.classList.add('active');
                } else {
                    courseFilterBtn.classList.remove('active');
                }
            }
        });
    }

    // Initialize search
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const searchTerm = searchInput.value.toLowerCase();
                filterTableBySearch(searchTerm);
            }, 300);
        });
    }

    function handleSort(header) {
        const sortBy = header.getAttribute('data-sort');
        const currentOrder = header.getAttribute('data-order') || 'asc';
        const newOrder = currentOrder === 'asc' ? 'desc' : 'asc';

        headers.forEach(h => {
            h.classList.remove('sorted-asc', 'sorted-desc');
            h.removeAttribute('data-order');
        });

        header.classList.add(`sorted-${newOrder}`);
        header.setAttribute('data-order', newOrder);

        sortTable(sortBy, newOrder);
    }

    function filterByCourse(selectedCourse) {
        const rows = tbody.querySelectorAll('tr');
        rows.forEach(row => {
            const rowCourse = row.cells[3].textContent.trim();
            row.style.display = (selectedCourse === 'all' || rowCourse === selectedCourse) ? '' : 'none';
        });
    }

    function sortTable(sortBy, order) {
        const rows = Array.from(tbody.querySelectorAll('tr'));

        rows.sort((a, b) => {
            let aVal, bVal;

            switch(sortBy) {
                case 'number':
                    aVal = parseInt(a.cells[0].textContent.replace(/^0+/, ''));
                    bVal = parseInt(b.cells[0].textContent.replace(/^0+/, ''));
                    break;
                case 'id':
                    aVal = a.cells[1].textContent;
                    bVal = b.cells[1].textContent;
                    break;
                case 'name':
                    aVal = a.cells[2].textContent;
                    bVal = b.cells[2].textContent;
                    break;
                case 'section':
                    aVal = a.cells[4].textContent;
                    bVal = b.cells[4].textContent;
                    break;
                case 'year':
                    aVal = parseInt(a.cells[5].textContent) || 0;
                    bVal = parseInt(b.cells[5].textContent) || 0;
                    break;
                case 'department':
                    aVal = a.cells[6].textContent;
                    bVal = b.cells[6].textContent;
                    break;
                default:
                    return 0;
            }

            if (typeof aVal === 'number' && typeof bVal === 'number') {
                return order === 'asc' ? aVal - bVal : bVal - aVal;
            }

            return order === 'asc' ? 
                String(aVal).localeCompare(String(bVal)) : 
                String(bVal).localeCompare(String(aVal));
        });

        rows.forEach(row => tbody.appendChild(row));
    }

    function filterTableBySearch(searchTerm) {
        const rows = tbody.querySelectorAll('tr');
        rows.forEach(row => {
            const text = Array.from(row.cells)
                .map(cell => cell.textContent.toLowerCase())
                .join(' ');
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    }
}

// Add these helper functions for the dropdown functionality
function editStudent(studentData) {
    if (typeof studentData === 'string') {
        try {
            studentData = JSON.parse(studentData);
        } catch (e) {
            console.error('Error parsing student data:', e);
            return;
        }
    }

    const modal = document.getElementById('participantModal');
    const form = document.getElementById('participantForm');
    const title = document.getElementById('modalTitle');

    if (!modal || !form || !title) {
        console.error('Required modal elements not found');
        return;
    }

    title.textContent = 'Edit Student';
    
    // Fill the form with student data
    document.getElementById('participant-number').value = studentData.number;
    document.getElementById('participant-id').value = studentData.ID;
    document.getElementById('participant-firstname').value = studentData.first_name;
    document.getElementById('participant-lastname').value = studentData.last_name;
    document.getElementById('participant-course').value = studentData.Course;
    document.getElementById('participant-section').value = studentData.Section?.slice(-1) || '';
    document.getElementById('participant-gender').value = studentData.Gender;
    document.getElementById('participant-age').value = studentData.Age;
    document.getElementById('participant-year').value = studentData.Year;
    document.getElementById('participant-dept').value = studentData.Dept;

    modal.style.display = 'block';
}

function deleteStudent(studentId, studentName) {
    Swal.fire({
        title: 'Are you sure?',
        text: `Do you want to delete ${studentName}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
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
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json().catch(() => {
                    throw new Error('Invalid JSON response from server');
                });
            })
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Failed to delete student');
                }
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: data.message,
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.reload();
                });
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'An error occurred while deleting the student'
                });
            });
        }
    });
}

function openAddStudentModal() {
    const modal = document.getElementById('participantModal');
    const form = document.getElementById('participantForm');
    const title = document.getElementById('modalTitle');

    title.textContent = 'Add New Student';
    form.reset();
    document.getElementById('participant-number').value = '';
    modal.style.display = 'block';
}

function closeParticipantModal() {
    const modal = document.getElementById('participantModal');
    modal.style.display = 'none';
    document.getElementById('participantForm').reset();
}

function downloadCSVTemplate(event) {
    event.preventDefault();
    
    // Create CSV content
    const headers = ['ID', 'First Name', 'Last Name', 'Course', 'Section', 'Gender', 'Age', 'Year', 'Department'];
    const csvContent = [
        headers.join(','),
        '23-00001,John,Doe,BSIT,A,Male,20,1,CCS',
        '23-00002,Jane,Smith,BSCS,B,Female,19,2,CCS'
    ].join('\n');
    
    // Create blob and download
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'student_template.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// File upload handling
document.getElementById('fileInput').addEventListener('change', function(e) {
    const fileName = document.getElementById('fileName');
    if (this.files.length > 0) {
        const file = this.files[0];
        if (file.type !== 'text/csv' && !file.name.endsWith('.csv')) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid File',
                text: 'Please select a valid CSV file.'
            });
            this.value = '';
            fileName.textContent = 'No file chosen';
            return;
        }
        fileName.textContent = file.name;
        handleFileUpload(file);
    } else {
        fileName.textContent = 'No file chosen';
    }
});

function handleFileUpload(file) {
    const formData = new FormData();
    formData.append('file', file);

    // Show loading state
    Swal.fire({
        title: 'Uploading...',
        text: 'Please wait while we process your file',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Send request
    fetch('../php/importData.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message,
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location.reload();
            });
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'An error occurred while processing the file'
        });
        document.getElementById('fileInput').value = '';
        document.getElementById('fileName').textContent = 'No file chosen';
    });
}

function initializeDropdowns() {
    // Initialize dropdown toggles
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
    // Close all dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.matches('.dropdown-toggle')) {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });

    // Toggle dropdown on button click
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation(); // Prevent document click from immediately closing
            
            // Close all other dropdowns
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                if (menu !== this.nextElementSibling) {
                    menu.classList.remove('show');
                }
            });
            
            // Toggle current dropdown
            const dropdownMenu = this.nextElementSibling;
            dropdownMenu.classList.toggle('show');
            
            // Position the dropdown
            if (dropdownMenu.classList.contains('show')) {
                const rect = this.getBoundingClientRect();
                const spaceBelow = window.innerHeight - rect.bottom;
                const spaceAbove = rect.top;
                
                // Reset previous positioning
                dropdownMenu.style.top = '';
                dropdownMenu.style.bottom = '';
                
                // If there's not enough space below, show above
                if (spaceBelow < 150 && spaceAbove > spaceBelow) {
                    dropdownMenu.style.bottom = '100%';
                    dropdownMenu.style.top = 'auto';
                } else {
                    dropdownMenu.style.top = '100%';
                    dropdownMenu.style.bottom = 'auto';
                }
            }
        });
    });
}