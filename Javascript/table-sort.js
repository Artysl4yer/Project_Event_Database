// Table sorting and filtering functionality
class TableManager {
    constructor(tableId, options = {}) {
        this.table = document.getElementById(tableId);
        this.filterButtons = document.querySelectorAll('.filter-btn');
        this.departmentFilter = document.getElementById('departmentFilter');
        this.currentSort = { column: null, direction: 'asc' };
        this.currentFilter = 'all';
        this.options = {
            filterColumn: options.filterColumn || 9, // Department column
            nameColumn: options.nameColumn || 3,    // Name column
            courseColumn: options.courseColumn || 4, // Course column
            ...options
        };

        this.initialize();
    }

    initialize() {
        if (!this.table) return;

        // Initialize filter buttons
        this.filterButtons.forEach(button => {
            button.addEventListener('click', () => this.handleFilterClick(button));
        });

        // Initialize department filter
        if (this.departmentFilter) {
            this.departmentFilter.addEventListener('change', () => this.filterTable());
        }

        // Initialize sorting headers
        this.table.querySelectorAll('th[data-sort]').forEach(header => {
            header.addEventListener('click', () => this.handleSortClick(header));
        });
    }

    handleFilterClick(button) {
        this.filterButtons.forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
        this.currentFilter = button.dataset.filter;
        
        if (this.currentFilter === 'department' && this.departmentFilter) {
            this.departmentFilter.style.display = 'inline-block';
        } else if (this.departmentFilter) {
            this.departmentFilter.style.display = 'none';
        }
        
        this.filterTable();
    }

    handleSortClick(header) {
        const column = header.dataset.sort;
        
        if (this.currentSort.column === column) {
            this.currentSort.direction = this.currentSort.direction === 'asc' ? 'desc' : 'asc';
        } else {
            this.currentSort.column = column;
            this.currentSort.direction = 'asc';
        }
        
        this.filterTable();
    }

    filterTable() {
        const tbody = this.table.querySelector('tbody');
        if (!tbody) return;

        const rows = Array.from(tbody.querySelectorAll('tr'));
        const departmentValue = this.departmentFilter ? this.departmentFilter.value : 'all';
        
        // Sort rows
        if (this.currentSort.column) {
            rows.sort((a, b) => {
                const aValue = a.querySelector(`td:nth-child(${this.getColumnIndex(this.currentSort.column)})`).textContent;
                const bValue = b.querySelector(`td:nth-child(${this.getColumnIndex(this.currentSort.column)})`).textContent;
                
                if (this.currentSort.direction === 'asc') {
                    return aValue.localeCompare(bValue);
                } else {
                    return bValue.localeCompare(aValue);
                }
            });
        }
        
        // Filter rows
        rows.forEach(row => {
            const department = row.querySelector(`td:nth-child(${this.options.filterColumn})`).textContent;
            const name = row.querySelector(`td:nth-child(${this.options.nameColumn})`).textContent;
            const course = row.querySelector(`td:nth-child(${this.options.courseColumn})`).textContent;
            
            let show = true;
            
            if (this.currentFilter === 'department' && departmentValue !== 'all') {
                show = department === departmentValue;
            } else if (this.currentFilter === 'name' || this.currentFilter === 'course') {
                // Name and course sorting are handled by the sort function
                show = true;
            }
            
            row.style.display = show ? '' : 'none';
        });

        // Reorder rows in the table
        rows.forEach(row => tbody.appendChild(row));
    }

    getColumnIndex(column) {
        const columns = {
            'number': 1,
            'id': 2,
            'name': 3,
            'course': 4,
            'section': 5,
            'gender': 6,
            'age': 7,
            'year': 8,
            'department': 9
        };
        return columns[column] || 1;
    }
}

// Initialize table manager when the document is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize participant table
    const participantTable = new TableManager('participantTable', {
        filterColumn: 9,  // Department column
        nameColumn: 3,    // Name column
        courseColumn: 4   // Course column
    });
}); 