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


script_dir = os.path.dirname(__file__) #<-- absolute dir the script is in
rel_path = "nfl/data/nfl_odds.json"
abs_file_path = os.path.join(script_dir, rel_path)

f = open(abs_file_path)

data = json.loads(f.read())

sport = data["sport"]
oddsData = data["oddsData"]

for game in oddsData:
  # get all the data you want

  # if no team specified go to next game
  # same with game date
  awayTeam = game["awayTeam"]
  homeTeam = game["homeTeam"]
  if homeTeam == "" or awayTeam == "":
    continue
  
  try:
    game_date = datetime.strptime(game["dateTime"][:-3], "%B %d, %Y, %I:%M %p")
  except:
    print("error with date")
    continue

  # need to use last scrapes values if we didnt get one
  ml_bool = True
  spread_bool = True
  overunder_bool = True

  awayML = game["awayML"]
  homeML = game["homeML"]
  if awayML == "" or homeML == "":
    ml_bool = False
    awayML = None
    homeML = None

  awaySpread = ""
  homeSpread = ""
  awaySpreadOdds = ""
  homeSpreadOdds = ""
  if len(game["awaySpread"]) > 0 and len(game["homeSpread"]) > 0:
    if len(game["awaySpread"].split(" ")) == 2 and len(game["homeSpread"].split(" ")) == 2:
      awaySpread = game["awaySpread"].split(" ")[0]
      awaySpreadOdds = game["awaySpread"].split(" ")[1][1:-1]
      homeSpread = game["homeSpread"].split(" ")[0]
      homeSpreadOdds = game["homeSpread"].split(" ")[1][1:-1]
      if awaySpread == "Ev":
          awaySpread = 0
      if homeSpread == "Ev":
          homeSpread = 0
    else:
      spread_bool = False
      awaySpread = None
      homeSpread = None
      awaySpreadOdds = None
      homeSpreadOdds = None
  else:
      spread_bool = False
      awaySpread = None
      homeSpread = None
      awaySpreadOdds = None
      homeSpreadOdds = None
  if awaySpread == "" or homeSpread == "" or awaySpreadOdds == "" or homeSpreadOdds == "":
      spread_bool = False
      awaySpread = None
      homeSpread = None
      awaySpreadOdds = None
      homeSpreadOdds = None
  else:
      awaySpread = float(awaySpread)
      homeSpread = float(homeSpread)
      awaySpreadOdds = int(awaySpreadOdds)
      homeSpreadOdds = int(homeSpreadOdds)


  over = ""
  overOdds = ""
  under = ""
  underOdds = ""
  if len(game["over"]) > 0 and len(game["under"]) > 0:
      over = game["over"].split(" ")[0]
      overOdds = game["over"].split(" ")[1][1:-1]
      under = game["under"].split(" ")[0]
      underOdds = game["under"].split(" ")[1][1:-1]
      if over == "" or under == "" or overOdds == "" or underOdds == "":
          overunder_bool = False
          over = None
          overOdds = None
          under = None
          underOdds = None
      else:
          over = float(over)
          under = float(under)
          overOdds = int(overOdds)
          underOdds = int(underOdds)
  else:
      overunder_bool = False
      over = None
      overOdds = None
      under = None
      underOdds = None
  

  #check if game has score and is completed
  completed_bool = False
  home_score = game["homeScore"]
  away_score = game["awayScore"]
  if not(home_score == "undefined" or away_score == "undefined"):
    completed_bool = True
    home_score = int(home_score)
    away_score = int(away_score)

  # get game
  try:
    sql = "select * from Games where home_team = %s and away_team = %s and game_date = %s"
    val = [homeTeam, awayTeam, game_date]
    mycursor.execute(sql, val)
  except:
    print("error with getting game")
    continue

  for x in mycursor:
    # need to read all values
    pass

  # if game doesnt exist create game 
  if mycursor.rowcount == -1:
    try: 
      sql = "INSERT INTO Games (home_team, away_team, completed, game_date, sport) VALUES (%s, %s, %s, %s, %s)"
      val = (homeTeam, awayTeam, 0, game_date, sport)
      mycursor.execute(sql, val)
      for x in mycursor:
        print(x)
    except:
      print("error with creating new game")
      continue
  
  # get game id
  sql = "select game_id from Games where home_team = %s and away_team = %s and game_date = %s"
  val = [homeTeam, awayTeam, game_date]
  game_id = -1
  mycursor.execute(sql, val)
  for x in mycursor:
    game_id = x[0]

  # replace values not gotten with values from last successful read
  if not ml_bool or not spread_bool or not overunder_bool:
    # get previous value
    sql = "select * from Bets where game_id = %s order by collect_date desc limit 1"
    prev = []
    for x in mycursor:
      prev = x

    # if preveious exists get the values ythat you are missing
    if not mycursor.rowcount == -1:
      if not ml_bool:
        homeML = prev[2]
        awayML = prev[3]
      if not spread_bool:
        homeSpreadOdds = prev[4]
        awaySpreadOdds = prev[5]
        homeSpread = prev[6]
        awaySpread = prev[7]
      if not overunder_bool:
        over = prev[8]
        over_odds = prev[9]
        under_odds = prev[10]

  # insert new bet timestamp
  try:
    sql = "INSERT INTO Bets (game_id, collect_date, ml_home_odds, ml_away_odds, s_home_odds, s_away_odds, s_home_line, s_away_line, ou_total, ou_over_odds, ou_under_odds) values (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)"
    val = (game_id, datetime.now(), homeML, awayML, homeSpreadOdds, awaySpreadOdds, homeSpread, awaySpread, over, overOdds, underOdds)
    mycursor.execute(sql, val)
  except Exception as e:
    print(e)
    print(val)
    print("error inserting Bets")
  
  # set game as complete
  if completed_bool:
    # sql = "select * from Games where game_id = %s"
    # val = [game_id]
    # mycursor.execute(sql,val)
    try:
      sql = "update Games Set completed = %s, home_score = %s, away_score = %s where game_id = %s"
      val = [1, home_score, away_score, game_id]
      mycursor.execute(sql,val)
    except:
      print("error with completed game")

    try:
      sql = "select home_score, away_score from Games where game_id = %s"
      val = [game_id]
      mycursor.execute(sql,val)
      home_score = 0
      away_score = 0
      for x in mycursor:
        home_score = x[0]
        away_score = x[1]

      sql = "update Games Set completed = %s, home_score = %s, away_score = %s where game_id = %s"
      val = [1, home_score, away_score, game_id]
      mycursor.execute(sql,val)
      # deal with updating contracts and such
      # over under
      try:
        sql = "Select * from Contracts where game_id = %s and paidout = %s"
        val = (game_id, 0)
        mycursor.execute(sql, val)
        # for each contract see if it won
        results = []
        for x in mycursor:
          results.append(x)
        for x in results:
          # load data
          contract_id = x[0]
          user_id = x[1]
          game_id = x[3]
          bet_type = x[5]
          bet_choice = x[6]
          amount = x[7]
          ml_home_odds = x[8]
          ml_away_odds = x[9]
          s_home_odds = x[10]
          s_away_odds = x[11]
          s_home_line = x[12]
          s_away_line = x[13]           
          ou_total = x[14]              
          ou_over_odds = x[15]          
          ou_under_odds = x[16]         
          purchase_date = x[17]         
          original_purchase_date = x[18]
          previous_owner_id = x[19]
          
          # overunder
          if bet_type == "OU":
            curOdds = None
            if bet_choice == "Over":
              curOdds = ou_over_odds
            elif bet_choice == "Under":
              curOdds = ou_under_odds

            if not curOdds == None:
              payout = None
              if curOdds > 0:
                payout = curOdds *  amount / 100 + amount
              else:
                payout = (amount / (curOdds * -1)) * 100 + amount
              if bet_choice == "Over" and home_score + away_score > ou_total:
                sql = "update Users Set balance = balance + %s where user_id = %s"
                val = (payout, user_id)
                mycursor.execute(sql, val)
                sql = "update Contracts Set loss = %s where contract_id = %s"
                val = (payout, contract_id)
                mycursor.execute(sql, val)
                sql = "update Users Set balance = balance - %s where user_id = 77"    # The Book's user_id is 77
                val = [payout]
                mycursor.execute(sql, val)
              elif bet_choice == "Under" and home_score + away_score < ou_total:
                sql = "update Users Set balance = balance + %s where user_id = %s"
                val = (payout, user_id)
                mycursor.execute(sql, val)
                sql = "update Contracts Set loss = %s where contract_id = %s"
                val = (payout, contract_id)
                mycursor.execute(sql, val)
                sql = "update Users Set balance = balance - %s where user_id = 77"    # The Book's user_id is 77
                val = [payout]
                mycursor.execute(sql, val)
              elif home_score + away_score == ou_total:
                sql = "update Users Set balance = balance + %s where user_id = %s"
                val = (amount, user_id)
                mycursor.execute(sql, val)
                sql = "update Contracts Set loss = %s where contract_id = %s"
                val = (amount, contract_id)
                mycursor.execute(sql, val)
                sql = "update Users Set balance = balance - %s where user_id = 77"    # The Book's user_id is 77
                val = [amount]
                mycursor.execute(sql, val)

          #spread
          elif bet_type == "Spread":
            if (bet_choice == "Away" and away_score + s_away_line > home_score) or (bet_choice == "Home" and home_score + s_home_line > away_score):
              curOdds = 0
              if bet_choice == "Away":
                curOdds = s_away_odds
              else:
                curOdds = s_home_odds

              payout = None
              if curOdds > 0:
                payout = curOdds *  amount / 100 + amount
              else:
                payout = (amount / (curOdds * -1)) * 100 + amount
              sql = "update Users Set balance = balance + %s where user_id = %s"
              val = (payout, user_id)
              mycursor.execute(sql, val)
              sql = "update Contracts Set loss = %s where contract_id = %s"
              val = (payout, contract_id)
              mycursor.execute(sql, val)
              sql = "update Users Set balance = balance - %s where user_id = 77"    # The Book 's user_id is 77
              val = [payout]
              mycursor.execute(sql, val)
            elif bet_choice == "Home" and home_score + s_home_line == away_score:
              sql = "update Users Set balance = balance + %s where user_id = %s"
              val = (amount, user_id)
              mycursor.execute(sql, val)
              sql = "update Contracts Set loss = %s where contract_id = %s"
              val = (amount, contract_id)
              mycursor.execute(sql, val)
              sql = "update Users Set balance = balance - %s where user_id = 77"    # The Book 's user_id is 77
              val = [amount]
              mycursor.execute(sql, val)
              
              
            
          # money line
          elif bet_type == "ML":
            if (bet_choice == "Away" and away_score > home_score) or (bet_choice == "Home" and home_score > away_score):
              curOdds = 0
              if bet_choice == "Away":
                curOdds = ml_away_odds
              else:
                curOdds = ml_home_odds
              
              payout = None
              if curOdds > 0:
                payout = curOdds *  amount / 100 + amount
              else:
                payout = (amount / (curOdds * -1)) * 100 + amount
              if (bet_choice == "Away" and away_score > home_score) or (bet_choice == "Home" and home_score > away_score):
                sql = "update Users Set balance = balance + %s where user_id = %s"
                val = (payout, user_id)
                mycursor.execute(sql, val)
                sql = "update Contracts Set loss = %s where contract_id = %s"
                val = (payout, contract_id)
                mycursor.execute(sql, val)
                sql = "update Users Set balance = balance - %s where user_id = 77"    # The Book 's user_id is 77
                val = [payout]
                mycursor.execute(sql, val)
              elif (home_score == away_score):
                sql = "update Users Set balance = balance + %s where user_id = %s"
                val = (amount, user_id)
                mycursor.execute(sql, val)
                sql = "update Contracts Set loss = %s where contract_id = %s"
                val = (amount, contract_id)
                mycursor.execute(sql, val)
                sql = "update Users Set balance = balance - %s where user_id = 77"    # The Book 's user_id is 77
                val = [amount]
                mycursor.execute(sql, val)
              
          #set contract as paidout
          sql = "update Contracts set paidout = 1 where contract_id = %s"
          val = [contract_id]
          mycursor.execute(sql, val)
      except Exception as e:
        print(e)
        print("error with completed game")
    except Exception as d:
      print(d)
      print("error with getting score")
  

print("done")
for x in mycursor:
  pass

mydb.commit()
