<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class AcademicCalendarModel extends Model {
    protected $table = 'academic_calendar_events';

    /**
     * Get all events for a given academic year, ordered by start date
     */
    public function getByYear($yearId) {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE academic_year_id = ?
            ORDER BY tanggal_mulai ASC
        ");
        $stmt->execute([$yearId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a single event by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new event
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table}
                (academic_year_id, tanggal_mulai, tanggal_selesai, keterangan, kategori)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['academic_year_id'],
            $data['tanggal_mulai'],
            $data['tanggal_selesai'] ?: null,
            $data['keterangan'],
            $data['kategori'],
        ]);
    }

    /**
     * Update an existing event
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE {$this->table}
            SET academic_year_id = ?,
                tanggal_mulai    = ?,
                tanggal_selesai  = ?,
                keterangan       = ?,
                kategori         = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['academic_year_id'],
            $data['tanggal_mulai'],
            $data['tanggal_selesai'] ?: null,
            $data['keterangan'],
            $data['kategori'],
            $id,
        ]);
    }

    /**
     * Delete an event
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Count events for a given academic year
     */
    public function countByYear($yearId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE academic_year_id = ?");
        $stmt->execute([$yearId]);
        return (int) $stmt->fetchColumn();
    }
}
