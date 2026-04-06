<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class KelasModel extends Model {
    protected $table = 'kelas';

    public function getAllGrouped() {
        $stmt = $this->db->query("SELECT * FROM kelas ORDER BY tingkat ASC, abjad ASC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Group by Tingkat
        $groupedKelas = [];
        foreach ($rows as $k) {
            $tingkat = $k['tingkat'] ?? 'Lainnya';
            $groupedKelas[$tingkat][] = $k;
        }

        // Sort Keys (Levels) naturally (1, 2, 10, etc.)
        uksort($groupedKelas, 'strnatcmp');
        
        return $groupedKelas;
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO kelas (tingkat, abjad, jumlah_murid) VALUES (?, ?, ?)");
        return $stmt->execute([$data['tingkat'], $data['abjad'], $data['jumlah_murid']]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE kelas SET tingkat = ?, abjad = ?, jumlah_murid = ? WHERE id = ?");
        return $stmt->execute([$data['tingkat'], $data['abjad'], $data['jumlah_murid'], $id]);
    }
}
