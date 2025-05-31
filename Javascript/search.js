document.addEventListener('DOMContentLoaded', function() {
    initializeSearch();
});

function initializeSearch() {
    const searchInput = document.querySelector('.search-container input[type="text"]');
    const searchForm = document.querySelector('.search-container form');
    
    if (!searchInput || !searchForm) return;

    let searchTimeout;
    let previousSearch = '';

    // Handle input changes with debouncing
    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        const searchTerm = e.target.value.toLowerCase().trim();

        // Don't search if the term hasn't changed
        if (searchTerm === previousSearch) return;
        previousSearch = searchTerm;

        searchTimeout = setTimeout(() => {
            filterTableContent(searchTerm);
        }, 300);
    });

    // Prevent form submission and handle search
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const searchTerm = searchInput.value.toLowerCase().trim();
        filterTableContent(searchTerm);
    });
}

function filterTableContent(searchTerm) {
    const table = document.querySelector('table');
    if (!table) return;

    const tbody = table.querySelector('tbody');
    if (!tbody) return;

    const rows = tbody.getElementsByTagName('tr');
    let hasResults = false;

    // Show loading state
    document.body.style.cursor = 'progress';

    for (const row of rows) {
        const text = row.textContent.toLowerCase();
        const shouldShow = searchTerm === '' || text.includes(searchTerm);
        row.style.display = shouldShow ? '' : 'none';
        if (shouldShow) hasResults = true;
    }

    // Handle no results state
    const noResultsRow = tbody.querySelector('.no-results');
    if (!hasResults) {
        if (!noResultsRow) {
            const tr = document.createElement('tr');
            tr.className = 'no-results';
            const td = document.createElement('td');
            td.colSpan = table.rows[0].cells.length;
            td.textContent = 'No matching records found';
            td.style.textAlign = 'center';
            td.style.padding = '20px';
            tr.appendChild(td);
            tbody.appendChild(tr);
        } else {
            noResultsRow.style.display = '';
        }
    } else if (noResultsRow) {
        noResultsRow.style.display = 'none';
    }

    // Reset cursor
    document.body.style.cursor = 'default';

    // Update URL with search parameter without reloading
    const url = new URL(window.location.href);
    if (searchTerm) {
        url.searchParams.set('search', searchTerm);
    } else {
        url.searchParams.delete('search');
    }
    window.history.pushState({}, '', url);
}

// Handle browser back/forward
window.addEventListener('popstate', function() {
    const url = new URL(window.location.href);
    const searchTerm = url.searchParams.get('search') || '';
    const searchInput = document.querySelector('.search-container input[type="text"]');
    if (searchInput) {
        searchInput.value = searchTerm;
        filterTableContent(searchTerm.toLowerCase().trim());
    }
}); 