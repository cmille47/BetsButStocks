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
rel_path = "nfl/data/archive.json"
abs_file_path = os.path.join(script_dir, rel_path)

f = open(abs_file_path)

data = json.loads(f.read())
for date in data.keys():
    sport = data[date]["sport"]
    oddsData = data[date]["oddsData"]

    for game in oddsData:
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
            # sql = "Insert into Bets values (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)"
            val = (game_id, date, homeML, awayML, homeSpreadOdds, awaySpreadOdds, homeSpread, awaySpread, over, overOdds, underOdds)
            mycursor.execute(sql, val)
        except Exception as e:
            print(e)
            print(val)
            print("error inserting Bets")

        # deal with completed game
        if completed_bool:
            try:
                sql = "update Games Set completed = %s, home_score = %s, away_score = %s where game_id = %s"
                val = [1, home_score, away_score, game_id]
                mycursor.execute(sql,val)
            # deal with updating contracts and such
            # over under
            except:
                print("error with completed game")
        
mydb.commit()
