CREATE DATABASE IF NOT EXISTS gym_db;
USE gym_db;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','trainer','member') NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);

CREATE TABLE `trainers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `specialization` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

CREATE TABLE `members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `trainer_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `join_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`trainer_id`) REFERENCES `trainers`(`id`) ON DELETE SET NULL
);

CREATE TABLE `classes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `trainer_id` int(11) NOT NULL,
  `schedule` datetime NOT NULL,
  `capacity` int(11) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`trainer_id`) REFERENCES `trainers`(`id`) ON DELETE CASCADE
);

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `status` enum('booked','cancelled') DEFAULT 'booked',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_booking` (`class_id`, `member_id`)
);

CREATE TABLE `progress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `reps` int(11) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL COMMENT 'in minutes',
  `notes` text,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE
);

CREATE TABLE `workout_programs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trainer_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`trainer_id`) REFERENCES `trainers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE
);

CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `status` enum('paid','pending') DEFAULT 'pending',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE
);

CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`receiver_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

-- Insert Dummy Data
-- Password for all is: password123

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`) VALUES
(1, 'Admin Utama', 'admin@gym.com', '$2y$10$SnVM.3EwnQ5t2jrGVcRUW.u6rvE2DGZi4S.K8huR8Qy68pHynKXZS', 'admin'),
(2, 'Trainer Budi', 'trainer1@gym.com', '$2y$10$SnVM.3EwnQ5t2jrGVcRUW.u6rvE2DGZi4S.K8huR8Qy68pHynKXZS', 'trainer'),
(3, 'Trainer Siti', 'trainer2@gym.com', '$2y$10$SnVM.3EwnQ5t2jrGVcRUW.u6rvE2DGZi4S.K8huR8Qy68pHynKXZS', 'trainer'),
(4, 'Member Andi', 'member1@gym.com', '$2y$10$SnVM.3EwnQ5t2jrGVcRUW.u6rvE2DGZi4S.K8huR8Qy68pHynKXZS', 'member'),
(5, 'Member Caca', 'member2@gym.com', '$2y$10$SnVM.3EwnQ5t2jrGVcRUW.u6rvE2DGZi4S.K8huR8Qy68pHynKXZS', 'member'),
(6, 'Member Doni', 'member3@gym.com', '$2y$10$SnVM.3EwnQ5t2jrGVcRUW.u6rvE2DGZi4S.K8huR8Qy68pHynKXZS', 'member');

INSERT INTO `trainers` (`id`, `user_id`, `specialization`) VALUES
(1, 2, 'Weightlifting & Bodybuilding'),
(2, 3, 'Cardio & Yoga');

INSERT INTO `members` (`id`, `user_id`, `trainer_id`, `status`, `join_date`) VALUES
(1, 4, 1, 'active', '2023-01-10'),
(2, 5, 2, 'active', '2023-02-15'),
(3, 6, 1, 'inactive', '2022-11-05');

INSERT INTO `classes` (`id`, `name`, `description`, `trainer_id`, `schedule`, `capacity`) VALUES
(1, 'Yoga Dasar', 'Kelas pengenalan yoga untuk pemula.', 2, DATE_ADD(NOW(), INTERVAL 1 DAY), 20),
(2, 'Angkat Beban 101', 'Membangun massa otot dengan teknik yang benar.', 1, DATE_ADD(NOW(), INTERVAL 2 DAY), 10),
(3, 'Zumba Fitness', 'Kardio menyenangkan dengan musik Zumba.', 2, DATE_ADD(NOW(), INTERVAL 3 DAY), 30);

INSERT INTO `bookings` (`id`, `class_id`, `member_id`, `status`) VALUES
(1, 1, 2, 'booked'),
(2, 2, 1, 'booked');

INSERT INTO `progress` (`member_id`, `date`, `weight`, `reps`, `duration`, `notes`) VALUES
(1, DATE_SUB(NOW(), INTERVAL 5 DAY), 60.5, 30, 45, 'Latihan punggung, terasa berat di akhir.'),
(1, DATE_SUB(NOW(), INTERVAL 3 DAY), 60.2, 35, 50, 'Mulai terbiasa, form lebih baik.'),
(1, DATE_SUB(NOW(), INTERVAL 1 DAY), 59.8, 40, 60, 'Peningkatan repetisi signifikan.');

INSERT INTO `workout_programs` (`trainer_id`, `member_id`, `title`, `description`, `start_date`, `end_date`) VALUES
(1, 1, 'Bulking 1 Bulan', 'Fokus latihan compound: squat, bench press, deadlift. Makan surplus kalori.', DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_ADD(NOW(), INTERVAL 25 DAY));

INSERT INTO `payments` (`member_id`, `amount`, `payment_date`, `status`) VALUES
(1, 300000.00, DATE_SUB(NOW(), INTERVAL 10 DAY), 'paid'),
(2, 250000.00, DATE_SUB(NOW(), INTERVAL 5 DAY), 'paid'),
(3, 300000.00, NOW(), 'pending');

INSERT INTO `messages` (`sender_id`, `receiver_id`, `message`, `is_read`, `created_at`) VALUES
(4, 2, 'Halo Coach, besok latihannya jam berapa ya?', 1, DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(2, 4, 'Halo Andi, kita mulai jam 8 pagi ya. Siapkan mental!', 0, NOW());
