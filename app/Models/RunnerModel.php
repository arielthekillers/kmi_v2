<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class RunnerModel extends Model
{
    // ==========================================
    // 1. EVENT & GROUP MANAGEMENT
    // ==========================================

    public function getGroups()
    {
        return $this->db->query("SELECT * FROM runner_groups ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveGroup($name, $desc = '')
    {
        $stmt = $this->db->prepare("INSERT INTO runner_groups (name, description) VALUES (?, ?)");
        $stmt->execute([$name, $desc]);
        return $this->db->lastInsertId();
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

    public function updateActivity($id, $userId, $data)
    {
        // Hanya bisa diedit jika status masih pending
        $sql = "UPDATE runner_activities SET 
                    activity_date = ?, activity_type = ?, distance_km = ?,
                    duration_seconds = ?, pace_seconds = ?, elevation_gain_m = ?,
                    strava_url = ?, notes = ?
                WHERE id = ? AND user_id = ? AND status = 'pending'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['activity_date'],
            $data['activity_type'] ?? 'Run',
            (float)$data['distance_km'],
            (int)$data['duration_seconds'],
            (int)$data['pace_seconds'],
            (int)($data['elevation_gain_m'] ?? 0),
            $data['strava_url'] ?? null,
            $data['notes'] ?? null,
            (int)$id,
            (int)$userId
        ]);
    }

    public function deleteActivity($id, $userId, $isAdmin = false)
    {
        $stmt = $this->db->prepare("SELECT * FROM runner_activities WHERE id = ?");
        $stmt->execute([(int)$id]);
        $act = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$act) return false;

        if (!$isAdmin && ((int)$act['user_id'] !== (int)$userId || $act['status'] !== 'pending')) {
            return false; // Guru hanya boleh hapus lari pending miliknya
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
