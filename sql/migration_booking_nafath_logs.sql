-- تشغيل مرة واحدة على قاعدة موجودة (بعد install.sql إن لزم)
SET NAMES utf8mb4;

-- عمود رمز نفاذ على الطلب (يظهر في booking/nafath.php)
ALTER TABLE `orders`
  ADD COLUMN `nafath_code` VARCHAR(20) DEFAULT NULL AFTER `transaction_no`;

CREATE TABLE IF NOT EXISTS `order_booking_customer_info_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `mobile` VARCHAR(30) DEFAULT NULL,
  `provider` VARCHAR(100) DEFAULT NULL,
  `national_id_or_iqama` VARCHAR(50) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_obci_order` (`order_id`),
  CONSTRAINT `fk_obci_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `order_booking_success_verify_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `transaction_no` VARCHAR(20) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_obs_order` (`order_id`),
  CONSTRAINT `fk_obs_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
