document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('.filter').addEventListener('click', function() {
        var dropdownContent = document.getElementById('filter-option');
        dropdownContent.classList.toggle('show');
    });
});
