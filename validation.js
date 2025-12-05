// Client-side validation 

document.addEventListener("DOMContentLoaded", function() {
    const forms = document.querySelectorAll("form");
    forms.forEach(form => {
        form.addEventListener("submit", function(e) {
            let isValid = true;

            form.querySelectorAll("imput[required], textarea[required]").forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add("error");
                    isValid = false;
                } else {
                    input.classList.remove("error");
                }
            });

            const password = form.querySelector("input[name='password']");
            if (password && password.value.length < 8) {
                password.style.borderColor = "red";
                alert("Password must be at least 8 characters long");
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                alert("Please fill in all required fields and ensure the password is at least 8 characters long");
            }
        });
    });
});
