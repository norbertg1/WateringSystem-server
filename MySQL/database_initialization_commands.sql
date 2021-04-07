#usage: source path/database_initialization_commands
CREATE DATABASE watering_server COLLATE = 'utf8_general_ci';
USE watering_server;
CREATE TABLE IF NOT EXISTS `users` (
  `USER_ID` int(11) NOT NULL AUTO_INCREMENT,
  `USER_NAME` varchar(15) NOT NULL,
  `USER_EMAIL` varchar(40) NOT NULL,
  `USER_PASSWORD` varchar(255) NOT NULL,
  `JOINING_DATE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LOGIN_TOKEN` varchar(50),
  `LOGIN_TOKEN_CREATED_AT` timestamp,
  PRIMARY KEY (`USER_ID`, `USER_EMAIL`)
);

CREATE TABLE devices (
	USER_ID					    int(11)                 	COMMENT 'Ez alapján párosítódnak a felhasználók az eszközökkel.',
	DEVICE_NAME					varchar(127)				COMMENT 'Ezt a felhasználó szabadon választhatja és cserélgetheti is. Minden bejelentkezéskor átmásolódik a data táblába, így ezzel az akutális értékeknek rövid leírás név',
	DEVICE_ID					varchar(127)				COMMENT 'A hardver egyedi azonosítója, hardverből kiolvasható. Csak olyan eszköz engedélyezett ami szerepel ebben a táblában.',
	LAST_ON_TIME				datetime					COMMENT 'Az utolsó öntözés időpontja',
	ON_COMMAND					bool						COMMENT 'Kézi vezérlés: 1 -> elindul az öntözés, 2 - winter state -> nem mukodik mert ujraindulasnal mindig becsukodik aminek hardveres okai vannak',
	REPEAT_RATE					int							COMMENT 'Az öntözés ismétlődése órákban. A 0 azt jelenti, hogy ki van kapcsolva a funkcio. NINCS RÁ MEGÍRVA A KÓD',
	IRRIGATION_TIME				time						COMMENT 'Az öntözés időpontja',
	IRRIGATION_LENGTH			int							COMMENT 'ilyen hosszan tartson az öntözés',
	IRRIGATION_LITERS           float						COMMENT 'ilyen hosszan tartson az öntözés',
	IRRIGATION_MM   			float						COMMENT 'ilyen hosszan tartson az öntözés',
	MOISTURE_PERCENT			int							COMMENT 'Az öntözés elindítása a talajnedvesség függvényében. A 0 azt jelenti hogy ki van kapcsolva a funkció. A megadott érték alá csökkenés alatt aktiválódik az öntözés a következő időpontra',
    IRRIGATION_ON_TEMPERATURE   int default 0				COMMENT 'Az öntözés elindítása a napi hőmérséklet függvényében. A 0 azt jelenti hogy ki van kapcsolva a funkció. Ennyi pontot kell elérnie, hogy reggel elinduljon az öntözés',
    TEMPERATURE_POINTS	        int default 0	            COMMENT 'Minden eszköz a napi legmagasabb hőmréséklettől függően pontokat kap. Ha eléri ezt a pontszámot akkor másnapra létrejön egy automatikus öntözés. A 0 érték a funkció kikapcsolását jelenti.',
    AREA                        int							COMMENT 'Az öntözendő terület nagysága m2-ben',
    DELAY_TIME					int							COMMENT 'Ennyi másodperces időközönként ébred fel a hardver ha a szelep nyitva van, ellenőrzi hogy kell-e még tovább locsolni',
	SLEEP_TIME					int							COMMENT 'Ennyi másodperces időközönként ébred fel a hardver ellenőrzi, hogy kell-e locsolni',
	REMOTE_UPDATE				int							COMMENT 'Ha 0 ====> normális működés ha 1 ====> az eszköz IP címén bejön a web update interfész',
    REMOTE_LOG                  bool default 0 				COMMENT 'Táv loggolás FTPn keresztül',
    LATID                       float			        	COMMENT 'Helyadat az openweathermap APInak',
    LONGIT                      float				        COMMENT 'Helyadat az openweathermap APInak',
    DAILY_MAX                   float						COMMENT 'Openweathermap-ból lekérdezett érték',
    FORECAST_MM                 float default 0 			COMMENT 'Openweathermap-ból éjfélkor előrejelzett esőmennyiség 18 órán belül. Ha nagyobb mint x mm, nincs automatizált öntözés következő nap   '
);

/*A repeat rate megengedett értéke a 24 többszöröse (tehát 1 nap).
**Ha negatív értékeke vesz fel az azt jelenti hogy az öntözés véletlenszerűen
**eltolódhat 24 órával. Tehát -24 ===> 24-48h/öntözés, -48===> 48-72h/öntözés, stb...*/

CREATE TABLE data (
	DEVICE_ID			varchar(127) NOT NULL,
    DEVICE_NAME			varchar(127)            COMMENT 'A devices táblából másolódik ide az eszköz aktuális neve',
	LOGIN_TIME			datetime,
	TEMPERATURE			float,
	HUMIDITY			smallint,
	MOISTURE			smallint,
	PRESSURE			float,
    WATER_VOLUME        float,
	WATER_VELOCITY      float,
    MM                  float,
    VOLTAGE				float,
    ON_OFF_STATE		bool 					COMMENT 'Ha 1 ON_LENGTH álltal elindított öntözés, 2 - Winter state (hosszú távon nyitva van), 10 - és felette valamilyen hardveres hibára ualó jel (befagyás is ezt okoz) bővebben: kliens forráskódja',
	RSSI				int,
	AWAKE_LENGTH		float,
    TEMP_OPENWEATHER	float 					COMMENT 'Openweathermaps-ról származó hőmérsékletadat',
    RAIN_MM             float 					COMMENT '3 óra esőzése',
    VERSION             varchar(10),
    RST_REASON          varchar(25),
    CONSTRAINT FK_data_DEVICE_ID FOREIGN KEY (DEVICE_ID) REFERENCES devices (DEVICE_ID)
);

CREATE TABLE scheduled_irrigation (
    IRRIGATION_ID 		int AUTO_INCREMENT PRIMARY KEY,
	DEVICE_ID			varchar(127) NOT NULL,
	ON_DATE             date NOT NULL 			COMMENT 'ez nem lehet NULL',
    END_DATE            date					COMMENT 'ha megvan adva egy dátum addig ismétlődik az öntözés, ahhoz hogy ne ismétlődjön ugyanannak kell lennie mint az ON_DATE vagy kitöltetlennek',
    ON_TIME				time 					COMMENT 'ez nem lehet NULL',
	LENGTH				int 					COMMENT 'vagy időben',
	LITERS      		float 					COMMENT 'vagy literben',
    MM          		float 					COMMENT 'vagy mm-ben kell meghatarozni az öntözés hosszát',
    DONE         		tinyint(4) 				COMMENT 'a sikeres öntözések, számát mutatja',
    TODAY 				tinyint(4) 				COMMENT 'a mai napi öntözés elvégzésének megpróbálását jelzi',
    COMMAND_ID          tinyint default 0 		COMMENT 'Az öntözést kiváltó esemény (devices.ON_COMMAND-al indított nem kerül ide): 0 - user, 1 - temperature points, 2 - hottest days irrigation (ez itt lehet 21,22,23 - ahol, ez a három beállítható öntöése száma), 3 - moisture',
    CONSTRAINT FK_scheduled_irrigation_DEVICE_ID FOREIGN KEY (DEVICE_ID) REFERENCES devices (DEVICE_ID)
) COMMENT = 'A tervezett öntözések.';

CREATE TABLE scheduled_irrigation_result (
    IRRIGATION_ID       int NOT NULL			COMMENT 'ezzel tudom párosítani a scheduled_irrigationnal',
	DEVICE_ID			varchar(127),
    REAL_DATETIME		datetime 				COMMENT '↓↓↓ amit a betervezettből sikerült megvalósítani ↓↓↓',
    REAL_LENGTH      	int,
    REAL_LITERS      	float,
    REAL_MM          	float,
    RESULT              int 					COMMENT '1 - sikeres volt az öntözés, 2 - rossz idő szólt közbe, 3 - elérte a maximális időt ami öntözésre volt szánva (mert pl. nincs víz), 4 - alacsony akkufeszt jelöli, Ha nem másolódik ide sor, akkor nincs kapcsolat az eszközzel',
    PRIMARY KEY (IRRIGATION_ID, DEVICE_ID),
    CONSTRAINT FK_scheduled_irrigation_result_IRRIGATION_ID FOREIGN KEY (IRRIGATION_ID) REFERENCES scheduled_irrigation (IRRIGATION_ID),
    CONSTRAINT FK_scheduled_irrigation_result_DEVICE_ID FOREIGN KEY (DEVICE_ID) REFERENCES devices (DEVICE_ID)
) COMMENT = 'A scheduled_irrigation-ben tervezett öntözések eredménye napi lebontásban';

CREATE TABLE hottest_days_irrigation (
    DEVICE_ID                       varchar(127),               COMMENT 'Hardverazonosító',
	IRRIGATION_ONE_TEMPERATURE      float   default 35,         COMMENT 'Mekkora hőmérsékletnél jöjjön létre öntözés, pl.: 30 °C',
	IRRIGATION_ONE_CHECK_TIME       time    not null default 0, COMMENT 'Mikor történjen a hőmérséklet ellenérzése, pl.: 11:00',
	IRRIGATION_ONE_TIME             time    not null default 0, COMMENT 'Mikor aktiválódjon az öntözés, pl.: 11:30',
    IRRIGATION_TWO_TEMPERATURE      float   default 35,
	IRRIGATION_TWO_CHECK_TIME       time    not null default 0,
	IRRIGATION_TWO_TIME             time    not null default 0,
	IRRIGATION_THREE_TEMPERATURE    float   default 35,
	IRRIGATION_THREE_CHECK_TIME     time    not null default 0,
	IRRIGATION_THREE_TIME           time    not null default 0,
    CONSTRAINT FK_hottest_days_irrigation_DEVICE_ID FOREIGN KEY (DEVICE_ID) REFERENCES devices (DEVICE_ID)
) COMMENT = 'A legmelegebb napokon (30°C+) szükséges lehet lehet rövid (2 perces) öntözésekkel lehűteni a gyepet akár többször is!';

CREATE TABLE pairs (
	VALVE_ID			varchar(20),
	SENSOR_ID   		varchar(20)
) COMMENT = 'Az érzékelők álltal mért adat összepárosítása a szelepvezérlőkkel. Ez jelenelg akkor szükséges ha talajnedvesség függvénéyben szeretnénk locsolni';

CREATE VIEW data_last_week AS SELECT * from data WHERE LAST_LOGIN >= DATE_ADD(CURDATE(),INTERVAL -7 DAY);
CREATE VIEW last_week as SELECT data.DEVICE_ID, devices.DEVICE_NAME, data.LAST_LOGIN, data.TEMPERATURE, data.HUMIDITY, data.MOISTURE, data.PRESSURE, data.WATER_VOLUME, data.WATER_VELOCITY, data.VOLTAGE, data.ON_OFF_STATE, data.TEMP_OPENWEATHER, data.RAIN_MM, data.RSSI, data.AWAKE_TIME, data.VERSION, data.RST_REASON from data  JOIN devices ON data.DEVICE_ID = devices.DEVICE_ID WHERE LAST_LOGIN >= DATE_ADD(CURDATE(),INTERVAL -7 DAY);
CREATE VIEW last_week_desc as SELECT data.DEVICE_ID, devices.DEVICE_NAME, data.LAST_LOGIN, data.TEMPERATURE, data.HUMIDITY, data.MOISTURE, data.PRESSURE, data.WATER_VOLUME, data.WATER_VELOCITY, data.VOLTAGE, data.ON_OFF_STATE, data.TEMP_OPENWEATHER, data.RAIN_MM, data.RSSI, data.AWAKE_TIME, data.VERSION, data.RST_REASON from data  JOIN devices ON data.DEVICE_ID = devices.DEVICE_ID WHERE LAST_LOGIN >= DATE_ADD(CURDATE(),INTERVAL -7 DAY) ORDER BY LAST_LOGIN desc;

#insert into users (USER_NAME,USER_EMAIL,USER_PASSWORD) values('anna','valaki@valaki.com','titok');
insert into devices values('1','locsolo1','288f83-1640ef','2015-06-30 10:00:00',0,0,'10:00:00',0,0,10,0,9,0,25,30,300,0,0,48.1520,17.8654,0,NULL,0);
insert into devices values('1','locsolo2','795041-16301c','2015-06-30 10:00:00',0,0,'10:00:00',0,0,10,0,9,0,25,30,300,0,0,48.1520,17.8654,0,NULL,0);
#insert into hottest_days_irrigation values('288f83-1640ef',29,'11:00:00','11:30:00',30,'12:30:00','13:00:00',33,'00:00:00','00:00:00');
#insert into hottest_days_irrigation values('795041-16301c',29,'11:00:00','11:30:00',30,'12:30:00','13:00:00',33,'00:00:00','00:00:00');

CREATE USER 'webserver_agent'@'localhost' IDENTIFIED BY 'ide_jön_a_python_agent_jelszava';
GRANT ALL PRIVILEGES ON watering_server.* TO 'webserver_agent'@localhost;
CREATE USER 'norbert'@'%';
GRANT SELECT ON watering_server.* to 'norbert'@'%';
FLUSH PRIVILEGES;

#USERS:
#root:root //localhost
#webserver_agent:iuh5dswe3Wdas6cvQ@2+ //mqtt_mysql_handler.py, jelszó megváltozott

#insert into scheduled_irrigation values (null,'288f83-1640ef','2020-3-28',null,'18:25:00', 180, 0,0,0,0,0);
#ALTER USER 'webserver_agent'@'localhost' IDENTIFIED by 'U.iEw+aLMN+NM.*';