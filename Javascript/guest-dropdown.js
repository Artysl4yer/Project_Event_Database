document.addEventListener('DOMContentLoaded', function() {
    let activeDropdown = null;

    // Function to close active dropdown
    function closeActiveDropdown() {
        if (activeDropdown) {
            activeDropdown.classList.remove('show');
            activeDropdown = null;
        }
    }

    // Function to toggle dropdown
    function toggleDropdown(dropdownMenu) {
        if (activeDropdown === dropdownMenu) {
            closeActiveDropdown();
        } else {
            closeActiveDropdown();
            dropdownMenu.classList.add('show');
            activeDropdown = dropdownMenu;
        }
    }

    // Handle dropdown toggle clicks
    document.addEventListener('click', function(e) {
        const toggleButton = e.target.closest('.dropdown-toggle');
        if (toggleButton) {
            e.preventDefault();
            e.stopPropagation();
            const dropdownMenu = toggleButton.nextElementSibling;
            if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
                toggleDropdown(dropdownMenu);
            }
            return;
        }

        // Handle clicks on dropdown menu buttons
        const menuButton = e.target.closest('.dropdown-menu button');
        if (menuButton) {
            e.stopPropagation();
            if (menuButton.classList.contains('delete-btn')) {
                closeActiveDropdown();
            }
            return;
        }

        // Close dropdown when clicking outside
        if (!e.target.closest('.dropdown-menu')) {
            closeActiveDropdown();
        }
    });

    // Prevent dropdown from closing when clicking inside menu
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
        menu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
}); 