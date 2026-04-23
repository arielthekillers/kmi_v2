<?php
// helpers/profile_helper.php

if (defined('PROFILE_HELPER_LOADED')) return;
define('PROFILE_HELPER_LOADED', true);

/**
 * Extract initials from a name
 * @param string $nama Full name
 * @return string Initials (e.g., "Budi Susanto" -> "BS")
 */
function get_teacher_initials($nama) {
    if (empty($nama)) return '??';
    
    // Remove common prefixes
    $nama = preg_replace('/^(Al-Ustadz|Al-Ustadzah|Kiai|Ustadz|Ustadzah)\s+/i', '', $nama);
    
    // Remove titles and degrees
    $nama = preg_replace('/,?\s+(S\.Pd\.?I?|M\.Pd\.?I?|Lc\.?|S\.H\.?I?|M\.S\.?I?|S\.Ag\.?|BA\.?|S\.E\.?|S\.A\.B\.?)\.?$/i', '', $nama);
    
    // Split into words
    $words = preg_split('/\s+/', trim($nama));
    $words = array_filter($words); // Remove empty elements
    
    if (empty($words)) return '??';
    
    // Get first letter of first word and last word
    if (count($words) == 1) {
        $initials = strtoupper(substr($words[0], 0, 2));
    } else {
        $first = strtoupper(substr($words[0], 0, 1));
        $last = strtoupper(substr(end($words), 0, 1));
        $initials = $first . $last;
    }
    
    return $initials;
}

/**
 * Generate a color based on string hash
 * @param string $str String to hash
 * @return string Hex color code
 */
function get_avatar_color($str) {
    $colors = [
        '#6366f1', // Indigo
        '#8b5cf6', // Violet
        '#ec4899', // Pink
        '#f43f5e', // Rose
        '#f59e0b', // Amber
        '#10b981', // Emerald
        '#06b6d4', // Cyan
        '#3b82f6', // Blue
        '#14b8a6', // Teal
        '#a855f7', // Purple
    ];
    
    $hash = crc32($str);
    $index = abs($hash) % count($colors);
    return $colors[$index];
}

/**
 * Generate SVG avatar with initials
 * @param string $initials Initials to display
 * @param string $color Background color
 * @return string SVG content
 */
function generate_initials_svg($initials, $color = null) {
    if ($color === null) {
        $color = get_avatar_color($initials);
    }
    
    $initials = htmlspecialchars($initials, ENT_QUOTES, 'UTF-8');
    
    $svg = <<<SVG
<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
    <rect width="200" height="200" fill="{$color}"/>
    <text x="50%" y="50%" text-anchor="middle" dy=".35em" fill="white" font-family="Arial, sans-serif" font-size="80" font-weight="bold">{$initials}</text>
</svg>
SVG;
    
    return $svg;
}

/**
 * Get profile picture URL for a teacher
 * @param string $teacherId Teacher ID
 * @param string $teacherName Teacher name (fallback if no ID)
 * @return string URL to profile picture or avatar
 */
function get_profile_picture_url($teacherId, $teacherName = '', $prefetchedPath = null) {
    // 1. Use Prefetched path if available (Efficiency!)
    $path = $prefetchedPath;
    
    // 2. Check Database only if no prefetched path provided
    if ($path === null && $teacherId) {
        $teacher = get_teacher_biodata($teacherId);
        $path = $teacher['profile_picture'] ?? null;
    }

    if (!empty($path)) {
        // Ensure path is relative to web root if stored absolute or with prefix
        if (file_exists(__DIR__ . '/../' . $path)) {
             return $path . '?t=' . time();
        }
    }

    // 2. Fallback to existing file check (legacy or if DB update pending)
    $extensions = ['jpg', 'jpeg', 'png', 'webp'];
    $basePath = __DIR__ . '/../uploads/profiles/';
    
    foreach ($extensions as $ext) {
        $filePath = $basePath . $teacherId . '.' . $ext;
        if (file_exists($filePath)) {
            return 'uploads/profiles/' . $teacherId . '.' . $ext . '?t=' . filemtime($filePath);
        }
    }
    
    // 3. Avatar Fallback
    if ($teacherId) {
        return 'avatar.php?id=' . urlencode($teacherId);
    } else if ($teacherName) {
        return 'avatar.php?name=' . urlencode($teacherName);
    }
    
    return 'avatar.php?name=Unknown';
}

/**
 * Get complete teacher biodata
 * @param string $teacherId Teacher ID (legacy_id or numeric ID?)
 * @return array|null Teacher data or null if not found
 */
function get_teacher_biodata($teacherId) {
    $db = \App\Core\Database::getInstance();
    
    // Try by numeric ID
    if (is_numeric($teacherId)) {
        $sql = "SELECT u.*, tp.*, u.id as user_id, u.nama as nama, 
                       tp.phone as hp, tp.gender as jenis_kelamin, 
                       tp.birth_place as tempat_lahir, tp.birth_date as tanggal_lahir,
                       tp.address as alamat, tp.education as pendidikan_terakhir,
                       tp.year_graduated as tahun_lulus, tp.father_name as nama_ayah,
                       tp.mother_name as nama_ibu, tp.nip as nik
                FROM users u 
                LEFT JOIN teacher_profiles tp ON u.id = tp.user_id 
                WHERE u.id = ?";
        $stmt = $db->query($sql, [$teacherId]);
    } else {
        // Try by legacy_id
        $sql = "SELECT u.*, tp.*, u.id as user_id, u.nama as nama,
                       tp.phone as hp, tp.gender as jenis_kelamin, 
                       tp.birth_place as tempat_lahir, tp.birth_date as tanggal_lahir,
                       tp.address as alamat, tp.education as pendidikan_terakhir,
                       tp.year_graduated as tahun_lulus, tp.father_name as nama_ayah,
                       tp.mother_name as nama_ibu, tp.nip as nik
                FROM users u 
                LEFT JOIN teacher_profiles tp ON u.id = tp.user_id 
                WHERE u.legacy_id = ?";
        $stmt = $db->query($sql, [$teacherId]);
    }
    
    $teacher = $stmt->fetch();
    return $teacher ?: null;
}

/**
 * Update teacher biodata
 * @param string $teacherId Teacher ID
 * @param array $data Data to update
 * @return bool Success status
 */
function update_teacher_biodata($teacherId, $data) {
    $db = \App\Core\Database::getInstance();
    
    // Get User ID
    $teacher = get_teacher_biodata($teacherId);
    if (!$teacher) return false;
    
    $userId = $teacher['user_id'];
    
    try {
        $db->getConnection()->beginTransaction();
        
        // 1. Update User Table (Nama, Password)
        $userUpdates = [];
        $userParams = [];
        
        if (isset($data['nama'])) {
            $userUpdates[] = "nama = ?";
            $userParams[] = $data['nama'];
        }
        if (isset($data['password'])) {
            $userUpdates[] = "password = ?";
            $userParams[] = $data['password'];
        }
        
        if (!empty($userUpdates)) {
            $userParams[] = $userId;
            $db->query("UPDATE users SET " . implode(', ', $userUpdates) . " WHERE id = ?", $userParams);
        }
        
        // 2. Update/Insert Profile Table
        // Check if profile exists
        $stmt = $db->query("SELECT id FROM teacher_profiles WHERE user_id = ?", [$userId]);
        $profile = $stmt->fetch();
        
        $profileFields = [
            'nip', 'gender', 'birth_place', 'birth_date', 'address', 'phone', 
            'education', 'year_graduated', 'father_name', 'mother_name', 'profile_picture'
        ];
        
        // Map input keys to DB keys
        // Input: 'jenis_kelamin' -> DB: 'gender'
        // Input: 'tempat_lahir' -> DB: 'birth_place'
        // Input: 'tanggal_lahir' -> DB: 'birth_date'
        // Input: 'alamat' -> DB: 'address'
        // Input: 'hp' -> DB: 'phone'
        // Input: 'pendidikan_terakhir' -> DB: 'education'
        // Input: 'tahun_lulus' -> DB: 'year_graduated'
        // Input: 'nama_ayah' -> DB: 'father_name'
        // Input: 'nama_ibu' -> DB: 'mother_name'
        
        $mapping = [
            'jenis_kelamin' => 'gender',
            'tempat_lahir' => 'birth_place',
            'tanggal_lahir' => 'birth_date',
            'alamat' => 'address',
            'hp' => 'phone',
            'pendidikan_terakhir' => 'education',
            'tahun_lulus' => 'year_graduated',
            'nama_ayah' => 'father_name',
            'nama_ibu' => 'mother_name',
            'profile_picture' => 'profile_picture',
            'nip' => 'nip'
        ];
        
        $profileUpdates = [];
        $profileParams = [];
        
        foreach ($data as $key => $value) {
            if (isset($mapping[$key])) {
                $dbField = $mapping[$key];
                $profileUpdates[] = "$dbField = ?";
                $profileParams[] = $value;
            }
        }
        
        if (!empty($profileUpdates)) {
            if ($profile) {
                // Update
                $profileParams[] = $userId;
                $db->query("UPDATE teacher_profiles SET " . implode(', ', $profileUpdates) . " WHERE user_id = ?", $profileParams);
            } else {
                // Insert (Need to handle logic if partial data provided? For now assume update only updates provided fields)
                // If no profile, we must insert. But we need values for all columns or rely on NULL.
                // Construct Insert
                $cols = [];
                $vals = [];
                $params = [];
                $params[] = $userId;
                
                foreach ($data as $key => $value) {
                    if (isset($mapping[$key])) {
                        $cols[] = $mapping[$key];
                        $vals[] = "?";
                        $params[] = $value;
                    }
                }
                
                if (!empty($cols)) {
                    $sql = "INSERT INTO teacher_profiles (user_id, " . implode(', ', $cols) . ") VALUES (?, " . implode(', ', $vals) . ")";
                    $db->query($sql, $params);
                }
            }
        }
        
        $db->getConnection()->commit();
        return true;
        
    } catch (Exception $e) {
        $db->getConnection()->rollBack();
        error_log("Update teacher biodata error: " . $e->getMessage());
        return false;
    }
}
