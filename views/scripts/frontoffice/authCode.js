document.addEventListener("DOMContentLoaded", function () {

    const button = document.querySelector(".popupA2f button");
    
    let num1 = document.getElementById("num1").value;
    let num2 = document.getElementById("num2").value;
    let num3 = document.getElementById("num3").value;
    let num4 = document.getElementById("num4").value;
    let num5 = document.getElementById("num5").value;
    let num6 = document.getElementById("num6").value;

    let number = num1 + num2 + num3 + num4 + num5 + num6;

    button.addEventListener("click", function (e) {
        e.preventDefault();

        fetch("verify_otp.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ otp: number })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = "accueilConnecte.php";
            } else {
                alert("Code incorrect");
            }
        });
    });

});