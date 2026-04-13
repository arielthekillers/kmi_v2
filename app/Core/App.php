<?php

namespace App\Core;

class App
{
    protected $router;

    public function __construct()
    {
        $this->router = new Router();
        $this->registerRoutes();
    }

    protected function registerRoutes()
    {
        // Root Route
        $this->router->get('/', ['App\Controllers\DashboardController', 'index']);

        // Auth Routes
        $this->router->get('/login', ['App\Controllers\AuthController', 'login']);
        $this->router->post('/authenticate', ['App\Controllers\AuthController', 'authenticate']);
        $this->router->get('/logout', ['App\Controllers\AuthController', 'logout']);

        // Profile Routes
        $this->router->get('/profil', ['App\Controllers\ProfileController', 'index']);
        $this->router->post('/profil/simpan', ['App\Controllers\ProfileController', 'update']);
        $this->router->get('/change-password', ['App\Controllers\ProfileController', 'changePassword']);
        $this->router->post('/change-password/update', ['App\Controllers\ProfileController', 'updatePassword']);

        // Teacher Schedule
        $this->router->get('/jadwal-saya', ['App\Controllers\ScheduleController', 'mySchedule']);

        // Media & Showcase Routes
        $this->router->get('/avatar', ['App\Controllers\MediaController', 'avatar']);
        $this->router->get('/tvshowcase', ['App\Controllers\TvShowcaseController', 'index']);
        $this->router->get('/api/tv-data', ['App\Controllers\TvShowcaseController', 'apiData']);
        $this->router->post('/tvshowcase/upload-audio', ['App\Controllers\TvShowcaseController', 'uploadAudio']);

        // Subjects Routes
        $this->router->get('/subjects', ['App\Controllers\SubjectController', 'index']);
        $this->router->post('/subjects/store', ['App\Controllers\SubjectController', 'store']);
        $this->router->get('/subjects/delete', ['App\Controllers\SubjectController', 'delete']);

        // Teachers Routes
        $this->router->get('/teachers', ['App\Controllers\TeacherController', 'index']);
        $this->router->post('/teachers/store', ['App\Controllers\TeacherController', 'store']);
        $this->router->get('/teachers/delete', ['App\Controllers\TeacherController', 'delete']);
        $this->router->post('/teachers/reset-password', ['App\Controllers\TeacherController', 'resetPassword']);

        // Classes Routes
        $this->router->get('/classes', ['App\Controllers\KelasController', 'index']);
        $this->router->post('/classes/store', ['App\Controllers\KelasController', 'store']);
        // Classes Routes
        $this->router->get('/classes', ['App\Controllers\KelasController', 'index']);
        $this->router->post('/classes/store', ['App\Controllers\KelasController', 'store']);
        $this->router->get('/classes/delete', ['App\Controllers\KelasController', 'delete']);

        // Schedule Routes
        $this->router->get('/schedule', ['App\Controllers\ScheduleController', 'index']);
        $this->router->post('/schedule/store', ['App\Controllers\ScheduleController', 'store']);

        // Grades Routes
        $this->router->get('/grades', ['App\Controllers\GradeController', 'index']);
        $this->router->post('/grades/create', ['App\Controllers\GradeController', 'create']);
        $this->router->get('/grades/edit', ['App\Controllers\GradeController', 'edit']);
        $this->router->post('/grades/update', ['App\Controllers\GradeController', 'update']);
        $this->router->get('/grades/delete', ['App\Controllers\GradeController', 'delete']);
        $this->router->post('/grades/unlock', ['App\Controllers\GradeController', 'unlock']);

        // Piket Routes
        $this->router->get('/piket/office', ['App\Controllers\PiketController', 'indexOffice']);
        $this->router->post('/piket/office/update', ['App\Controllers\PiketController', 'updateOffice']);
        $this->router->get('/piket/roaming', ['App\Controllers\PiketController', 'indexRoaming']);
        $this->router->post('/piket/roaming/update', ['App\Controllers\PiketController', 'updateRoaming']);

        // Tanqih Routes
        $this->router->get('/tanqih', ['App\Controllers\TanqihController', 'index']);
        $this->router->post('/tanqih/verify', ['App\Controllers\TanqihController', 'verify']);
        $this->router->get('/tanqih/report', ['App\Controllers\TanqihController', 'report']);

        // Attendance Routes
        $this->router->get('/attendance', ['App\Controllers\AttendanceController', 'index']);
        $this->router->post('/attendance/store', ['App\Controllers\AttendanceController', 'store']);
        $this->router->get('/attendance/report', ['App\Controllers\AttendanceController', 'report']);
    }

    public function run()
    {
        echo $this->router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
    }
}
