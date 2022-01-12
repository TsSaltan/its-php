CREATE TABLE IF NOT EXISTS `cash-codes` (
  `code` VARCHAR(30) NOT NULL , 
  `balance` decimal(10,4) NOT NULL,
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;