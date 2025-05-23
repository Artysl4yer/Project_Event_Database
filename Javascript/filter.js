// Table filtering and sorting functionality
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const statusFilter = document.getElementById('statusFilter');
    const table = document.getElementById('eventTable');
    
    if (!table) return;

    // Filter button click handlers
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            const filter = this.dataset.filter;
            
            // Show/hide status filter dropdown
            if (statusFilter) {
                statusFilter.style.display = filter === 'status' ? 'block' : 'none';
            }
            
            // Apply filters
            applyFilters(filter);
        });
    });

    // Status filter change handler
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            applyFilters('status');
        });
    }

    // Table sorting
    const headers = table.querySelectorAll('th[data-sort]');
    headers.forEach(header => {
        header.addEventListener('click', function() {
            const sortBy = this.dataset.sort;
            sortTable(sortBy);
        });
    });
});

function applyFilters(filter) {
    const table = document.getElementById('eventTable');
    const rows = table.querySelectorAll('tbody tr');
    const statusFilter = document.getElementById('statusFilter');
    
    rows.forEach(row => {
        let show = true;
        
        switch(filter) {
            case 'title':
                // Sort by title
                sortTable('title');
                break;
            case 'attendees':
                // Sort by attendees
                sortTable('attendees');
                break;
            case 'status':
                // Filter by status
                const status = row.querySelector('td:nth-last-child(2)').textContent;
                const selectedStatus = statusFilter.value;
                show = selectedStatus === 'all' || status === selectedStatus;
                break;
            default:
                // Show all
                show = true;
        }
        
        row.style.display = show ? '' : 'none';
    });
}

function sortTable(sortBy) {
    const table = document.getElementById('eventTable');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        let aVal, bVal;
        
        switch(sortBy) {
            case 'title':
                aVal = a.querySelector('td:nth-child(2)').textContent;
                bVal = b.querySelector('td:nth-child(2)').textContent;
                break;
            case 'attendees':
                aVal = parseInt(a.querySelector('td:nth-child(3)').textContent) || 0;
                bVal = parseInt(b.querySelector('td:nth-child(3)').textContent) || 0;
                break;
            default:
                return 0;
        }
        
        return aVal.localeCompare ? aVal.localeCompare(bVal) : aVal - bVal;
    });
    
    // Reorder rows
    rows.forEach(row => tbody.appendChild(row));
}

function initializeDropdowns() {
    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const menu = this.nextElementSibling;
            document.querySelectorAll('.dropdown-menu').forEach(m => {
                if (m !== menu) m.classList.remove('show');
            });
            menu.classList.toggle('show');
        });
    });

    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.classList.remove('show');
        });
    });
}

function initializeEventCode() {
    const codeField = document.getElementById('codeField');
    if (codeField) {
        codeField.value = generateCode(12);
    }
}

function generateCode(length) {
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let result = '';
    for (let i = 0; i < length; i++) {
        result += characters.charAt(Math.floor(Math.random() * characters.length));
    }
    return result;
}
