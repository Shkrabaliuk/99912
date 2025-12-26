<?php
class Validator {
    private $errors = [];
    
    public function required($value, $field) {
        if (empty($value)) {
            $this->errors[$field][] = "{$field} є обов'язковим";
        }
        return $this;
    }
    
    public function maxLength($value, $field, $max) {
        if (mb_strlen($value) > $max) {
            $this->errors[$field][] = "{$field} не може бути довшим ніж {$max} символів";
        }
        return $this;
    }
    
    public function minLength($value, $field, $min) {
        if (mb_strlen($value) < $min) {
            $this->errors[$field][] = "{$field} має бути не менше {$min} символів";
        }
        return $this;
    }
    
    public function email($value, $field) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = "{$field} має бути валідною email адресою";
        }
        return $this;
    }
    
    public function url($value, $field) {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $this->errors[$field][] = "{$field} має бути валідним URL";
        }
        return $this;
    }
    
    public function in($value, $field, $allowed) {
        if (!in_array($value, $allowed)) {
            $this->errors[$field][] = "{$field} має бути одним з: " . implode(', ', $allowed);
        }
        return $this;
    }
    
    public function fails() {
        return !empty($this->errors);
    }
    
    public function getErrors() {
        return $this->errors;
    }
    
    public function firstError() {
        foreach ($this->errors as $field => $errors) {
            return $errors[0];
        }
        return null;
    }
    
    // Готові валідатори для типових сценаріїв
    public static function validatePost($data) {
        $v = new self();
        $v->required($data['title'] ?? '', 'title')
          ->maxLength($data['title'] ?? '', 'title', 255);
        
        $v->required($data['content'] ?? '', 'content')
          ->minLength($data['content'] ?? '', 'content', 10);
        
        if (!empty($data['status'])) {
            $v->in($data['status'], 'status', ['draft', 'published']);
        }
        
        return $v;
    }
    
    public static function validatePage($data) {
        $v = new self();
        $v->required($data['title'] ?? '', 'title')
          ->maxLength($data['title'] ?? '', 'title', 255);
        
        $v->required($data['content'] ?? '', 'content')
          ->minLength($data['content'] ?? '', 'content', 10);
        
        if (!empty($data['meta_description'])) {
            $v->maxLength($data['meta_description'], 'meta_description', 500);
        }
        
        return $v;
    }
    
    public static function validateComment($data) {
        $v = new self();
        $v->required($data['author_name'] ?? '', 'author_name')
          ->maxLength($data['author_name'] ?? '', 'author_name', 100);
        
        $v->required($data['author_email'] ?? '', 'author_email')
          ->email($data['author_email'] ?? '', 'author_email');
        
        $v->required($data['content'] ?? '', 'content')
          ->minLength($data['content'] ?? '', 'content', 3)
          ->maxLength($data['content'] ?? '', 'content', 5000);
        
        return $v;
    }
    
    public static function validateSettings($data) {
        $v = new self();
        
        if (!empty($data['site_name'])) {
            $v->required($data['site_name'], 'site_name')
              ->maxLength($data['site_name'], 'site_name', 255);
        }
        
        if (!empty($data['posts_per_page'])) {
            if (!is_numeric($data['posts_per_page']) || $data['posts_per_page'] < 1 || $data['posts_per_page'] > 100) {
                $v->errors['posts_per_page'][] = 'Кількість постів має бути від 1 до 100';
            }
        }
        
        if (!empty($data['new_password'])) {
            $v->minLength($data['new_password'], 'new_password', 8);
        }
        
        return $v;
    }
}
