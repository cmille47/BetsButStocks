function validateInfo(){

    let name = String(document.getElementById("NAME").value);
    let email = String(document.getElementById("EMAIL").value);
    let email2 = String(document.getElementById("EMAIL2").value);
    let password = String(document.getElementById("PASSWORD").value);
    let password2 = String(document.getElementById("PASSWORD2").value);

    const invalidEmailChars = /[ `!#$%^&*()_+\-=\[\]{};':"\\|,<>\/?~]/;

    // Validate inputs for name
    if (name.length > 20 || name.length < 1) document.getElementById("verification").innerText = "Enter a name that is between 1 and 20 characters"
    // Validate inputs for email
    else if (email != email2) document.getElementById("verification").innerText = "Make sure your email is the same in both entries";
    else if (invalidEmailChars.test(email) || email.length < 1) document.getElementById("verification").innerText = "Enter a valid email";
    // Validate inputs for password
    else if (password != password2) document.getElementById("verification").innerText = "Make sure your password is the same in both entries";
    else if (password.length > 50 || password.length < 1) document.getElementById("verification").innerText = "Enter a password that is betwee 1 and 50 characters";
    // 
    else document.getElementById("verification").innerText = "Let's get started";

}