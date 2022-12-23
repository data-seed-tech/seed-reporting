/*--------------------------------------------------------------*/
/*--		INSTALL	DATA-SEED REPORTING                     */
/*--------------------------------------------------------------*/
CREATE TABLE `seed_priorities` (
  `priorityId` smallint(6) NOT NULL,
  `priority` char(10) COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`priorityId`),
  UNIQUE KEY `priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='SEED: priorities nomenclature for report alerts or tasks';

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
  `sqlReport` varchar(10000) DEFAULT NULL COMMENT 'Report SQL select',
  `activationCriteria` varchar(5000) NOT NULL COMMENT 'Generally it is in a form of a count().',
  `sqlMinCondition` varchar(1000) NOT NULL COMMENT 'Condition to make the report result an alert if activationCriteria is lower than sqlMinCondition',
  `sqlMaxCondition` varchar(100) NOT NULL COMMENT 'Condition to make the report result an alert if activationCriteria is higher than sqlMaxCondition',
  `slowExecution` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Those with slowExecution = 1 are not automatically run on the main page',
  `priority` smallint(6) NOT NULL COMMENT 'Priority of the alert created in case of activationCriteria is lower than sqlMinCondition of higher than sqlMaxCondition. Possible values: 1,2,3,4,5, where 1 is maximum priority',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='DATA-SEED Reports';