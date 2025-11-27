-- Add plans and subscriptions tables (for subscription feature)
-- Ensures foreign key references match existing `customer` table
-- Run in the `shoppn` database

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

-- Create plans table
CREATE TABLE IF NOT EXISTS `plans` (
  `plan_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(10) NOT NULL DEFAULT 'GHS',
  `paystack_plan_code` varchar(150) DEFAULT NULL,
  `features` text DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NULL,
  PRIMARY KEY (`plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create subscriptions table (references customer.customer_id and plans.plan_id)
CREATE TABLE IF NOT EXISTS `subscriptions` (
  `subscription_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `paystack_subscription_id` varchar(150) DEFAULT NULL,
  `status` varchar(40) NOT NULL DEFAULT 'pending',
  `started_at` datetime DEFAULT NULL,
  `next_billing_at` datetime DEFAULT NULL,
  `canceled_at` datetime DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(10) NOT NULL DEFAULT 'GHS',
  `auto_renew` tinyint(1) DEFAULT 1,
  `metadata` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`subscription_id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_plan` (`plan_id`),
  CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  -- Most DBs in this project use `plan_id` as the plans primary key. If your `plans` table uses `id`, change the next line to reference `plans(id)` instead.
  CONSTRAINT `subscriptions_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`plan_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default plan rows
INSERT INTO `plans` (`name`, `price`, `currency`, `paystack_plan_code`, `features`, `is_public`) VALUES
('Basic', 0.00, 'GHS', NULL, '["Standard visibility","Basic features"]', 1),
('Pro', 150.00, 'GHS', NULL, '["Featured search results","Unlimited portfolio images","Prioritised customer service","Advanced dashboard analytics"]', 1),
('Premium', 300.00, 'GHS', NULL, '["All Pro features","Special homepage feature placement","Promotional opportunities","Designated Account Manager (opt-in)"]', 1);

-- IMPORTANT:
-- If your database already has a `plans` table that uses `id` as the column name for the primary key,
-- change the FK above from `plans (plan_id)` to `plans (id)` before running this migration in your environment.
-- To check the plans schema, run:
-- SHOW CREATE TABLE plans;

