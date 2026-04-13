<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ScheduleModel;
use App\Models\KelasModel;
use App\Models\SubjectModel;
use App\Models\TeacherModel;

class ScheduleController extends Controller {
    public function index() {
        require_admin(); // Ensure admin access

        $scheduleModel = new ScheduleModel();
        $kelasModel = new KelasModel();
        $subjectModel = new SubjectModel();
        $teacherModel = new TeacherModel();

        // Check if models exist, otherwise fallback (migration in progress)
        // Ideally we assume models exist. If not, we might need to query raw SQL 
        // but let's assume Migration 11, 12, 13 are done properly.

        // Fetch Data for Dropdowns
        // Classes
        $classes = $kelasModel->findAll();
        $kelasData = [];
        foreach ($classes as $c) {
            $kelasData[$c['id']] = $c;
        }
        // Sor Classes
         try {
            uasort($kelasData, function ($a, $b) {
                $t = strnatcmp($a['tingkat'] ?? '', $b['tingkat'] ?? '');
                if ($t === 0) return strnatcmp($a['abjad'] ?? '', $b['abjad'] ?? '');
                return $t;
            });
        } catch (\Throwable $e) {}


        // Subjects
        $subjects = $subjectModel->findAll();
        $pelajaranData = [];
        foreach ($subjects as $s) {
            $pelajaranData[$s['id']] = $s;
        }
        // Sort Subjects
        usort($subjects, function($a, $b) {
            return strnatcmp($a['nama'], $b['nama']);
        });

        // Teachers (Filter only pengajar and admin)
        // TeacherModel might return all, we filter manually or assume TeacherModel handles it?
        // TeacherModel currently returns findAll users. Let's filter in PHP for now to be safe or verify Model.
        // Looking at TeacherModel code (if I could see it), it likely returns all. 
        // Let's use custom query if needed, but for now let's query users table directly via Model query if available, 
        // BUT TeacherModel usually wraps `users` table.
        // Let's assume findAll returns all users and we filter role.
        $allTeachers = $teacherModel->findAll();
        $pengajarData = [];
        $teacherOptions = [];
        foreach ($allTeachers as $t) {
            if (in_array($t['role'], ['pengajar', 'admin'])) {
                $pengajarData[$t['id']] = $t;
                $teacherOptions[] = $t;
            }
        }
        usort($teacherOptions, function($a, $b) {
            return strnatcmp($a['nama'], $b['nama']);
        });

        // Filter Logic
        $selectedKelasId = $_GET['kelas_id'] ?? '';
        $selectedPengajarId = $_GET['pengajar_id'] ?? '';

        if ($selectedPengajarId) {
            $selectedKelasId = '';
        }

        $viewTitle = '';
        $currentJadwal = [];
        $teacherSchedule = [];

        if ($selectedKelasId && isset($kelasData[$selectedKelasId])) {
            $k = $kelasData[$selectedKelasId];
            $viewTitle = 'Jadwal Kelas ' . $k['tingkat'] . '-' . $k['abjad'];
            $currentJadwal = $scheduleModel->getByClass($selectedKelasId);
        }

        if ($selectedPengajarId && isset($pengajarData[$selectedPengajarId])) {
            $p = $pengajarData[$selectedPengajarId];
            $viewTitle = 'Jadwal Mengajar: ' . $p['nama'];
            $teacherSchedule = $scheduleModel->getByTeacher($selectedPengajarId);
        }

        $days = ['Sabtu', 'Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis'];
        $hours = range(1, 7);

        $this->view('schedule/index', [
            'kelasData' => $kelasData, // Associative for lookup
            'subjectOptions' => $subjects, // Sorted array for dropdowns
            'pelajaranData' => $pelajaranData, // Associative for lookup
            'teacherOptions' => $teacherOptions, // Sorted array for dropdowns
            'pengajarData' => $pengajarData, // Associative for lookup
            'selectedKelasId' => $selectedKelasId,
            'selectedPengajarId' => $selectedPengajarId,
            'viewTitle' => $viewTitle,
            'currentJadwal' => $currentJadwal,
            'teacherSchedule' => $teacherSchedule,
            'days' => $days,
            'hours' => $hours
        ]);
    }

    public function store() {
        require_admin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/schedule');
        }

        csrf_validate_token();

        $kelasId = $_POST['kelas_id'] ?? '';
        $newSchedule = $_POST['schedule'] ?? [];

        if (empty($kelasId)) {
            add_flash('error', 'ID Kelas tidak ditemukan.');
            redirect('/schedule');
        }

        $model = new ScheduleModel();
        try {
            $model->updateBatch($kelasId, $newSchedule);
            add_flash('success', 'Jadwal berhasil diperbarui.');
        } catch (\Exception $e) {
            add_flash('error', 'Gagal menyimpan jadwal: ' . $e->getMessage());
        }

        redirect('/schedule?kelas_id=' . urlencode($kelasId));
    }

    public function mySchedule() {
        require_once __DIR__ . '/../../helpers/auth.php';
        require_login();
        
        $currentUserId = auth_get_user_id();
        if (!$currentUserId) {
            $this->redirect('/login');
        }

        $kelasModel = new KelasModel();
        $subjectModel = new SubjectModel();
        $scheduleModel = new ScheduleModel();

        $classes = $kelasModel->findAll();
        $kelasData = [];
        foreach ($classes as $c) {
            $kelasData[$c['id']] = $c;
        }

        $subjects = $subjectModel->findAll();
        $pelajaranData = [];
        foreach ($subjects as $s) {
            $pelajaranData[$s['id']] = $s;
        }

        $days = ['Sabtu', 'Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis'];
        $hours = range(1, 7);

        // getByTeacher() already returns a nested [$day][$hour] => ['mapel'=>..., 'kelas'=>...] array
        $mySchedule = $scheduleModel->getByTeacher($currentUserId);

        $this->view('schedule/my_schedule', [
            'kelasData' => $kelasData,
            'pelajaranData' => $pelajaranData,
            'days' => $days,
            'hours' => $hours,
            'mySchedule' => $mySchedule
        ]);
    }
}
