document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.querySelector('.search-container form');
    const searchInput = document.getElementById('search');
    const tableBody = document.querySelector('#participantTable tbody');
    let searchTimeout;

    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        performSearch();
    });

    searchInput.addEventListener('input', function() {
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        // Set new timeout to prevent too many requests
        searchTimeout = setTimeout(() => {
            performSearch();
        }, 300);
    });

    function performSearch() {
        const searchTerm = searchInput.value.trim();
        
        if (searchTerm === '') {
            // If search is empty, reload the page to show all records
            window.location.reload();
            return;
        }

        fetch(`../php/action_page.php?search=${encodeURIComponent(searchTerm)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateTable(data.data);
                }
            })
            .catch(error => {
                console.error('Search error:', error);
            });
    }

    function updateTable(participants) {
        // Clear existing table content
        tableBody.innerHTML = '';

        if (participants.length === 0) {
            // Show "No results found" message
            const noResultsRow = document.createElement('tr');
            noResultsRow.innerHTML = '<td colspan="10" class="no-results">No matching records found.</td>';
            tableBody.appendChild(noResultsRow);
            return;
        }

        // Add new rows
        participants.forEach(participant => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${participant.number}</td>
                <td>${participant.ID}</td>
                <td>${participant.name}</td>
                <td>${participant.Course}</td>
                <td>${participant.Section}</td>
                <td>${participant.Gender}</td>
                <td>${participant.Age}</td>
                <td>${participant.Year}</td>
                <td>${participant.Dept}</td>
                <td class="dropdown-wrapper">
                    <button class="dropdown-toggle">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <div class="dropdown-menu">
                        <button onclick="openParticipantModal(${participant.number})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <form method="POST" action="7_StudentTable.php" onsubmit="return confirm('Are you sure you want to delete this participant?');">
                            <input type="hidden" name="action" value="delete_participant">
                            <input type="hidden" name="delete_id" value="${participant.number}">
                            <button type="submit">
                                <i class="fas fa-trash-alt"></i> Delete
                            </button>
                        </form>
                        <button onclick="generateQRCode(${participant.number}, '${participant.ID}')">
                            <i class="fas fa-qrcode"></i> Generate QR
                        </button>
                    </div>
                </td>
            `;
            tableBody.appendChild(row);
        });

        // Reinitialize dropdown functionality
        initializeDropdowns();
    }

    function initializeDropdowns() {
        // Reinitialize dropdown toggles for the new rows
        document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                const menu = this.nextElementSibling;
                const isShowing = menu.classList.contains('show');
                
                // Close all other dropdowns
                document.querySelectorAll('.dropdown-menu.show').forEach(openMenu => {
                    if (openMenu !== menu) {
                        openMenu.classList.remove('show');
                    }
                });
                
                // Toggle current dropdown
                menu.classList.toggle('show', !isShowing);
                e.stopPropagation();
            });
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown-menu')) {
                document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        });
    }
}); 