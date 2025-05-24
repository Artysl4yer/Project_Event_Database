function studentid(inputId) {
    const input = document.getElementById(inputId);
    input.addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^0-9]/g, ''); 
        if (value.length > 2) {
            value = value.slice(0, 2) + '-' + value.slice(2);
        }
        e.target.value = value;
    });
}