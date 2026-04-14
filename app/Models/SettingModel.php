<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class SettingModel extends Model {
    protected $table = 'settings';
    protected $primaryKey = 'setting_key';

    /**
     * Get a setting by key. 
     * If not found, returns $default.
     */
    public function get($key, $default = null) {
        try {
            $stmt = $this->db->prepare("SELECT setting_value FROM {$this->table} WHERE {$this->primaryKey} = ?");
            $stmt->execute([$key]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                $value = json_decode($row['setting_value'], true);
                return $value === null ? $row['setting_value'] : $value;
            }
        } catch (\PDOException $e) {
            // Table might not exist yet on production, return default
            return $default;
        }
        
        return $default;
    }

    /**
     * Set a setting key.
     */
    public function set($key, $value) {
        $jsonValue = json_encode($value);
        
        $sql = "INSERT INTO {$this->table} (setting_key, setting_value) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$key, $jsonValue]);
    }

    /**
     * Get TV Showcase hours config or default
     */
    public function getTvHours() {
        $default = [
            ['type' => 'jam', 'label' => 'Jam 1', 'start' => '07:00', 'end' => '07:45', 'value' => 1],
            ['type' => 'jam', 'label' => 'Jam 2', 'start' => '07:45', 'end' => '08:30', 'value' => 2],
            ['type' => 'break', 'label' => 'Istirahat I', 'start' => '08:30', 'end' => '09:00', 'value' => 'istirahat1'],
            ['type' => 'jam', 'label' => 'Jam 3', 'start' => '09:00', 'end' => '09:45', 'value' => 3],
            ['type' => 'jam', 'label' => 'Jam 4', 'start' => '09:45', 'end' => '10:30', 'value' => 4],
            ['type' => 'break', 'label' => 'Istirahat II', 'start' => '10:30', 'end' => '11:00', 'value' => 'istirahat2'],
            ['type' => 'jam', 'label' => 'Jam 5', 'start' => '11:00', 'end' => '11:45', 'value' => 5],
            ['type' => 'jam', 'label' => 'Jam 6', 'start' => '11:45', 'end' => '12:30', 'value' => 6],
            ['type' => 'break', 'label' => 'Dzuhur & Makan', 'start' => '12:30', 'end' => '14:00', 'value' => 'dzuhur'],
            ['type' => 'jam', 'label' => 'Jam 7', 'start' => '14:00', 'end' => '14:45', 'value' => 7]
        ];
        
        return $this->get('tv_showcase_hours', $default);
    }
}
