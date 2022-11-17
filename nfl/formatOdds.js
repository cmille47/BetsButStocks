const fs = require('fs');

const sport = 'nfl';
const rfilename = `/var/www/html/cse30246/betsbutstocks/${sport}/data/${sport}_odds.txt`;
const wfilename = `/var/www/html/cse30246/betsbutstocks/${sport}/data/${sport}_odds.json`;

class oddsObj {
    constructor(awayTeam, homeTeam, awayML, homeML, awaySpread, homeSpread, over, under, dateTime, awayScore, homeScore) {
        this.awayTeam = awayTeam;
        this.homeTeam = homeTeam;
        this.awayML = awayML;
        this.homeML = homeML;
        this.awaySpread = awaySpread;
        this.homeSpread = homeSpread;
        this.over = over;
        this.under = under;
        this.dateTime = dateTime;
        this.awayScore = awayScore;
        this.homeScore = homeScore;
    }
}

let lines = fs.readFileSync(rfilename, 'utf-8').split('\n');
let elements = lines.map(line => {
    return new oddsObj(...line.split('/'))
})

let finalObj = {
    sport : sport,
    oddsData : elements.slice(0,-1)
};

finalObj = JSON.stringify(finalObj);

fs.writeFile(wfilename, finalObj, (err) => {if (err) console.log(err)});
