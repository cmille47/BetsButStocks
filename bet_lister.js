function showPreview(bet) {
    document.getElementById("bet-preview").style.display = "block";

    var text = `<ul class="list"><li class="list-group-item"><h4><b>List Your Bet</b></h4></ul>`;
    
    let form = `
    <form action="list_bet.php" method="POST">
        <div>
            <input type="hidden" name="contract_id" value="${bet.contract_id}"/>
            <label for="list_price">List Price ($)</label>
            <input name="list_price"/>
            <button type="submit">Submit</button>
        </div>
    </form>
    `;

    document.getElementById("bet-preview").innerHTML = `<div class="container bg-light" style="margin: 50px;"><div class="row"><div class="col-sm">${text}</div><div class="col-sm">${form}</div></div></div>`;
}

function hidePreview() {
    document.getElementById("bet-preview").style.display = "none";
}

function showBet (element) {
    bet = {
        contract_id : element.id
    };
    if (element.style.background == "cornflowerblue") {
        hidePreview();
        element.style.background = "none";
    }
    else {
        let allButtons = document.getElementsByClassName("btn");
        for (let i = 0; i < allButtons.length; i++) {
            allButtons[i].style.background = "none";
        }
        element.style.background = "cornflowerblue";
        showPreview(bet);
    }
}

