CREATE TABLE IF NOT EXISTS `CodeList` (
  `country`     VARCHAR(2)   NOT NULL,
  `location`    VARCHAR(3)   NOT NULL,
  `name`        VARCHAR(100) NOT NULL,
  `subdivision` VARCHAR(10)  DEFAULT NULL,
  `status`      VARCHAR(2)   DEFAULT NULL,
  `function`    VARCHAR(10)  DEFAULT NULL,
  `date`        VARCHAR(4)   DEFAULT NULL,
  `IATA`        VARCHAR(3)   DEFAULT NULL,
  `coordinates` VARCHAR(12)  DEFAULT NULL,
  `remarks`     VARCHAR(255) DEFAULT NULL,
  `ch`          VARCHAR(1)   DEFAULT NULL,
  PRIMARY KEY (`country`, `location`)
);

CREATE TABLE IF NOT EXISTS `subdivision` (
  `countryCode` VARCHAR(2)   NOT NULL,
  `code`        VARCHAR(10)  NOT NULL,
  `type`        VARCHAR(50)  DEFAULT NULL,
  `name`        VARCHAR(100) NOT NULL,
  PRIMARY KEY (`countryCode`, `code`)
);

INSERT INTO `subdivision` (`countryCode`, `code`, `type`, `name`) VALUES
  ('NL', 'ZH', 'Province', 'Zuid-Holland'),
  ('NL', 'NH', 'Province', 'Noord-Holland'),
  ('NL', 'NB', 'Province', 'Noord-Brabant');

INSERT INTO `CodeList` (`country`, `location`, `name`, `subdivision`, `status`, `function`, `date`, `IATA`, `coordinates`, `remarks`, `ch`) VALUES
  ('NL', 'RTM', 'Rotterdam',    'ZH', 'AA', '-23-----', '0401', 'RTM', '5155N 00426E', NULL, NULL),
  ('NL', 'AMS', 'Amsterdam',    'NH', 'AA', '-2-45---', '0401', 'AMS', '5222N 00454E', NULL, NULL),
  ('NL', 'EIN', 'Eindhoven',    'NB', 'AA', '----5---', '0401', 'EIN', '5127N 00530E', NULL, NULL),
  ('NL', 'HAG', 'Den Haag',     'ZH', 'AA', '-2------', '0401', NULL,  '5205N 00419E', NULL, NULL),
  ('NL', 'UTR', 'Utrecht',      'UT', 'AA', '--3-----', '0401', NULL,  '5205N 00508E', NULL, NULL);
