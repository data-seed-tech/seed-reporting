/*--------------------------------------------------------------*/
/*--		INSTALL	DATA-SEED REPORTING                     */
/*--------------------------------------------------------------*/
CREATE TABLE `seed_priorities` (
  `priorityId` smallint NOT NULL,
  `priority` char(10) NOT NULL,
  PRIMARY KEY (`priorityId`),
  UNIQUE KEY `priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=UTF8MB4 COLLATE=utf8mb4_unicode_ci COMMENT='SEED: priorities nomenclature for report alerts or tasks';

INSERT INTO seed_priorities (priorityId, priority)
VALUES ('1', 'PRIO 1'),
('2', 'PRIO 2'),
('3', 'PRIO 3'),
('4', 'PRIO 4'),
('5', 'PRIO 5');


CREATE TABLE `seed_app_reports` (
  `reportId` varchar(50) NOT NULL,
  `appCode` varchar(50) DEFAULT NULL COMMENT 'Seed App for which the report is created',
  `reportName` varchar(300) NOT NULL,
  `reportDescription` varchar(1000) NOT NULL,
  `sqlReport` varchar(5000) DEFAULT NULL COMMENT 'Report SQL select',
  `activationCriteria` varchar(5000) NOT NULL COMMENT 'Generally it is in a form of a count().',
  `sqlMinCondition` varchar(1000) NOT NULL COMMENT 'Condition to make the report result an alert if activationCriteria is lower than sqlMinCondition',
  `sqlMaxCondition` varchar(100) NOT NULL COMMENT 'Condition to make the report result an alert if activationCriteria is higher than sqlMaxCondition',
  `slowExecution` tinyint NOT NULL DEFAULT '1' COMMENT 'Those with slowExecution = 1 are not automatically run on the main page',
  `priority` smallint NOT NULL COMMENT 'Priority of the alert created in case of activationCriteria is lower than sqlMinCondition of higher than sqlMaxCondition. Possible values: 1,2,3,4,5, where 1 is maximum priority',
  `linkAddress` varchar(250) NOT NULL COMMENT 'IMPORTANT!!!! Instead of &amp; use &amp;amp;!!!!!',
  `linkId` varchar(100) NOT NULL,
  `linkDetails` varchar(100) NOT NULL,
  `sendEmail` varchar(500) NOT NULL COMMENT 'The system job seedReports (jobReports.php) will send notification email of the report in case of criteriu_activare is bigger than sql_conditie_min or criteriu_activare is lower than sql_conditie_max',
  PRIMARY KEY (`reportId`),
  UNIQUE KEY `reportName` (`reportName`),
  KEY `priority` (`priority`),
  KEY `appCode` (`appCode`),
  CONSTRAINT `seed_reports_ibfk_1` FOREIGN KEY (`priority`) REFERENCES `seed_priorities` (`priorityId`),
  CONSTRAINT `seed_reports_ibfk_2` FOREIGN KEY (`appCode`) REFERENCES `seed_apps` (`appCode`)
) ENGINE=InnoDB DEFAULT CHARSET=UTF8MB4 COLLATE=utf8mb4_unicode_ci COMMENT='DATA-SEED Reports';



CREATE TABLE `hosts` (
  `hostID` int NOT NULL AUTO_INCREMENT,
  `IP` varchar(100) NOT NULL,
  `hostName` varchar(500) NOT NULL,
  PRIMARY KEY (`hostID`),
  UNIQUE KEY `IP` (`IP`)
) ENGINE=InnoDB DEFAULT CHARSET=UTF8MB4 COLLATE=utf8mb4_unicode_ci COMMENT='Hosts recorder in the database';


CREATE TABLE `visitors` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `SessionID` varchar(250) NOT NULL,
  `FirstVisitTime` datetime DEFAULT NULL,
  `IP` varchar(100) DEFAULT NULL,
  `Page` varchar(100) DEFAULT NULL,
  `Referrer` varchar(255) DEFAULT NULL,
  `RemoteHost` varchar(500) DEFAULT NULL,
  `cookie_ok` tinyint DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=UTF8MB4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `counter` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `VisitorID` bigint NOT NULL,
  `SessionID` varchar(250) NOT NULL,
  `VisitTime` datetime DEFAULT NULL,
  `IP` varchar(100) CHARACTER SET armscii8 DEFAULT NULL,
  `Page` varchar(100) CHARACTER SET ascii DEFAULT NULL,
  `Referrer` varchar(255) CHARACTER SET armscii8 DEFAULT NULL,
  `RemoteHost` varchar(250) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB CHARSET=UTF8MB4 COLLATE=utf8mb4_unicode_ci;



INSERT INTO seed_apps (appCode, appName, appDescription, icon)
VALUES ('count', 'Counter', 'Visitor reports based on the count.inc script', '&#128202;');


INSERT INTO seed_menus (menuId, tableName, menuText, appCode)
VALUES 
('4', 'seed_app_reports', 'Reports', '_system'),
('5', 'counter', 'counter', 'count'),
('6', 'visitors', 'visitors', 'count');
    





INSERT INTO seed_app_reports(reportId, appCode, reportName, reportDescription, sqlReport, activationCriteria, sqlMinCondition, sqlMaxCondition, slowExecution, priority, linkAddress, linkId, linkDetails, sendEmail)
VALUES ('tableUsage', '_system', 'Database utility', 'Be very careful with this report! Do not expose it public!
YOU SHOULD REPLACE: table_schema = "your_database_name". OTHERWISE IT WONT WORK!!!', 'SELECT * FROM information_schema.tables WHERE table_schema = "u40448data_dataseed" AND table_type = "BASE TABLE"', 'SELECT count(*) as numberOfTables FROM information_schema.tables WHERE table_schema = "u40448data_dataseed" AND table_type = "BASE TABLE"', 'select 0', 'select 999999999', '0', '1', 'tableUsage.php', 'TABLE_NAME', '', ''),
('visitorsLastDay', 'count', 'Visits today', 'Visits in the last day taken from counter table and joined with visitors', 'SELECT VisitorID, count(VisitTime) as VisitsToday, FirstVisitTime, MAX(VisitTime) as LastVisitTime, counter.IP as ip, hosts.hostName, visitors.cookie_ok
FROM counter 
LEFT JOIN hosts ON counter.IP = hosts.IP
LEFT JOIN visitors ON counter.VisitorID = visitors.ID
WHERE DATE(VisitTime) BETWEEN DATE(NOW() - INTERVAL 1 DAY) AND CURDATE()
GROUP BY counter.IP
ORDER BY VisitsToday DESC', 'SELECT count(VisitTime) as VisitsToday
FROM counter
WHERE DATE(VisitTime) BETWEEN DATE(NOW() - INTERVAL 1 DAY) AND CURDATE()', 'select 1', 'select 99999999999', '0', '1', './count/host.php', 'ip', '', '');
    

