<?php
/**
 * Migration: Add name, zone, notes columns to restaurant_tables
 * and update shape enum to include 'rectangle'.
 * 
 * Run once: php database/migrate_floor_plan.php
 */
require __DIR__ . '/../config.php';

function columnExists(PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    $stmt->execute([$table, $column]);
    return (int)$stmt->fetchColumn() > 0;
}

try {
    // Add 'name' column
    if (!columnExists($pdo, 'restaurant_tables', 'name')) {
        $pdo->exec("ALTER TABLE `restaurant_tables` ADD COLUMN `name` VARCHAR(100) DEFAULT '' AFTER `table_number`");
        echo "Column 'name' added\n";
    } else {
        echo "Column 'name' already exists\n";
    }

    // Update 'shape' enum to include 'rectangle'
    $pdo->exec("ALTER TABLE `restaurant_tables` MODIFY COLUMN `shape` ENUM('square','round','rectangle') DEFAULT 'square'");
    echo "Column 'shape' updated (added 'rectangle')\n";

    // Add 'zone' column
    if (!columnExists($pdo, 'restaurant_tables', 'zone')) {
        $pdo->exec("ALTER TABLE `restaurant_tables` ADD COLUMN `zone` ENUM('interieur','terrasse','prive','bar') DEFAULT 'interieur' AFTER `rotation`");
        echo "Column 'zone' added\n";
    } else {
        echo "Column 'zone' already exists\n";
    }

    // Add 'notes' column
    if (!columnExists($pdo, 'restaurant_tables', 'notes')) {
        $pdo->exec("ALTER TABLE `restaurant_tables` ADD COLUMN `notes` TEXT DEFAULT NULL AFTER `zone`");
        echo "Column 'notes' added\n";
    } else {
        echo "Column 'notes' already exists\n";
    }

    // Add 'table_id' column to reservations
    if (!columnExists($pdo, 'reservations', 'table_id')) {
        $pdo->exec("ALTER TABLE `reservations` ADD COLUMN `table_id` INT DEFAULT NULL AFTER `status`");
        $pdo->exec("ALTER TABLE `reservations` ADD CONSTRAINT `fk_reservation_table` FOREIGN KEY (`table_id`) REFERENCES `restaurant_tables`(`id`) ON DELETE SET NULL");
        echo "Column 'table_id' added to reservations\n";
    } else {
        echo "Column 'table_id' already exists in reservations\n";
    }

    echo "\nMigration completed successfully!\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
