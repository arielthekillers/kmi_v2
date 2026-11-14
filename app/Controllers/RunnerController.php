<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\RunnerModel;

class RunnerController extends Controller
{
    private $runnerModel;

    public function __construct()
    {
        $this->runnerModel = new RunnerModel();
    }

    /**
     * Halaman Utama Modul Runner (/ekskul/runner)
     * Menggabungkan Leaderboard, Aktivitas Saya, dan Manajemen/Validasi Admin dalam 1 tampilan tab
     */
    public function index()
    {
        require_login();
        $user = auth_user();
        $isAdmin = (auth_get_role() === 'admin');

        $events = $this->runnerModel->getActiveEvents();
        $eventId = !empty($_GET['event_id']) ? (int)$_GET['event_id'] : null;

        if (empty($eventId) && !empty($events)) {
            $eventId = (int)$events[0]['id'];
        }

        $event = $eventId ? $this->runnerModel->getEventById($eventId) : null;
        $leaderboard = $eventId ? $this->runnerModel->getLeaderboard($eventId) : [];

        // Data spesifik user yang login
        $myParticipant = $eventId ? $this->runnerModel->getParticipant($eventId, $user['id']) : null;
        $myActivities = $this->runnerModel->getActivitiesByUser($user['id'], $eventId);

        // Data khusus admin
        $pendingActivities = [];
        $allEvents = [];
        $groups = [];
        if ($isAdmin) {
            $pendingActivities = $this->runnerModel->getPendingActivities();
            $allEvents = $this->runnerModel->getAllEvents();
            $groups = $this->runnerModel->getGroups();
        }

        $activeTab = $_GET['tab'] ?? 'leaderboard';

        $this->view('ekskul/runner', [
            'user' => $user,
            'isAdmin' => $isAdmin,
            'events' => $events,
            'eventId' => $eventId,
            'event' => $event,
            'leaderboard' => $leaderboard,
            'myParticipant' => $myParticipant,
            'myActivities' => $myActivities,
            'pendingActivities' => $pendingActivities,
            'allEvents' => $allEvents,
            'groups' => $groups,
            'activeTab' => $activeTab
        ]);
    }

    /**
     * Guru mendaftar / bergabung ke event lari
     */
    public function join()
    {
        require_login();
        $user = auth_user();
        $eventId = (int)($_POST['event_id'] ?? 0);
        $bib = trim($_POST['bib_number'] ?? '');

        if (!$eventId) {
            add_flash('Event tidak valid.', 'error');
            $this->redirect('/ekskul/runner');
        }

        $proofPath = null;
        if (!empty($_FILES['payment_proof']['name']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'public/uploads/runner_proofs/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION);
            $fileName = 'proof_' . $eventId . '_' . $user['id'] . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $uploadDir . $fileName)) {
                $proofPath = $uploadDir . $fileName;
            }
        }

        $this->runnerModel->joinEvent($eventId, $user['id'], $bib ?: null, $proofPath);
        add_flash('Alhamdulillah, Anda berhasil bergabung ke event lari ini!', 'success');
        $this->redirect('/ekskul/runner?event_id=' . $eventId . '&tab=my_activities');
    }

    /**
     * Setor Aktivitas Lari Manual oleh Guru
     */
    public function submitActivity()
    {
        require_login();
        $user = auth_user();

        $eventId = (int)($_POST['event_id'] ?? 0);
        $actDate = $_POST['activity_date'] ?? date('Y-m-d');
        $distKm = (float)str_replace(',', '.', $_POST['distance_km'] ?? 0);
        $actType = $_POST['activity_type'] ?? 'Run';
        $elevM = (int)($_POST['elevation_gain_m'] ?? 0);
        $stravaUrl = trim($_POST['strava_url'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        // Hitung durasi dalam detik (format HH:MM:SS)
        $durStr = trim($_POST['duration'] ?? '00:00:00');
        $durSec = $this->parseTimeToSeconds($durStr);

        if ($distKm <= 0) {
            add_flash('Jarak lari (KM) harus lebih dari 0.', 'error');
            $this->redirect('/ekskul/runner?event_id=' . $eventId . '&tab=my_activities');
        }

        if ($durSec <= 0) {
            add_flash('Durasi waktu lari harus diisi dengan benar.', 'error');
            $this->redirect('/ekskul/runner?event_id=' . $eventId . '&tab=my_activities');
        }

        // Hitung pace per KM (detik)
        $paceStr = trim($_POST['pace'] ?? '');
        $paceSec = $this->parseTimeToSeconds($paceStr);
        if ($paceSec <= 0 && $distKm > 0) {
            $paceSec = (int)round($durSec / $distKm);
        }

        // Cek pendaftaran peserta
        $participant = $this->runnerModel->getParticipant($eventId, $user['id']);
        if (!$participant) {
            // Otomatis join jika belum
            $partId = $this->runnerModel->joinEvent($eventId, $user['id']);
        } else {
            $partId = $participant['id'];
        }

        $this->runnerModel->submitActivity([
            'event_id' => $eventId,
            'participant_id' => $partId,
            'user_id' => $user['id'],
            'activity_date' => $actDate,
            'activity_type' => $actType,
            'distance_km' => $distKm,
            'duration_seconds' => $durSec,
            'pace_seconds' => $paceSec,
            'elevation_gain_m' => $elevM,
            'strava_url' => $stravaUrl ?: null,
            'notes' => $notes ?: null
        ]);

        add_flash('Aktivitas lari berhasil disimpan! Menunggu verifikasi admin.', 'success');
        $this->redirect('/ekskul/runner?event_id=' . $eventId . '&tab=my_activities');
    }

    /**
     * Edit Aktivitas Lari yang Masih Pending
     */
    public function updateActivity()
    {
        require_login();
        $user = auth_user();

        $id = (int)($_POST['activity_id'] ?? 0);
        $eventId = (int)($_POST['event_id'] ?? 0);
        $actDate = $_POST['activity_date'] ?? date('Y-m-d');
        $distKm = (float)str_replace(',', '.', $_POST['distance_km'] ?? 0);
        $actType = $_POST['activity_type'] ?? 'Run';
        $elevM = (int)($_POST['elevation_gain_m'] ?? 0);
        $stravaUrl = trim($_POST['strava_url'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        $durStr = trim($_POST['duration'] ?? '00:00:00');
        $durSec = $this->parseTimeToSeconds($durStr);
        $paceSec = ($distKm > 0) ? (int)round($durSec / $distKm) : 0;

        $ok = $this->runnerModel->updateActivity($id, $user['id'], [
            'activity_date' => $actDate,
            'activity_type' => $actType,
            'distance_km' => $distKm,
            'duration_seconds' => $durSec,
            'pace_seconds' => $paceSec,
            'elevation_gain_m' => $elevM,
            'strava_url' => $stravaUrl ?: null,
            'notes' => $notes ?: null
        ]);

        if ($ok) {
            add_flash('Aktivitas lari berhasil diperbarui.', 'success');
        } else {
            add_flash('Gagal memperbarui aktivitas. Hanya aktivitas berstatus pending yang dapat diubah.', 'error');
        }

        $this->redirect('/ekskul/runner?event_id=' . $eventId . '&tab=my_activities');
    }

    /**
     * Hapus Aktivitas Lari
     */
    public function deleteActivity()
    {
        require_login();
        $user = auth_user();
        $isAdmin = (auth_get_role() === 'admin');

        $id = (int)($_POST['activity_id'] ?? 0);
        $eventId = (int)($_POST['event_id'] ?? 0);

        $ok = $this->runnerModel->deleteActivity($id, $user['id'], $isAdmin);
        if ($ok) {
            add_flash('Aktivitas berhasil dihapus.', 'success');
        } else {
            add_flash('Gagal menghapus aktivitas.', 'error');
        }

        $this->redirect('/ekskul/runner?event_id=' . $eventId . '&tab=' . ($isAdmin && !empty($_GET['from_admin']) ? 'admin_panel' : 'my_activities'));
    }

    /**
     * Verifikasi Aktivitas oleh Admin (Setujui / Tolak)
     */
    public function verifyActivity()
    {
        require_admin();
        $admin = auth_user();

        $activityId = (int)($_POST['activity_id'] ?? 0);
        $status = $_POST['status'] ?? 'approved';
        $reason = trim($_POST['rejection_reason'] ?? '');
        $eventId = (int)($_POST['event_id'] ?? 0);

        $ok = $this->runnerModel->verifyActivity($activityId, $status, $admin['id'], $reason);
        if ($ok) {
            if ($status === 'approved') {
                add_flash('Aktivitas lari berhasil diverifikasi dan disetujui.', 'success');
            } else {
                add_flash('Aktivitas lari ditolak dengan alasan yang dicatat.', 'info');
            }
        } else {
            add_flash('Gagal memverifikasi aktivitas.', 'error');
        }

        $this->redirect('/ekskul/runner?event_id=' . $eventId . '&tab=admin_panel');
    }

    /**
     * Simpan Event Baru / Edit Event oleh Admin
     */
    public function saveEvent()
    {
        require_admin();
        $admin = auth_user();

        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $name = trim($_POST['name'] ?? '');
        $groupId = !empty($_POST['group_id']) ? (int)$_POST['group_id'] : null;
        $startDate = $_POST['start_date'] ?? date('Y-m-01');
        $endDate = $_POST['end_date'] ?? date('Y-m-t');
        $targetKm = (float)str_replace(',', '.', $_POST['target_km'] ?? 42.0);
        $mode = $_POST['mode'] ?? 'MANUAL';
        $cost = (float)str_replace(['.', ','], ['', '.'], $_POST['cost'] ?? 0);
        $status = $_POST['status'] ?? 'ACTIVE';
        $desc = trim($_POST['description'] ?? '');

        // Jika membuat grup baru di tempat
        $newGroup = trim($_POST['new_group_name'] ?? '');
        if (!empty($newGroup)) {
            $groupId = $this->runnerModel->saveGroup($newGroup);
        }

        if (empty($name)) {
            add_flash('Nama event wajib diisi.', 'error');
            $this->redirect('/ekskul/runner?tab=admin_panel');
        }

        $savedId = $this->runnerModel->saveEvent([
            'id' => $id,
            'name' => $name,
            'group_id' => $groupId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'target_km' => $targetKm,
            'mode' => $mode,
            'team_mode' => 'solo',
            'cost' => $cost,
            'status' => $status,
            'description' => $desc,
            'created_by' => $admin['id']
        ]);

        add_flash('Event lari berhasil disimpan.', 'success');
        $this->redirect('/ekskul/runner?event_id=' . $savedId . '&tab=admin_panel');
    }

    /**
     * Hapus Event Lari oleh Admin
     */
    public function deleteEvent()
    {
        require_admin();
        $id = (int)($_POST['id'] ?? 0);
        $this->runnerModel->deleteEvent($id);
        add_flash('Event lari berhasil dihapus.', 'success');
        $this->redirect('/ekskul/runner?tab=admin_panel');
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    private function parseTimeToSeconds($timeStr)
    {
        if (empty($timeStr)) return 0;
        $parts = array_map('intval', explode(':', $timeStr));
        if (count($parts) === 3) {
            return ($parts[0] * 3600) + ($parts[1] * 60) + $parts[2];
        } elseif (count($parts) === 2) {
            return ($parts[0] * 60) + $parts[1];
        } elseif (count($parts) === 1) {
            return $parts[0] * 60; // default menit
        }
        return 0;
    }
}
