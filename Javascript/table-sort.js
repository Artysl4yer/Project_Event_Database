// Table sorting and filtering functionality
class TableManager {
    constructor(tableId, options = {}) {
        this.table = document.getElementById(tableId);
        if (!this.table) return;

        this.filterButtons = this.table.closest('.event-table-section')?.querySelectorAll('.filter-btn:not(.btn-import)');
        this.statusFilter = this.table.closest('.event-table-section')?.querySelector('#statusFilter');
        this.currentSort = { column: 'number', direction: 'desc' };
        this.currentFilter = 'all';
        
        // Column configurations for different tables
        this.columnConfig = {
            'eventTable': {
                number: 1,
                title: 2,
                code: 3,
                start: 4,
                end: 5,
                location: 6,
                description: 7,
                organization: 8,
                status: 9
            },
            'participantTable': {
                number: 1,
                id: 2,
                name: 3,
                course: 4,
                section: 5,
                gender: 6,
                age: 7,
                year: 8,
                department: 9
            },
            'guestTable': {
                name: 1,
                email: 2,
                gender: 3,
                age: 4,
                number: 5,
                id: 6
            },
            'archiveTable': {
                number: 1,
                title: 2,
                code: 3,
                location: 4,
                start: 5,
                end: 6,
                organization: 7,
                status: 8,
                reg_status: 9
            }
        }[tableId] || {};

        this.initialize();
    }

    initialize() {
        // Initialize sorting
        const headers = this.table.querySelectorAll('th[data-sort]');
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => this.handleSortClick(header));
        });

        // Initialize filtering
        if (this.filterButtons) {
            this.filterButtons.forEach(button => {
                button.addEventListener('click', () => {
                    this.filterButtons.forEach(btn => btn.classList.remove('active'));
                    button.classList.add('active');
                    this.currentFilter = button.dataset.filter;
                    
                    if (this.statusFilter) {
                        this.statusFilter.style.display = 
                            (this.currentFilter === 'status') ? 'block' : 'none';
                    }
                    
                    this.applyFilters();
                });
            });
        }

        // Initialize status filter
        if (this.statusFilter) {
            this.statusFilter.addEventListener('change', () => this.applyFilters());
        }
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
            th.classList.remove('sorted-asc', 'sorted-desc');
        });
        header.classList.add('sorted-' + this.currentSort.direction);
        
        this.applyFilters();
    }

    applyFilters() {
        const tbody = this.table.querySelector('tbody');
        if (!tbody) return;

        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        // Sort rows
        if (this.currentSort.column) {
            const columnIndex = this.columnConfig[this.currentSort.column];
            if (columnIndex) {
                rows.sort((a, b) => {
                    const aValue = a.querySelector(`td:nth-child(${columnIndex})`).textContent.trim();
                    const bValue = b.querySelector(`td:nth-child(${columnIndex})`).textContent.trim();
                    
                    // Handle numeric values
                    if (this.currentSort.column === 'number' || this.currentSort.column === 'age') {
                        const aNum = parseInt(aValue);
                        const bNum = parseInt(bValue);
                        if (!isNaN(aNum) && !isNaN(bNum)) {
                            return this.currentSort.direction === 'asc' ? aNum - bNum : bNum - aNum;
                        }
                    }
                    
                    // Handle date values
                    if (this.currentSort.column === 'start' || this.currentSort.column === 'end') {
                        const aDate = new Date(aValue);
                        const bDate = new Date(bValue);
                        if (!isNaN(aDate) && !isNaN(bDate)) {
                            return this.currentSort.direction === 'asc' ? 
                                aDate - bDate : bDate - aDate;
                        }
                    }
                    
                    // Default string comparison
                    return this.currentSort.direction === 'asc' ? 
                        aValue.localeCompare(bValue) : 
                        bValue.localeCompare(aValue);
                });
            }
        }
        
        // Apply filters
        rows.forEach(row => {
            let show = true;
            
            if (this.currentFilter === 'status' && this.statusFilter) {
                const status = row.querySelector(`td:nth-child(${this.columnConfig.status})`).textContent.trim().toLowerCase();
                const selectedStatus = this.statusFilter.value.toLowerCase();
                
                // Show all rows if "all" is selected
                if (selectedStatus === 'all') {
                    show = true;
                } else {
                    // For scheduled status, show events that haven't started yet
                    if (selectedStatus === 'scheduled') {
                        const startDate = new Date(row.querySelector(`td:nth-child(${this.columnConfig.start})`).textContent.trim());
                        show = startDate > new Date() && status === 'scheduled';
                    }
                    // For ongoing status, show events that are currently happening
                    else if (selectedStatus === 'ongoing') {
                        const startDate = new Date(row.querySelector(`td:nth-child(${this.columnConfig.start})`).textContent.trim());
                        const endDate = new Date(row.querySelector(`td:nth-child(${this.columnConfig.end})`).textContent.trim());
                        const now = new Date();
                        show = startDate <= now && endDate > now && status === 'ongoing';
                    }
                    // For completed status, show events that have ended
                    else if (selectedStatus === 'completed') {
                        const endDate = new Date(row.querySelector(`td:nth-child(${this.columnConfig.end})`).textContent.trim());
                        show = endDate < new Date() && status === 'completed';
                    }
                    // For other statuses (cancelled, archived), match exactly
                    else {
                        show = status === selectedStatus;
                    }
                }
            }
            
            row.style.display = show ? '' : 'none';
        });

        // Reorder rows in the table
        rows.forEach(row => tbody.appendChild(row));
    }
}

// Initialize tables when the document is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize event table
    const eventTable = document.getElementById('eventTable');
    if (eventTable) {
        new TableManager('eventTable');
    }

    // Initialize participant table
    const participantTable = document.getElementById('participantTable');
    if (participantTable) {
        new TableManager('participantTable');
    }

    // Initialize guest table
    const guestTable = document.getElementById('guestTable');
    if (guestTable) {
        new TableManager('guestTable');
    }

    // Initialize archive table
    const archiveTable = document.getElementById('archiveTable');
    if (archiveTable) {
        new TableManager('archiveTable');
    }
}); 