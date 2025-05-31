// Table filtering and sorting functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize both tables
    initializeTable('eventTable');
    initializeTable('participantTable');
});

function initializeTable(tableId) {
    const table = document.getElementById(tableId);
    if (!table) return;

    const filterButtons = table.closest('.event-table-section')?.querySelectorAll('.filter-btn');
    const statusFilter = table.closest('.event-table-section')?.querySelector('#statusFilter, #departmentFilter');

    // Filter button click handlers
    if (filterButtons) {
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
                // Remove active class from all buttons in this section
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            const filter = this.dataset.filter;
            
                // Show/hide filter dropdown
            if (statusFilter) {
                    statusFilter.style.display = (filter === 'status' || filter === 'department') ? 'block' : 'none';
            }
            
            // Apply filters
                applyFilters(filter, tableId);
            });
        });
    }

    // Filter dropdown change handler
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            const filter = this.id === 'statusFilter' ? 'status' : 'department';
            applyFilters(filter, tableId);
        });
    }

    // Table sorting
    const headers = table.querySelectorAll('th[data-sort]');
    headers.forEach(header => {
        header.addEventListener('click', function() {
            const sortBy = this.dataset.sort;
            const currentOrder = this.dataset.order || 'asc';
            const newOrder = currentOrder === 'asc' ? 'desc' : 'asc';
            
            // Update order indicator
            this.dataset.order = newOrder;
            
            // Remove order from other headers
            headers.forEach(h => {
                if (h !== this) h.dataset.order = '';
            });
            
            sortTable(sortBy, tableId, newOrder);
        });
    });
}

function applyFilters(filter, tableId) {
    const table = document.getElementById(tableId);
    const rows = table.querySelectorAll('tbody tr');
    const statusFilter = table.closest('.event-table-section')?.querySelector('#statusFilter, #departmentFilter');
    
    rows.forEach(row => {
        let show = true;
        
        switch(filter) {
            case 'title':
            case 'name':
                sortTable('title', tableId, 'asc');
                break;
            case 'date':
            case 'start':
                sortTable('start', tableId, 'desc');
                break;
            case 'course':
                sortTable('course', tableId, 'asc');
                break;
            case 'status':
                const status = row.querySelector('td:nth-child(9)').textContent.trim();
                const selectedStatus = statusFilter.value;
                show = selectedStatus === 'all' || status === selectedStatus;
                break;
            case 'department':
                const dept = row.querySelector('td:nth-child(9)').textContent.trim();
                const selectedDept = statusFilter.value;
                show = selectedDept === 'all' || dept === selectedDept;
                break;
            case 'all':
                show = true;
                break;
            default:
                show = true;
        }
        
        row.style.display = show ? '' : 'none';
    });
}

function sortTable(sortBy, tableId, order = 'asc') {
    const table = document.getElementById(tableId);
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        let aVal, bVal;
        
        switch(sortBy) {
            // Event table sorting
            case 'number':
                aVal = parseInt(a.querySelector('td:nth-child(1)').textContent.trim());
                bVal = parseInt(b.querySelector('td:nth-child(1)').textContent.trim());
                return order === 'asc' ? aVal - bVal : bVal - aVal;
            case 'title':
                aVal = a.querySelector('td:nth-child(2)').textContent.trim();
                bVal = b.querySelector('td:nth-child(2)').textContent.trim();
                break;
            case 'code':
                aVal = a.querySelector('td:nth-child(3)').textContent.trim();
                bVal = b.querySelector('td:nth-child(3)').textContent.trim();
                break;
            case 'start':
                aVal = new Date(a.querySelector('td:nth-child(4)').textContent.trim());
                bVal = new Date(b.querySelector('td:nth-child(4)').textContent.trim());
                return order === 'asc' ? aVal - bVal : bVal - aVal;
            case 'end':
                aVal = new Date(a.querySelector('td:nth-child(5)').textContent.trim());
                bVal = new Date(b.querySelector('td:nth-child(5)').textContent.trim());
                return order === 'asc' ? aVal - bVal : bVal - aVal;
            case 'location':
                aVal = a.querySelector('td:nth-child(6)').textContent.trim();
                bVal = b.querySelector('td:nth-child(6)').textContent.trim();
                break;
            case 'description':
                aVal = a.querySelector('td:nth-child(7)').textContent.trim();
                bVal = b.querySelector('td:nth-child(7)').textContent.trim();
                break;
            case 'organization':
                aVal = a.querySelector('td:nth-child(8)').textContent.trim();
                bVal = b.querySelector('td:nth-child(8)').textContent.trim();
                break;
            case 'status':
                aVal = a.querySelector('td:nth-child(9)').textContent.trim();
                bVal = b.querySelector('td:nth-child(9)').textContent.trim();
                break;
            // Participant table sorting
            case 'id':
                aVal = a.querySelector('td:nth-child(2)').textContent.trim();
                bVal = b.querySelector('td:nth-child(2)').textContent.trim();
                break;
            case 'name':
                aVal = a.querySelector('td:nth-child(3)').textContent.trim();
                bVal = b.querySelector('td:nth-child(3)').textContent.trim();
                break;
            case 'course':
                aVal = a.querySelector('td:nth-child(4)').textContent.trim();
                bVal = b.querySelector('td:nth-child(4)').textContent.trim();
                break;
            case 'section':
                aVal = a.querySelector('td:nth-child(5)').textContent.trim();
                bVal = b.querySelector('td:nth-child(5)').textContent.trim();
                break;
            case 'gender':
                aVal = a.querySelector('td:nth-child(6)').textContent.trim();
                bVal = b.querySelector('td:nth-child(6)').textContent.trim();
                break;
            case 'age':
                aVal = parseInt(a.querySelector('td:nth-child(7)').textContent.trim());
                bVal = parseInt(b.querySelector('td:nth-child(7)').textContent.trim());
                return order === 'asc' ? aVal - bVal : bVal - aVal;
            case 'year':
                aVal = a.querySelector('td:nth-child(8)').textContent.trim();
                bVal = b.querySelector('td:nth-child(8)').textContent.trim();
                break;
            case 'department':
                aVal = a.querySelector('td:nth-child(9)').textContent.trim();
                bVal = b.querySelector('td:nth-child(9)').textContent.trim();
                break;
            default:
                return 0;
        }
        
        // For string comparisons
        if (typeof aVal === 'string' && typeof bVal === 'string') {
            return order === 'asc' ? 
                aVal.localeCompare(bVal) : 
                bVal.localeCompare(aVal);
        }
        
        // For date and number comparisons
        return order === 'asc' ? aVal - bVal : bVal - aVal;
    });
    
    // Reorder rows
    rows.forEach(row => tbody.appendChild(row));
    
    // Update header indicators
    const headers = table.querySelectorAll('th[data-sort]');
    headers.forEach(header => {
        if (header.dataset.sort === sortBy) {
            header.classList.add('sorted-' + order);
        } else {
            header.classList.remove('sorted-asc', 'sorted-desc');
        }
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
