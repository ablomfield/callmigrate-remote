import logging, json, yaml, pymysql
import requests

#-------------Configure Logging---------------#
logger = logging.getLogger('callmigrate')
logger.setLevel(logging.DEBUG)
fh = logging.FileHandler('/opt/callmigrate/logs/callmigrate.log')
fh.setLevel(logging.DEBUG)
formatter = logging.Formatter('%(asctime)s - %(levelname)s - %(message)s', datefmt='%Y-%m-%d %H:%M:%S')
fh.setFormatter(formatter)
logger.addHandler(fh)

#-------------Import Settings-----------------#
yamlfile = open("/opt/callmigrate/settings.yaml")
yamlsettings = yaml.load(yamlfile, Loader=yaml.FullLoader)
dbserver        = yamlsettings["Database"]["ServerName"]
dbuser          = yamlsettings["Database"]["Username"]
dbpass          = yamlsettings["Database"]["Password"]
dbname          = yamlsettings["Database"]["DBName"]
charset         = "utf8mb4"
cusrortype      = pymysql.cursors.DictCursor

#---------------Load DB Settings--------------#
dbconn = pymysql.connect(host=dbserver, user=dbuser, password=dbpass, db=dbname, charset=charset,cursorclass=cusrortype)
objsettings = dbconn.cursor()
objsettings.execute("SELECT * FROM settings")
rssettings = objsettings.fetchone()
regstatus    = rssettings["regstatus"]
clientid     = rssettings["clientid"]
clientsecret = rssettings["clientsecret"]
dbconn.close

#--------------Check Registration-------------#
if regstatus == 0:
  logger.info('Unregistered... getting claim token')
  registerurl = "https://callmigrate.click/remote/register"
  regresponse = requests.get(registerurl)
  regdata = regresponse.json()
  clientid = regdata["clientid"]
  clientsecret = regdata["clientsecret"]
  logger.info("Registering as " + clientid)
  regsql = "UPDATE settings SET regstatus = 1, clientid = '" + clientid + "', clientsecret = '" + clientsecret + "'"
  dbconn = pymysql.connect(host=dbserver, user=dbuser, password=dbpass, db=dbname, charset=charset,cursorclass=cusrortype)
  objregister = dbconn.cursor()
  objregister.execute(regsql)
  dbconn.commit()
  objregister.close
  dbconn.close


#----------------Check Tasks------------------#
logger.info("Checking for new tasks")
checktaskurl = "https://callmigrate.click/remote/checktasks/"
checktaskdata = {
    'clientid': clientid,
    'clientsecret': clientsecret
}
checkresponse = requests.post(checktaskurl, data=checktaskdata)
taskdata = checkresponse.json()
taskstatus = taskdata["status"]
tasks = taskdata["tasks"]
print("Status: " + taskstatus)
print("Tasks: " + str(tasks))
for x in range(len(taskdata['tasklist'])):
  print(taskdata['tasklist'][x]['description'])
