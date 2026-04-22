<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class AttendanceModel extends Model {
    protected $table = 'attendance_logs';

    /**
     * Get aggregated schedule with attendance data for a specific date
     */
    public function getDailyScheduleWithAttendance($date) {
        // 1. Fetch Schedule for the Day
        $timestamp = strtotime($date);
        $dayMap = [
            'Sun' => 'Ahad', 'Mon' => 'Senin', 'Tue' => 'Selasa',
            'Wed' => 'Rabu', 'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu'
        ];
        $dayName = $dayMap[date('D', $timestamp)] ?? '';

        $sql = "SELECT s.*, 
                       k.tingkat, k.abjad, 
                       sub.nama as mapel_nama,
                       u.nama as teacher_nama
                FROM schedules s
                JOIN kelas k ON s.kelas_id = k.id
                JOIN subjects sub ON s.subject_id = sub.id
                LEFT JOIN users u ON s.teacher_id = u.id
                WHERE s.day = ? AND s.academic_year_id = ?
                ORDER BY s.hour ASC, k.tingkat ASC, k.abjad ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dayName, $this->academic_year_id]);
        $schedulesRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Fetch Attendance Logs for Date
        $attStmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE date = ? AND academic_year_id = ?");
        $attStmt->execute([$date, $this->academic_year_id]);
        
        $attendanceLogs = []; // Key: classId|hour
        while($row = $attStmt->fetch(PDO::FETCH_ASSOC)) {
            $attendanceLogs[$row['kelas_id'] . '|' . $row['hour']] = $row;
        }

        // 3. Merge
        $dailySchedule = [];
        foreach ($schedulesRaw as $row) {
            $kelasId = $row['kelas_id'];
            $hour = $row['hour'];
            $key = $kelasId . '|' . $hour;
            
            $log = $attendanceLogs[$key] ?? null;
            $attendanceData = null;

            if ($log) {
                // Parse legacy note format
                $ketepatan = ($log['status'] === 'hadir') ? 'tepat_waktu' : null;
                $jamDatang = null;
                
                if ($log['status'] === 'hadir' && $log['note'] && strpos($log['note'], 'Terlambat') !== false) {
                     $ketepatan = 'terlambat';
                     preg_match('/Terlambat: (.*)/', $log['note'], $matches); // Old format: "Terlambat: 07:15"
                     $jamDatang = $matches[1] ?? '';
                     if (!$jamDatang) {
                         // Try extracting time if format is different (e.g. from report parser)
                         preg_match('/\((\d{2}:\d{2})\)/', $log['note'], $matches2);
                         $jamDatang = $matches2[1] ?? '';
                     }
                }

                $attendanceData = [
                    'status' => $log['status'],
                    'ketepatan' => $ketepatan,
                    'jam_datang' => $jamDatang,
                    'note' => $log['note'],
                    'pengajar_pengganti' => $log['substitute_teacher_id'],
                    'petugas_id' => $log['petugas_id'] ?? null
                ];
            }

            $dailySchedule[] = [
                'key' => $key,
                'kelas_id' => $kelasId,
                'hour' => $hour,
                'mapel_id' => $row['subject_id'],
                'pengajar_id' => $row['teacher_id'],
                
                'kelas_name' => "Kelas " . ($row['tingkat'] ?? '?') . "-" . ($row['abjad'] ?? '?'),
                'mapel_name' => $row['mapel_nama'],
                'teacher_name' => $row['teacher_nama'],
                'absensi' => $attendanceData
            ];
        }
        
        return $dailySchedule;
    }

    public function saveAttendance($data) {
        $date = $data['date'];
        $kelasId = $data['kelas_id'];
        $hour = $data['hour'];
        $teacherId = $data['teacher_id'];
        $status = $data['status'];
        $petugasId = $data['petugas_id']; // Log who inputted it

        // Process Note / Status Logic
        $note = null;
        $substituteId = null;
        $actualStatus = $status;

        if ($status === 'hadir') {
            $ketepatan = $data['ketepatan'] ?? 'tepat_waktu';
            if ($ketepatan === 'terlambat') {
                $jamDatang = $data['jam_datang'] ?? '';
                $note = "Terlambat: $jamDatang";
            } else {
                 $note = "Tepat Waktu"; // Or empty? Legacy seemed to rely on empty for Tepat?
                 // Let's set it to empty or specific if needed. Legacy simpan_absensi didn't save "Tepat Waktu".
                 // But simpan_absensi logic was: if ($ketepatan === 'tepat_waktu') $note = null;
                 $note = null; 
            }
        } elseif ($status === 'diganti') {
            $actualStatus = 'substitute'; 
            $substituteId = $data['pengajar_pengganti'];
        } elseif ($status === 'tidak_hadir') {
            $actualStatus = 'alpha';
        }

        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (date, kelas_id, hour, teacher_id, status, substitute_teacher_id, note, petugas_id, academic_year_id, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
                status = VALUES(status), 
                substitute_teacher_id = VALUES(substitute_teacher_id), 
                note = VALUES(note),
                petugas_id = VALUES(petugas_id),
                updated_at = NOW()
        ");
        return $stmt->execute([
            $date, $kelasId, $hour, $teacherId, $actualStatus, $substituteId, $note, $petugasId, $this->academic_year_id
        ]);
    }
    
    public function getReportStats($startDate, $endDate, $classId = null, $teacherId = null, $hour = null) {
        $params = [$startDate, $endDate, $this->academic_year_id];
        $sql = "SELECT al.*, 
                       k.tingkat, k.abjad,
                       u.nama as teacher_nama,
                       subst.nama as subst_nama
                FROM {$this->table} al
                JOIN kelas k ON al.kelas_id = k.id
                LEFT JOIN users u ON al.teacher_id = u.id
                LEFT JOIN users subst ON al.substitute_teacher_id = subst.id
                WHERE al.date BETWEEN ? AND ? AND al.academic_year_id = ?";
        
        if ($classId !== '' && $classId !== null) {
            $sql .= " AND al.kelas_id = ?";
            $params[] = $classId;
        }

        if ($teacherId !== '' && $teacherId !== null) {
            $sql .= " AND (al.teacher_id = ? OR al.substitute_teacher_id = ?)";
            $params[] = $teacherId;
            $params[] = $teacherId;
        }

        if ($hour !== '' && $hour !== null) {
            $sql .= " AND al.hour = ?";
            $params[] = $hour;
        }

        $sql .= " ORDER BY al.date DESC, k.tingkat ASC, k.abjad ASC, al.hour ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
