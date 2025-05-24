// Utility functions for code generation
const CodeGenerator = {
    // Generate event code (6 characters)
    generateEventCode: function() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let code = '';
        for (let i = 0; i < 6; i++) {
            code += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return code;
    },

    // Generate participant code (12 characters with timestamp)
    generateParticipantCode: function(length = 12) {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        const timestamp = Date.now().toString().slice(-4);
        let code = '';
        for (let i = 0; i < length - 4; i++) {
            code += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return code + timestamp;
    },

    // Generate random code with custom length
    generateRandomCode: function(length = 12) {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let code = '';
        for (let i = 0; i < length; i++) {
            code += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return code;
    }
};

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CodeGenerator;
} else {
    window.CodeGenerator = CodeGenerator;
} 