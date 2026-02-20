<?php

class ViewHelper {
    public static function getValue($key, $data) {
        return isset($data[$key]) ? htmlspecialchars($data[$key]) : '';
    }

    public static function isSelected($key, $value, $data) {
        return (isset($data[$key]) && $data[$key] == $value) ? 'selected' : '';
    }

    public static function isChecked($key, $value, $data) {
        return (isset($data[$key]) && $data[$key] == $value) ? 'checked' : '';
    }
}
