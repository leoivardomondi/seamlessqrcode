-- Chargebee-lite subscription management schema.
-- Safe to run multiple times; application code also creates these tables defensively.

CREATE TABLE IF NOT EXISTS `subscriptions` (
    `subscription_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `plan_id` VARCHAR(16) NOT NULL,
    `status` VARCHAR(24) NOT NULL DEFAULT 'active',
    `type` VARCHAR(16) NULL DEFAULT NULL,
    `frequency` VARCHAR(16) NULL DEFAULT NULL,
    `currency` VARCHAR(4) NULL DEFAULT NULL,
    `base_amount` DECIMAL(14,4) NULL DEFAULT NULL,
    `discount_amount` DECIMAL(14,4) NULL DEFAULT NULL,
    `total_amount` DECIMAL(14,4) NULL DEFAULT NULL,
    `total_amount_default_currency` DECIMAL(14,4) NULL DEFAULT NULL,
    `taxes_ids` TEXT NULL DEFAULT NULL,
    `code` VARCHAR(32) NULL DEFAULT NULL,
    `processor` VARCHAR(32) NULL DEFAULT NULL,
    `external_subscription_id` VARCHAR(191) NULL DEFAULT NULL,
    `external_customer_id` VARCHAR(191) NULL DEFAULT NULL,
    `external_payment_id` VARCHAR(191) NULL DEFAULT NULL,
    `auto_collection` TINYINT(1) NOT NULL DEFAULT 1,
    `current_period_start` DATETIME NULL DEFAULT NULL,
    `current_period_end` DATETIME NULL DEFAULT NULL,
    `trial_start` DATETIME NULL DEFAULT NULL,
    `trial_end` DATETIME NULL DEFAULT NULL,
    `cancel_at` DATETIME NULL DEFAULT NULL,
    `canceled_at` DATETIME NULL DEFAULT NULL,
    `pause_start` DATETIME NULL DEFAULT NULL,
    `pause_end` DATETIME NULL DEFAULT NULL,
    `metadata` LONGTEXT NULL DEFAULT NULL,
    `datetime` DATETIME NOT NULL,
    `last_datetime` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`subscription_id`),
    KEY `user_id` (`user_id`),
    KEY `plan_id` (`plan_id`),
    KEY `status` (`status`),
    KEY `processor` (`processor`),
    KEY `external_subscription_id` (`external_subscription_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `subscription_items` (
    `subscription_item_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `subscription_id` BIGINT UNSIGNED NOT NULL,
    `item_type` VARCHAR(24) NOT NULL DEFAULT 'plan',
    `item_id` VARCHAR(64) NOT NULL,
    `name` VARCHAR(128) NULL DEFAULT NULL,
    `quantity` DECIMAL(14,4) NOT NULL DEFAULT 1,
    `unit_amount` DECIMAL(14,4) NULL DEFAULT NULL,
    `total_amount` DECIMAL(14,4) NULL DEFAULT NULL,
    `currency` VARCHAR(4) NULL DEFAULT NULL,
    `is_recurring` TINYINT(1) NOT NULL DEFAULT 1,
    `metadata` LONGTEXT NULL DEFAULT NULL,
    `datetime` DATETIME NOT NULL,
    PRIMARY KEY (`subscription_item_id`),
    KEY `subscription_id` (`subscription_id`),
    KEY `item_type` (`item_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `invoices` (
    `invoice_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `subscription_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `payment_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `plan_id` VARCHAR(16) NULL DEFAULT NULL,
    `status` VARCHAR(24) NOT NULL DEFAULT 'paid',
    `processor` VARCHAR(32) NULL DEFAULT NULL,
    `external_payment_id` VARCHAR(191) NULL DEFAULT NULL,
    `type` VARCHAR(16) NULL DEFAULT NULL,
    `frequency` VARCHAR(16) NULL DEFAULT NULL,
    `code` VARCHAR(32) NULL DEFAULT NULL,
    `taxes_ids` TEXT NULL DEFAULT NULL,
    `subtotal_amount` DECIMAL(14,4) NULL DEFAULT NULL,
    `discount_amount` DECIMAL(14,4) NULL DEFAULT NULL,
    `tax_amount` DECIMAL(14,4) NULL DEFAULT NULL,
    `total_amount` DECIMAL(14,4) NULL DEFAULT NULL,
    `total_amount_default_currency` DECIMAL(14,4) NULL DEFAULT NULL,
    `currency` VARCHAR(4) NULL DEFAULT NULL,
    `due_datetime` DATETIME NULL DEFAULT NULL,
    `paid_datetime` DATETIME NULL DEFAULT NULL,
    `void_datetime` DATETIME NULL DEFAULT NULL,
    `billing` TEXT NULL DEFAULT NULL,
    `business` TEXT NULL DEFAULT NULL,
    `metadata` LONGTEXT NULL DEFAULT NULL,
    `datetime` DATETIME NOT NULL,
    PRIMARY KEY (`invoice_id`),
    KEY `subscription_id` (`subscription_id`),
    KEY `payment_id` (`payment_id`),
    KEY `user_id` (`user_id`),
    KEY `status` (`status`),
    KEY `external_payment_id` (`external_payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `invoice_items` (
    `invoice_item_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `invoice_id` BIGINT UNSIGNED NOT NULL,
    `item_type` VARCHAR(24) NOT NULL DEFAULT 'plan',
    `description` VARCHAR(256) NULL DEFAULT NULL,
    `quantity` DECIMAL(14,4) NOT NULL DEFAULT 1,
    `unit_amount` DECIMAL(14,4) NULL DEFAULT NULL,
    `total_amount` DECIMAL(14,4) NULL DEFAULT NULL,
    `currency` VARCHAR(4) NULL DEFAULT NULL,
    `metadata` LONGTEXT NULL DEFAULT NULL,
    `datetime` DATETIME NOT NULL,
    PRIMARY KEY (`invoice_item_id`),
    KEY `invoice_id` (`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `billing_events` (
    `billing_event_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `subscription_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `invoice_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `event_type` VARCHAR(64) NOT NULL,
    `source` VARCHAR(32) NOT NULL DEFAULT 'app',
    `idempotency_key` VARCHAR(191) NULL DEFAULT NULL,
    `payload` LONGTEXT NULL DEFAULT NULL,
    `processed_datetime` DATETIME NULL DEFAULT NULL,
    `datetime` DATETIME NOT NULL,
    PRIMARY KEY (`billing_event_id`),
    UNIQUE KEY `idempotency_key` (`idempotency_key`),
    KEY `subscription_id` (`subscription_id`),
    KEY `invoice_id` (`invoice_id`),
    KEY `user_id` (`user_id`),
    KEY `event_type` (`event_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `dunning_attempts` (
    `dunning_attempt_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `subscription_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `invoice_id` BIGINT UNSIGNED NOT NULL,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `attempt_number` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `status` VARCHAR(24) NOT NULL DEFAULT 'scheduled',
    `scheduled_datetime` DATETIME NOT NULL,
    `attempted_datetime` DATETIME NULL DEFAULT NULL,
    `failure_reason` TEXT NULL DEFAULT NULL,
    `metadata` LONGTEXT NULL DEFAULT NULL,
    `datetime` DATETIME NOT NULL,
    PRIMARY KEY (`dunning_attempt_id`),
    KEY `subscription_id` (`subscription_id`),
    KEY `invoice_id` (`invoice_id`),
    KEY `user_id` (`user_id`),
    KEY `status` (`status`),
    KEY `scheduled_datetime` (`scheduled_datetime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `features` (
    `feature_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `feature_key` VARCHAR(128) NOT NULL,
    `name` VARCHAR(128) NOT NULL,
    `type` VARCHAR(24) NOT NULL DEFAULT 'boolean',
    `metadata` LONGTEXT NULL DEFAULT NULL,
    `datetime` DATETIME NOT NULL,
    PRIMARY KEY (`feature_id`),
    UNIQUE KEY `feature_key` (`feature_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `plan_entitlements` (
    `plan_entitlement_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `plan_id` VARCHAR(16) NOT NULL,
    `feature_key` VARCHAR(128) NOT NULL,
    `value` LONGTEXT NULL DEFAULT NULL,
    `datetime` DATETIME NOT NULL,
    PRIMARY KEY (`plan_entitlement_id`),
    UNIQUE KEY `plan_feature` (`plan_id`, `feature_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `subscription_entitlement_overrides` (
    `subscription_entitlement_override_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `subscription_id` BIGINT UNSIGNED NOT NULL,
    `feature_key` VARCHAR(128) NOT NULL,
    `value` LONGTEXT NULL DEFAULT NULL,
    `reason` VARCHAR(256) NULL DEFAULT NULL,
    `datetime` DATETIME NOT NULL,
    PRIMARY KEY (`subscription_entitlement_override_id`),
    UNIQUE KEY `subscription_feature` (`subscription_id`, `feature_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
