CREATE TABLE IF NOT EXISTS `Users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `Name` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create Posts table
CREATE TABLE IF NOT EXISTS `Posts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `Title` VARCHAR(255) NOT NULL,
    `content` TEXT,
    `ImgUrl` VARCHAR(500),
    `User_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`User_id`) REFERENCES `Users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

INSERT INTO `Users` (`Name`) VALUES 
('Admin User'),
('John Doe'),
('Jane Smith')
ON DUPLICATE KEY UPDATE `Name` = VALUES(`Name`);

INSERT INTO `Posts` (`Title`, `content`, `ImgUrl`, `User_id`) VALUES 
('Welcome Post', 'Welcome to our platform!', NULL, 1),
('Sample Post', 'This is a sample post with some content.', NULL, 2)
ON DUPLICATE KEY UPDATE `Title` = VALUES(`Title`); 