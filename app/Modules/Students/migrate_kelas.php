<?php

require_once __DIR__ . '/../../Core/Database.php';

use App\Core\Database;

$jsonFile = __DIR__ . '/../../../data/kelas.json';
if (!file_exists($jsonFile)) {
    die("kelas.json not found at $jsonFile");
}

$kelasData = json_decode(file_get_contents($jsonFile), true);
$db = Database::getInstance();

foreach ($kelasData as $id => $data) {
    // Determine gender based on abjad context if possible, or default
    // logic: "D Pi" -> Pi, "B Pa" -> Pa, "Pa+Pi" -> Pa+Pi
    $abjad = $data['abjad'];
    $gender = 'Pa'; // Default
    if (strpos($abjad, 'Pi') !== false && strpos($abjad, 'Pa') === false) {
        $gender = 'Pi';
    } elseif (strpos($abjad, 'Pi') !== false && strpos($abjad, 'Pa') !== false) {
        $gender = 'Pa+Pi';
    }

    // Check exist
    $stmt = $db->query("SELECT id FROM kelas WHERE legacy_id = ?", [$id]);
    if ($stmt->fetch()) {
        echo "Kelas $id already exists.\n";
        continue;
    }

    $db->query(
        "INSERT INTO kelas (legacy_id, tingkat, abjad, gender, jumlah_murid) VALUES (?, ?, ?, ?, ?)",
        [$id, $data['tingkat'], $data['abjad'], $gender, $data['jumlah_murid']]
    );
    echo "Migrated kelas: {$data['tingkat']} {$data['abjad']}\n";
}
