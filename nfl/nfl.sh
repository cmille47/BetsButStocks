#!/bin/bash
source ../venv/bin/activate
node getLinks.js
node searchLinks_1.js
node searchLinks_2.js
node formatOdds.js
deactivate
