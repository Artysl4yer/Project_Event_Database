function showPass() {

    var pass = document.getElementById("password");
    var confirmPass = document.getElementById("reg-confirm-password");
    var regpass = document.getElementById("reg-password");

    if (pass && confirmPass) {
        if (pass.type === "password") {
            pass.type = "text";
            confirmPass.type = "text";
            regpass.type = "text";
        } else {
            pass.type = "password";
            confirmPass.type = "password";
            regpass.type = "password";
        }
    }
}
