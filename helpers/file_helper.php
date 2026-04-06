<?php

/**
 * Safely updates a JSON file using exclusive locking to prevent race conditions.
 * 
 * @param string $filepath Path to the JSON file.
 * @param callable $callback Function that receives the array data by reference and modifies it.
 * @return bool True on success, False on failure.
 */
function update_json_file($filepath, $callback) {
    if (!file_exists($filepath)) {
        // Create file if not exists
        if (file_put_contents($filepath, json_encode([])) === false) {
            return false;
        }
    }

    $fp = fopen($filepath, 'r+');
    if (!$fp) return false;

    // Acquire exclusive lock
    if (flock($fp, LOCK_EX)) {
        // Read current data
        $content = '';
        while (!feof($fp)) {
            $content .= fread($fp, 8192);
        }
        
        $data = json_decode($content, true) ?? [];
        
        // Execute callback to modify data
        $msg = call_user_func_array($callback, [&$data]);
        
        // Truncate file and rewind
        ftruncate($fp, 0);
        rewind($fp);
        
        // Write updated data
        fwrite($fp, json_encode($data, JSON_PRETTY_PRINT));
        fflush($fp); // Flush output before releasing lock
        
        // Release lock
        flock($fp, LOCK_UN);
        fclose($fp);
        
        return true;
    } else {
        fclose($fp);
        return false;
    }
}
