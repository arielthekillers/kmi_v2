<?php

namespace App\Controllers;

use App\Core\Controller;

class MediaController extends Controller {
    public function avatar() {
        require_once __DIR__ . '/../../helpers/profile_helper.php';

        $teacherId = $_GET['id'] ?? '';
        $name = $_GET['name'] ?? '';

        // Determine name and initials
        if ($teacherId) {
            $teacher = get_teacher_biodata($teacherId);
            if ($teacher) {
                $name = $teacher['nama'] ?? 'Unknown';
            }
        }

        if (empty($name)) {
            $name = 'Unknown';
        }

        $initials = get_teacher_initials($name);
        $color = get_avatar_color($name);

        // Generate SVG
        $svg = generate_initials_svg($initials, $color);

        // Set headers
        header('Content-Type: image/svg+xml');
        header('Cache-Control: public, max-age=86400'); // Cache for 1 day
        header('Vary: Accept-Encoding');

        echo $svg;
        exit;
    }
}
