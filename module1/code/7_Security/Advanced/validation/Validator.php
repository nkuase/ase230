<?php

class Validator {
    private $errors = [];
    
    // Check if field is required (not empty)
    public function required($value, $field_name) {
        if (empty($value)) {
            $this->errors[] = "$field_name is required";
        }
        return $this; // For method chaining
    }
    
    // Check if email format is valid
    public function email($value, $field_name) {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "$field_name must be a valid email";
        }
        return $this;
    }
    
    // Check minimum length
    public function minLength($value, $min, $field_name) {
        if (!empty($value) && strlen($value) < $min) {
            $this->errors[] = "$field_name must be at least $min characters";
        }
        return $this;
    }
    
    // Check maximum length
    public function maxLength($value, $max, $field_name) {
        if (!empty($value) && strlen($value) > $max) {
            $this->errors[] = "$field_name must be no more than $max characters";
        }
        return $this;
    }
    
    // Check if value is numeric
    public function numeric($value, $field_name) {
        if (!empty($value) && !is_numeric($value)) {
            $this->errors[] = "$field_name must be numeric";
        }
        return $this;
    }
    
    // Check minimum value
    public function min($value, $min, $field_name) {
        if (!empty($value) && is_numeric($value) && $value < $min) {
            $this->errors[] = "$field_name must be at least $min";
        }
        return $this;
    }
    
    // Check maximum value
    public function max($value, $max, $field_name) {
        if (!empty($value) && is_numeric($value) && $value > $max) {
            $this->errors[] = "$field_name must be no more than $max";
        }
        return $this;
    }
    
    // Check pattern (regular expression)
    public function pattern($value, $pattern, $field_name, $error_message = null) {
        if (!empty($value) && !preg_match($pattern, $value)) {
            $message = $error_message ?? "$field_name format is invalid";
            $this->errors[] = $message;
        }
        return $this;
    }
    
    // Username validation (letters, numbers, underscore, hyphen only)
    public function username($value, $field_name = 'Username') {
        if (!empty($value) && !preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $value)) {
            $this->errors[] = "$field_name must be 3-20 characters, letters, numbers, underscore, or hyphen only";
        }
        return $this;
    }
    
    // Strong password validation
    public function strongPassword($value, $field_name) {
        if (empty($value)) return $this;
        
        $errors = [];
        
        if (strlen($value) < 8) {
            $errors[] = "at least 8 characters";
        }
        if (!preg_match('/[A-Z]/', $value)) {
            $errors[] = "at least one uppercase letter";
        }
        if (!preg_match('/[a-z]/', $value)) {
            $errors[] = "at least one lowercase letter";
        }
        if (!preg_match('/[0-9]/', $value)) {
            $errors[] = "at least one number";
        }
        if (!preg_match('/[!@#$%^&*]/', $value)) {
            $errors[] = "at least one special character (!@#$%^&*)";
        }
        
        if (!empty($errors)) {
            $this->errors[] = "$field_name must have " . implode(', ', $errors);
        }
        
        return $this;
    }
    
    // Check if there are any errors
    public function hasErrors() {
        return !empty($this->errors);
    }
    
    // Get all errors
    public function getErrors() {
        return $this->errors;
    }
    
    // Get first error
    public function getFirstError() {
        return $this->errors[0] ?? null;
    }
    
    // Get errors as string
    public function getErrorsAsString($separator = '<br>') {
        return implode($separator, $this->errors);
    }
    
    // Clear all errors
    public function clear() {
        $this->errors = [];
        return $this;
    }
    
    // Count errors
    public function count() {
        return count($this->errors);
    }
}
