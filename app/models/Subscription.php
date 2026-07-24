<?php

namespace Altum\Models;

defined('ALTUMCODE') || die();

class Subscription extends Model {

    private static bool $schema_checked = false;

    public const STATUSES = [
        'trialing',
        'active',
        'non_renewing',
        'paused',
        'past_due',
        'canceled',
        'expired',
        'lifetime',
    ];

    private function resolve_paid_subscription_status($frequency, $period_end = null) {
        if($frequency == 'lifetime') {
            return 'lifetime';
        }

        if($period_end) {
            try {
                $period_end_date = new \DateTime($period_end);

                if($period_end_date < new \DateTime()) {
                    return 'expired';
                }

                if($period_end_date > (new \DateTime())->modify('+10 years')) {
                    return 'lifetime';
                }
            } catch(\Exception $exception) {
                /* Keep paid subscriptions active if an imported period date is malformed. */
            }
        }

        return 'active';
    }

    public function ensure_schema() {
        if(self::$schema_checked) {
            return;
        }

        if(!\Altum\Cache::$adapter) {
            \Altum\Cache::initialize();
        }

        $queries = [
            "CREATE TABLE IF NOT EXISTS `subscriptions` (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS `subscription_items` (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS `invoices` (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS `invoice_items` (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS `billing_events` (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS `dunning_attempts` (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS `features` (
                `feature_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `feature_key` VARCHAR(128) NOT NULL,
                `name` VARCHAR(128) NOT NULL,
                `type` VARCHAR(24) NOT NULL DEFAULT 'boolean',
                `metadata` LONGTEXT NULL DEFAULT NULL,
                `datetime` DATETIME NOT NULL,
                PRIMARY KEY (`feature_id`),
                UNIQUE KEY `feature_key` (`feature_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS `plan_entitlements` (
                `plan_entitlement_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `plan_id` VARCHAR(16) NOT NULL,
                `feature_key` VARCHAR(128) NOT NULL,
                `value` LONGTEXT NULL DEFAULT NULL,
                `datetime` DATETIME NOT NULL,
                PRIMARY KEY (`plan_entitlement_id`),
                UNIQUE KEY `plan_feature` (`plan_id`, `feature_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS `subscription_entitlement_overrides` (
                `subscription_entitlement_override_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `subscription_id` BIGINT UNSIGNED NOT NULL,
                `feature_key` VARCHAR(128) NOT NULL,
                `value` LONGTEXT NULL DEFAULT NULL,
                `reason` VARCHAR(256) NULL DEFAULT NULL,
                `datetime` DATETIME NOT NULL,
                PRIMARY KEY (`subscription_entitlement_override_id`),
                UNIQUE KEY `subscription_feature` (`subscription_id`, `feature_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        ];

        foreach($queries as $query) {
            database()->query($query);
        }

        if(!database()->query("SHOW COLUMNS FROM `subscriptions` LIKE 'total_amount_default_currency'")->num_rows) {
            database()->query("ALTER TABLE `subscriptions` ADD `total_amount_default_currency` DECIMAL(14,4) NULL DEFAULT NULL AFTER `total_amount`");
        }

        if(!database()->query("SHOW COLUMNS FROM `invoices` LIKE 'total_amount_default_currency'")->num_rows) {
            database()->query("ALTER TABLE `invoices` ADD `total_amount_default_currency` DECIMAL(14,4) NULL DEFAULT NULL AFTER `total_amount`");
        }

        if(!database()->query("SHOW COLUMNS FROM `invoices` LIKE 'paid_datetime'")->num_rows) {
            database()->query("ALTER TABLE `invoices` ADD `paid_datetime` DATETIME NULL DEFAULT NULL AFTER `due_datetime`");
        }

        self::$schema_checked = true;

        $this->backfill_existing_user_subscriptions();
    }

    public function get_active_by_user_id($user_id) {
        $this->ensure_schema();

        return db()
            ->where('user_id', (int) $user_id)
            ->where('status', ['trialing', 'active', 'non_renewing', 'paused', 'past_due', 'lifetime'], 'IN')
            ->orderBy('subscription_id', 'DESC')
            ->getOne('subscriptions');
    }

    public function backfill_existing_user_subscriptions($limit = 500) {
        $now = get_date();
        $limit = (int) $limit;

        $result = database()->query("
            SELECT
                `users`.*
            FROM
                `users`
            LEFT JOIN
                `subscriptions` ON `subscriptions`.`user_id` = `users`.`user_id`
            WHERE
                `subscriptions`.`subscription_id` IS NULL
                AND `users`.`plan_id` NOT IN ('', 'guest', 'free', 'custom')
                AND `users`.`plan_expiration_date` IS NOT NULL
            LIMIT {$limit}
        ");

        while($user = $result->fetch_object()) {
            $plan = db()->where('plan_id', $user->plan_id)->getOne('plans');

            if(!$plan) {
                continue;
            }

            $payment = db()
                ->where('user_id', $user->user_id)
                ->where('status', 1)
                ->orderBy('id', 'DESC')
                ->getOne('payments');

            $frequency = $payment->frequency ?? (((new \DateTime($user->plan_expiration_date)) > (new \DateTime())->modify('+10 years')) ? 'lifetime' : 'monthly');
            $status = $this->resolve_paid_subscription_status($frequency, $user->plan_expiration_date);

            $subscription_currency = $user->payment_currency ?: ($payment->currency ?? settings()->payment->default_currency);
            $subscription_total_amount = $this->decimal($user->payment_total_amount ?? $payment->total_amount ?? 0);
            $subscription_total_amount_default_currency = $this->get_total_amount_default_currency(
                $subscription_total_amount,
                $subscription_currency,
                $payment->total_amount_default_currency ?? null
            );

            $subscription_id = db()->insert('subscriptions', [
                'user_id' => $user->user_id,
                'plan_id' => $user->plan_id,
                'status' => $status,
                'type' => $payment->type ?? (!empty($user->payment_subscription_id) ? 'recurring' : 'one_time'),
                'frequency' => $frequency,
                'currency' => $subscription_currency,
                'base_amount' => $this->decimal($payment->base_amount ?? $user->payment_total_amount),
                'discount_amount' => $this->decimal($payment->discount_amount ?? 0),
                'total_amount' => $subscription_total_amount,
                'total_amount_default_currency' => $subscription_total_amount_default_currency,
                'taxes_ids' => $payment->taxes_ids ?? $plan->taxes_ids,
                'code' => $payment->code ?? null,
                'processor' => $user->payment_processor ?: ($payment->processor ?? null),
                'external_subscription_id' => $user->payment_subscription_id ?: null,
                'external_payment_id' => $payment->payment_id ?? null,
                'auto_collection' => !empty($user->payment_subscription_id) ? 1 : 0,
                'current_period_start' => $payment->datetime ?? $user->datetime ?? $now,
                'current_period_end' => $user->plan_expiration_date,
                'metadata' => json_encode(['backfilled' => true]),
                'datetime' => $payment->datetime ?? $now,
                'last_datetime' => $now,
            ]);

            db()->insert('subscription_items', [
                'subscription_id' => $subscription_id,
                'item_type' => 'plan',
                'item_id' => $user->plan_id,
                'name' => $plan->name,
                'quantity' => 1,
                'unit_amount' => $this->decimal($payment->base_amount ?? $user->payment_total_amount),
                'total_amount' => $this->decimal($payment->base_amount ?? $user->payment_total_amount),
                'currency' => $user->payment_currency ?: ($payment->currency ?? settings()->payment->default_currency),
                'is_recurring' => !empty($user->payment_subscription_id) ? 1 : 0,
                'metadata' => json_encode(['backfilled' => true]),
                'datetime' => $now,
            ]);

            if($payment) {
                $this->create_paid_invoice($subscription_id, [
                    'local_payment_id' => $payment->id,
                    'external_payment_id' => $payment->payment_id,
                    'payment_processor' => $payment->processor,
                    'payment_total' => $payment->total_amount,
                    'total_amount_default_currency' => $payment->total_amount_default_currency,
                    'payment_currency' => $payment->currency,
                    'user_id' => $user->user_id,
                    'plan_id' => $user->plan_id,
                    'plan_name' => $plan->name,
                    'payment_frequency' => $frequency,
                    'code' => $payment->code,
                    'discount_amount' => $payment->discount_amount,
                    'base_amount' => $payment->base_amount,
                    'taxes_ids' => $payment->taxes_ids,
                    'payment_type' => $payment->type,
                    'payment_subscription_id' => $user->payment_subscription_id,
                    'billing' => $payment->billing,
                    'business' => $payment->business,
                    'metadata' => ['backfilled' => true],
                ]);
            }

            $this->log_event('subscription.backfilled', [
                'subscription_id' => $subscription_id,
                'user_id' => $user->user_id,
                'source' => 'app',
            ]);
        }
    }

    public function get_by_id($subscription_id) {
        $this->ensure_schema();

        return db()->where('subscription_id', (int) $subscription_id)->getOne('subscriptions');
    }

    public function get_invoice_by_id($invoice_id) {
        $this->ensure_schema();

        return db()->where('invoice_id', (int) $invoice_id)->getOne('invoices');
    }

    public function sync_from_successful_payment(array $data) {
        $this->ensure_schema();

        $processed_datetime = get_date();
        $plan_id = (string) $data['plan_id'];
        $user_id = (int) $data['user_id'];
        $payment_type = $data['payment_type'] ?: 'one_time';
        $payment_frequency = $data['payment_frequency'] ?: 'monthly';
        $external_subscription_id = (string) ($data['payment_subscription_id'] ?? '');
        $external_payment_id = (string) ($data['external_payment_id'] ?? '');
        $payment_datetime = $this->normalize_datetime($data['payment_datetime'] ?? ($data['paid_datetime'] ?? ($data['period_start'] ?? null))) ?? $processed_datetime;
        $period_start = $this->normalize_datetime($data['period_start'] ?? null) ?? $payment_datetime;
        $period_end = $data['period_end'] ?? $this->calculate_period_end($period_start, $payment_frequency);
        $status = $this->resolve_paid_subscription_status($payment_frequency, $period_end);
        $data['payment_datetime'] = $payment_datetime;
        $data['period_start'] = $period_start;
        $data['period_end'] = $period_end;

        $subscription = null;

        if($external_subscription_id) {
            $subscription = db()
                ->where('processor', $data['payment_processor'])
                ->where('external_subscription_id', $external_subscription_id)
                ->getOne('subscriptions');
        }

        if(!$subscription) {
            $subscription = $this->get_active_by_user_id($user_id);
        }

        $total_amount_default_currency = $this->get_total_amount_default_currency(
            $data['payment_total'],
            $data['payment_currency'],
            $data['total_amount_default_currency'] ?? null
        );

        $subscription_data = [
            'user_id' => $user_id,
            'plan_id' => $plan_id,
            'status' => $status,
            'type' => $payment_type,
            'frequency' => $payment_frequency,
            'currency' => $data['payment_currency'],
            'base_amount' => $this->decimal($data['base_amount'] ?? $data['payment_total']),
            'discount_amount' => $this->decimal($data['discount_amount'] ?? 0),
            'total_amount' => $this->decimal($data['payment_total']),
            'total_amount_default_currency' => $total_amount_default_currency,
            'taxes_ids' => $data['taxes_ids'] ?? null,
            'code' => $data['code'] ?? null,
            'processor' => $data['payment_processor'],
            'external_subscription_id' => $external_subscription_id ?: null,
            'external_payment_id' => $external_payment_id ?: null,
            'auto_collection' => $payment_type == 'recurring' && $external_subscription_id ? 1 : 0,
            'current_period_start' => $period_start,
            'current_period_end' => $period_end,
            'cancel_at' => null,
            'canceled_at' => null,
            'pause_start' => null,
            'pause_end' => null,
            'metadata' => json_encode($data['metadata'] ?? []),
            'last_datetime' => $processed_datetime,
        ];

        if($subscription) {
            db()->where('subscription_id', $subscription->subscription_id)->update('subscriptions', $subscription_data);
            $subscription_id = (int) $subscription->subscription_id;
        } else {
            $subscription_data['datetime'] = $payment_datetime;
            $subscription_id = db()->insert('subscriptions', $subscription_data);
        }

        db()->where('subscription_id', $subscription_id)->delete('subscription_items');
        db()->insert('subscription_items', [
            'subscription_id' => $subscription_id,
            'item_type' => 'plan',
            'item_id' => $plan_id,
            'name' => $data['plan_name'] ?? null,
            'quantity' => 1,
            'unit_amount' => $this->decimal($data['base_amount'] ?? $data['payment_total']),
            'total_amount' => $this->decimal($data['base_amount'] ?? $data['payment_total']),
            'currency' => $data['payment_currency'],
            'is_recurring' => $payment_type == 'recurring' ? 1 : 0,
            'metadata' => json_encode([]),
            'datetime' => $period_start,
        ]);

        $invoice_id = $this->create_paid_invoice($subscription_id, $data);

        $this->log_event('subscription.synced', [
            'subscription_id' => $subscription_id,
            'invoice_id' => $invoice_id,
            'user_id' => $user_id,
            'source' => 'payment',
            'idempotency_key' => $external_payment_id ? 'payment:' . $data['payment_processor'] . ':' . $external_payment_id : null,
            'datetime' => $payment_datetime,
            'processed_datetime' => $processed_datetime,
            'payload' => [
                'plan_id' => $plan_id,
                'status' => $status,
                'frequency' => $payment_frequency,
                'payment_type' => $payment_type,
                'payment_datetime' => $payment_datetime,
                'period_start' => $period_start,
                'period_end' => $period_end,
            ],
        ]);

        return [
            'subscription_id' => $subscription_id,
            'invoice_id' => $invoice_id,
        ];
    }

    public function create_paid_invoice($subscription_id, array $data) {
        $this->ensure_schema();

        $external_payment_id = (string) ($data['external_payment_id'] ?? '');

        if($external_payment_id) {
            $existing_invoice = db()
                ->where('processor', $data['payment_processor'])
                ->where('external_payment_id', $external_payment_id)
                ->getOne('invoices', ['invoice_id']);

            if($existing_invoice) {
                return (int) $existing_invoice->invoice_id;
            }
        }

        $date = $this->normalize_datetime($data['invoice_datetime'] ?? ($data['payment_datetime'] ?? null)) ?? get_date();
        $paid_datetime = $this->normalize_datetime($data['paid_datetime'] ?? ($data['payment_datetime'] ?? null)) ?? $date;
        $due_datetime = $this->normalize_datetime($data['due_datetime'] ?? ($data['period_start'] ?? null)) ?? $date;
        $period_start = $this->normalize_datetime($data['period_start'] ?? null);
        $period_end = $this->normalize_datetime($data['period_end'] ?? null);

        if($paid_datetime) {
            $period_start = $paid_datetime;

            if(!empty($data['payment_frequency'])) {
                $period_end = $this->calculate_period_end($period_start, $data['payment_frequency']);
            }
        }

        $subtotal = $this->decimal($data['base_amount'] ?? $data['payment_total']);
        $discount = $this->decimal($data['discount_amount'] ?? 0);
        $total = $this->decimal($data['payment_total']);
        $total_amount_default_currency = $this->get_total_amount_default_currency(
            $total,
            $data['payment_currency'],
            $data['total_amount_default_currency'] ?? null
        );
        $tax = max(0, $total - max(0, $subtotal - $discount));
        $metadata = $data['metadata'] ?? [];

        if($period_start) {
            $metadata['period_start'] = $period_start;
        }

        if($period_end) {
            $metadata['period_end'] = $period_end;
        }

        $business = json_decode($data['business'] ?? '');

        if(!$business) {
            $business = json_decode(json_encode(settings()->business));
        }

        $this->normalize_invoice_prefix($business);

        $invoice_id = db()->insert('invoices', [
            'subscription_id' => (int) $subscription_id,
            'payment_id' => $data['local_payment_id'] ?? null,
            'user_id' => (int) $data['user_id'],
            'plan_id' => (string) $data['plan_id'],
            'status' => 'paid',
            'processor' => $data['payment_processor'],
            'external_payment_id' => $external_payment_id ?: null,
            'type' => $data['payment_type'] ?? null,
            'frequency' => $data['payment_frequency'] ?? null,
            'code' => $data['code'] ?? null,
            'taxes_ids' => $data['taxes_ids'] ?? null,
            'subtotal_amount' => $subtotal,
            'discount_amount' => $discount,
            'tax_amount' => $this->decimal($tax),
            'total_amount' => $total,
            'total_amount_default_currency' => $total_amount_default_currency,
            'currency' => $data['payment_currency'],
            'due_datetime' => $due_datetime,
            'paid_datetime' => $paid_datetime,
            'billing' => $data['billing'] ?? null,
            'business' => json_encode($business),
            'metadata' => json_encode($metadata),
            'datetime' => $date,
        ]);

        db()->insert('invoice_items', [
            'invoice_id' => $invoice_id,
            'item_type' => 'plan',
            'description' => $data['plan_name'] ?? null,
            'quantity' => 1,
            'unit_amount' => $subtotal,
            'total_amount' => $subtotal,
            'currency' => $data['payment_currency'],
            'metadata' => json_encode(array_filter([
                'period_start' => $period_start,
                'period_end' => $period_end,
            ])),
            'datetime' => $date,
        ]);

        return (int) $invoice_id;
    }

    public function process_proforma_invoices($limit = 100) {
        $this->ensure_schema();

        $now = get_date();
        $limit = (int) $limit;
        $future_limit = (new \DateTime($now))->modify('+30 days')->format('Y-m-d H:i:s');
        $created = 0;
        $plans = [];

        $result = database()->query("
            SELECT
                `subscriptions`.*,
                `users`.`email` AS `user_email`,
                `users`.`name` AS `user_name`,
                `users`.`billing` AS `user_billing`,
                `users`.`language` AS `user_language`,
                `users`.`anti_phishing_code` AS `user_anti_phishing_code`
            FROM
                `subscriptions`
            INNER JOIN
                `users` ON `subscriptions`.`user_id` = `users`.`user_id`
            WHERE
                `subscriptions`.`status` IN ('trialing', 'active', 'past_due')
                AND `subscriptions`.`frequency` IN ('monthly', 'annual')
                AND `subscriptions`.`current_period_end` IS NOT NULL
                AND `subscriptions`.`current_period_end` > '{$now}'
                AND `subscriptions`.`current_period_end` <= '{$future_limit}'
            ORDER BY
                `subscriptions`.`current_period_end` ASC
            LIMIT {$limit}
        ");

        while($subscription = $result->fetch_object()) {
            try {
                $today = new \DateTime((new \DateTime($now))->format('Y-m-d'));
                $due_datetime = new \DateTime($subscription->current_period_end);
                $due_day = new \DateTime($due_datetime->format('Y-m-d'));
            } catch(\Exception $exception) {
                continue;
            }

            $days_until_due = (int) $today->diff($due_day)->format('%r%a');
            $notice_days = $subscription->frequency == 'annual' ? [30, 1] : [9, 1];

            if(!in_array($days_until_due, $notice_days, true)) {
                continue;
            }

            if(!isset($plans[$subscription->plan_id])) {
                $plans[$subscription->plan_id] = (new Plan())->get_plan_by_id($subscription->plan_id);
            }

            $plan = $plans[$subscription->plan_id];

            if(!$plan) {
                continue;
            }

            $period_start = $due_datetime->format('Y-m-d H:i:s');
            $period_end = $this->calculate_period_end($period_start, $subscription->frequency);
            $proforma_key = 'proforma_' . md5($subscription->subscription_id . '|' . $period_start . '|' . $days_until_due);

            if(db()->where('external_payment_id', $proforma_key)->has('invoices')) {
                continue;
            }

            $currency = $subscription->currency ?: settings()->payment->default_currency;
            $amount = $this->decimal($subscription->total_amount ?? 0);

            if((float) $amount <= 0) {
                $plan_price = $this->get_plan_frequency_price($plan, $subscription->frequency, $currency);

                if($plan_price === null && $currency != settings()->payment->default_currency) {
                    $currency = settings()->payment->default_currency;
                    $plan_price = $this->get_plan_frequency_price($plan, $subscription->frequency, $currency);
                }

                if($plan_price === null) {
                    continue;
                }

                $amount = $this->decimal($plan_price);
            }

            $total_amount_default_currency = $this->get_total_amount_default_currency(
                $amount,
                $currency,
                $subscription->total_amount_default_currency ?? null
            );

            $metadata = [
                'proforma' => true,
                'proforma_notice_days' => $days_until_due,
                'period_start' => $period_start,
                'period_end' => $period_end,
            ];

            $business = json_decode(json_encode(settings()->business));
            $this->normalize_invoice_prefix($business);

            $invoice_id = db()->insert('invoices', [
                'subscription_id' => (int) $subscription->subscription_id,
                'payment_id' => null,
                'user_id' => (int) $subscription->user_id,
                'plan_id' => (string) $subscription->plan_id,
                'status' => 'draft',
                'processor' => 'proforma',
                'external_payment_id' => $proforma_key,
                'type' => 'proforma',
                'frequency' => $subscription->frequency,
                'code' => $subscription->code,
                'taxes_ids' => $subscription->taxes_ids ?: ($plan->taxes_ids ?? null),
                'subtotal_amount' => $amount,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => $amount,
                'total_amount_default_currency' => $total_amount_default_currency,
                'currency' => $currency,
                'due_datetime' => $period_start,
                'paid_datetime' => null,
                'billing' => settings()->payment->taxes_and_billing_is_enabled && $subscription->user_billing ? $subscription->user_billing : null,
                'business' => json_encode($business),
                'metadata' => json_encode($metadata),
                'datetime' => $now,
            ]);

            db()->insert('invoice_items', [
                'invoice_id' => $invoice_id,
                'item_type' => 'plan',
                'description' => $plan->name ?? $subscription->plan_id,
                'quantity' => 1,
                'unit_amount' => $amount,
                'total_amount' => $amount,
                'currency' => $currency,
                'metadata' => json_encode($metadata),
                'datetime' => $now,
            ]);

            $this->log_event('subscription.proforma_invoice_created', [
                'subscription_id' => $subscription->subscription_id,
                'invoice_id' => $invoice_id,
                'user_id' => $subscription->user_id,
                'source' => 'cron',
                'payload' => $metadata,
            ]);

            $this->send_proforma_invoice_email($subscription, $invoice_id, $plan, $amount, $currency, $period_start, $period_end, $now);

            $created++;
        }

        return $created;
    }

    private function send_proforma_invoice_email($subscription, $invoice_id, $plan, $amount, $currency, $period_start, $period_end, $invoice_datetime) {
        if(empty($subscription->user_email)) {
            return false;
        }

        $language = $subscription->user_language ?: settings()->main->default_language;
        $business = json_decode(json_encode(settings()->business));
        $this->normalize_invoice_prefix($business);
        $invoice_number = ($business->invoice_nr_prefix ?? '') . $invoice_id;
        $invoice = (object) [
            'invoice_id' => (int) $invoice_id,
            'invoice_number' => $invoice_number,
            'subtotal_amount' => $amount,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => $amount,
            'currency' => $currency,
            'datetime' => $invoice_datetime,
            'due_datetime' => $period_start,
            'period_start' => $period_start,
            'period_end' => $period_end,
            'frequency' => $subscription->frequency,
        ];

        $payment_link = $this->get_proforma_payment_link($subscription, $invoice_id, $plan, $amount, $currency);

        $email_content = (new \Altum\View('partials/cron/proforma_invoice'))->run([
            'subscription' => $subscription,
            'invoice' => $invoice,
            'plan' => $plan,
            'invoice_url' => url('invoice/proforma/' . $invoice_id),
            'pay_now_url' => $payment_link['url'],
            'pay_now_processor' => $payment_link['processor'],
            'pay_now_processor_label' => $payment_link['processor_label'],
            'language' => $language,
        ]);

        $subject_template = l('global.emails.proforma_invoice.subject', $language, true) ?? 'Proforma invoice {{INVOICE_NUMBER}} - {{WEBSITE_TITLE}}';
        $subject = str_replace(
            ['{{INVOICE_NUMBER}}', '{{WEBSITE_TITLE}}'],
            [$invoice_number, settings()->main->title],
            $subject_template
        );

        try {
            return send_mail($subscription->user_email, $subject, $email_content, [
                'anti_phishing_code' => $subscription->user_anti_phishing_code ?? null,
                'language' => $language,
            ]);
        } catch(\Throwable $exception) {
            if(DEBUG) {
                echo $exception->getMessage();
            }

            return false;
        }
    }

    private function get_proforma_payment_link($subscription, $invoice_id, $plan, $amount, $currency) {
        if(!settings()->payment->is_enabled || (float) $amount <= 0) {
            return [
                'url' => url('invoice/proforma/' . $invoice_id),
                'processor' => null,
                'processor_label' => null,
            ];
        }

        $frequency_price = $plan->prices->{$subscription->frequency}->{$currency} ?? null;

        if(!$frequency_price) {
            return [
                'url' => url('invoice/proforma/' . $invoice_id),
                'processor' => null,
                'processor_label' => null,
            ];
        }

        $processor = $this->get_best_proforma_payment_processor($currency, $amount, $subscription->processor ?? null);
        $processor_label = $processor ? (l('pay.custom_plan.' . $processor, $subscription->user_language ?? null, true) ?? ucwords(str_replace('_', ' ', $processor))) : null;

        $query = [
            'payment_frequency' => $subscription->frequency,
            'payment_type' => 'one_time',
            'currency' => $currency,
            'invoice_id' => (int) $invoice_id,
        ];

        if($processor) {
            $query['payment_processor'] = $processor;
        }

        return [
            'url' => url('pay/' . $subscription->plan_id . '?' . http_build_query($query)),
            'processor' => $processor,
            'processor_label' => $processor_label,
        ];
    }

    private function get_best_proforma_payment_processor($currency, $amount, $current_processor = null) {
        if((float) $amount <= 0) {
            return null;
        }

        $payment_processors = require APP_PATH . 'includes/payment_processors.php';

        $is_available = function($processor) use ($payment_processors, $currency) {
            return isset($payment_processors[$processor])
                && isset(settings()->{$processor})
                && settings()->{$processor}->is_enabled
                && in_array($currency, settings()->{$processor}->currencies ?? [])
                && in_array('one_time', $payment_processors[$processor]['payment_type']);
        };

        $preferred_processors = [];
        $currency_settings = settings()->payment->currencies->{$currency} ?? null;

        if(!empty($currency_settings->default_payment_processor)) {
            $preferred_processors[] = $currency_settings->default_payment_processor;
        }

        $preferred_processors = array_merge($preferred_processors, match($currency) {
            'KES' => ['paystack', 'flutterwave', 'stripe', 'paypal'],
            'NGN', 'GHS', 'ZAR' => ['paystack', 'flutterwave', 'stripe', 'paypal'],
            'UGX', 'TZS', 'RWF' => ['flutterwave', 'paystack', 'stripe', 'paypal'],
            'EUR' => ['stripe', 'paypal', 'mollie', 'revolut'],
            'USD', 'GBP', 'CAD', 'AUD' => ['stripe', 'paypal'],
            default => ['stripe', 'paypal', 'paystack', 'flutterwave'],
        });

        if($current_processor && $current_processor != 'offline_payment') {
            $preferred_processors[] = $current_processor;
        }

        $preferred_processors[] = 'offline_payment';

        foreach(array_unique($preferred_processors) as $processor) {
            if($is_available($processor)) {
                return $processor;
            }
        }

        foreach(array_keys($payment_processors) as $processor) {
            if($processor == 'offline_payment') {
                continue;
            }

            if($is_available($processor)) {
                return $processor;
            }
        }

        return $is_available('offline_payment') ? 'offline_payment' : null;
    }

    private function normalize_invoice_prefix($business): void {
        $prefix = trim((string) ($business->invoice_nr_prefix ?? ''));

        if($prefix === '' && !empty(settings()->business->invoice_nr_prefix)) {
            $prefix = trim((string) settings()->business->invoice_nr_prefix);
        }

        $business->invoice_nr_prefix = in_array($prefix, ['', 'INV-'], true) ? 'INV-642021' : $prefix;
    }

    public function apply_manual_period($subscription_id, $frequency, $currency = null, $payment_datetime = null) {
        $this->ensure_schema();

        $subscription = $this->get_by_id($subscription_id);

        if(!$subscription || $subscription->status == 'lifetime' || $subscription->frequency == 'lifetime' || $subscription->external_subscription_id) {
            return false;
        }

        if(!in_array($frequency, ['monthly', 'annual'])) {
            return false;
        }

        $plan = (new Plan())->get_plan_by_id($subscription->plan_id);

        if(!$plan || empty($plan->prices)) {
            return false;
        }

        $user = db()->where('user_id', $subscription->user_id)->getOne('users', ['user_id', 'email', 'name', 'billing']);

        if(!$user) {
            return false;
        }

        $currency = $currency ?: ($subscription->currency ?: settings()->payment->default_currency);
        $price = $this->get_plan_frequency_price($plan, $frequency, $currency);

        if($price === null && $currency != settings()->payment->default_currency) {
            $currency = settings()->payment->default_currency;
            $price = $this->get_plan_frequency_price($plan, $frequency, $currency);
        }

        if($price === null) {
            return false;
        }

        $price = $this->decimal($price);

        if($price < 0) {
            return false;
        }

        $total_amount_default_currency = $this->get_total_amount_default_currency($price, $currency);
        $processed_datetime = get_date();
        $payment_datetime = $this->normalize_datetime($payment_datetime) ?? $processed_datetime;
        $period_start = $this->get_renewal_period_start($subscription->current_period_end ?? null, $payment_datetime);
        $period_end = $this->calculate_period_end($period_start, $frequency);
        $status = $this->resolve_paid_subscription_status($frequency, $period_end);

        db()->where('subscription_id', $subscription->subscription_id)->update('subscriptions', [
            'status' => $status,
            'type' => 'one_time',
            'frequency' => $frequency,
            'currency' => $currency,
            'base_amount' => $price,
            'discount_amount' => 0,
            'total_amount' => $price,
            'total_amount_default_currency' => $total_amount_default_currency,
            'taxes_ids' => $plan->taxes_ids ?? null,
            'processor' => 'offline_payment',
            'external_subscription_id' => null,
            'external_payment_id' => null,
            'auto_collection' => 0,
            'current_period_start' => $period_start,
            'current_period_end' => $period_end,
            'cancel_at' => null,
            'canceled_at' => null,
            'pause_start' => null,
            'pause_end' => null,
            'metadata' => json_encode([
                'manual_period' => true,
                'payment_datetime' => $payment_datetime,
                'period_start' => $period_start,
                'price_source' => 'plan.' . $frequency . '.' . $currency,
            ]),
            'last_datetime' => $processed_datetime,
        ]);

        db()->where('subscription_id', $subscription->subscription_id)->delete('subscription_items');
        db()->insert('subscription_items', [
            'subscription_id' => $subscription->subscription_id,
            'item_type' => 'plan',
            'item_id' => $subscription->plan_id,
            'name' => $plan->name ?? null,
            'quantity' => 1,
            'unit_amount' => $price,
            'total_amount' => $price,
            'currency' => $currency,
            'is_recurring' => 1,
            'metadata' => json_encode([
                'manual_period' => true,
                'price_source' => 'plan.' . $frequency . '.' . $currency,
            ]),
            'datetime' => $period_start,
        ]);

        $payment_unique_id = 'manual_' . md5($subscription->subscription_id . $subscription->user_id . $subscription->plan_id . $frequency . $payment_datetime . $processed_datetime);
        $payment_id = db()->insert('payments', [
            'user_id' => $subscription->user_id,
            'plan_id' => $subscription->plan_id,
            'processor' => 'offline_payment',
            'type' => 'one_time',
            'frequency' => $frequency,
            'code' => null,
            'discount_amount' => 0,
            'base_amount' => $price,
            'email' => $user->email,
            'payment_id' => $payment_unique_id,
            'name' => $user->name,
            'plan' => json_encode([
                'plan_id' => $subscription->plan_id,
                'name' => $plan->name ?? $subscription->plan_id,
            ]),
            'billing' => settings()->payment->taxes_and_billing_is_enabled && $user->billing ? $user->billing : null,
            'business' => json_encode(settings()->business),
            'taxes_ids' => $plan->taxes_ids ?? null,
            'total_amount' => $price,
            'total_amount_default_currency' => $total_amount_default_currency ?? 0,
            'currency' => $currency,
            'status' => 1,
            'datetime' => $payment_datetime,
        ]);

        $invoice_id = $this->create_paid_invoice($subscription->subscription_id, [
            'local_payment_id' => $payment_id,
            'external_payment_id' => $payment_unique_id,
            'payment_processor' => 'offline_payment',
            'payment_total' => $price,
            'total_amount_default_currency' => $total_amount_default_currency,
            'payment_currency' => $currency,
            'payment_datetime' => $payment_datetime,
            'invoice_datetime' => $payment_datetime,
            'user_id' => $subscription->user_id,
            'plan_id' => $subscription->plan_id,
            'plan_name' => $plan->name ?? null,
            'payment_frequency' => $frequency,
            'code' => null,
            'discount_amount' => 0,
            'base_amount' => $price,
            'taxes_ids' => $plan->taxes_ids ?? null,
            'payment_type' => 'one_time',
            'payment_subscription_id' => null,
            'period_start' => $period_start,
            'period_end' => $period_end,
            'billing' => settings()->payment->taxes_and_billing_is_enabled && $user->billing ? $user->billing : null,
            'business' => json_encode(settings()->business),
            'metadata' => [
                'manual_period' => true,
                'price_source' => 'plan.' . $frequency . '.' . $currency,
            ],
        ]);

        $this->sync_user_from_subscription($subscription->subscription_id);

        $this->log_event('subscription.manual_period_applied', [
            'subscription_id' => $subscription->subscription_id,
            'invoice_id' => $invoice_id,
            'user_id' => $subscription->user_id,
            'source' => 'admin',
            'datetime' => $payment_datetime,
            'processed_datetime' => $processed_datetime,
            'payload' => [
                'frequency' => $frequency,
                'currency' => $currency,
                'amount' => $price,
                'payment_datetime' => $payment_datetime,
                'period_start' => $period_start,
                'period_end' => $period_end,
            ],
        ]);

        return [
            'subscription_id' => (int) $subscription->subscription_id,
            'invoice_id' => $invoice_id,
            'payment_id' => $payment_id,
            'frequency' => $frequency,
            'currency' => $currency,
            'amount' => $price,
            'period_end' => $period_end,
        ];
    }

    public function refresh_empty_amounts_from_plan_prices($limit = 1000) {
        $this->ensure_schema();

        $limit = (int) $limit;
        $date = get_date();
        $default_currency = settings()->payment->default_currency;
        $plans_model = new Plan();
        $updated = 0;

        $result = database()->query("
            SELECT
                *
            FROM
                `subscriptions`
            WHERE
                `plan_id` NOT IN ('', 'guest', 'free', 'custom')
                AND `frequency` IN ('monthly', 'quarterly', 'biannual', 'annual', 'lifetime')
                AND (
                    `total_amount` IS NULL
                    OR `total_amount` <= 0
                    OR `base_amount` IS NULL
                    OR `base_amount` <= 0
                    OR `currency` IS NULL
                    OR `currency` = ''
                )
            LIMIT {$limit}
        ");

        while($subscription = $result->fetch_object()) {
            $plan = $plans_model->get_plan_by_id($subscription->plan_id);

            if(!$plan || empty($plan->prices)) {
                continue;
            }

            $currency = $subscription->currency ?: $default_currency;
            $price = $this->get_plan_frequency_price($plan, $subscription->frequency, $currency);

            if($price === null && $currency != $default_currency) {
                $currency = $default_currency;
                $price = $this->get_plan_frequency_price($plan, $subscription->frequency, $currency);
            }

            if($price === null || $price <= 0) {
                continue;
            }

            $total_amount_default_currency = $this->get_total_amount_default_currency($price, $currency);

            db()->where('subscription_id', $subscription->subscription_id)->update('subscriptions', [
                'type' => $subscription->type ?: 'one_time',
                'currency' => $currency,
                'base_amount' => $price,
                'discount_amount' => $subscription->discount_amount ?: 0,
                'total_amount' => $price,
                'total_amount_default_currency' => $total_amount_default_currency,
                'taxes_ids' => $subscription->taxes_ids ?: ($plan->taxes_ids ?? null),
                'processor' => $subscription->processor ?: 'offline_payment',
                'last_datetime' => $date,
                'metadata' => $this->append_metadata($subscription->metadata, [
                    'amount_refreshed_from_plan' => true,
                    'price_source' => 'plan.' . $subscription->frequency . '.' . $currency,
                ]),
            ]);

            $item = db()
                ->where('subscription_id', $subscription->subscription_id)
                ->where('item_type', 'plan')
                ->getOne('subscription_items');

            $item_data = [
                'item_id' => $subscription->plan_id,
                'name' => $plan->name ?? null,
                'quantity' => 1,
                'unit_amount' => $price,
                'total_amount' => $price,
                'currency' => $currency,
                'is_recurring' => $subscription->frequency == 'lifetime' ? 0 : 1,
                'metadata' => json_encode([
                    'amount_refreshed_from_plan' => true,
                    'price_source' => 'plan.' . $subscription->frequency . '.' . $currency,
                ]),
            ];

            if($item) {
                db()->where('subscription_item_id', $item->subscription_item_id)->update('subscription_items', $item_data);
            } else {
                $item_data['subscription_id'] = $subscription->subscription_id;
                $item_data['item_type'] = 'plan';
                $item_data['datetime'] = $date;
                db()->insert('subscription_items', $item_data);
            }

            $updated++;
        }

        return $updated;
    }

    public function refresh_default_currency_amounts($limit = 1000) {
        $this->ensure_schema();

        $limit = (int) $limit;
        $default_currency = db()->escape(settings()->payment->default_currency);
        $updated = 0;

        $result = database()->query("
            SELECT
                `subscription_id`,
                `currency`,
                `total_amount`,
                `total_amount_default_currency`
            FROM
                `subscriptions`
            WHERE
                `total_amount` IS NOT NULL
                AND (
                    `total_amount_default_currency` IS NULL
                    OR (`currency` = '{$default_currency}' AND `total_amount_default_currency` != `total_amount`)
                    OR (`currency` != '{$default_currency}' AND `total_amount_default_currency` = `total_amount`)
                )
            LIMIT {$limit}
        ");

        while($subscription = $result->fetch_object()) {
            $total_amount_default_currency = $this->get_total_amount_default_currency(
                $subscription->total_amount,
                $subscription->currency
            );

            if($total_amount_default_currency === null && $subscription->total_amount_default_currency === null) {
                continue;
            }

            db()->where('subscription_id', $subscription->subscription_id)->update('subscriptions', [
                'total_amount_default_currency' => $total_amount_default_currency,
            ]);

            $updated++;
        }

        return $updated;
    }

    public function refresh_invoice_dates_from_payments($limit = 1000) {
        $this->ensure_schema();

        $limit = (int) $limit;
        $updated = 0;

        $result = database()->query("
            SELECT
                `invoices`.`invoice_id`,
                `payments`.`datetime` AS `payment_datetime`
            FROM
                `invoices`
            INNER JOIN
                `payments` ON `payments`.`id` = `invoices`.`payment_id`
            WHERE
                `payments`.`datetime` IS NOT NULL
                AND `payments`.`datetime` != ''
                AND (
                    `invoices`.`paid_datetime` IS NULL
                    OR `invoices`.`paid_datetime` != `payments`.`datetime`
                    OR `invoices`.`datetime` != `payments`.`datetime`
                )
            LIMIT {$limit}
        ");

        while($invoice = $result->fetch_object()) {
            $payment_datetime = $this->normalize_datetime($invoice->payment_datetime);

            if(!$payment_datetime) {
                continue;
            }

            db()->where('invoice_id', $invoice->invoice_id)->update('invoices', [
                'paid_datetime' => $payment_datetime,
                'datetime' => $payment_datetime,
            ]);

            db()->where('invoice_id', $invoice->invoice_id)->update('invoice_items', [
                'datetime' => $payment_datetime,
            ]);

            $this->update_invoice_period_metadata($invoice->invoice_id, $payment_datetime);

            db()
                ->where('invoice_id', $invoice->invoice_id)
                ->where('event_type', ['subscription.synced', 'subscription.manual_period_applied'], 'IN')
                ->update('billing_events', [
                    'datetime' => $payment_datetime,
                ]);

            $updated++;
        }

        return $updated;
    }

    public function update_invoice_paid_datetime($invoice_id, $paid_datetime) {
        $this->ensure_schema();

        $invoice_id = (int) $invoice_id;
        $paid_datetime = $this->normalize_datetime($paid_datetime);

        if(!$invoice_id || !$paid_datetime) {
            return false;
        }

        $invoice = $this->get_invoice_by_id($invoice_id);

        if(!$invoice) {
            return false;
        }

        db()->where('invoice_id', $invoice_id)->update('invoices', [
            'paid_datetime' => $paid_datetime,
            'datetime' => $paid_datetime,
        ]);

        db()->where('invoice_id', $invoice_id)->update('invoice_items', [
            'datetime' => $paid_datetime,
        ]);

        $this->update_invoice_period_metadata($invoice_id, $paid_datetime, $invoice);

        if(!empty($invoice->payment_id)) {
            db()->where('id', (int) $invoice->payment_id)->update('payments', [
                'datetime' => $paid_datetime,
            ]);
        }

        $events = db()
            ->where('invoice_id', $invoice_id)
            ->where('event_type', ['subscription.synced', 'subscription.manual_period_applied'], 'IN')
            ->get('billing_events');

        foreach($events as $event) {
            $payload = json_decode($event->payload ?? '', true);

            if(is_array($payload) && array_key_exists('payment_datetime', $payload)) {
                $payload['payment_datetime'] = $paid_datetime;
            }

            db()->where('billing_event_id', $event->billing_event_id)->update('billing_events', [
                'datetime' => $paid_datetime,
                'payload' => is_array($payload) ? json_encode($payload) : $event->payload,
            ]);
        }

        return true;
    }

    private function update_invoice_period_metadata($invoice_id, $period_start, $invoice = null) {
        $invoice_id = (int) $invoice_id;
        $period_start = $this->normalize_datetime($period_start);

        if(!$invoice_id || !$period_start) {
            return false;
        }

        $invoice = $invoice ?: $this->get_invoice_by_id($invoice_id);

        if(!$invoice || $invoice->status != 'paid') {
            return false;
        }

        $metadata = json_decode($invoice->metadata ?? '', true);

        if(!is_array($metadata)) {
            $metadata = [];
        }

        $metadata['period_start'] = $period_start;

        if(!empty($invoice->frequency)) {
            $metadata['period_end'] = $this->calculate_period_end($period_start, $invoice->frequency);
        }

        db()->where('invoice_id', $invoice_id)->update('invoices', [
            'metadata' => json_encode($metadata),
        ]);

        $invoice_items = db()->where('invoice_id', $invoice_id)->get('invoice_items');

        foreach($invoice_items as $invoice_item) {
            $item_metadata = json_decode($invoice_item->metadata ?? '', true);

            if(!is_array($item_metadata)) {
                $item_metadata = [];
            }

            $item_metadata['period_start'] = $metadata['period_start'];

            if(isset($metadata['period_end'])) {
                $item_metadata['period_end'] = $metadata['period_end'];
            }

            db()->where('invoice_item_id', $invoice_item->invoice_item_id)->update('invoice_items', [
                'metadata' => json_encode($item_metadata),
            ]);
        }

        return true;
    }

    public function sync_from_user_plan($user_id, $source = 'admin_user_update', array $options = []) {
        $this->ensure_schema();

        $user = db()->where('user_id', (int) $user_id)->getOne('users');

        if(!$user) {
            return false;
        }

        $subscription = $this->get_active_by_user_id($user->user_id);
        $date = get_date();

        if(!$user->plan_id || in_array($user->plan_id, ['guest', 'free', 'custom']) || empty($user->plan_expiration_date)) {
            if($subscription && !in_array($subscription->status, ['canceled', 'expired'])) {
                db()->where('subscription_id', $subscription->subscription_id)->update('subscriptions', [
                    'status' => 'canceled',
                    'auto_collection' => 0,
                    'cancel_at' => $date,
                    'canceled_at' => $date,
                    'last_datetime' => $date,
                    'metadata' => $this->append_metadata($subscription->metadata, [
                        'synced_from_user_plan' => true,
                        'user_plan_id' => $user->plan_id,
                    ]),
                ]);

                $this->log_event('subscription.synced_from_user', [
                    'subscription_id' => $subscription->subscription_id,
                    'user_id' => $user->user_id,
                    'source' => $source,
                    'payload' => [
                        'action' => 'canceled',
                        'user_plan_id' => $user->plan_id,
                    ],
                ]);
            }

            return false;
        }

        $plan = (new Plan())->get_plan_by_id($user->plan_id);

        if(!$plan) {
            return false;
        }

        $frequency = $this->infer_user_plan_frequency($user, $subscription);
        $status = $this->resolve_paid_subscription_status($frequency, $user->plan_expiration_date);
        $currency = $user->payment_currency ?: settings()->payment->default_currency;
        $price = $this->get_plan_frequency_price($plan, $frequency, $currency);

        if($price === null && $currency != settings()->payment->default_currency) {
            $currency = settings()->payment->default_currency;
            $price = $this->get_plan_frequency_price($plan, $frequency, $currency);
        }

        if($price === null) {
            $price = $this->decimal($user->payment_total_amount ?? 0);
        }

        $latest_payment = db()
            ->where('user_id', $user->user_id)
            ->where('plan_id', $user->plan_id)
            ->where('status', 1)
            ->orderBy('id', 'DESC')
            ->getOne('payments', ['datetime']);
        $event_datetime = $this->normalize_datetime($options['event_datetime'] ?? ($options['payment_datetime'] ?? ($latest_payment->datetime ?? null))) ?? $date;
        $explicit_period_start = $this->normalize_datetime($options['period_start'] ?? null);
        $derived_period_start = $this->calculate_period_start_from_end($user->plan_expiration_date, $frequency);
        $period_start = $explicit_period_start
            ?? $derived_period_start
            ?? ($subscription->current_period_start ?? $event_datetime);

        $total_amount_default_currency = $this->get_total_amount_default_currency($price, $currency);
        $external_subscription_id = $user->payment_subscription_id ?: ($subscription->external_subscription_id ?? null);
        $processor = $user->payment_processor ?: ($subscription->processor ?? 'offline_payment');
        $subscription_data = [
            'user_id' => $user->user_id,
            'plan_id' => $user->plan_id,
            'status' => $status,
            'type' => $external_subscription_id ? 'recurring' : ($subscription->type ?? 'one_time'),
            'frequency' => $frequency,
            'currency' => $currency,
            'base_amount' => $price,
            'discount_amount' => $subscription->discount_amount ?? 0,
            'total_amount' => $price,
            'total_amount_default_currency' => $total_amount_default_currency,
            'taxes_ids' => $subscription->taxes_ids ?? ($plan->taxes_ids ?? null),
            'processor' => $processor,
            'external_subscription_id' => $external_subscription_id ?: null,
            'auto_collection' => $external_subscription_id ? 1 : 0,
            'current_period_start' => $period_start,
            'current_period_end' => $user->plan_expiration_date,
            'cancel_at' => null,
            'canceled_at' => null,
            'pause_start' => null,
            'pause_end' => null,
            'metadata' => json_encode([
                'synced_from_user_plan' => true,
                'source' => $source,
            ]),
            'last_datetime' => $date,
        ];

        if($subscription) {
            db()->where('subscription_id', $subscription->subscription_id)->update('subscriptions', $subscription_data);
            $subscription_id = (int) $subscription->subscription_id;
        } else {
            $subscription_data['datetime'] = $date;
            $subscription_id = db()->insert('subscriptions', $subscription_data);
        }

        db()->where('subscription_id', $subscription_id)->delete('subscription_items');
        db()->insert('subscription_items', [
            'subscription_id' => $subscription_id,
            'item_type' => 'plan',
            'item_id' => $user->plan_id,
            'name' => $plan->name ?? null,
            'quantity' => 1,
            'unit_amount' => $price,
            'total_amount' => $price,
            'currency' => $currency,
            'is_recurring' => $frequency == 'lifetime' ? 0 : 1,
            'metadata' => json_encode([
                'synced_from_user_plan' => true,
                'price_source' => 'plan.' . $frequency . '.' . $currency,
            ]),
            'datetime' => $period_start,
        ]);

        $this->log_event('subscription.synced_from_user', [
            'subscription_id' => $subscription_id,
            'user_id' => $user->user_id,
            'source' => $source,
            'datetime' => $event_datetime,
            'processed_datetime' => $date,
            'payload' => [
                'plan_id' => $user->plan_id,
                'status' => $status,
                'frequency' => $frequency,
                'currency' => $currency,
                'amount' => $price,
                'period_start' => $period_start,
                'period_end' => $user->plan_expiration_date,
            ],
        ]);

        return $subscription_id;
    }

    public function cancel($subscription_id, $reason = null, $cancel_at_period_end = true) {
        $this->ensure_schema();

        $subscription = $this->get_by_id($subscription_id);

        if(!$subscription) {
            return false;
        }

        $date = get_date();
        $status = $cancel_at_period_end && $subscription->current_period_end && (new \DateTime($subscription->current_period_end)) > new \DateTime()
            ? 'non_renewing'
            : 'canceled';

        db()->where('subscription_id', $subscription->subscription_id)->update('subscriptions', [
            'status' => $status,
            'auto_collection' => 0,
            'cancel_at' => $status == 'non_renewing' ? $subscription->current_period_end : $date,
            'canceled_at' => $date,
            'last_datetime' => $date,
            'metadata' => $this->append_metadata($subscription->metadata, ['cancel_reason' => $reason]),
        ]);

        if($status == 'canceled') {
            $this->downgrade_user_to_free($subscription->user_id);
        } else {
            db()->where('user_id', $subscription->user_id)->update('users', ['payment_subscription_id' => '']);
            cache()->deleteItemsByTag('user_id=' . $subscription->user_id);
        }

        $this->log_event('subscription.canceled', [
            'subscription_id' => $subscription->subscription_id,
            'user_id' => $subscription->user_id,
            'source' => 'app',
            'payload' => ['reason' => $reason, 'status' => $status],
        ]);

        return true;
    }

    public function pause($subscription_id, $pause_end = null) {
        $this->ensure_schema();

        $subscription = $this->get_by_id($subscription_id);

        if(!$subscription || !in_array($subscription->status, ['trialing', 'active', 'past_due', 'non_renewing'])) {
            return false;
        }

        $date = get_date();

        db()->where('subscription_id', $subscription->subscription_id)->update('subscriptions', [
            'status' => 'paused',
            'pause_start' => $date,
            'pause_end' => $pause_end,
            'last_datetime' => $date,
        ]);

        db()->where('user_id', $subscription->user_id)->update('users', ['payment_subscription_id' => '']);
        cache()->deleteItemsByTag('user_id=' . $subscription->user_id);

        $this->log_event('subscription.paused', [
            'subscription_id' => $subscription->subscription_id,
            'user_id' => $subscription->user_id,
            'source' => 'app',
            'payload' => ['pause_end' => $pause_end],
        ]);

        return true;
    }

    public function resume($subscription_id) {
        $this->ensure_schema();

        $subscription = $this->get_by_id($subscription_id);

        if(!$subscription || !in_array($subscription->status, ['paused', 'past_due', 'non_renewing', 'canceled'])) {
            return false;
        }

        $date = get_date();

        db()->where('subscription_id', $subscription->subscription_id)->update('subscriptions', [
            'status' => 'active',
            'auto_collection' => $subscription->external_subscription_id ? 1 : 0,
            'cancel_at' => null,
            'canceled_at' => null,
            'pause_start' => null,
            'pause_end' => null,
            'last_datetime' => $date,
        ]);

        $this->sync_user_from_subscription($subscription->subscription_id);

        $this->log_event('subscription.resumed', [
            'subscription_id' => $subscription->subscription_id,
            'user_id' => $subscription->user_id,
            'source' => 'app',
        ]);

        return true;
    }

    public function mark_past_due($subscription_id, $invoice_id = null, $reason = null) {
        $this->ensure_schema();

        $subscription = $this->get_by_id($subscription_id);

        if(!$subscription) {
            return false;
        }

        $date = get_date();

        db()->where('subscription_id', $subscription->subscription_id)->update('subscriptions', [
            'status' => 'past_due',
            'last_datetime' => $date,
        ]);

        if($invoice_id) {
            db()->where('invoice_id', (int) $invoice_id)->update('invoices', [
                'status' => 'payment_due',
            ]);
        }

        $this->log_event('subscription.payment_due', [
            'subscription_id' => $subscription->subscription_id,
            'invoice_id' => $invoice_id,
            'user_id' => $subscription->user_id,
            'source' => 'app',
            'payload' => ['reason' => $reason],
        ]);

        return true;
    }

    public function sync_user_from_subscription($subscription_id) {
        $this->ensure_schema();

        $subscription = $this->get_by_id($subscription_id);

        if(!$subscription) {
            return false;
        }

        $plan = (new Plan())->get_plan_by_id($subscription->plan_id);

        if(!$plan) {
            return false;
        }

        db()->where('user_id', $subscription->user_id)->update('users', [
            'plan_id' => $subscription->plan_id,
            'plan_settings' => json_encode($plan->settings),
            'plan_expiration_date' => $subscription->current_period_end,
            'plan_expiry_reminder' => 0,
            'payment_subscription_id' => $subscription->external_subscription_id,
            'payment_processor' => $subscription->processor,
            'payment_total_amount' => $subscription->total_amount,
            'payment_currency' => $subscription->currency,
        ]);

        cache()->deleteItemsByTag('user_id=' . $subscription->user_id);

        return true;
    }

    public function get_entitlements($subscription_id) {
        $this->ensure_schema();

        $subscription = $this->get_by_id($subscription_id);

        if(!$subscription) {
            return (object) [];
        }

        $plan = (new Plan())->get_plan_by_id($subscription->plan_id);
        $entitlements = json_decode(json_encode($plan->settings ?? (object) []), true);

        $plan_entitlements = db()->where('plan_id', $subscription->plan_id)->get('plan_entitlements');
        foreach($plan_entitlements as $row) {
            $entitlements[$row->feature_key] = json_decode($row->value ?? 'null');
        }

        $overrides = db()->where('subscription_id', $subscription_id)->get('subscription_entitlement_overrides');
        foreach($overrides as $row) {
            $entitlements[$row->feature_key] = json_decode($row->value ?? 'null');
        }

        return json_decode(json_encode($entitlements));
    }

    public function process_dunning() {
        $this->ensure_schema();

        $now = get_date();
        $processed = 0;

        $result = database()->query("
            SELECT
                `invoices`.*,
                `subscriptions`.`status` AS `subscription_status`
            FROM
                `invoices`
            LEFT JOIN
                `subscriptions` ON `invoices`.`subscription_id` = `subscriptions`.`subscription_id`
            WHERE
                `invoices`.`status` IN ('open', 'payment_due', 'past_due')
                AND (`invoices`.`due_datetime` IS NULL OR `invoices`.`due_datetime` <= '{$now}')
            LIMIT 100
        ");

        while($invoice = $result->fetch_object()) {
            $last_attempt = db()
                ->where('invoice_id', $invoice->invoice_id)
                ->orderBy('attempt_number', 'DESC')
                ->getOne('dunning_attempts');

            $attempt_number = $last_attempt ? ((int) $last_attempt->attempt_number + 1) : 1;
            $delay_days = [1 => 0, 2 => 3, 3 => 7][$attempt_number] ?? null;

            if($delay_days === null) {
                db()->where('invoice_id', $invoice->invoice_id)->update('invoices', ['status' => 'uncollectible']);

                if($invoice->subscription_id) {
                    db()->where('subscription_id', $invoice->subscription_id)->update('subscriptions', [
                        'status' => 'canceled',
                        'auto_collection' => 0,
                        'cancel_at' => $now,
                        'canceled_at' => $now,
                        'last_datetime' => $now,
                    ]);
                    $this->downgrade_user_to_free($invoice->user_id);
                }

                $this->log_event('dunning.finalized', [
                    'subscription_id' => $invoice->subscription_id,
                    'invoice_id' => $invoice->invoice_id,
                    'user_id' => $invoice->user_id,
                    'source' => 'cron',
                ]);

                $processed++;
                continue;
            }

            $scheduled_datetime = (new \DateTime($now))->modify('+' . $delay_days . ' days')->format('Y-m-d H:i:s');
            $status = $delay_days ? 'scheduled' : 'sent';

            db()->insert('dunning_attempts', [
                'subscription_id' => $invoice->subscription_id,
                'invoice_id' => $invoice->invoice_id,
                'user_id' => $invoice->user_id,
                'attempt_number' => $attempt_number,
                'status' => $status,
                'scheduled_datetime' => $scheduled_datetime,
                'attempted_datetime' => $delay_days ? null : $now,
                'metadata' => json_encode([]),
                'datetime' => $now,
            ]);

            db()->where('invoice_id', $invoice->invoice_id)->update('invoices', ['status' => 'past_due']);

            if($invoice->subscription_id) {
                db()->where('subscription_id', $invoice->subscription_id)->update('subscriptions', ['status' => 'past_due']);
            }

            $this->log_event('dunning.attempt_scheduled', [
                'subscription_id' => $invoice->subscription_id,
                'invoice_id' => $invoice->invoice_id,
                'user_id' => $invoice->user_id,
                'source' => 'cron',
                'payload' => ['attempt_number' => $attempt_number, 'scheduled_datetime' => $scheduled_datetime],
            ]);

            $processed++;
        }

        return $processed;
    }

    public function downgrade_user_to_free($user_id) {
        db()->where('user_id', (int) $user_id)->update('users', [
            'plan_id' => 'free',
            'plan_settings' => json_encode(settings()->plan_free->settings),
            'payment_subscription_id' => '',
            'payment_processor' => '',
        ]);

        cache()->deleteItemsByTag('user_id=' . (int) $user_id);
    }

    public function log_event($event_type, array $data = []) {
        $this->ensure_schema();

        if(!empty($data['idempotency_key']) && db()->where('idempotency_key', $data['idempotency_key'])->has('billing_events')) {
            return null;
        }

        return db()->insert('billing_events', [
            'subscription_id' => $data['subscription_id'] ?? null,
            'invoice_id' => $data['invoice_id'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'event_type' => $event_type,
            'source' => $data['source'] ?? 'app',
            'idempotency_key' => $data['idempotency_key'] ?? null,
            'payload' => json_encode($data['payload'] ?? []),
            'processed_datetime' => $this->normalize_datetime($data['processed_datetime'] ?? null) ?? get_date(),
            'datetime' => $this->normalize_datetime($data['datetime'] ?? null) ?? get_date(),
        ]);
    }

    public function calculate_renewal_period($current_period_end, $frequency, $payment_datetime = null) {
        $payment_datetime = $this->normalize_datetime($payment_datetime) ?? get_date();
        $period_start = $this->get_renewal_period_start($current_period_end, $payment_datetime);

        return [
            'payment_datetime' => $payment_datetime,
            'period_start' => $period_start,
            'period_end' => $this->calculate_period_end($period_start, $frequency),
        ];
    }

    public function calculate_period_end($start, $frequency) {
        $modifier = match ($frequency) {
            'monthly' => '+30 days +12 hours',
            'quarterly' => '+3 months +12 hours',
            'biannual' => '+6 months +12 hours',
            'annual' => '+12 months +12 hours',
            'lifetime' => '+100 years +12 hours',
            default => '+30 days +12 hours',
        };

        return (new \DateTime($start))->modify($modifier)->format('Y-m-d H:i:s');
    }

    private function get_renewal_period_start($current_period_end, $payment_datetime) {
        $payment_datetime = $this->normalize_datetime($payment_datetime) ?? get_date();

        if($current_period_end) {
            try {
                $current_period_end_date = new \DateTime($current_period_end);

                if($current_period_end_date > new \DateTime($payment_datetime)) {
                    return $current_period_end_date->format('Y-m-d H:i:s');
                }
            } catch(\Exception $exception) {
                /* Fall back to the payment date for malformed imported dates. */
            }
        }

        return $payment_datetime;
    }

    private function calculate_period_start_from_end($period_end, $frequency) {
        if(!$period_end || $frequency == 'lifetime') {
            return null;
        }

        $modifier = match ($frequency) {
            'monthly' => '-30 days -12 hours',
            'quarterly' => '-3 months -12 hours',
            'biannual' => '-6 months -12 hours',
            'annual' => '-12 months -12 hours',
            default => null,
        };

        if(!$modifier) {
            return null;
        }

        try {
            return (new \DateTime($period_end))->modify($modifier)->format('Y-m-d H:i:s');
        } catch(\Exception $exception) {
            return null;
        }
    }

    private function normalize_datetime($value) {
        if(!$value) {
            return null;
        }

        try {
            return (new \DateTime(str_replace('T', ' ', $value)))->format('Y-m-d H:i:s');
        } catch(\Exception $exception) {
            return null;
        }
    }

    private function get_plan_frequency_price($plan, $frequency, $currency) {
        if(!$plan || empty($plan->prices->{$frequency}) || !isset($plan->prices->{$frequency}->{$currency})) {
            return null;
        }

        return $this->decimal($plan->prices->{$frequency}->{$currency});
    }

    private function infer_user_plan_frequency($user, $subscription = null) {
        $allowed_frequencies = ['monthly', 'quarterly', 'biannual', 'annual', 'lifetime'];

        $payment = db()
            ->where('user_id', $user->user_id)
            ->where('plan_id', $user->plan_id)
            ->where('status', 1)
            ->orderBy('id', 'DESC')
            ->getOne('payments', ['frequency']);

        if($payment && in_array($payment->frequency, $allowed_frequencies)) {
            return $payment->frequency;
        }

        if($subscription && $subscription->plan_id == $user->plan_id && in_array($subscription->frequency, $allowed_frequencies)) {
            return $subscription->frequency;
        }

        try {
            $expiration_date = new \DateTime($user->plan_expiration_date);
            $now = new \DateTime();

            if($expiration_date > (clone $now)->modify('+10 years')) {
                return 'lifetime';
            }

            if($expiration_date > (clone $now)->modify('+330 days')) {
                return 'annual';
            }
        } catch(\Exception $exception) {
            /* Fall through to monthly for malformed admin-entered dates. */
        }

        return 'monthly';
    }

    private function get_total_amount_default_currency($amount, $currency, $provided_default_amount = null) {
        $amount = $this->decimal($amount);
        $currency = $currency ?: settings()->payment->default_currency;
        $default_currency = settings()->payment->default_currency;

        if((float) $amount == 0 || $currency == $default_currency) {
            return $amount;
        }

        if($provided_default_amount !== null && $provided_default_amount !== '' && (float) $provided_default_amount != (float) $amount) {
            return $this->decimal($provided_default_amount);
        }

        if(!settings()->payment->currency_exchange_api_key) {
            return null;
        }

        try {
            $response = \Unirest\Request::get('https://api.freecurrencyapi.com/v1/latest?apikey=' . settings()->payment->currency_exchange_api_key . '&base_currency=' . $currency . '&currencies=' . $default_currency);

            if($response->code == 200 && isset($response->body->data->{$default_currency})) {
                return $this->decimal((float) $amount * (float) $response->body->data->{$default_currency});
            }
        } catch(\Exception $exception) {
            /* Keep the source amount untouched if conversion cannot be resolved. */
        }

        return null;
    }

    private function decimal($value) {
        return number_format((float) $value, 4, '.', '');
    }

    private function append_metadata($metadata, array $new_data) {
        $decoded = json_decode($metadata ?? '', true);

        if(!is_array($decoded)) {
            $decoded = [];
        }

        return json_encode(array_merge($decoded, $new_data));
    }

}
