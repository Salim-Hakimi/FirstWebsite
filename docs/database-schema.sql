-- Fanous Dormitory, Library, and Finance System
-- Complete MySQL/MariaDB database schema based on Laravel migrations.
-- Run this file on an empty database.
-- Optional:
-- CREATE DATABASE `fanous` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE `fanous`;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `finance_audit_logs`;
DROP TABLE IF EXISTS `finance_attachments`;
DROP TABLE IF EXISTS `student_payments`;
DROP TABLE IF EXISTS `finance_transactions`;
DROP TABLE IF EXISTS `finance_projects`;
DROP TABLE IF EXISTS `finance_donors`;
DROP TABLE IF EXISTS `finance_categories`;
DROP TABLE IF EXISTS `membership_cards`;
DROP TABLE IF EXISTS `book_loans`;
DROP TABLE IF EXISTS `book_copies`;
DROP TABLE IF EXISTS `books`;
DROP TABLE IF EXISTS `library_members`;
DROP TABLE IF EXISTS `dorm_expenses`;
DROP TABLE IF EXISTS `food_finances`;
DROP TABLE IF EXISTS `student_collections`;
DROP TABLE IF EXISTS `dorm_students`;
DROP TABLE IF EXISTS `dorm_rooms`;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `failed_jobs`;
DROP TABLE IF EXISTS `job_batches`;
DROP TABLE IF EXISTS `jobs`;
DROP TABLE IF EXISTS `cache_locks`;
DROP TABLE IF EXISTS `cache`;
DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `password_reset_tokens`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `migrations`;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `migrations` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `migration` VARCHAR(255) NOT NULL,
    `batch` INT NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(30) NULL,
    `role` VARCHAR(255) NOT NULL DEFAULT 'guard',
    `status` VARCHAR(30) NOT NULL DEFAULT 'active',
    `theme` VARCHAR(20) NOT NULL DEFAULT 'light',
    `profile_photo_path` VARCHAR(255) NULL,
    `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `remember_token` VARCHAR(100) NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_reset_tokens` (
    `email` VARCHAR(255) NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sessions` (
    `id` VARCHAR(255) NOT NULL,
    `user_id` BIGINT UNSIGNED NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `payload` LONGTEXT NOT NULL,
    `last_activity` INT NOT NULL,
    PRIMARY KEY (`id`),
    KEY `sessions_user_id_index` (`user_id`),
    KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache` (
    `key` VARCHAR(255) NOT NULL,
    `value` MEDIUMTEXT NOT NULL,
    `expiration` INT NOT NULL,
    PRIMARY KEY (`key`),
    KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache_locks` (
    `key` VARCHAR(255) NOT NULL,
    `owner` VARCHAR(255) NOT NULL,
    `expiration` INT NOT NULL,
    PRIMARY KEY (`key`),
    KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jobs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `queue` VARCHAR(255) NOT NULL,
    `payload` LONGTEXT NOT NULL,
    `attempts` TINYINT UNSIGNED NOT NULL,
    `reserved_at` INT UNSIGNED NULL,
    `available_at` INT UNSIGNED NOT NULL,
    `created_at` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_batches` (
    `id` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `total_jobs` INT NOT NULL,
    `pending_jobs` INT NOT NULL,
    `failed_jobs` INT NOT NULL,
    `failed_job_ids` LONGTEXT NOT NULL,
    `options` MEDIUMTEXT NULL,
    `cancelled_at` INT NULL,
    `created_at` INT NOT NULL,
    `finished_at` INT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `failed_jobs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `uuid` VARCHAR(255) NOT NULL,
    `connection` TEXT NOT NULL,
    `queue` TEXT NOT NULL,
    `payload` LONGTEXT NOT NULL,
    `exception` LONGTEXT NOT NULL,
    `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `audit_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NULL,
    `action` VARCHAR(80) NOT NULL,
    `model_type` VARCHAR(255) NULL,
    `model_id` BIGINT UNSIGNED NULL,
    `old_values` JSON NULL,
    `new_values` JSON NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(500) NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `audit_logs_user_id_foreign` (`user_id`),
    KEY `audit_logs_model_type_model_id_index` (`model_type`, `model_id`),
    KEY `audit_logs_action_created_at_index` (`action`, `created_at`),
    CONSTRAINT `audit_logs_user_id_foreign`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `dorm_rooms` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `room_number` VARCHAR(40) NOT NULL,
    `capacity` TINYINT UNSIGNED NOT NULL,
    `floor` VARCHAR(40) NULL,
    `status` VARCHAR(40) NOT NULL DEFAULT 'active',
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `dorm_rooms_room_number_unique` (`room_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `dorm_students` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `registered_by` BIGINT UNSIGNED NULL,
    `dorm_room_id` BIGINT UNSIGNED NULL,
    `admission_decision_by` BIGINT UNSIGNED NULL,
    `full_name` VARCHAR(120) NOT NULL,
    `father_name` VARCHAR(120) NOT NULL,
    `phone` VARCHAR(30) NOT NULL,
    `whatsapp` VARCHAR(30) NULL,
    `email` VARCHAR(120) NULL,
    `tazkira_number` VARCHAR(80) NOT NULL,
    `education_place` VARCHAR(160) NOT NULL,
    `department_or_grade` VARCHAR(160) NULL,
    `province` VARCHAR(80) NULL,
    `room_number` VARCHAR(40) NULL,
    `bed_number` VARCHAR(40) NULL,
    `guarantor_name` VARCHAR(120) NULL,
    `guarantor_relation` VARCHAR(80) NULL,
    `guarantor_phone` VARCHAR(30) NULL,
    `guarantor_tazkira_number` VARCHAR(80) NULL,
    `guarantor_job` VARCHAR(120) NULL,
    `guarantor_permanent_address` VARCHAR(255) NULL,
    `guarantor_current_address` VARCHAR(255) NULL,
    `document_names` JSON NULL,
    `profile_photo_path` VARCHAR(255) NULL,
    `application_date` DATE NULL,
    `education_score` DECIMAL(5,2) NULL,
    `eligibility_score` TINYINT UNSIGNED NULL,
    `eligibility_notes` TEXT NULL,
    `guarantee_deposit_amount` INT UNSIGNED NOT NULL DEFAULT 1000,
    `dorm_expense_fee_amount` INT UNSIGNED NOT NULL DEFAULT 1000,
    `registration_card_fee_amount` INT UNSIGNED NOT NULL DEFAULT 50,
    `registration_payment_status` VARCHAR(30) NOT NULL DEFAULT 'paid',
    `registration_paid_at` DATE NULL,
    `admitted_at` TIMESTAMP NULL DEFAULT NULL,
    `status` VARCHAR(40) NOT NULL DEFAULT 'active',
    `joined_at` DATE NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `dorm_students_registered_by_foreign` (`registered_by`),
    KEY `dorm_students_dorm_room_id_foreign` (`dorm_room_id`),
    KEY `dorm_students_admission_decision_by_foreign` (`admission_decision_by`),
    CONSTRAINT `dorm_students_registered_by_foreign`
        FOREIGN KEY (`registered_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `dorm_students_dorm_room_id_foreign`
        FOREIGN KEY (`dorm_room_id`) REFERENCES `dorm_rooms` (`id`) ON DELETE SET NULL,
    CONSTRAINT `dorm_students_admission_decision_by_foreign`
        FOREIGN KEY (`admission_decision_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `student_collections` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `dorm_student_id` BIGINT UNSIGNED NULL,
    `recorded_by` BIGINT UNSIGNED NULL,
    `type` VARCHAR(40) NOT NULL,
    `amount` INT UNSIGNED NOT NULL,
    `collected_at` DATE NOT NULL,
    `period` VARCHAR(80) NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `student_collections_dorm_student_id_foreign` (`dorm_student_id`),
    KEY `student_collections_recorded_by_foreign` (`recorded_by`),
    CONSTRAINT `student_collections_dorm_student_id_foreign`
        FOREIGN KEY (`dorm_student_id`) REFERENCES `dorm_students` (`id`) ON DELETE SET NULL,
    CONSTRAINT `student_collections_recorded_by_foreign`
        FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `food_finances` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `dorm_student_id` BIGINT UNSIGNED NULL,
    `recorded_by` BIGINT UNSIGNED NULL,
    `type` VARCHAR(40) NOT NULL,
    `amount` INT UNSIGNED NOT NULL,
    `recorded_at` DATE NOT NULL,
    `period` VARCHAR(80) NULL,
    `vendor_or_source` VARCHAR(160) NULL,
    `description` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `food_finances_dorm_student_id_foreign` (`dorm_student_id`),
    KEY `food_finances_recorded_by_foreign` (`recorded_by`),
    CONSTRAINT `food_finances_dorm_student_id_foreign`
        FOREIGN KEY (`dorm_student_id`) REFERENCES `dorm_students` (`id`) ON DELETE SET NULL,
    CONSTRAINT `food_finances_recorded_by_foreign`
        FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `dorm_expenses` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `created_by` BIGINT UNSIGNED NULL,
    `category` VARCHAR(40) NOT NULL,
    `title` VARCHAR(160) NOT NULL,
    `amount` DECIMAL(12,2) NOT NULL,
    `spent_on` DATE NOT NULL,
    `paid_to` VARCHAR(160) NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `dorm_expenses_created_by_foreign` (`created_by`),
    CONSTRAINT `dorm_expenses_created_by_foreign`
        FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `library_members` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `registered_by` BIGINT UNSIGNED NULL,
    `member_code` VARCHAR(60) NULL,
    `full_name` VARCHAR(120) NOT NULL,
    `father_name` VARCHAR(120) NULL,
    `phone` VARCHAR(30) NOT NULL,
    `email` VARCHAR(120) NULL,
    `tazkira_number` VARCHAR(80) NULL,
    `education_place` VARCHAR(160) NULL,
    `department_or_grade` VARCHAR(160) NULL,
    `address` VARCHAR(220) NULL,
    `profile_photo_path` VARCHAR(255) NULL,
    `membership_fee` INT UNSIGNED NOT NULL DEFAULT 0,
    `monthly_fee_daily_fine` INT UNSIGNED NOT NULL DEFAULT 20,
    `monthly_fee_fine_amount` INT UNSIGNED NOT NULL DEFAULT 0,
    `payment_status` VARCHAR(30) NOT NULL DEFAULT 'unpaid',
    `last_paid_at` DATE NULL,
    `next_payment_due_at` DATE NULL,
    `last_fee_reminder_at` DATE NULL,
    `joined_at` DATE NULL,
    `membership_expires_at` DATE NULL,
    `status` VARCHAR(40) NOT NULL DEFAULT 'active',
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `library_members_member_code_unique` (`member_code`),
    KEY `library_members_registered_by_foreign` (`registered_by`),
    CONSTRAINT `library_members_registered_by_foreign`
        FOREIGN KEY (`registered_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `books` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `registered_by` BIGINT UNSIGNED NULL,
    `isbn` VARCHAR(40) NULL,
    `title` VARCHAR(180) NOT NULL,
    `author` VARCHAR(160) NULL,
    `publisher` VARCHAR(160) NULL,
    `language` VARCHAR(80) NULL,
    `edition` VARCHAR(80) NULL,
    `published_year` SMALLINT UNSIGNED NULL,
    `pages` SMALLINT UNSIGNED NULL,
    `category` VARCHAR(120) NULL,
    `shelf_code` VARCHAR(80) NULL,
    `barcode` VARCHAR(80) NULL,
    `total_copies` INT UNSIGNED NOT NULL DEFAULT 1,
    `available_copies` INT UNSIGNED NOT NULL DEFAULT 1,
    `status` VARCHAR(40) NOT NULL DEFAULT 'available',
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `books_barcode_unique` (`barcode`),
    KEY `books_registered_by_foreign` (`registered_by`),
    CONSTRAINT `books_registered_by_foreign`
        FOREIGN KEY (`registered_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `book_copies` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `book_id` BIGINT UNSIGNED NOT NULL,
    `copy_code` VARCHAR(80) NOT NULL,
    `barcode` VARCHAR(100) NULL,
    `shelf_code` VARCHAR(80) NULL,
    `status` VARCHAR(40) NOT NULL DEFAULT 'available',
    `condition` VARCHAR(120) NULL,
    `purchase_price` INT UNSIGNED NOT NULL DEFAULT 0,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `book_copies_copy_code_unique` (`copy_code`),
    UNIQUE KEY `book_copies_barcode_unique` (`barcode`),
    KEY `book_copies_book_id_foreign` (`book_id`),
    CONSTRAINT `book_copies_book_id_foreign`
        FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `book_loans` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `library_member_id` BIGINT UNSIGNED NOT NULL,
    `book_id` BIGINT UNSIGNED NOT NULL,
    `book_copy_id` BIGINT UNSIGNED NULL,
    `recorded_by` BIGINT UNSIGNED NULL,
    `loan_code` VARCHAR(60) NULL,
    `borrowed_at` DATE NOT NULL,
    `due_at` DATE NULL,
    `condition_out` VARCHAR(120) NULL,
    `returned_at` DATE NULL,
    `condition_in` VARCHAR(120) NULL,
    `fine_amount` INT UNSIGNED NOT NULL DEFAULT 0,
    `status` VARCHAR(40) NOT NULL DEFAULT 'borrowed',
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `book_loans_loan_code_unique` (`loan_code`),
    KEY `book_loans_library_member_id_foreign` (`library_member_id`),
    KEY `book_loans_book_id_foreign` (`book_id`),
    KEY `book_loans_book_copy_id_foreign` (`book_copy_id`),
    KEY `book_loans_recorded_by_foreign` (`recorded_by`),
    CONSTRAINT `book_loans_library_member_id_foreign`
        FOREIGN KEY (`library_member_id`) REFERENCES `library_members` (`id`) ON DELETE CASCADE,
    CONSTRAINT `book_loans_book_id_foreign`
        FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
    CONSTRAINT `book_loans_book_copy_id_foreign`
        FOREIGN KEY (`book_copy_id`) REFERENCES `book_copies` (`id`) ON DELETE SET NULL,
    CONSTRAINT `book_loans_recorded_by_foreign`
        FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `membership_cards` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `cardable_type` VARCHAR(255) NOT NULL,
    `cardable_id` BIGINT UNSIGNED NOT NULL,
    `created_by` BIGINT UNSIGNED NULL,
    `scope` VARCHAR(30) NOT NULL,
    `card_number` VARCHAR(60) NOT NULL,
    `holder_name` VARCHAR(160) NOT NULL,
    `father_name` VARCHAR(160) NULL,
    `issued_at` DATE NOT NULL,
    `expires_at` DATE NOT NULL,
    `fee_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
    `payment_status` VARCHAR(30) NOT NULL DEFAULT 'unpaid',
    `paid_at` DATE NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `membership_cards_card_number_unique` (`card_number`),
    KEY `membership_cards_cardable_type_cardable_id_index` (`cardable_type`, `cardable_id`),
    KEY `membership_cards_created_by_foreign` (`created_by`),
    CONSTRAINT `membership_cards_created_by_foreign`
        FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `finance_categories` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `type` VARCHAR(20) NOT NULL,
    `name` VARCHAR(120) NOT NULL,
    `slug` VARCHAR(120) NULL,
    `color` VARCHAR(24) NULL,
    `description` TEXT NULL,
    `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `finance_categories_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `finance_donors` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(160) NOT NULL,
    `phone` VARCHAR(60) NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `finance_projects` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(180) NOT NULL,
    `category` VARCHAR(80) NULL,
    `estimated_budget` BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `status` VARCHAR(40) NOT NULL DEFAULT 'active',
    `started_on` DATE NULL,
    `completed_on` DATE NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `finance_transactions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `transaction_number` VARCHAR(40) NOT NULL,
    `type` VARCHAR(20) NOT NULL,
    `finance_category_id` BIGINT UNSIGNED NULL,
    `finance_donor_id` BIGINT UNSIGNED NULL,
    `finance_project_id` BIGINT UNSIGNED NULL,
    `dorm_student_id` BIGINT UNSIGNED NULL,
    `recorded_by` BIGINT UNSIGNED NULL,
    `expected_amount` BIGINT UNSIGNED NULL,
    `amount` BIGINT UNSIGNED NOT NULL,
    `transaction_date` DATE NOT NULL,
    `period` VARCHAR(80) NULL,
    `source_or_payee` VARCHAR(180) NULL,
    `payer_name` VARCHAR(180) NULL,
    `payee_name` VARCHAR(180) NULL,
    `donor_name` VARCHAR(160) NULL,
    `donor_phone` VARCHAR(60) NULL,
    `project_name` VARCHAR(160) NULL,
    `receipt_number` VARCHAR(80) NULL,
    `payment_method` VARCHAR(40) NOT NULL DEFAULT 'cash',
    `payment_status` VARCHAR(40) NOT NULL DEFAULT 'completed',
    `status` VARCHAR(40) NOT NULL DEFAULT 'paid',
    `attachment_path` VARCHAR(255) NULL,
    `notes` TEXT NULL,
    `description` TEXT NULL,
    `attachment_required` BOOLEAN NOT NULL DEFAULT FALSE,
    `payment_month` TINYINT UNSIGNED NULL,
    `payment_year` SMALLINT UNSIGNED NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `finance_transactions_transaction_number_unique` (`transaction_number`),
    KEY `finance_transactions_finance_category_id_foreign` (`finance_category_id`),
    KEY `finance_transactions_finance_donor_id_foreign` (`finance_donor_id`),
    KEY `finance_transactions_finance_project_id_foreign` (`finance_project_id`),
    KEY `finance_transactions_dorm_student_id_foreign` (`dorm_student_id`),
    KEY `finance_transactions_recorded_by_foreign` (`recorded_by`),
    KEY `finance_transactions_type_transaction_date_index` (`type`, `transaction_date`),
    KEY `finance_transactions_payment_status_transaction_date_index` (`payment_status`, `transaction_date`),
    CONSTRAINT `finance_transactions_finance_category_id_foreign`
        FOREIGN KEY (`finance_category_id`) REFERENCES `finance_categories` (`id`) ON DELETE SET NULL,
    CONSTRAINT `finance_transactions_finance_donor_id_foreign`
        FOREIGN KEY (`finance_donor_id`) REFERENCES `finance_donors` (`id`) ON DELETE SET NULL,
    CONSTRAINT `finance_transactions_finance_project_id_foreign`
        FOREIGN KEY (`finance_project_id`) REFERENCES `finance_projects` (`id`) ON DELETE SET NULL,
    CONSTRAINT `finance_transactions_dorm_student_id_foreign`
        FOREIGN KEY (`dorm_student_id`) REFERENCES `dorm_students` (`id`) ON DELETE SET NULL,
    CONSTRAINT `finance_transactions_recorded_by_foreign`
        FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `student_payments` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `dorm_student_id` BIGINT UNSIGNED NOT NULL,
    `finance_transaction_id` BIGINT UNSIGNED NULL,
    `recorded_by` BIGINT UNSIGNED NULL,
    `payment_month` TINYINT UNSIGNED NOT NULL,
    `payment_year` SMALLINT UNSIGNED NOT NULL,
    `expected_amount` BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `paid_amount` BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `remaining_amount` BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `status` VARCHAR(40) NOT NULL DEFAULT 'pending',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `student_payments_dorm_student_id_foreign` (`dorm_student_id`),
    KEY `student_payments_finance_transaction_id_foreign` (`finance_transaction_id`),
    KEY `student_payments_recorded_by_foreign` (`recorded_by`),
    CONSTRAINT `student_payments_dorm_student_id_foreign`
        FOREIGN KEY (`dorm_student_id`) REFERENCES `dorm_students` (`id`) ON DELETE CASCADE,
    CONSTRAINT `student_payments_finance_transaction_id_foreign`
        FOREIGN KEY (`finance_transaction_id`) REFERENCES `finance_transactions` (`id`) ON DELETE SET NULL,
    CONSTRAINT `student_payments_recorded_by_foreign`
        FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `finance_attachments` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `finance_transaction_id` BIGINT UNSIGNED NOT NULL,
    `uploaded_by` BIGINT UNSIGNED NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `mime_type` VARCHAR(120) NULL,
    `size` BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `finance_attachments_finance_transaction_id_foreign` (`finance_transaction_id`),
    KEY `finance_attachments_uploaded_by_foreign` (`uploaded_by`),
    CONSTRAINT `finance_attachments_finance_transaction_id_foreign`
        FOREIGN KEY (`finance_transaction_id`) REFERENCES `finance_transactions` (`id`) ON DELETE CASCADE,
    CONSTRAINT `finance_attachments_uploaded_by_foreign`
        FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `finance_audit_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `finance_transaction_id` BIGINT UNSIGNED NULL,
    `action` VARCHAR(40) NOT NULL,
    `old_values` JSON NULL,
    `new_values` JSON NULL,
    `performed_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `finance_audit_logs_finance_transaction_id_foreign` (`finance_transaction_id`),
    KEY `finance_audit_logs_performed_by_foreign` (`performed_by`),
    CONSTRAINT `finance_audit_logs_finance_transaction_id_foreign`
        FOREIGN KEY (`finance_transaction_id`) REFERENCES `finance_transactions` (`id`) ON DELETE SET NULL,
    CONSTRAINT `finance_audit_logs_performed_by_foreign`
        FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `finance_categories`
    (`type`, `name`, `slug`, `color`, `description`, `is_active`, `created_at`, `updated_at`)
VALUES
    ('income', 'Student monthly fee', 'student-monthly-fee', NULL, 'Dorm student monthly payments.', TRUE, NOW(), NOW()),
    ('income', 'Student registration fee', 'student-registration-fee', NULL, 'Registration, card, and admission related income.', TRUE, NOW(), NOW()),
    ('income', 'Donor contribution', 'donor-contribution', NULL, 'Cash or estimated value of donor support.', TRUE, NOW(), NOW()),
    ('income', 'Organization support', 'organization-support', NULL, 'Institutional support and grants.', TRUE, NOW(), NOW()),
    ('income', 'Other income', 'other-income', NULL, 'Other dorm income.', TRUE, NOW(), NOW()),
    ('expense', 'Construction and repair', 'construction-and-repair', NULL, 'Building, room, electrical, plumbing, and repair costs.', TRUE, NOW(), NOW()),
    ('expense', 'Guard salary', 'guard-salary', NULL, 'Guard salary payments.', TRUE, NOW(), NOW()),
    ('expense', 'Staff salary', 'staff-salary', NULL, 'Staff and worker salary payments.', TRUE, NOW(), NOW()),
    ('expense', 'Library repair', 'library-repair', NULL, 'Library repair, books, labels, furniture, and equipment.', TRUE, NOW(), NOW()),
    ('expense', 'Food and kitchen', 'food-and-kitchen', NULL, 'Food, kitchen, and daily supplies.', TRUE, NOW(), NOW()),
    ('expense', 'Utilities', 'utilities', NULL, 'Electricity, water, internet, and services.', TRUE, NOW(), NOW()),
    ('expense', 'Other expense', 'other-expense', NULL, 'Other dorm expenses.', TRUE, NOW(), NOW());

-- Optional admin user query.
-- Replace the values and use a real bcrypt password hash generated by Laravel Hash::make().
-- INSERT INTO `users`
--     (`name`, `email`, `phone`, `role`, `status`, `theme`, `email_verified_at`, `password`, `created_at`, `updated_at`)
-- VALUES
--     ('Admin', 'admin@example.com', '0000000000', 'admin', 'active', 'light', NOW(), '$2y$12$replace_with_laravel_bcrypt_hash', NOW(), NOW());
