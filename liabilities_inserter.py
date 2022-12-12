import mysql.connector
import json
import os
from datetime import datetime

mydb = mysql.connector.connect(
  host="localhost",
  user="hplante",
  password="pwpwpwpw",
  database = "hplante"
)

mycursor = mydb.cursor()

# get disctict games that have active bets
sql = "select distinct game_id from Contracts where paidout = 0;"
mycursor.execute(sql)
games = {}
game_ids = []
# games will store liability got each bet and outcome
for x in mycursor:
    game_ids.append(str(x[0]))
    games[str(x[0])+ "sa"] = 0
    games[str(x[0])+ "mla"] = 0
    games[str(x[0])+ "ouo"] = 0
    games[str(x[0])+ "sh"] = 0
    games[str(x[0])+ "mlh"] = 0
    games[str(x[0])+ "ouu"] = 0
    games[str(x[0])+ "st"] = 0
    games[str(x[0])+ "out"] = 0


# get all contracts that haven't been paid out
sql = "Select * from Contracts where paidout = 0;"

mycursor.execute(sql)

out_rev =  0


for x in mycursor:
    # parse contract
    contract_id        = x[0]
    user_id            = x[1]
    paidout            = x[2]
    game_id            = x[3]
    parlay_id          = x[4]
    bet_type           = x[5]
    bet_choice         = x[6]
    amount             = x[7]
    ml_home_odds       = x[8]
    ml_away_odds       = x[9]
    s_home_odds        = x[10]
    s_away_odds        = x[11]
    s_home_line        = x[12]
    s_away_line        = x[13]
    ou_total           = x[14]
    ou_over_odds       = x[15]
    ou_under_odds      = x[16]
    purchase_date      = x[17]
    original_purchase_date = x[18]
    previous_owner_id  = x[19]
    for_sale           = x[20]
    sale_price         = x[21]
    purchased_price    = x[22]

    # get reveue for contract
    out_rev += amount

    # calulate liability for contract
    liab = 0 

    cur_odds = 0
    if bet_type == "Spread":
        if bet_choice == "Away":
            cur_odds = s_away_odds
        else:
            cur_odds = s_home_odds
    elif bet_type == "ML":
        if bet_choice == "Away":
            cur_odds = ml_away_odds
        else:
            cur_odds = ml_home_odds
    elif bet_type == "OU":
        if bet_choice == "Over":
            cur_odds = ou_over_odds
        else:
            cur_odds = ou_under_odds
    

    if cur_odds > 0:
        liab = cur_odds *  amount / 100 + amount
    else:
        liab = (amount / (cur_odds * -1)) * 100 + amount
 


    # update total liability for that specific bet/ outcome
    if bet_type == "Spread":
        if bet_choice == "Away":
            games[str(game_id) + "sa"] = games[str(game_id) + "sa"] + liab
        else:
            games[str(game_id) + "sh"] = games[str(game_id) + "sh"] + liab
        games[str(game_id) + "st"] = games[str(game_id) + "st"] + amount
    elif bet_type == "ML":
        if bet_choice == "Away":
            games[str(game_id) + "mla"] = games[str(game_id) + "mla"] + liab
        else:
            games[str(game_id) + "mlh"] = games[str(game_id) + "mlh"] + liab
    elif bet_type == "OU":
        if bet_choice == "Over":
            games[str(game_id) + "ouo"] = games[str(game_id) + "ouo"] + liab
        else:
            games[str(game_id) + "ouu"] = games[str(game_id) + "ouu"] + liab
        games[str(game_id) + "out"] = games[str(game_id) + "out"] + amount


# calulate total max and min liability
max_liab = 0
min_liab = 0

for i in game_ids:
    max_liab += max(games[i + "mla"], games[i + "mlh"])
    max_liab += max(max(games[i + "sa"], games[i + "sh"]), games[i + "st"])
    max_liab += max(max(games[i + "ouu"], games[i + "ouo"]), games[i + "out"])
    min_liab += min(games[i + "mla"], games[i + "mlh"])
    min_liab += min(min(games[i + "sa"], games[i + "sh"]), games[i + "st"])
    min_liab += min(min(games[i + "ouu"], games[i + "ouo"]), games[i + "out"])

book_total = 0
sql = "select balance from Users where user_id = %s"
vals = [77]
mycursor.execute(sql, vals)
for x in mycursor:
    book_total = x[0]

# insert into database
sql = "insert into Liabilities values (%s, %s, %s, %s, %s)"
vals = (datetime.now(), out_rev, min_liab, max_liab, book_total)

mycursor.execute(sql, vals)

mydb.commit()