START TRANSACTION;

USE `bitcoin`;

CREATE TABLE IF NOT EXISTS `BTCUSD_1d` (
  `timestamp` bigint NOT NULL,
  `close` decimal(13,3) DEFAULT NULL,
  PRIMARY KEY (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `BTCUSD_1h` (
  `timestamp` bigint NOT NULL,
  `close` decimal(13,3) DEFAULT NULL,
  PRIMARY KEY (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `BTCUSD_1m` (
  `timestamp` bigint NOT NULL,
  `close` decimal(13,3) DEFAULT NULL,
  PRIMARY KEY (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;
