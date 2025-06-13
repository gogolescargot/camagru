<?php

class PasswordHelper
{
    public static function validatePassword($password)
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = "8 characters long.";
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "one uppercase letter.";
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "one lowercase letter.";
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "one number.";
        }

        if (!empty($errors)) {
            return "Password must be at least " . implode(', ', $errors);
        }

        return null;
    }
}