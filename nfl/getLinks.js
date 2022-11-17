#!/var/www/html/cse30246/betsbutstocks/venv/bin/node node

'use strict';
const puppeteer = require('puppeteer');
const fs = require('fs/promises');

const sport = 'nfl';
const filename = `/var/www/html/cse30246/betsbutstocks/${sport}/data/${sport}_links.txt`;
const url = `https://www.oddsshark.com/${sport}/scores`;

async function writeLinks(data) {
    await fs.appendFile(filename, data + '\n');
}

async function getLinks(url) {
    const browser = await puppeteer.launch();
    const page = await browser.newPage();
 
    await page.goto(url);
 
    await page.type('.scoreboard', 'Headless Chrome');
 
    const allResultsSelector = '.scoreboard';
    await page.waitForSelector(allResultsSelector);
    await page.click(allResultsSelector);
 
    const resultsSelector = '.scores-matchup__link';
    await page.waitForSelector(resultsSelector);
 
    const links = await page.evaluate(resultsSelector => {
        const anchors = Array.from(document.querySelectorAll(resultsSelector));
        return anchors.map(anchor => {
            return anchor.href;
        });
    }, resultsSelector);

    links.forEach(link => {
        if (!link.includes('betting'))
            writeLinks(link);
    });

    await browser.close();
}

fs.writeFile(filename, '');
getLinks(url);
