const sqft = document.getElementById("sqft");
const sqftValue = document.getElementById("sqftValue");

sqft.oninput = function () {
    sqftValue.innerHTML = this.value + " sqft";
};

function calculateCost() {

    let area = document.getElementById("sqft").value;
    let rate = document.getElementById("quality").value;

    let total = area * rate;

    total = total.toLocaleString('en-IN');

    document.getElementById("totalCost").innerHTML =
        "₹" + total;
}

const faqQuestions =
    document.querySelectorAll(".faq-question");

faqQuestions.forEach(question => {

    question.addEventListener("click", () => {

        const answer =
            question.nextElementSibling;

        answer.style.display =
            answer.style.display === "block"
            ? "none"
            : "block";
    });

});

function openLogin(){

    document.getElementById("loginPopup")
        .style.display = "flex";
}

function closeLogin(){

    document.getElementById("loginPopup")
        .style.display = "none";
}

/* CLOSE ON OUTSIDE CLICK */

window.onclick = function(event){

    let popup =
        document.getElementById("loginPopup");

    if(event.target == popup){

        popup.style.display = "none";
    }
}