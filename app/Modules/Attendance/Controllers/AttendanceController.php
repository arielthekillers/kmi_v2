<?php

namespace App\Modules\Attendance\Controllers;

use App\Core\Controller;
use App\Modules\Attendance\Models\Attendance;

class AttendanceController extends Controller {

    public function index() {
        // Show selection form (Class & Date)
        $model = new Attendance();
        $kelas = $model->getKelasList();
        
        $selectedKelas = $_GET['kelas_id'] ?? null;
        $selectedDate = $_GET['date'] ?? date('Y-m-d');
        $students = [];

        if ($selectedKelas) {
            $students = $model->getAttendanceByClassAndDate($selectedKelas, $selectedDate);
        }

        $this->view('Attendance/Views/index', [
            'kelas' => $kelas,
            'students' => $students,
            'selectedKelas' => $selectedKelas,
            'selectedDate' => $selectedDate
        ]);
    }

    public function store() {
        session_start();
        $model = new Attendance();
        $date = $_POST['date'];
        $kelasId = $_POST['kelas_id'];
        $attendanceData = $_POST['attendance'] ?? [];
        $userId = $_SESSION['user_id'] ?? null;

        foreach ($attendanceData as $studentId => $data) {
            $model->save([
                'student_id' => $studentId,
                'date' => $date,
                'status' => $data['status'],
                'created_by' => $userId
            ]);
        }
        
        // Redirect back to show success
        $this->redirect("/attendance?kelas_id=$kelasId&date=$date");
    }
}
