// Table sorting and filtering functionality
class TableManager {
    constructor(options = {}) {
        this.tableId = options.tableId || 'eventTable';
        this.filterButtonsSelector = options.filterButtonsSelector || '.filter-btn';
        this.statusFilterId = options.statusFilterId || 'statusFilter';
        this.columnConfig = options.columnConfig || {
            'number': 1,
            'title': 2,
            'code': 3,
            'start': 4,
            'end': 5,
            'location': 6,
            'description': 7,
            'organization': 8,
            'status': 9
        };
        
        this.currentSort = { column: null, direction: 'asc' };
        this.currentFilter = 'all';
        
        this.initialize();
    }

    initialize() {
        const table = document.getElementById(this.tableId);
        if (!table) return;

        const filterButtons = document.querySelectorAll(this.filterButtonsSelector);
        const statusFilter = document.getElementById(this.statusFilterId);

        // Initialize filter buttons
        filterButtons.forEach(button => {
            button.addEventListener('click', () => this.handleFilterClick(button, filterButtons, statusFilter));
        });

        // Initialize status filter
        if (statusFilter) {
            statusFilter.addEventListener('change', () => this.applyFilters(table, statusFilter));
        }

        // Initialize sorting headers
        table.querySelectorAll('th[data-sort]').forEach(header => {
            header.addEventListener('click', () => this.handleSortClick(header, table));
        });
    }

    handleFilterClick(button, allButtons, statusFilter) {
        // Update active button
        allButtons.forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
        
        // Update current filter
        this.currentFilter = button.dataset.filter;
        
        // Handle status filter visibility
        if (statusFilter) {
            if (this.currentFilter === 'status') {
                statusFilter.style.display = 'inline-block';
            } else {
                statusFilter.style.display = 'none';
                this.applyFilters(document.getElementById(this.tableId), statusFilter);
            }
        }
    }

    handleSortClick(header, table) {
        const column = header.dataset.sort;
        
        // Update sort direction
        if (this.currentSort.column === column) {
            this.currentSort.direction = this.currentSort.direction === 'asc' ? 'desc' : 'asc';
        } else {
            this.currentSort.column = column;
            this.currentSort.direction = 'asc';
        }

        // Update sort indicators
        table.querySelectorAll('th[data-sort]').forEach(th => {
            th.classList.remove('asc', 'desc');
        });
        header.classList.add(this.currentSort.direction);

        this.applyFilters(table, document.getElementById(this.statusFilterId));
    }

    applyFilters(table, statusFilter) {
        const tbody = table.querySelector('tbody');
        if (!tbody) return;

        const rows = Array.from(tbody.querySelectorAll('tr'));
        const statusValue = statusFilter ? statusFilter.value : 'all';
        
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
            const status = row.querySelector(`td:nth-child(${this.columnConfig.status})`).textContent;
            const title = row.querySelector(`td:nth-child(${this.columnConfig.title})`).textContent;
            
            let show = true;
            
            if (this.currentFilter === 'status' && statusValue !== 'all') {
                show = status === statusValue;
            } else if (this.currentFilter === 'title' || this.currentFilter === 'attendees') {
                // Title and attendees sorting are handled by the sort function
                show = true;
            }
            
            row.style.display = show ? '' : 'none';
        });

        // Reorder rows in the table
        rows.forEach(row => tbody.appendChild(row));
    }

    getColumnIndex(column) {
        return this.columnConfig[column] || 1;
    }
}

// Initialize table manager when the document is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize event table
    window.eventTable = new TableManager({
        tableId: 'eventTable',
        filterButtonsSelector: '.filter-btn',
        statusFilterId: 'statusFilter',
        columnConfig: {
            'number': 1,
            'title': 2,
            'code': 3,
            'start': 4,
            'end': 5,
            'location': 6,
            'description': 7,
            'organization': 8,
            'status': 9
        }
    });
}); 