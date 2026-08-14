<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AcademicCalendarModel;
use App\Models\AcademicYearModel;

class AcademicCalendarController extends Controller {

    protected $calendarModel;
    protected $yearModel;

    public function __construct() {
        parent::__construct();
        $this->calendarModel = new AcademicCalendarModel();
        $this->yearModel     = new AcademicYearModel();
    }

    /**
     * Show calendar list — accessible by admin & pengajar
     */
    public function index() {
        require_login();

        $activeYear = $this->yearModel->getActive();
        $activeYearId = $activeYear['id'] ?? null;

        $events = $activeYearId ? $this->calendarModel->getByYear($activeYearId) : [];

        // View mode
        $viewMode = $_GET['view'] ?? 'list';
        if (!in_array($viewMode, ['list', 'month'])) {
            $viewMode = 'list';
        }

        // Calendar variables
        $selectedMonth = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
        $selectedYearVal = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
        
        // Ensure month is within 1-12
        if ($selectedMonth < 1 || $selectedMonth > 12) {
            $selectedMonth = (int)date('m');
        }

        // Group events for list view
        $grouped = [];
        if ($viewMode === 'list') {
            foreach ($events as $event) {
                $monthKey = date('Y-m', strtotime($event['tanggal_mulai']));
                $grouped[$monthKey][] = $event;
            }
        }

        // Monthly view data preparation
        $calendarGrid = [];
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYearVal);
        $firstDayOfMonth = date('w', strtotime("$selectedYearVal-$selectedMonth-01"));
        
        // Pesantren week starts on Saturday (Sabtu = 0, Minggu = 1, Senin = 2, ..., Jumat = 6)
        $firstDayOffset = ($firstDayOfMonth + 1) % 7;

        // Collect events for the selected month
        $monthEvents = [];
        if ($viewMode === 'month') {
            foreach ($events as $event) {
                $start = strtotime($event['tanggal_mulai']);
                $end = empty($event['tanggal_selesai']) ? $start : strtotime($event['tanggal_selesai']);
                $monthStart = strtotime("$selectedYearVal-$selectedMonth-01");
                $monthEnd = strtotime("$selectedYearVal-$selectedMonth-$daysInMonth 23:59:59");
                
                // If event overlaps with the current month
                if ($start <= $monthEnd && $end >= $monthStart) {
                    $monthEvents[] = $event;
                }
            }
        }

        // Prev and Next month calculations
        $prevMonth = $selectedMonth - 1;
        $prevYear = $selectedYearVal;
        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear--;
        }
        $nextMonth = $selectedMonth + 1;
        $nextYear = $selectedYearVal;
        if ($nextMonth > 12) {
            $nextMonth = 1;
            $nextYear++;
        }

        $kelasModel = new \App\Models\KelasModel();
        $allKelas = $kelasModel->findAllActive();

        renderHeader('Kalender Akademik');
        $this->view('academic_calendar/index', [
            'activeYear'     => $activeYear,
            'activeYearId'   => $activeYearId,
            'grouped'        => $grouped,
            'eventCount'     => count($events),
            'viewMode'       => $viewMode,
            'selectedMonth'  => $selectedMonth,
            'selectedYearVal'=> $selectedYearVal,
            'daysInMonth'    => $daysInMonth,
            'firstDayOffset' => $firstDayOffset,
            'monthEvents'    => $monthEvents,
            'prevMonth'      => $prevMonth,
            'prevYear'       => $prevYear,
            'nextMonth'      => $nextMonth,
            'nextYear'       => $nextYear,
            'allKelas'       => $allKelas,
        ]);
        renderFooter();
    }

    /**
     * Store a new event — admin only
     */
    public function store() {
        require_admin();
        csrf_validate_token();

        $activeYear = $this->yearModel->getActive();
        
        $data = [
            'academic_year_id' => $activeYear['id'] ?? null,
            'tanggal_mulai'    => $_POST['tanggal_mulai']    ?? null,
            'tanggal_selesai'  => !empty($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : null,
            'keterangan'       => trim($_POST['keterangan']  ?? ''),
            'kategori'         => $_POST['kategori']         ?? 'Akademik',
        ];

        if (empty($data['academic_year_id']) || empty($data['tanggal_mulai']) || empty($data['keterangan'])) {
            add_flash('Data tidak lengkap atau tahun ajaran aktif tidak ditemukan.', 'error');
            $this->redirect('/academic-calendar');
            return;
        }

        $calendarId = $this->calendarModel->create($data);
        
        if ($calendarId && isset($_POST['is_override'])) {
            $is_full_day = isset($_POST['is_full_day']) ? 1 : 0;
            $hour_start = (int)($_POST['hour_start'] ?? 0);
            $hour_end = (int)($_POST['hour_end'] ?? 0);
            $target_type = $_POST['target_type'] ?? 'sekolah';
            $kelasIds = [];
            
            $kelasModel = new \App\Models\KelasModel();
            $allKelas = $kelasModel->findAllActive();

            if ($target_type === 'sekolah') {
                $kelasIds = array_column($allKelas, 'id');
            } elseif ($target_type === 'angkatan') {
                $tingkat = $_POST['target_tingkat'] ?? '';
                foreach ($allKelas as $k) {
                    if ((string)$k['tingkat'] === (string)$tingkat) {
                        $kelasIds[] = $k['id'];
                    }
                }
            } elseif ($target_type === 'kelas') {
                $kelasIds = $_POST['target_kelas'] ?? [];
            }
            
            if (!empty($kelasIds)) {
                $activityModel = new \App\Models\ActivityModel();
                $activityData = [
                    'name' => $data['keterangan'],
                    'type' => $data['kategori'],
                    'start_date' => $data['tanggal_mulai'],
                    'end_date' => $data['tanggal_selesai'] ?: $data['tanggal_mulai'],
                    'is_full_day' => $is_full_day,
                    'hour_start' => $hour_start,
                    'hour_end' => $hour_end
                ];
                $activityModel->createActivity($activityData, $kelasIds, $calendarId);
            }
        }

        add_flash('Event berhasil ditambahkan ke kalender.', 'success');
        $this->redirect('/academic-calendar');
    }

    /**
     * Show edit form — admin only
     */
    public function edit() {
        require_admin();

        $id    = $_GET['id'] ?? null;
        $event = $id ? $this->calendarModel->getById($id) : null;

        if (!$event) {
            add_flash('Event tidak ditemukan.', 'error');
            $this->redirect('/academic-calendar');
            return;
        }

        // Fetch existing override logic
        $activityModel = new \App\Models\ActivityModel();
        $stmt = $activityModel->query("SELECT * FROM school_activities WHERE academic_calendar_id = ?", [$id]);
        $existingOverride = $stmt->fetch(\PDO::FETCH_ASSOC);

        $existingTargets = [];
        $hourStart = null;
        $hourEnd = null;
        if ($existingOverride) {
            $stmt = $activityModel->query("SELECT kelas_id FROM activity_targets WHERE activity_id = ?", [$existingOverride['id']]);
            $existingTargets = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            // Fetch hour duration from activity_hours
            $stmt = $activityModel->query("SELECT hour_start, hour_end FROM activity_hours WHERE activity_id = ?", [$existingOverride['id']]);
            $hours = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($hours) {
                $hourStart = $hours['hour_start'];
                $hourEnd = $hours['hour_end'];
            }
        }

        $kelasModel = new \App\Models\KelasModel();
        $allKelas = $kelasModel->findAllActive();

        renderHeader('Edit Event Kalender');
        $this->view('academic_calendar/edit', [
            'event'            => $event,
            'existingOverride' => $existingOverride,
            'existingTargets'  => $existingTargets,
            'allKelas'         => $allKelas,
            'hourStart'        => $hourStart,
            'hourEnd'          => $hourEnd,
        ]);
        renderFooter();
    }

    /**
     * Update an event — admin only
     */
    public function update() {
        require_admin();
        csrf_validate_token();

        $id   = $_POST['id'] ?? null;
        $data = [
            'tanggal_mulai'    => $_POST['tanggal_mulai']    ?? null,
            'tanggal_selesai'  => !empty($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : null,
            'keterangan'       => trim($_POST['keterangan']  ?? ''),
            'kategori'         => $_POST['kategori']         ?? 'Akademik',
        ];

        if (!$id || empty($data['tanggal_mulai']) || empty($data['keterangan'])) {
            add_flash('Data tidak valid.', 'error');
            $this->redirect('/academic-calendar/edit?id=' . $id);
            return;
        }

        // Fetch existing to retain its academic_year_id
        $existing = $this->calendarModel->getById($id);
        if ($existing) {
            $data['academic_year_id'] = $existing['academic_year_id'];
            $this->calendarModel->update($id, $data);
            
            // Handle Override Logic Update
            $activityModel = new \App\Models\ActivityModel();
            
            // First, delete any existing override for this calendar_id
            $activityModel->query("DELETE FROM school_activities WHERE academic_calendar_id = ?", [$id]);
            
            if (isset($_POST['is_override'])) {
                $is_full_day = isset($_POST['is_full_day']) ? 1 : 0;
                $hour_start = (int)($_POST['hour_start'] ?? 0);
                $hour_end = (int)($_POST['hour_end'] ?? 0);
                $target_type = $_POST['target_type'] ?? 'kelas';
                $kelasIds = [];
                
                $kelasModel = new \App\Models\KelasModel();
                $allKelas = $kelasModel->findAllActive();

                if ($target_type === 'sekolah') {
                    $kelasIds = array_column($allKelas, 'id');
                } elseif ($target_type === 'angkatan') {
                    $tingkat = $_POST['target_tingkat'] ?? '';
                    foreach ($allKelas as $k) {
                        if ((string)$k['tingkat'] === (string)$tingkat) {
                            $kelasIds[] = $k['id'];
                        }
                    }
                } elseif ($target_type === 'kelas') {
                    $kelasIds = $_POST['target_kelas'] ?? [];
                }
                
                if (!empty($kelasIds)) {
                    $activityData = [
                        'name' => $data['keterangan'],
                        'type' => $data['kategori'],
                        'start_date' => $data['tanggal_mulai'],
                        'end_date' => $data['tanggal_selesai'] ?: $data['tanggal_mulai'],
                        'is_full_day' => $is_full_day,
                        'hour_start' => $hour_start,
                        'hour_end' => $hour_end
                    ];
                    $activityModel->createActivity($activityData, $kelasIds, $id);
                }
            }

            add_flash('Event berhasil diperbarui.', 'success');
        }

        $this->redirect('/academic-calendar');
    }

    /**
     * Delete an event — admin only
     */
    public function delete() {
        require_admin();

        $id = $_GET['id'] ?? null;

        if ($id) {
            $this->calendarModel->delete($id);
            add_flash('Event berhasil dihapus.', 'success');
        }

        $this->redirect('/academic-calendar');
    }
}
