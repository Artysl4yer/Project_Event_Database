function generateCode(length = 12) {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let code = '';
    for (let i = 0; i < length; i++) {
      code += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return code;
  }
  
  function populateCodeField() {
    const codeField = document.getElementById('codeField');
    if (codeField) {
      const newCode = generateCode(12);
      codeField.value = newCode;
      console.log('Generated code:', newCode); // Debug log
    }
  }
  
  // Generate code on page load
  document.addEventListener('DOMContentLoaded', () => {
    populateCodeField();
  });