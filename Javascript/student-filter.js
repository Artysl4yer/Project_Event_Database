document.addEventListener('DOMContentLoaded', function() {
    const courseFilterBtn = document.getElementById('courseFilterBtn');
    const courseSelect = document.getElementById('courseFilter');
    
    if (!courseFilterBtn || !courseSelect) return;

    // Close select box when clicking outside
    document.addEventListener('click', function(e) {
        if (!courseFilterBtn.contains(e.target) && !courseSelect.contains(e.target)) {
            courseSelect.classList.remove('show');
            courseFilterBtn.classList.remove('active');
        }
    });

    // Toggle select box when clicking the filter button
    courseFilterBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        courseSelect.classList.toggle('show');
        this.classList.toggle('active');
    });

    // Handle course selection
    courseSelect.addEventListener('change', function() {
        const selectedCourse = this.value;
        const buttonText = selectedCourse === 'all' 
            ? 'Filter by Course' 
            : `Filtered: ${selectedCourse}`;
        
        courseFilterBtn.innerHTML = `
            <i class="fas fa-filter"></i> ${buttonText}
        `;
        
        if (selectedCourse !== 'all') {
            courseFilterBtn.classList.add('active');
        } else {
            courseFilterBtn.classList.remove('active');
        }
        
        this.classList.remove('show');
    });
}); 