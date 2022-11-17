'use strict';
const puppeteer = require('puppeteer');
const fs = require('fs/promises');
const fx = require('fs');
require('events').EventEmitter.defaultMaxListeners = 16;

const sport = 'nfl';
const rfilename =  `/var/www/html/cse30246/betsbutstocks/${sport}/data/${sport}_links.txt`
const wfilename = `/var/www/html/cse30246/betsbutstocks/${sport}/data/${sport}_odds.txt`;
const url = `https://www.oddsshark.com/${sport}/scores`;

async function writeData(data) {
    await fs.appendFile(wfilename, data + '\n');
}

async function search(link) {
    const browser = await puppeteer.launch();
    const page = await browser.newPage();

    await page.setDefaultNavigationTimeout(0); // added
    
    await page.goto(link);
    
    await page.type('.node__content', 'Headless Chrome');

    const allResultsSelector = '.gc-team-record--left';
    await page.waitForSelector(allResultsSelector);
    await page.click(allResultsSelector);
    
    let resultsSelector = '.gc-team-info__text--primary';
    await page.waitForSelector(resultsSelector);
    
    const element = await page.evaluate(resultsSelector => {
        const anchors = Array.from(document.querySelectorAll(resultsSelector));
        return anchors.map(anchor => {
        const title = anchor.textContent.split('|')[0].trim();
        return title;
        });
    }, resultsSelector);
    let teams = [element[0] + ' ' +  element[1], element[2] + ' ' + element[3]];

    resultsSelector = '.table__odd';
    await page.waitForSelector(resultsSelector);

    const odds = await page.evaluate(resultsSelector => {
        const anchors = Array.from(document.querySelectorAll(resultsSelector));
        return anchors.map(anchor => {
        const title = anchor.textContent.split('|')[0].trim();
        return title;
        });
    }, resultsSelector);

    resultsSelector = '.gc-event-date';
    await page.waitForSelector(resultsSelector);

    const date = await page.evaluate(resultsSelector => {
        const anchors = Array.from(document.querySelectorAll(resultsSelector));
        return anchors.map(anchor => {
        const title = anchor.textContent.split('|')[0].trim();
        return title;
        });
    }, resultsSelector);

    resultsSelector = '.gc-score';
    await page.waitForSelector(resultsSelector);

    let awayScore = undefined;
    let homeScore = undefined;
    const final = await page.evaluate(resultsSelector => {
        const anchors = Array.from(document.querySelectorAll(resultsSelector));
        return anchors.map(anchor => {
        const title = anchor.textContent.split('|')[0].trim();
        return title;
        });
    }, resultsSelector);

    if (final[0].includes("Final")) {
        resultsSelector = '.gc-score__num--left';
        await page.waitForSelector(resultsSelector);

        const aScore = await page.evaluate(resultsSelector => {
            const anchors = Array.from(document.querySelectorAll(resultsSelector));
            return anchors.map(anchor => {
                const title = anchor.textContent.split('|')[0].trim();
                return title;
            });
        }, resultsSelector);
        awayScore = Number(aScore);

        resultsSelector = '.gc-score__num--right';
        await page.waitForSelector(resultsSelector);

        const hScore = await page.evaluate(resultsSelector => {
            const anchors = Array.from(document.querySelectorAll(resultsSelector));
            return anchors.map(anchor => {
                const title = anchor.textContent.split('|')[0].trim();
                return title;
            });
        }, resultsSelector);
        homeScore = Number(hScore);
    }

    // Need to verify these indices and make sure consistent
    let spreadHome = odds[17];
    let spreadAway = odds[19]
    let over = odds[1];
    let under = odds[3];
    let mlHome = odds[16];
    let mlAway = odds[18];

    let data = `${teams[0]}/${teams[1]}/${mlHome}/${mlAway}/${spreadHome}/${spreadAway}/${over}/${under}/${String(date).split('ET')[0] + 'ET'}/${awayScore}/${homeScore}`
    writeData(data);
    
    await browser.close();
};

let links = fx.readFileSync(rfilename, 'utf-8').split('\n');
links.pop()     // remove last newline

fs.writeFile(wfilename, '');
for (let i = 0; i < links.length / 2; i++) {
    search(links[i]);
}
