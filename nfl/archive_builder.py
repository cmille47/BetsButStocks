import json
from datetime import datetime
import os

# global data_path variable
sport = 'nfl'
data_path = '/var/www/html/cse30246/betsbutstocks/{}/data/'.format(sport)

def make_initial(file_path):
    f = open(file_path, "w")
    f.write('{}')


def main():

    # build list of all valid jsons to parse
    files_to_parse = []

    for filename in os.listdir(data_path):
        if '.json' in filename:
            if not 'archive' in filename:
                files_to_parse.append(data_path + filename)

   # extract json data from each file => note, if these files become overly large, this could be parallelized
    for file in files_to_parse:
        f = open(file)
        if not os.path.exists(data_path + 'archive.json'):
            make_initial(data_path + 'archive.json')

        with open(data_path + 'archive.json', "r+") as f2:
            loaded_json = json.load(f)

            archive_json = json.load(f2) # tentative change

            now = str(datetime.now())

            archive_json[now] = (loaded_json)

            f2.seek(0)
            
            json.dump(archive_json, f2)

        f.close()

if __name__ == "__main__":
    main()
