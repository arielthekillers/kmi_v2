<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class TeacherLeaveModel extends Model {
    protected $table = 'teacher_leaves';

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO teacher_leaves (date, teacher_id, academic_year_id, created_by, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['date'],
            $data['teacher_id'],
            $this->academic_year_id,
            $data['created_by'] ?? null,
            $data['status'] ?? 'draft'
        ]);
        return $this->db->lastInsertId();
    }

    public function findWithDetails($id) {
        $stmt = $this->db->prepare("
            SELECT l.*, 
                   u.nama as teacher_name, 
                   c.nama as creator_name
            FROM teacher_leaves l
            LEFT JOIN users u ON l.teacher_id = u.id
            LEFT JOIN users c ON l.created_by = c.id
            WHERE l.id = ?
        ");
        $stmt->execute([$id]);
        $leave = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($leave) {
            $stmt = $this->db->prepare("
                SELECT d.*, 
                       k.tingkat, k.abjad,
                       s.nama as subject_name,
                       u.nama as substitute_name
                FROM teaching_substitutions d
                LEFT JOIN kelas k ON d.kelas_id = k.id
                LEFT JOIN subjects s ON d.subject_id = s.id
                LEFT JOIN users u ON d.substitute_teacher_id = u.id
                WHERE d.leave_id = ?
                ORDER BY d.hour ASC
            ");
            $stmt->execute([$id]);
            $leave['details'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $leave;
    }

    public function getRecommendations($date, $hour, $subject_id, $kelas_id, $absent_teacher_id) {
        // Find day of week in Indonesian
        $timestamp = strtotime($date);
        $dayOfWeek = date('w', $timestamp);
        $daysIndo = ['Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $dayName = $daysIndo[$dayOfWeek];

        // 1. Get absent teacher's class level
        $stmt = $this->db->prepare("SELECT tingkat FROM kelas WHERE id = ?");
        $stmt->execute([$kelas_id]);
        $kelas = $stmt->fetch(PDO::FETCH_ASSOC);
        $tingkat = $kelas ? (int)$kelas['tingkat'] : 0;

        // 1b. Get absent teacher's gender
        $stmt = $this->db->prepare("SELECT gender FROM teacher_profiles WHERE user_id = ?");
        $stmt->execute([$absent_teacher_id]);
        $absentProfile = $stmt->fetch(PDO::FETCH_ASSOC);
        $absentGender = $absentProfile ? $absentProfile['gender'] : null;

        // 2. Check for Teacher Assistants
        $stmt = $this->db->prepare("
            SELECT assistant_id 
            FROM teacher_assistants 
            WHERE teacher_id = ? AND (subject_id = ? OR subject_id IS NULL)
        ");
        $stmt->execute([$absent_teacher_id, $subject_id]);
        $assistants = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // 3. Get ALL teachers (active) and their gender
        $stmt = $this->db->query("
            SELECT u.id, u.nama, tp.gender 
            FROM users u 
            LEFT JOIN teacher_profiles tp ON u.id = tp.user_id 
            WHERE u.role = 'pengajar' AND u.is_active = 1 AND u.deleted_at IS NULL AND u.id != " . (int)$absent_teacher_id
        );
        $allTeachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3b. Pre-fetch substitution count last 30 days
        $stmt = $this->db->query("
            SELECT substitute_teacher_id, COUNT(*) as subs_count 
            FROM teaching_substitutions 
            WHERE substitute_teacher_id IS NOT NULL 
              AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
            GROUP BY substitute_teacher_id
        ");
        $subsCounts = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $subsCounts[$row['substitute_teacher_id']] = (int)$row['subs_count'];
        }

        // 3c. Pre-fetch historical subject mastery (optimized)
        $stmt = $this->db->query("
            SELECT s.teacher_id, 
                   MAX(CASE WHEN s.subject_id = " . (int)$subject_id . " THEN 1 ELSE 0 END) as teaches_this_subject,
                   MIN(k.tingkat) as min_tingkat, 
                   MAX(k.tingkat) as max_tingkat
            FROM schedules s
            JOIN kelas k ON k.id = s.kelas_id
            WHERE s.academic_year_id = {$this->academic_year_id}
            GROUP BY s.teacher_id
        ");
        $historicalMastery = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $historicalMastery[$row['teacher_id']] = [
                'teaches_this_subject' => (bool)$row['teaches_this_subject'],
                'min' => (int)$row['min_tingkat'],
                'max' => (int)$row['max_tingkat']
            ];
        }

        // 4. Get today's schedules for ALL teachers
        $stmt = $this->db->prepare("
            SELECT s.teacher_id, s.hour, s.subject_id, k.tingkat, k.abjad, sub.nama as subject_name 
            FROM schedules s
            JOIN kelas k ON s.kelas_id = k.id
            JOIN subjects sub ON s.subject_id = sub.id
            WHERE s.day = ? AND s.academic_year_id = ?
            ORDER BY s.hour ASC
        ");
        $stmt->execute([$dayName, $this->academic_year_id]);
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $teacherSchedules = [];
        foreach ($schedules as $sch) {
            $teacherSchedules[$sch['teacher_id']][$sch['hour']] = $sch;
        }

        // 4b. Get busy substitutes for the same date and hour
        $stmt = $this->db->prepare("
            SELECT ts.substitute_teacher_id 
            FROM teaching_substitutions ts
            JOIN teacher_leaves tl ON ts.leave_id = tl.id
            WHERE tl.date = ? AND ts.hour = ? AND ts.substitute_teacher_id IS NOT NULL
        ");
        $stmt->execute([$date, $hour]);
        $busySubstitutes = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $busySubMap = array_flip($busySubstitutes);

        $recommendations = [];

        foreach ($allTeachers as $teacher) {
            $tid = $teacher['id'];
            
            // If they are teaching at the exact hour, they are unavailable
            if (isset($teacherSchedules[$tid][$hour])) {
                continue;
            }

            // If they are already substituting for someone else at the exact hour, they are unavailable
            if (isset($busySubMap[$tid])) {
                continue;
            }

            $isAssistant = in_array($tid, $assistants);
            $gender = $teacher['gender'] ?? null;
            $subsCount = $subsCounts[$tid] ?? 0;
            $hasScheduleToday = isset($teacherSchedules[$tid]);
            
            $baseScore = 0;
            $category = '';
            $reasonDetail = '';

            // Check historical mastery (optimized)
            $teachesSameSubject = false;
            $teachesSimilarLevel = false;
            
            if (isset($historicalMastery[$tid])) {
                $mastery = $historicalMastery[$tid];
                $teachesSameSubject = $mastery['teaches_this_subject'];
                if ($tingkat >= $mastery['min'] - 1 && $tingkat <= $mastery['max'] + 1) {
                    $teachesSimilarLevel = true;
                }
            }

            if ($isAssistant) {
                $category = 'Asisten Tetap';
                $baseScore = 1000; // Highest priority
                $reasonDetail = 'Pengajar ini adalah asisten tetap yang ditugaskan untuk Anda.';
            } else {
                if ($hasScheduleToday) {
                    if ($teachesSameSubject && $teachesSimilarLevel) {
                        $category = 'Prioritas 1 (Mapel & Level Sesuai)';
                        $baseScore = 300;
                        $reasonDetail = "Memiliki keterkaitan keilmuan yang serupa pada mata pelajaran ini di level kelas yang tidak berjauhan.";
                    } elseif ($teachesSimilarLevel) {
                        $category = 'Prioritas 2 (Level Kelas Sesuai)';
                        $baseScore = 200;
                        $reasonDetail = "Terbiasa mengajar di level kelas yang tidak berjauhan.";
                    } else {
                        $category = 'Prioritas 3 (Tersedia Hari Ini)';
                        $baseScore = 100;
                        $reasonDetail = "Tersedia di area pesantren/sekolah hari ini.";
                    }
                } else {
                    $category = 'Pilihan Terakhir (Hari Libur)';
                    $baseScore = 10;
                    $reasonDetail = "Pengajar ini sedang libur (tidak memiliki jadwal mengajar) hari ini.";
                }
            }
            
            // Apply Penalties and Bonuses
            $hoursToday = $hasScheduleToday ? count($teacherSchedules[$tid]) : 0;
            
            // Limit 6 hours daily
            if (!$isAssistant && $hoursToday >= 6) {
                continue;
            }

            $consecutivePenalty = 0;
            if ($hasScheduleToday) {
                if (isset($teacherSchedules[$tid][$hour - 1]) || isset($teacherSchedules[$tid][$hour + 1])) {
                    $consecutivePenalty = 100;
                }
            }

            $workloadPenalty = $hoursToday * 10;
            $fairnessPenalty = $subsCount * 5;
            $genderBonus = ($absentGender && $gender === $absentGender) ? 50 : 0;
            
            $finalScore = $baseScore - $workloadPenalty - $consecutivePenalty - $fairnessPenalty + $genderBonus;
            
            // Construct detailed tooltip reason
            $reason = "Skor Kelayakan: $finalScore\n\n";
            $reason .= "• $reasonDetail\n";
            if ($genderBonus > 0) {
                $reason .= "• Gender sesuai (+50 poin).\n";
            }
            if ($hoursToday > 0) {
                $reason .= "• Beban hari ini: $hoursToday jam pelajaran (-$workloadPenalty poin).\n";
            }
            if ($consecutivePenalty > 0) {
                $reason .= "• Jadwal beruntun: Mengajar di jam sebelum/sesudahnya (-$consecutivePenalty poin).\n";
            }
            if ($subsCount > 0) {
                $reason .= "• Telah menggantikan $subsCount kali 30 hari terakhir (-$fairnessPenalty poin).";
            }

            $recommendations[] = [
                'id' => $tid,
                'nama' => $teacher['nama'],
                'category' => $category,
                'score' => $finalScore,
                'is_assistant' => $isAssistant,
                'reason' => trim($reason)
            ];
        }

        // Sort by score descending
        usort($recommendations, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $recommendations;
    }

    public function getStatistics($filter, $academicYearId) {
        $whereDate = "";
        if ($filter === 'today') {
            $whereDate = "AND l.date = CURRENT_DATE()";
        } elseif ($filter === 'week') {
            // Pesantren week starts on Saturday. 
            // Adding 1 day shifts Saturday to Sunday. Mode 0 starts on Sunday.
            $whereDate = "AND YEARWEEK(DATE_ADD(l.date, INTERVAL 1 DAY), 0) = YEARWEEK(DATE_ADD(CURRENT_DATE(), INTERVAL 1 DAY), 0)";
        } elseif ($filter === 'month') {
            $whereDate = "AND MONTH(l.date) = MONTH(CURRENT_DATE()) AND YEAR(l.date) = YEAR(CURRENT_DATE())";
        }

        // Summary
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT l.id) as total_leaves,
                   COUNT(ts.id) as total_slots,
                   SUM(CASE WHEN ts.substitute_teacher_id IS NOT NULL THEN 1 ELSE 0 END) as total_substituted,
                   SUM(CASE WHEN ts.substitute_teacher_id IS NULL THEN 1 ELSE 0 END) as total_empty
            FROM teacher_leaves l
            LEFT JOIN teaching_substitutions ts ON l.id = ts.leave_id
            WHERE l.academic_year_id = ? $whereDate
        ");
        $stmt->execute([$academicYearId]);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);

        // Top Absentees
        $stmt = $this->db->prepare("
            SELECT u.id, u.nama, COUNT(DISTINCT l.id) as leave_count, COUNT(ts.id) as total_jam_kosong
            FROM teacher_leaves l
            JOIN users u ON l.teacher_id = u.id
            LEFT JOIN teaching_substitutions ts ON l.id = ts.leave_id
            WHERE l.academic_year_id = ? $whereDate
            GROUP BY u.id, u.nama
            ORDER BY leave_count DESC, total_jam_kosong DESC
            LIMIT 5
        ");
        $stmt->execute([$academicYearId]);
        $topAbsentees = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Top Substitutes
        $stmt = $this->db->prepare("
            SELECT u.id as teacher_id, u.nama, COUNT(ts.id) as substitution_count
            FROM teaching_substitutions ts
            JOIN teacher_leaves l ON ts.leave_id = l.id
            JOIN users u ON ts.substitute_teacher_id = u.id
            WHERE l.academic_year_id = ? $whereDate AND ts.substitute_teacher_id IS NOT NULL
            GROUP BY u.id, u.nama
            ORDER BY substitution_count DESC
            LIMIT 5
        ");
        $stmt->execute([$academicYearId]);
        $topSubstitutes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Top Subjects
        $stmt = $this->db->prepare("
            SELECT s.nama, COUNT(ts.id) as abandon_count
            FROM teaching_substitutions ts
            JOIN teacher_leaves l ON ts.leave_id = l.id
            JOIN subjects s ON ts.subject_id = s.id
            WHERE l.academic_year_id = ? $whereDate
            GROUP BY s.id, s.nama
            ORDER BY abandon_count DESC
            LIMIT 5
        ");
        $stmt->execute([$academicYearId]);
        $topSubjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Trend Previous Period
        $wherePrevDate = "";
        if ($filter === 'today') {
            $wherePrevDate = "AND l.date = DATE_SUB(CURRENT_DATE(), INTERVAL 1 DAY)";
        } elseif ($filter === 'week') {
            $wherePrevDate = "AND YEARWEEK(DATE_ADD(l.date, INTERVAL 1 DAY), 0) = YEARWEEK(DATE_ADD(DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY), INTERVAL 1 DAY), 0)";
        } elseif ($filter === 'month') {
            $wherePrevDate = "AND MONTH(l.date) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)) AND YEAR(l.date) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))";
        } elseif ($filter === 'year') {
            $wherePrevDate = "AND 1=0"; // Disabled for year view
        }

        $prevSummary = ['total_leaves' => 0, 'total_slots' => 0];
        if ($wherePrevDate) {
            $stmtPrev = $this->db->prepare("
                SELECT COUNT(DISTINCT l.id) as total_leaves,
                       COUNT(ts.id) as total_slots
                FROM teacher_leaves l
                LEFT JOIN teaching_substitutions ts ON l.id = ts.leave_id
                WHERE l.academic_year_id = ? $wherePrevDate
            ");
            $stmtPrev->execute([$academicYearId]);
            $prevSummary = $stmtPrev->fetch(PDO::FETCH_ASSOC);
        }

        // Chart Data (Absences by Day of Week)
        $stmtChart = $this->db->prepare("
            SELECT DAYNAME(l.date) as day_name, COUNT(DISTINCT l.id) as leave_count
            FROM teacher_leaves l
            WHERE l.academic_year_id = ? $whereDate
            GROUP BY DAYOFWEEK(l.date), DAYNAME(l.date)
            ORDER BY DAYOFWEEK(l.date)
        ");
        $stmtChart->execute([$academicYearId]);
        $chartData = $stmtChart->fetchAll(PDO::FETCH_ASSOC);

        return [
            'summary' => $summary,
            'topAbsentees' => $topAbsentees,
            'topSubstitutes' => $topSubstitutes,
            'topSubjects' => $topSubjects,
            'prevSummary' => $prevSummary,
            'chartData' => $chartData
        ];
    }

    public function getTeacherLeaveDetails($teacherId, $filter, $academicYearId) {
        $whereDate = "";
        if ($filter === 'today') {
            $whereDate = "AND l.date = CURRENT_DATE()";
        } elseif ($filter === 'week') {
            $whereDate = "AND YEARWEEK(DATE_ADD(l.date, INTERVAL 1 DAY), 0) = YEARWEEK(DATE_ADD(CURRENT_DATE(), INTERVAL 1 DAY), 0)";
        } elseif ($filter === 'month') {
            $whereDate = "AND MONTH(l.date) = MONTH(CURRENT_DATE()) AND YEAR(l.date) = YEAR(CURRENT_DATE())";
        }

        $stmt = $this->db->prepare("
            SELECT l.date, GROUP_CONCAT(DISTINCT ts.note SEPARATOR ', ') as reason, COUNT(ts.id) as jam_kosong
            FROM teacher_leaves l
            LEFT JOIN teaching_substitutions ts ON l.id = ts.leave_id
            WHERE l.academic_year_id = ? AND l.teacher_id = ? $whereDate
            GROUP BY l.id, l.date
            ORDER BY l.date DESC
        ");
        $stmt->execute([$academicYearId, $teacherId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTeacherSubstitutionDetails($teacherId, $filter, $academicYearId) {
        $whereDate = "";
        if ($filter === 'today') {
            $whereDate = "AND l.date = CURRENT_DATE()";
        } elseif ($filter === 'week') {
            $whereDate = "AND YEARWEEK(DATE_ADD(l.date, INTERVAL 1 DAY), 0) = YEARWEEK(DATE_ADD(CURRENT_DATE(), INTERVAL 1 DAY), 0)";
        } elseif ($filter === 'month') {
            $whereDate = "AND MONTH(l.date) = MONTH(CURRENT_DATE()) AND YEAR(l.date) = YEAR(CURRENT_DATE())";
        }

        $stmt = $this->db->prepare("
            SELECT l.date, sub.nama as subject_name, k.abjad as kelas_name, k.tingkat, ts.hour
            FROM teaching_substitutions ts
            JOIN teacher_leaves l ON ts.leave_id = l.id
            JOIN subjects sub ON ts.subject_id = sub.id
            JOIN kelas k ON ts.kelas_id = k.id
            WHERE l.academic_year_id = ? AND ts.substitute_teacher_id = ? $whereDate
            ORDER BY l.date DESC, ts.hour ASC
        ");
        $stmt->execute([$academicYearId, $teacherId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
