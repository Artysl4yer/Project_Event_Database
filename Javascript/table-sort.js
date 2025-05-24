// Table sorting and filtering functionality
class TableManager {
    constructor(tableId, options = {}) {
        this.table = document.getElementById(tableId);
        this.filterButtons = document.querySelectorAll('.filter-btn:not(.btn-import)');
        this.departmentFilter = document.getElementById('departmentFilter');
        this.statusFilter = document.getElementById('statusFilter');
        this.currentSort = { column: 'number', direction: 'desc' };
        this.currentFilter = 'all';
        this.options = {
            filterColumn: options.filterColumn || 9,
            nameColumn: options.nameColumn || 3,
            courseColumn: options.courseColumn || 4,
            ...options
        };

        this.initialize();
    }

    initialize() {
        if (!this.table) return;

        // Initialize filter buttons
        this.filterButtons.forEach(button => {
            if (!button.classList.contains('btn-import')) {
                button.addEventListener('click', () => this.handleFilterClick(button));
            }
        });

        // Initialize filters
        if (this.departmentFilter) {
            this.departmentFilter.addEventListener('change', () => this.filterTable());
        }
        if (this.statusFilter) {
            this.statusFilter.addEventListener('change', () => this.filterTable());
        }

        // Initialize sorting headers
        this.table.querySelectorAll('th[data-sort]').forEach(header => {
            header.addEventListener('click', () => this.handleSortClick(header));
        });

        // Set initial active state based on URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const currentFilter = urlParams.get('filter') || 'all';
        const currentSort = urlParams.get('sort') || 'number';
        const currentDirection = urlParams.get('direction') || 'desc';
        const currentDepartment = urlParams.get('department') || 'all';
        const currentStatus = urlParams.get('status') || 'all';
        
        this.currentSort = { column: currentSort, direction: currentDirection };
        this.currentFilter = currentFilter;

        // Update filter buttons
        this.filterButtons.forEach(button => {
            if (button.dataset.filter === currentFilter) {
                button.classList.add('active');
                // If the button has a data-sort attribute, use it for initial sort
                if (button.dataset.sort) {
                    this.currentSort.column = button.dataset.sort;
                }
            } else {
                button.classList.remove('active');
            }
        });

        // Show appropriate filter dropdown
        if (this.currentFilter === 'department' && this.departmentFilter) {
            this.departmentFilter.style.display = 'inline-block';
            if (currentDepartment !== 'all') {
                this.departmentFilter.value = currentDepartment;
            }
        } else if (this.currentFilter === 'status' && this.statusFilter) {
            this.statusFilter.style.display = 'inline-block';
            if (currentStatus !== 'all') {
                this.statusFilter.value = currentStatus;
            }
        }

        // Update sort indicators
        this.table.querySelectorAll('th[data-sort]').forEach(th => {
            if (th.dataset.sort === currentSort) {
                th.classList.add(currentDirection);
            } else {
                th.classList.remove('asc', 'desc');
            }
        });
    }

    handleFilterClick(button) {
        // Don't process if it's the Add button
        if (button.classList.contains('btn-import')) {
            return;
        }

        this.filterButtons.forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
        this.currentFilter = button.dataset.filter;
        
        // Handle filter visibility
        if (this.currentFilter === 'department' && this.departmentFilter) {
            this.departmentFilter.style.display = 'inline-block';
            if (this.statusFilter) this.statusFilter.style.display = 'none';
            return;
        } else if (this.currentFilter === 'status' && this.statusFilter) {
            this.statusFilter.style.display = 'inline-block';
            if (this.departmentFilter) this.departmentFilter.style.display = 'none';
            return;
        } else {
            if (this.departmentFilter) this.departmentFilter.style.display = 'none';
            if (this.statusFilter) this.statusFilter.style.display = 'none';
        }
        
        // If the button has a data-sort attribute, use it for sorting
        if (button.dataset.sort) {
            this.currentSort.column = button.dataset.sort;
            this.currentSort.direction = 'asc'; // Reset to ascending when changing sort column
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

        // Update sort indicators
        this.table.querySelectorAll('th[data-sort]').forEach(th => {
            th.classList.remove('asc', 'desc');
        });
        header.classList.add(this.currentSort.direction);
        
        this.filterTable();
    }

    filterTable() {
        // Build URL with current sort and filter parameters
        const params = new URLSearchParams({
            sort: this.currentSort.column,
            direction: this.currentSort.direction,
            filter: this.currentFilter
        });

        // Add appropriate filter value
        if (this.currentFilter === 'department' && this.departmentFilter) {
            params.append('department', this.departmentFilter.value);
        } else if (this.currentFilter === 'status' && this.statusFilter) {
            params.append('status', this.statusFilter.value);
        }

        // Reload the page with new parameters
        window.location.href = `${window.location.pathname}?${params.toString()}`;
    }
}

// Initialize table manager when the document is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize participant table if it exists
    const participantTable = document.getElementById('participantTable');
    if (participantTable) {
        new TableManager('participantTable', {
            filterColumn: 9,  // Department column
            nameColumn: 3,    // Name column
            courseColumn: 4   // Course column
        });
    }

    // Initialize event table if it exists
    const eventTable = document.getElementById('eventTable');
    if (eventTable) {
        new TableManager('eventTable', {
            filterColumn: 8,  // Status column
            nameColumn: 1,    // Title column
            courseColumn: 7   // Organization column
        });
    }
}); 