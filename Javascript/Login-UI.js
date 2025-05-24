function showPass() {
    const loginPass = document.getElementById("login-password");
    const regPass = document.getElementById("reg-password");

    if (loginPass) {
        loginPass.type = loginPass.type === "password" ? "text" : "password";
    }

    if (regPass) {
        regPass.type = regPass.type === "password" ? "text" : "password";
    }
}

function formatInput(input) {
    let value = input.value;

    //numbers only or numbers with hyphen
    if (!value.includes("@")) {
        // Remove non-digit characters
        let digits = value.replace(/\D/g, "").slice(0, 8);

        if (digits.length > 2) {
            input.value = digits.slice(0, 2) + "-" + digits.slice(2);
        } else {
            input.value = digits;
        }

        //  8 chars
        if (input.value.length > 8) {
            input.value = input.value.slice(0, 8);
        }
    }

}



function validateIdentifier(form) {
    let identifier;

    // Determine which field we're validating based on the form
    if (form.id === "login-form") {
        identifier = form.querySelector("#identifier").value.trim();
    } else if (form.id === "register-form") {
        const studentId = form.querySelector("#student_id").value.trim();
        const email = form.querySelector("#email").value.trim();

        // Validate student ID
        const idRegex = /^\d{2}-\d{5,6}$/;
        if (!idRegex.test(studentId)) {
            alert("Student ID must be in the format: 20-12345 or 20-123456");
            return false;
        }

        // Validate email
        const validDomains = ["plpasig.edu.ph", "gmail.com"];
        const domain = email.split('@')[1];
        if (!validDomains.includes(domain)) {
            alert("Email must be from plpasig.edu.ph or gmail.com");
            return false;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert("Please enter a valid email address.");
            return false;
        }

        return true;
    }

    if (identifier.includes('@')) {
        const validDomains = ["plpasig.edu.ph", "gmail.com"];
        const domain = identifier.split('@')[1];
        if (!validDomains.includes(domain)) {
            alert("Email must be from plpasig.edu.ph or gmail.com");
            return false;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(identifier)) {
            alert("Please enter a valid email address.");
            return false;
        }

    } else {
        const idRegex = /^\d{2}-\d{5,6}$/;
        if (!idRegex.test(identifier)) {
            alert("Student ID must be in the format: 20-12345 or 20-123456");
            return false;
        }
    }

    return true;
}



function showForm(formId) {
    document.querySelectorAll(".form-box").forEach(form => form.classList.remove("active"));
    document.getElementById(formId).classList.add("active");
}



