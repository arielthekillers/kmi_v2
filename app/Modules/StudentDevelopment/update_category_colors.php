<?php
require_once __DIR__ . '/../../Core/Database.php';

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    
    $colors = [
        'Akademik' => '#3b82f6',     // Blue
        'Kedisiplinan' => '#d97706', // Dark Amber
        'Kegiatan' => '#8b5cf6',     // Purple
        'Perilaku' => '#059669',     // Emerald Green
        'Potensi' => '#0d9488',      // Teal
        'Prestasi' => '#eab308',     // Gold / Yellow
        'Sosial' => '#ec4899',       // Pink / Rose
        'Lainnya' => '#64748b'       // Slate Gray
    ];
    
    $stmt = $db->prepare("UPDATE student_observation_categories SET color = ? WHERE name = ?");
    
    foreach ($colors as $name => $color) {
        $stmt->execute([$color, $name]);
        echo "Updated category '$name' to color '$color'.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
