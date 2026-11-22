<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class RunnerModel extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->ensureTablesExist();
    }

    private function ensureTablesExist()
    {
        try {
            $check = $this->db->query("SHOW TABLES LIKE 'runner_events'")->fetchAll();
            if (empty($check)) {
                $this->db->exec("CREATE TABLE IF NOT EXISTS `runner_groups` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `name` varchar(100) NOT NULL,
                    `description` text DEFAULT NULL,
                    `created_at` datetime DEFAULT current_timestamp(),
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

                $this->db->exec("CREATE TABLE IF NOT EXISTS `runner_events` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `group_id` int(11) DEFAULT NULL,
                    `name` varchar(255) NOT NULL,
                    `slug` varchar(255) NOT NULL,
                    `description` text DEFAULT NULL,
                    `start_date` date NOT NULL,
                    `end_date` date NOT NULL,
                    `target_km` decimal(8,2) NOT NULL DEFAULT 42.00,
                    `mode` enum('MANUAL','HYBRID','AUTO') NOT NULL DEFAULT 'MANUAL',
                    `team_mode` enum('solo','team') NOT NULL DEFAULT 'solo',
                    `team_size` int(11) DEFAULT 1,
                    `cost` decimal(12,2) NOT NULL DEFAULT 0.00,
                    `status` enum('DRAFT','ACTIVE','COMPLETED') NOT NULL DEFAULT 'ACTIVE',
                    `created_by` int(11) DEFAULT NULL,
                    `created_at` datetime DEFAULT current_timestamp(),
                    `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                    PRIMARY KEY (`id`),
                    KEY `group_id` (`group_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

                $this->db->exec("CREATE TABLE IF NOT EXISTS `runner_participants` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `event_id` int(11) NOT NULL,
                    `user_id` int(11) NOT NULL,
                    `bib_number` varchar(50) DEFAULT NULL,
                    `registered_at` datetime DEFAULT current_timestamp(),
                    `payment_status` enum('FREE','UNPAID','PENDING','PAID') NOT NULL DEFAULT 'FREE',
                    `payment_proof` varchar(255) DEFAULT NULL,
                    `total_km` decimal(8,2) NOT NULL DEFAULT 0.00,
                    `total_duration_seconds` int(11) NOT NULL DEFAULT 0,
                    `total_elevation_m` int(11) NOT NULL DEFAULT 0,
                    `activity_count` int(11) NOT NULL DEFAULT 0,
                    `is_finisher` tinyint(1) NOT NULL DEFAULT 0,
                    `finished_at` datetime DEFAULT NULL,
                    `finisher_rank` int(11) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `uq_event_user` (`event_id`,`user_id`),
                    KEY `user_id` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

                $this->db->exec("CREATE TABLE IF NOT EXISTS `runner_activities` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `event_id` int(11) NOT NULL,
                    `participant_id` int(11) NOT NULL,
                    `user_id` int(11) NOT NULL,
                    `activity_date` date NOT NULL,
                    `activity_type` enum('Run','Walk','Treadmill','Trail Run') NOT NULL DEFAULT 'Run',
                    `distance_km` decimal(8,2) NOT NULL,
                    `duration_seconds` int(11) NOT NULL,
                    `pace_seconds` int(11) NOT NULL,
                    `elevation_gain_m` int(11) NOT NULL DEFAULT 0,
                    `strava_url` varchar(500) DEFAULT NULL,
                    `notes` text DEFAULT NULL,
                    `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
                    `verified_by` int(11) DEFAULT NULL,
                    `verified_at` datetime DEFAULT NULL,
                    `rejection_reason` text DEFAULT NULL,
                    `created_at` datetime DEFAULT current_timestamp(),
                    `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                    PRIMARY KEY (`id`),
                    KEY `event_id` (`event_id`),
                    KEY `participant_id` (`participant_id`),
                    KEY `user_id` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

                // Seed initial group & event
                $this->db->exec("INSERT INTO runner_groups (name, description) VALUES ('Monthly Challenges', 'Tantangan kebugaran lari bulanan guru dan staf')");
                $this->db->exec("INSERT INTO runner_groups (name, description) VALUES ('Special Edition', 'Event lari khusus peringatan atau charity')");
                $this->db->exec("INSERT INTO runner_events (group_id, name, slug, description, start_date, end_date, target_km, mode, team_mode, cost, status)
                    VALUES (1, 'KMI Virtual Run 42K', 'kmi-virtual-run-42k', 'Tantangan akumulasi lari 42 KM untuk kebugaran para guru dan staf KMI', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 60 DAY), 42.00, 'MANUAL', 'solo', 0.00, 'ACTIVE')");
            }
        } catch (\Exception $e) {
            // Ignore if restricted or already exists
        }
    }

    // ==========================================
    // 1. EVENT & GROUP MANAGEMENT
    // ==========================================

    public function getGroups()
    {
        $sql = "SELECT g.*, 
                       (SELECT COUNT(*) FROM runner_events e WHERE e.group_id = g.id) as event_count 
                FROM runner_groups g 
                ORDER BY g.name ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveGroup($data, $desc = '')
    {
        if (is_string($data)) {
            $name = $data;
            $id = null;
        } else {
            $id = !empty($data['id']) ? (int)$data['id'] : null;
            $name = trim($data['name'] ?? '');
            $desc = trim($data['description'] ?? '');
        }

        if ($id) {
            $stmt = $this->db->prepare("UPDATE runner_groups SET name = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $desc, $id]);
            return $id;
        } else {
            $stmt = $this->db->prepare("INSERT INTO runner_groups (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $desc]);
            return (int)$this->db->lastInsertId();
        }
    }

    public function deleteGroup($id)
    {
        // Unlink events terlebih dahulu agar data event tetap aman
        $this->db->prepare("UPDATE runner_events SET group_id = NULL WHERE group_id = ?")->execute([(int)$id]);
        $stmt = $this->db->prepare("DELETE FROM runner_groups WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    public function getAllEvents()
    {
        $sql = "SELECT e.*, g.name as group_name,
                       (SELECT COUNT(*) FROM runner_participants p WHERE p.event_id = e.id) as participant_count,
                       (SELECT COUNT(*) FROM runner_participants p WHERE p.event_id = e.id AND p.is_finisher = 1) as finisher_count
                FROM runner_events e
                LEFT JOIN runner_groups g ON e.group_id = g.id
                ORDER BY e.status = 'ACTIVE' DESC, e.start_date DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActiveEvents()
    {
        $sql = "SELECT e.*, g.name as group_name,
                       (SELECT COUNT(*) FROM runner_participants p WHERE p.event_id = e.id AND p.is_finisher = 1) as finisher_count
                FROM runner_events e
                LEFT JOIN runner_groups g ON e.group_id = g.id
                WHERE e.status = 'ACTIVE'
                ORDER BY e.start_date DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventById($id)
    {
        $sql = "SELECT e.*, g.name as group_name,
                       (SELECT COUNT(*) FROM runner_participants p WHERE p.event_id = e.id AND p.is_finisher = 1) as finisher_count,
                       (SELECT COUNT(*) FROM runner_participants p WHERE p.event_id = e.id) as participant_count
                FROM runner_events e
                LEFT JOIN runner_groups g ON e.group_id = g.id
                WHERE e.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function saveEvent($data)
    {
        $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower(trim($data['name'])));
        $slug = trim($slug, '-');

        if (!empty($data['id'])) {
            $sql = "UPDATE runner_events SET 
                        group_id = ?, name = ?, slug = ?, description = ?,
                        start_date = ?, end_date = ?, target_km = ?, mode = ?,
                        team_mode = ?, cost = ?, status = ?
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                !empty($data['group_id']) ? (int)$data['group_id'] : null,
                $data['name'],
                $slug,
                $data['description'] ?? '',
                $data['start_date'],
                $data['end_date'],
                (float)$data['target_km'],
                $data['mode'] ?? 'MANUAL',
                $data['team_mode'] ?? 'solo',
                (float)($data['cost'] ?? 0),
                $data['status'] ?? 'ACTIVE',
                (int)$data['id']
            ]);
            return (int)$data['id'];
        } else {
            $sql = "INSERT INTO runner_events (
                        group_id, name, slug, description, start_date, end_date,
                        target_km, mode, team_mode, cost, status, created_by
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                !empty($data['group_id']) ? (int)$data['group_id'] : null,
                $data['name'],
                $slug,
                $data['description'] ?? '',
                $data['start_date'],
                $data['end_date'],
                (float)$data['target_km'],
                $data['mode'] ?? 'MANUAL',
                $data['team_mode'] ?? 'solo',
                (float)($data['cost'] ?? 0),
                $data['status'] ?? 'ACTIVE',
                $data['created_by'] ?? null
            ]);
            return (int)$this->db->lastInsertId();
        }
    }

    public function deleteEvent($id)
    {
        $stmt = $this->db->prepare("DELETE FROM runner_events WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    // ==========================================
    // 2. PARTICIPANTS & LEADERBOARD
    // ==========================================

    public function getParticipant($eventId, $userId)
    {
        $sql = "SELECT p.*, u.nama, u.username, tp.nip, tp.profile_picture
                FROM runner_participants p
                JOIN users u ON p.user_id = u.id
                LEFT JOIN teacher_profiles tp ON u.id = tp.user_id
                WHERE p.event_id = ? AND p.user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$eventId, (int)$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function joinEvent($eventId, $userId, $bibNumber = null, $paymentProof = null)
    {
        $existing = $this->getParticipant($eventId, $userId);
        if ($existing) return $existing['id'];

        $event = $this->getEventById($eventId);
        $payStatus = (!empty($event['cost']) && (float)$event['cost'] > 0) ? 'PAID' : 'FREE';

        $sql = "INSERT INTO runner_participants (event_id, user_id, bib_number, payment_status, payment_proof) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            (int)$eventId,
            (int)$userId,
            $bibNumber,
            $payStatus,
            $paymentProof
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function getLeaderboard($eventId)
    {
        $sql = "SELECT p.*, u.nama, u.username, tp.nip, tp.profile_picture
                FROM runner_participants p
                JOIN users u ON p.user_id = u.id
                LEFT JOIN teacher_profiles tp ON u.id = tp.user_id
                WHERE p.event_id = ?
                ORDER BY p.total_km DESC, p.total_duration_seconds ASC, p.id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$eventId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateParticipantStats($participantId)
    {
        $part = $this->db->query("SELECT * FROM runner_participants WHERE id = " . (int)$participantId)->fetch(PDO::FETCH_ASSOC);
        if (!$part) return;

        $eventId = (int)$part['event_id'];
        $event = $this->getEventById($eventId);
        $targetKm = (float)($event['target_km'] ?? 42.0);

        // Hitung total dari aktivitas yang statusnya APPROVED
        $sql = "SELECT COUNT(*) as act_count,
                       COALESCE(SUM(distance_km), 0) as sum_km,
                       COALESCE(SUM(duration_seconds), 0) as sum_dur,
                       COALESCE(SUM(elevation_gain_m), 0) as sum_elev
                FROM runner_activities
                WHERE participant_id = ? AND status = 'approved'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$participantId]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        $totKm = (float)$stats['sum_km'];
        $totDur = (int)$stats['sum_dur'];
        $totElev = (int)$stats['sum_elev'];
        $actCount = (int)$stats['act_count'];

        $isFinisher = ($totKm >= $targetKm && $targetKm > 0) ? 1 : 0;
        $finishedAt = $part['finished_at'];
        $finisherRank = $part['finisher_rank'];

        if ($isFinisher && empty($part['is_finisher'])) {
            // Baru saja jadi finisher! Catat tanggal finish
            $lastAct = $this->db->query("SELECT activity_date, created_at FROM runner_activities 
                                         WHERE participant_id = $participantId AND status = 'approved' 
                                         ORDER BY activity_date DESC, id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            $finishedAt = $lastAct['activity_date'] ?? date('Y-m-d');

            // Hitung urutan finisher
            $priorFinisherCount = (int)$this->db->query("SELECT COUNT(*) FROM runner_participants 
                                                         WHERE event_id = $eventId AND is_finisher = 1")->fetchColumn();
            $finisherRank = $priorFinisherCount + 1;
        } elseif (!$isFinisher) {
            $finishedAt = null;
            $finisherRank = null;
        }

        $upSql = "UPDATE runner_participants SET 
                    total_km = ?, total_duration_seconds = ?, total_elevation_m = ?,
                    activity_count = ?, is_finisher = ?, finished_at = ?, finisher_rank = ?
                  WHERE id = ?";
        $upStmt = $this->db->prepare($upSql);
        $upStmt->execute([$totKm, $totDur, $totElev, $actCount, $isFinisher, $finishedAt, $finisherRank, (int)$participantId]);
    }

    // ==========================================
    // 3. ACTIVITIES (LOG LARI & VALIDASI)
    // ==========================================

    public function submitActivity($data)
    {
        $sql = "INSERT INTO runner_activities (
                    event_id, participant_id, user_id, activity_date,
                    activity_type, distance_km, duration_seconds, pace_seconds,
                    elevation_gain_m, strava_url, notes, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            (int)$data['event_id'],
            (int)$data['participant_id'],
            (int)$data['user_id'],
            $data['activity_date'],
            $data['activity_type'] ?? 'Run',
            (float)$data['distance_km'],
            (int)$data['duration_seconds'],
            (int)$data['pace_seconds'],
            (int)($data['elevation_gain_m'] ?? 0),
            $data['strava_url'] ?? null,
            $data['notes'] ?? null
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function updateActivity($id, $userId, $data, $isAdmin = false)
    {
        $stmt = $this->db->prepare("SELECT * FROM runner_activities WHERE id = ?");
        $stmt->execute([(int)$id]);
        $act = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$act) return false;

        // User can only edit their own activity, Admin can edit any activity
        if (!$isAdmin && (int)$act['user_id'] !== (int)$userId) {
            return false;
        }

        $sql = "UPDATE runner_activities SET 
                    activity_date = ?, activity_type = ?, distance_km = ?,
                    duration_seconds = ?, pace_seconds = ?, elevation_gain_m = ?,
                    strava_url = ?, notes = ?
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([
            $data['activity_date'],
            $data['activity_type'] ?? 'Run',
            (float)$data['distance_km'],
            (int)$data['duration_seconds'],
            (int)$data['pace_seconds'],
            (int)($data['elevation_gain_m'] ?? 0),
            $data['strava_url'] ?? null,
            $data['notes'] ?? null,
            (int)$id
        ]);

        if ($ok && $act['status'] === 'approved') {
            $this->updateParticipantStats((int)$act['participant_id']);
        }

        return $ok;
    }

    public function deleteActivity($id, $userId, $isAdmin = false)
    {
        $stmt = $this->db->prepare("SELECT * FROM runner_activities WHERE id = ?");
        $stmt->execute([(int)$id]);
        $act = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$act) return false;

        // User can delete their own activity, Admin can delete any activity
        if (!$isAdmin && (int)$act['user_id'] !== (int)$userId) {
            return false;
        }

        $del = $this->db->prepare("DELETE FROM runner_activities WHERE id = ?");
        $ok = $del->execute([(int)$id]);
        if ($ok && $act['status'] === 'approved') {
            $this->updateParticipantStats((int)$act['participant_id']);
        }
        return $ok;
    }

    public function getActivityById($id)
    {
        $stmt = $this->db->prepare("SELECT a.*, u.nama, e.name as event_name 
                                    FROM runner_activities a
                                    JOIN users u ON a.user_id = u.id
                                    JOIN runner_events e ON a.event_id = e.id
                                    WHERE a.id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getActivitiesByUser($userId, $eventId = null)
    {
        $where = ["a.user_id = ?"];
        $params = [(int)$userId];
        if (!empty($eventId)) {
            $where[] = "a.event_id = ?";
            $params[] = (int)$eventId;
        }
        $whereStr = implode(" AND ", $where);
        $sql = "SELECT a.*, e.name as event_name, e.target_km 
                FROM runner_activities a
                JOIN runner_events e ON a.event_id = e.id
                WHERE $whereStr
                ORDER BY a.activity_date DESC, a.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPendingActivities($eventId = null)
    {
        $where = ["a.status = 'pending'"];
        $params = [];
        if (!empty($eventId)) {
            $where[] = "a.event_id = ?";
            $params[] = (int)$eventId;
        }
        $whereStr = implode(" AND ", $where);
        $sql = "SELECT a.*, u.nama, u.username, tp.nip, e.name as event_name, p.total_km as current_total_km
                FROM runner_activities a
                JOIN users u ON a.user_id = u.id
                LEFT JOIN teacher_profiles tp ON u.id = tp.user_id
                JOIN runner_events e ON a.event_id = e.id
                JOIN runner_participants p ON a.participant_id = p.id
                WHERE $whereStr
                ORDER BY a.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function verifyActivity($activityId, $status, $adminId, $notes = '')
    {
        $act = $this->getActivityById($activityId);
        if (!$act) return false;

        $newStatus = ($status === 'approved') ? 'approved' : 'rejected';
        $sql = "UPDATE runner_activities SET 
                    status = ?, verified_by = ?, verified_at = NOW(), rejection_reason = ?
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([$newStatus, (int)$adminId, $notes, (int)$activityId]);

        if ($ok) {
            // Update akumulasi KM peserta
            $this->updateParticipantStats((int)$act['participant_id']);
        }
        return $ok;
    }
}
