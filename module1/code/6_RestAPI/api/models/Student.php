<?php
/**
 * Simple Student Model
 * 
 * This class represents a student with basic properties and methods.
 * It's designed to be simple and easy to understand for beginners.
 */

class Student {
    private $id;
    private $name;
    private $email;
    private $major;
    private $year;
    private $created_at;
    private $updated_at;
    
    /**
     * Constructor - creates a new student
     */
    public function __construct() {
        $this->created_at = date('Y-m-d H:i:s');
        $this->updated_at = date('Y-m-d H:i:s');
    }
    
    /**
     * Convert student data to array format
     * This is useful for JSON encoding
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'major' => $this->major,
            'year' => $this->year,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
    
    // Simple getter and setter methods
    public function getId() {
        return $this->id;
    }
    
    public function setId($id) {
        $this->id = $id;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function setName($name) {
        $this->name = trim($name);
    }
    
    public function getEmail() {
        return $this->email;
    }
    
    public function setEmail($email) {
        $this->email = trim($email);
    }
    
    public function getMajor() {
        return $this->major;
    }
    
    public function setMajor($major) {
        $this->major = trim($major);
    }
    
    public function getYear() {
        return $this->year;
    }
    
    public function setYear($year) {
        $this->year = (int)$year;
    }
}
