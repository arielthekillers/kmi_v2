<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\PiketModel;
use App\Models\TeacherModel;

class PiketController extends Controller {

    protected $teacherModel;
    protected $piketModel;

    public function __construct() {
        parent::__construct();
        $this->teacherModel = new TeacherModel();
        $this->piketModel = new PiketModel();
    }


    private function getCommonData() {
        require_login();
        
        // Fetch Teachers for display/select
        $allTeachers = $this->teacherModel->findAll();
        $teachers = [];
        foreach ($allTeachers as $t) {
             if (in_array($t['role'], ['pengajar', 'admin'])) {
                 $teachers[$t['id']] = $t;
             }
        }
        // Sort by name
        uasort($teachers, function($a, $b) { return strnatcmp($a['nama'], $b['nama']); });

        return $teachers;
    }

    public function indexOffice() {
        $teachers = $this->getCommonData();
        $schedule = $this->piketModel->getSchedule('syeikh');

        $this->view('piket/index', [
            'title' => 'Jadwal Syeikh Diwan',
            'desc' => 'Daftar guru yang bertugas sebagai Syeikh Diwan setiap hari.',
            'type' => 'syeikh',
            'actionUrl' => '/piket/office/update',
            'schedule' => $schedule,
            'teachers' => $teachers,
            'days' => ['Sabtu', 'Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis']
        ]);
    }

    public function updateOffice() {
        require_admin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/piket/office');
        csrf_validate_token();

        $data = $_POST['piket'] ?? [];
        try {
            $this->piketModel->updateSchedule('syeikh', $data);
            add_flash('Jadwal Syeikh Diwan berhasil disimpan.', 'success');
        } catch (\Exception $e) {
            add_flash('Gagal menyimpan jadwal: ' . $e->getMessage(), 'error');
        }
        redirect('/piket/office');
    }

    public function indexRoaming() {
        $teachers = $this->getCommonData();
        $schedule = $this->piketModel->getSchedule('keliling');

        $this->view('piket/index', [
            'title' => 'Jadwal Piket Keliling',
            'desc' => 'Daftar guru yang bertugas sebagai Piket Keliling setiap hari.',
            'type' => 'keliling',
            'actionUrl' => '/piket/roaming/update',
            'schedule' => $schedule,
            'teachers' => $teachers,
            'days' => ['Sabtu', 'Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis']
        ]);
    }

    public function updateRoaming() {
        require_admin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/piket/roaming');
        csrf_validate_token();

        $data = $_POST['piket'] ?? [];
        try {
            $this->piketModel->updateSchedule('keliling', $data);
            add_flash('Jadwal Piket Keliling berhasil disimpan.', 'success');
        } catch (\Exception $e) {
            add_flash('Gagal menyimpan jadwal: ' . $e->getMessage(), 'error');
        }
        redirect('/piket/roaming');
    }
}
