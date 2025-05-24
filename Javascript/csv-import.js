document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('fileInput');
    const fileName = document.getElementById('fileName');
    const importSubmit = document.getElementById('importSubmit');
    const importForm = document.getElementById('csvImportForm');
    const importStatus = document.getElementById('importStatus');

    if (!fileInput || !fileName || !importSubmit || !importForm || !importStatus) {
        return;
    }

    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            const file = this.files[0];
            if (file.type !== 'text/csv' && !file.name.endsWith('.csv')) {
                importStatus.textContent = 'Please select a valid CSV file.';
                importStatus.style.display = 'block';
                importStatus.className = 'import-status error';
                importSubmit.disabled = true;
                fileName.textContent = 'No file chosen';
                this.value = '';
                return;
            }
            fileName.textContent = file.name;
            importSubmit.disabled = false;
            importStatus.style.display = 'none';
        } else {
            fileName.textContent = 'No file chosen';
            importSubmit.disabled = true;
        }
    });

    importForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        importSubmit.disabled = true;
        importStatus.style.display = 'block';
        importStatus.textContent = 'Importing...';
        importStatus.className = 'import-status loading';

        fetch('../php/importData.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            importStatus.textContent = data.message;
            importStatus.className = 'import-status ' + (data.success ? 'success' : 'error');
            
            if (data.success) {
                setTimeout(() => window.location.reload(), 2000);
            } else {
                importSubmit.disabled = false;
            }
        })
        .catch(() => {
            importStatus.textContent = 'An error occurred during import. Please try again.';
            importStatus.className = 'import-status error';
            importSubmit.disabled = false;
        });
    });
}); 