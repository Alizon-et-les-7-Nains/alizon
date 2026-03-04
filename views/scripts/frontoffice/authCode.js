let number;

const button = document.querySelector(".popupA2f button");
button.addEventListener("click", function (e) {
    e.preventDefault();

    let num1 = document.getElementById("num1");
    let num2 = document.getElementById("num2");
    let num3 = document.getElementById("num3");
    let num4 = document.getElementById("num4");
    let num5 = document.getElementById("num5");
    let num6 = document.getElementById("num6");

    number = num1.value + num2.value + num3.value + num4.value + num5.value + num6.value;

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
            window.location.href = "../../views/frontoffice/verify_otp.php";
        } else {
            alert("Code incorrect");
        }
    })
    .catch(err => {
        console.error("Erreur fetch:", err);
        alert("Erreur serveur, vérifie la console.");
    });
});
