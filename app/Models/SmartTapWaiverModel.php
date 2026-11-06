<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class SmartTapWaiverModel extends Model
{
    protected $table = 'smart_tap_attendance_waivers';

    /**
     * Ambil semua data waiver / pengecualian presensi
     */
    public function getAll($academicYearId = null)
    {
        $ayId = $academicYearId ?? $this->academic_year_id;
        $sql = "SELECT w.*, 
                       ev.name as event_name, ev.category as event_category,
                       u.nama as creator_name
                FROM smart_tap_attendance_waivers w
                LEFT JOIN smart_tap_events ev ON w.event_id = ev.id
                LEFT JOIN users u ON w.created_by = u.id
                WHERE w.academic_year_id = ?
                ORDER BY w.start_date DESC, w.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ayId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ambil detail waiver berdasarkan ID
     */
    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM smart_tap_attendance_waivers WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Simpan waiver baru
     */
    public function store(array $data)
    {
        $sql = "INSERT INTO smart_tap_attendance_waivers (
                    academic_year_id, title, reason_type, scope, target_category,
                    event_id, target_audience, target_classes, start_date, end_date,
                    description, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['academic_year_id'] ?? $this->academic_year_id,
            $data['title'],
            $data['reason_type'] ?? 'lainnya',
            $data['scope'] ?? 'all_events',
            $data['target_category'] ?? null,
            !empty($data['event_id']) ? (int)$data['event_id'] : null,
            $data['target_audience'] ?? 'all',
            $data['target_classes'] ?? null,
            $data['start_date'],
            $data['end_date'],
            $data['description'] ?? null,
            $data['created_by'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    /**
     * Update waiver
     */
    public function update($id, array $data)
    {
        $sql = "UPDATE smart_tap_attendance_waivers SET
                    title = ?, reason_type = ?, scope = ?, target_category = ?,
                    event_id = ?, target_audience = ?, target_classes = ?,
                    start_date = ?, end_date = ?, description = ?
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['title'],
            $data['reason_type'] ?? 'lainnya',
            $data['scope'] ?? 'all_events',
            $data['target_category'] ?? null,
            !empty($data['event_id']) ? (int)$data['event_id'] : null,
            $data['target_audience'] ?? 'all',
            $data['target_classes'] ?? null,
            $data['start_date'],
            $data['end_date'],
            $data['description'] ?? null,
            (int)$id
        ]);
    }

    /**
     * Hapus waiver
     */
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM smart_tap_attendance_waivers WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    /**
     * Cek apakah sebuah event pada tanggal tertentu terkena waiver (diliburkan/diudzurkan)
     * 
     * @param int $eventId
     * @param string $eventCategory ('sholat', 'office', 'incidental')
     * @param string $date (Y-m-d)
     * @param string $targetAudience ('all', 'students', 'teachers')
     * @param int|null $classId
     * @return array|null Info waiver jika bebas, null jika tetap wajib
     */
    public function getActiveWaiver($eventId, $eventCategory, $date, $targetAudience = 'all', $classId = null)
    {
        $sql = "SELECT * FROM smart_tap_attendance_waivers
                WHERE ? BETWEEN start_date AND end_date
                  AND (
                      scope = 'all_events'
                      OR (scope = 'category' AND target_category = ?)
                      OR (scope = 'specific_event' AND event_id = ?)
                  )";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$date, $eventCategory, (int)$eventId]);
        $waivers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($waivers)) return null;

        foreach ($waivers as $w) {
            // Cek target audience
            if ($w['target_audience'] !== 'all' && $targetAudience !== 'all') {
                if ($w['target_audience'] !== $targetAudience) {
                    continue; // Misalnya waiver khusus santri, sedangkan kita cek guru
                }
            }

            // Cek target classes jika ada spesifikasi kelas
            if (!empty($w['target_classes']) && $classId !== null) {
                $allowedClasses = array_map('trim', explode(',', $w['target_classes']));
                if (!in_array((string)$classId, $allowedClasses)) {
                    continue; // Kelas ini tidak termasuk yang diliburkan
                }
            }

            return $w; // Match!
        }

        return null;
    }

    /**
     * Ambil semua waiver aktif dalam rentang tanggal untuk optimasi batch rekap
     */
    public function getWaiversInRange($startDate, $endDate, $academicYearId = null)
    {
        $ayId = $academicYearId ?? $this->academic_year_id;
        $sql = "SELECT * FROM smart_tap_attendance_waivers
                WHERE academic_year_id = ?
                  AND start_date <= ? AND end_date >= ?
                ORDER BY start_date ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ayId, $endDate, $startDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

