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

        $this->calendarModel->create($data);
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

        renderHeader('Edit Event Kalender');
        $this->view('academic_calendar/edit', [
            'event' => $event,
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
