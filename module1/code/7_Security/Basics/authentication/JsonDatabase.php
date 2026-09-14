<?php

class JsonDatabase {
    private $file_path;
    private $data = [];
    
    public function __construct($file_path) {
        $this->file_path = $file_path;
        
        // Create data directory if it doesn't exist
        $dir = dirname($file_path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        // Load data from file
        $this->load_data();
    }
    
    // Load data from JSON file
    private function load_data() {
        if (file_exists($this->file_path)) {
            $json = file_get_contents($this->file_path);
            $this->data = json_decode($json, true) ?? [];
        } else {
            $this->data = [];
        }
    }
    
    // Save data to JSON file
    private function save_data() {
        $json = json_encode($this->data, JSON_PRETTY_PRINT);
        file_put_contents($this->file_path, $json);
    }
    
    // Add new record
    public function add($record) {
        // Generate unique ID
        $id = $this->get_next_id();
        $record['id'] = $id;
        
        $this->data[] = $record;
        $this->save_data();
        
        return $record;
    }
    
    // Find record by ID
    public function find_by_id($id) {
        foreach ($this->data as $record) {
            if ($record['id'] == $id) {
                return $record;
            }
        }
        return null;
    }
    
    // Find record by field value
    public function find_by_field($field, $value) {
        foreach ($this->data as $record) {
            if (isset($record[$field]) && $record[$field] === $value) {
                return $record;
            }
        }
        return null;
    }
    
    // Update record by ID
    public function update($id, $updates) {
        for ($i = 0; $i < count($this->data); $i++) {
            if ($this->data[$i]['id'] == $id) {
                $this->data[$i] = array_merge($this->data[$i], $updates);
                $this->save_data();
                return $this->data[$i];
            }
        }
        return null;
    }
    
    // Delete record by ID
    public function delete($id) {
        for ($i = 0; $i < count($this->data); $i++) {
            if ($this->data[$i]['id'] == $id) {
                $deleted = $this->data[$i];
                array_splice($this->data, $i, 1);
                $this->save_data();
                return $deleted;
            }
        }
        return null;
    }
    
    // Get all records
    public function read_data() {
        return $this->data;
    }
    
    // Get next available ID
    private function get_next_id() {
        $max_id = 0;
        foreach ($this->data as $record) {
            if (isset($record['id']) && $record['id'] > $max_id) {
                $max_id = $record['id'];
            }
        }
        return $max_id + 1;
    }
    
    // Count records
    public function count() {
        return count($this->data);
    }
    
    // Clear all data
    public function clear() {
        $this->data = [];
        $this->save_data();
    }
}
