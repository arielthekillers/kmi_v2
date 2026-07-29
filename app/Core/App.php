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

        // Settings Routes (admin only)
        $this->router->get('/settings/general', ['App\Controllers\SettingsController', 'general']);
        
        $this->router->get('/settings/whatsapp', ['App\Controllers\SettingsController', 'whatsappApi']);
        $this->router->post('/settings/whatsapp/update', ['App\Controllers\SettingsController', 'updateWhatsappApi']);

        $this->router->get('/settings/tv/layout', ['App\Controllers\SettingsController', 'tvShowcaseLayout']);
        $this->router->get('/settings/messaging', ['App\Controllers\MessagingController', 'index']);
        $this->router->post('/settings/messaging/send', ['App\Controllers\MessagingController', 'sendManualMessage']);
        $this->router->post('/settings/messaging/delete', ['App\Controllers\MessagingController', 'deleteMessage']);
        $this->router->post('/settings/messaging/bulk-delete', ['App\Controllers\MessagingController', 'bulkDelete']);
        $this->router->post('/settings/messaging/bulk-resend', ['App\Controllers\MessagingController', 'bulkResend']);
        $this->router->get('/api/users/search', ['App\Controllers\MessagingController', 'searchUsers']);
        $this->router->post('/api/messaging/status', ['App\Controllers\MessagingController', 'getStatuses']);
        $this->router->get('/api/cron/whatsapp', ['App\Controllers\WhatsappWorkerController', 'processQueue']);
        $this->router->post('/api/db-sync', ['App\Controllers\ApiSyncController', 'handleSync']);
        $this->router->get('/settings/tv/layout', ['App\Controllers\SettingsController', 'tvShowcaseLayout']);
        $this->router->get('/activity-logs', ['App\Controllers\ActivityLogController', 'index']);
        $this->router->get('/settings/tv/bgm', ['App\Controllers\SettingsController', 'tvShowcaseBgm']);
        $this->router->get('/settings/tv/hours', ['App\Controllers\SettingsController', 'tvShowcaseHours']);
        $this->router->get('/settings/tv/quotes', ['App\Controllers\SettingsController', 'tvShowcaseQuotes']);
        $this->router->post('/settings/tv/bgm-youtube', ['App\Controllers\SettingsController', 'updateYoutubeBgm']);
        $this->router->post('/settings/tv/update-mode', ['App\Controllers\SettingsController', 'updateShowcaseMode']);
        $this->router->post('/settings/upload-audio', ['App\Controllers\SettingsController', 'uploadAudio']);
        $this->router->post('/settings/update-hours', ['App\Controllers\SettingsController', 'updateHours']);
        $this->router->post('/settings/update-quotes', ['App\Controllers\SettingsController', 'updateQuotes']);

        // Subjects Routes
        $this->router->get('/subjects', ['App\Controllers\SubjectController', 'index']);
        $this->router->post('/subjects/store', ['App\Controllers\SubjectController', 'store']);
        $this->router->get('/subjects/delete', ['App\Controllers\SubjectController', 'delete']);

        // Teachers Routes
        $this->router->get('/teachers', ['App\Controllers\TeacherController', 'index']);
        $this->router->post('/teachers/store', ['App\Controllers\TeacherController', 'store']);
        $this->router->get('/teachers/delete', ['App\Controllers\TeacherController', 'delete']);
        $this->router->get('/teachers/toggle-status', ['App\Controllers\TeacherController', 'toggleStatus']);
        $this->router->get('/teachers/trash', ['App\Controllers\TeacherController', 'trash']);
        $this->router->get('/teachers/restore', ['App\Controllers\TeacherController', 'restore']);
        $this->router->get('/teachers/forceDelete', ['App\Controllers\TeacherController', 'forceDelete']);
        $this->router->post('/teachers/reset-password', ['App\Controllers\TeacherController', 'resetPassword']);
        $this->router->post('/teachers/export', ['App\Controllers\TeacherController', 'export']);
        $this->router->post('/teachers/share-credentials', ['App\Controllers\TeacherController', 'shareCredentials']);

        // Classes Routes
        $this->router->get('/classes', ['App\Controllers\KelasController', 'index']);
        $this->router->get('/classes/detail', ['App\Controllers\KelasController', 'detail']);
        $this->router->get('/classes/export-leger', ['App\Controllers\KelasController', 'exportLeger']);
        $this->router->post('/classes/store', ['App\Controllers\KelasController', 'store']);
        $this->router->get('/classes/delete', ['App\Controllers\KelasController', 'delete']);
        $this->router->post('/classes/save-perilaku', ['App\Controllers\KelasController', 'savePerilaku']);

        // Schedule Routes
        $this->router->get('/schedule', ['App\Controllers\ScheduleController', 'index']);
        $this->router->post('/schedule/store', ['App\Controllers\ScheduleController', 'store']);

        // Grades Routes
        $this->router->get('/grades', ['App\Controllers\GradeController', 'index']);
        $this->router->post('/grades/create', ['App\Controllers\GradeController', 'create']);
        $this->router->get('/grades/edit', ['App\Controllers\GradeController', 'edit']);
        $this->router->post('/grades/update', ['App\Controllers\GradeController', 'update']);
        $this->router->get('/grades/delete', ['App\Controllers\GradeController', 'delete']);
        $this->router->get('/grades/trash', ['App\Controllers\GradeController', 'trash']);
        $this->router->get('/grades/restore', ['App\Controllers\GradeController', 'restore']);
        $this->router->get('/grades/forceDelete', ['App\Controllers\GradeController', 'force_delete']);
        $this->router->post('/grades/unlock', ['App\Controllers\GradeController', 'unlock']);
        
        // Panitia Ujian Routes
        $this->router->get('/grades/panitia', ['App\Controllers\PanitiaController', 'index']);
        $this->router->post('/grades/panitia/session/status', ['App\Controllers\PanitiaController', 'updateSessionStatus']);
        $this->router->post('/grades/panitia/committee/update', ['App\Controllers\PanitiaController', 'updateCommittee']);

        // Piket Routes
        $this->router->get('/piket/office', ['App\Controllers\PiketController', 'indexOffice']);
        $this->router->post('/piket/office/update', ['App\Controllers\PiketController', 'updateOffice']);
        $this->router->get('/piket/roaming', ['App\Controllers\PiketController', 'indexRoaming']);
        $this->router->post('/piket/roaming/update', ['App\Controllers\PiketController', 'updateRoaming']);

        // Page Routes
        $this->router->get('/tanqih', ['App\Controllers\TanqihController', 'index']);
        $this->router->post('/tanqih/verify', ['App\Controllers\TanqihController', 'verify']);
        $this->router->get('/tanqih/report', ['App\Controllers\TanqihController', 'report']);

        // Attendance Routes
        $this->router->get('/attendance', ['App\Controllers\AttendanceController', 'index']);
        $this->router->post('/attendance/store', ['App\Controllers\AttendanceController', 'store']);
        $this->router->get('/attendance/report', ['App\Controllers\AttendanceController', 'report']);

        // Student Attendance Routes (Absensi Santri)
        $this->router->get('/student-attendance', ['App\Controllers\StudentAttendanceController', 'index']);
        $this->router->post('/student-attendance/store', ['App\Controllers\StudentAttendanceController', 'store']);

        // PBM Committee Routes (Bagian PBM)
        $this->router->get('/student-attendance/pbm', ['App\Controllers\StudentAttendanceController', 'pbmIndex']);
        $this->router->post('/student-attendance/pbm/session/status', ['App\Controllers\StudentAttendanceController', 'updateSessionStatus']);
        $this->router->post('/student-attendance/pbm/committee/update', ['App\Controllers\StudentAttendanceController', 'updateCommittee']);

        // Students Routes (Modul)
        $this->router->get('/students', ['App\Modules\Students\Controllers\StudentController', 'index']);
        $this->router->get('/students/create', ['App\Modules\Students\Controllers\StudentController', 'create']);
        $this->router->post('/students/store', ['App\Modules\Students\Controllers\StudentController', 'store']);
        $this->router->get('/students/edit', ['App\Modules\Students\Controllers\StudentController', 'edit']);
        $this->router->post('/students/update', ['App\Modules\Students\Controllers\StudentController', 'update']);
        $this->router->get('/students/delete', ['App\Modules\Students\Controllers\StudentController', 'delete']);
        $this->router->get('/students/trash', ['App\Modules\Students\Controllers\StudentController', 'trash']);
        $this->router->get('/students/restore', ['App\Modules\Students\Controllers\StudentController', 'restore']);
        $this->router->get('/students/forceDelete', ['App\Modules\Students\Controllers\StudentController', 'forceDelete']);
        $this->router->get('/students/promote', ['App\Modules\Students\Controllers\StudentController', 'promote']);
        $this->router->post('/students/promote/store', ['App\Modules\Students\Controllers\StudentController', 'processPromotion']);
        $this->router->post('/students/export', ['App\Modules\Students\Controllers\StudentController', 'export']);

        // API regions proxy
        $this->router->get('/api/regions', ['App\Modules\Students\Controllers\StudentController', 'apiRegions']);
        $this->router->get('/api/postal-codes', ['App\Modules\Students\Controllers\StudentController', 'apiPostalCode']);
        $this->router->get('/api/kelas', ['App\Modules\Students\Controllers\StudentController', 'apiGetKelas']);

        // Public PPSB Routes
        $this->router->get('/ppsb/daftar', ['App\Modules\Students\Controllers\PpsbController', 'register']);
        $this->router->post('/ppsb/store', ['App\Modules\Students\Controllers\PpsbController', 'storePublic']);
        $this->router->get('/ppsb/success', ['App\Modules\Students\Controllers\PpsbController', 'success']);

        // Admin PPSB Routes
        $this->router->get('/admin/ppsb', ['App\Modules\Students\Controllers\PpsbController', 'adminIndex']);
        $this->router->get('/admin/ppsb/statistik', ['App\Modules\Students\Controllers\PpsbController', 'statistics']);
        $this->router->get('/admin/ppsb/edit', ['App\Modules\Students\Controllers\PpsbController', 'edit']);
        $this->router->post('/admin/ppsb/update', ['App\Modules\Students\Controllers\PpsbController', 'updateData']);
        $this->router->post('/admin/ppsb/status', ['App\Modules\Students\Controllers\PpsbController', 'updateStatus']);
        $this->router->post('/admin/ppsb/bulk', ['App\Modules\Students\Controllers\PpsbController', 'bulkAction']);
        $this->router->post('/admin/ppsb/enroll', ['App\Modules\Students\Controllers\PpsbController', 'enroll']);
        $this->router->post('/admin/ppsb/cancel-enroll', ['App\Modules\Students\Controllers\PpsbController', 'cancelEnroll']);
        $this->router->get('/admin/ppsb/delete', ['App\Modules\Students\Controllers\PpsbController', 'delete']);
        $this->router->post('/admin/ppsb/import-csv', ['App\Modules\Students\Controllers\PpsbController', 'importCsv']);

        // Student History & Inactive Management
        $this->router->get('/students/history', ['App\Modules\Students\Controllers\StudentController', 'history']);
        $this->router->post('/students/history/update-status', ['App\Modules\Students\Controllers\StudentController', 'updateStatus']);
        $this->router->post('/students/history/re-enroll', ['App\Modules\Students\Controllers\StudentController', 'reEnroll']);
        $this->router->post('/students/history/rollback', ['App\Modules\Students\Controllers\StudentController', 'rollbackHistory']);

        // Academic Year Routes
        $this->router->get('/academic-years', ['App\Controllers\AcademicYearController', 'index']);
        $this->router->post('/academic-years/store', ['App\Controllers\AcademicYearController', 'store']);
        $this->router->post('/academic-years/set-active', ['App\Controllers\AcademicYearController', 'setActive']);

        // Academic Calendar Routes
        $this->router->get('/academic-calendar', ['App\Controllers\AcademicCalendarController', 'index']);
        $this->router->post('/academic-calendar/store', ['App\Controllers\AcademicCalendarController', 'store']);
        $this->router->get('/academic-calendar/edit', ['App\Controllers\AcademicCalendarController', 'edit']);
        $this->router->post('/academic-calendar/update', ['App\Controllers\AcademicCalendarController', 'update']);
        $this->router->get('/academic-calendar/delete', ['App\Controllers\AcademicCalendarController', 'delete']);

        // WhatsApp Worker Route
        $this->router->get('/whatsapp/process', ['App\Controllers\WhatsappWorkerController', 'processQueue']);
    }

    public function run()
    {
        try {
            echo $this->router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
        } catch (\Throwable $e) {
            if (defined('APP_ENV') && APP_ENV === 'development') {
                echo "<h1>App Error</h1>";
                echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
                echo "<p><strong>File:</strong> " . $e->getFile() . " on line " . $e->getLine() . "</p>";
                echo "<pre>" . $e->getTraceAsString() . "</pre>";
            } else {
                error_log("App Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
                http_response_code(500);
                $errorView = __DIR__ . '/../Views/errors/500.php';
                if (file_exists($errorView)) {
                    include $errorView;
                } else {
                    echo "<h1>500 Internal Server Error</h1>";
                    echo "<p>Something went wrong. Please try again later.</p>";
                }
            }
        }
    }
}
