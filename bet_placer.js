function showPreview(bet) {
    document.getElementById("bet-preview").style.display = "block";

    var odds;
    if (bet.type == "ML") {
        if (bet.choice == "Home") {
            var text = `<ul class="list"><li class="list-group-item" id="odds"><h4><b>${bet.homeTeam} ${bet.mlHomeOdds}</b></h4></li><li class="list-group-item">${bet.homeTeam} vs. ${bet.awayTeam}</li></ul>`;
            // odds = bet.mlHomeOdds;
        }
        else {
            text = `<ul class="list"><li class="list-group-item" id="odds"><h4><b>${bet.awayTeam} ${bet.mlAwayOdds}</b></h4></li><li class="list-group-item">${bet.homeTeam} vs. ${bet.awayTeam}</li></ul>`;
            // odds = bet.mlAwayOdds;
        }
    }
    else if (bet.type == "Spread") {
        if (bet.choice == "Home") {
            var text = `<ul class="list"><li class="list-group-item" id="odds"><h4><b>${bet.homeTeam} ${bet.homeSpread}</b></h4></li><li class="list-group-item">${bet.homeTeam} vs. ${bet.awayTeam}</li></ul>`;
            // odds = bet.homeSpread.split(' ')[1];
        }
        else {
            text = `<ul class="list"><li class="list-group-item" id="odds"><h4><b>${bet.awayTeam} ${bet.awaySpread}</b></h4></li><li class="list-group-item">${bet.homeTeam} vs. ${bet.awayTeam}</li></ul>`;
            // odds = bet.awaySpread.split(' ')[1];
        }
    }
    else if (bet.type == "OU") {
        if (bet.choice == "Over") {
            var text = `<ul class="list"><li class="list-group-item" id="odds"><h4><b>${bet.over}</b></h4></li><li class="list-group-item">${bet.homeTeam} vs. ${bet.awayTeam}</li></ul>`;
        }
        else {
            text = `<ul class="list"><li class="list-group-item" id="odds"><h4><b>${bet.under}</b></h4></li><li class="list-group-item">${bet.homeTeam} vs. ${bet.awayTeam}</li></ul>`;
        }
    }

    let form = `
    <form action="place_bet.php" method="POST">
        <div>
            <input type="hidden" name="homeTeam" value="${bet.homeTeam}"/>
            <input type="hidden" name="type" value="${bet.type}"/>
            <input type="hidden" name="choice" value="${bet.choice}"/>
            <label for="wager">Wager Amount ($)</label>
            <input oninput=payoutPreview(this) name="wager"/>
            <button type="submit">Submit</button>
        </div>
    </form>
    `;

    document.getElementById("bet-preview").innerHTML = `<div class="container bg-light" style="margin: 50px;"><div class="row"><div class="col-sm">${text}</div><div class="col-sm">${form}<div class="row" id="toWin" style="display: none">${odds}</div></div></div></div>`;
}

function hidePreview() {
    document.getElementById("bet-preview").style.display = "none";
}

function homeML(element) {
    bet = {
        type : "ML",
        choice : "Home",
        homeTeam : element.id.replaceAll('-', ' ').split('%')[0],
        awayTeam : element.id.replaceAll('-', ' ').split('%')[1],
        mlHomeOdds : element.innerText
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

function awayML(element) {
    bet = {
        type : "ML",
        choice : "Away",
        homeTeam : element.id.replaceAll('-', ' ').split('%')[0],
        awayTeam : element.id.replaceAll('-', ' ').split('%')[1],
        mlAwayOdds : element.innerText
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

function homeSpread(element) {
    bet = {
        type : "Spread",
        choice : "Home",
        homeTeam : element.id.replaceAll('-', ' ').split('%')[0],
        awayTeam : element.id.replaceAll('-', ' ').split('%')[1],
        homeSpread : element.innerText
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

function awaySpread(element) {
    bet = {
        type : "Spread",
        choice : "Away",
        homeTeam : element.id.replaceAll('-', ' ').split('%')[0],
        awayTeam : element.id.replaceAll('-', ' ').split('%')[1],
        awaySpread : element.innerText
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

function Over(element) {
    bet = {
        type : "OU",
        choice : "Over",
        homeTeam : element.id.replaceAll('-', ' ').split('%')[0],
        awayTeam : element.id.replaceAll('-', ' ').split('%')[1],
        over : element.innerText
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


function Under(element) {
    bet = {
        type : "OU",
        choice : "Under",
        homeTeam : element.id.replaceAll('-', ' ').split('%')[0],
        awayTeam : element.id.replaceAll('-', ' ').split('%')[1],
        under : element.innerText
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

function payoutPreview(element)
{
    document.getElementById("toWin").innerHTML = "";

    let wager = Number(element.value);
    let odds = document.getElementById("odds").innerText;
    odds = Number(odds.substring(odds.indexOf("(") + 1, odds.lastIndexOf(")")).replace("+", ""));

    let win;

    if (wager < 0) {
        win = "";
    }
    else {
        if (odds < 0) {
            win = Math.round((100 / (-1 * odds)) * wager);
        }
        else {
            win = Math.round((odds / 100) * wager);
        }
    }

    document.getElementById("toWin").innerHTML = `<b>To Win: $ ${win}</b>`;
    document.getElementById("toWin").style.display = "block";
}