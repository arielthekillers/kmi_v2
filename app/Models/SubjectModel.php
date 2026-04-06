<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class SubjectModel extends Model {
    protected $table = 'subjects';

    public function getAll() {
        // Fetch all, ordered by name (legacy behavior)
        // Also supports search/pagination in Controller if needed, but Model can just return basic query builder or raw results
        // For standard getAll, let's return all.
        $stmt = $this->db->query("SELECT * FROM subjects ORDER BY nama ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function search($keyword, $limit, $offset) {
         // Using prepared statements for search
         $sql = "SELECT * FROM subjects WHERE nama LIKE ? ORDER BY nama ASC LIMIT ? OFFSET ?";
         // PDO LIMIT/OFFSET needs integers, but binding sometimes tricky. 
         // Let's use bindParam or cast.
         $stmt = $this->db->prepare($sql);
         $like = "%$keyword%";
         $stmt->bindValue(1, $like);
         $stmt->bindValue(2, (int)$limit, PDO::PARAM_INT);
         $stmt->bindValue(3, (int)$offset, PDO::PARAM_INT);
         $stmt->execute();
         return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countSearch($keyword) {
        $sql = "SELECT COUNT(*) as total FROM subjects WHERE nama LIKE ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["%$keyword%"]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO subjects (nama, skor_maks, skala) VALUES (?, ?, ?)");
        return $stmt->execute([$data['nama'], $data['skor_maks'], $data['skala']]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE subjects SET nama = ?, skor_maks = ?, skala = ? WHERE id = ?");
        return $stmt->execute([$data['nama'], $data['skor_maks'], $data['skala'], $id]);
    }
}
