CREATE TABLE IF NOT EXISTS `puzzles` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `type` VARCHAR(255) NOT NULL DEFAULT 'sudoku',
    `added` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `cells` (
    `index` INT NOT NULL,
    `puzzle_id` INT NOT NULL,
    `value` VARCHAR(3),
    `is_fixed` BOOLEAN NOT NULL DEFAULT FALSE,
    PRIMARY KEY (`index`, `puzzle_id`),
    FOREIGN KEY (`puzzle_id`) REFERENCES `puzzles` (`id`) ON DELETE CASCADE
);