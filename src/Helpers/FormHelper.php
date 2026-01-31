<?php

namespace Helpers;

class FormHelper
{
    public static function validatePassword($password)
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = "be at least 8 characters long";
        }

        if (strlen($password) > 255) {
            $errors[] = "not exceed 255 characters";
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "contain at least one uppercase letter";
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "contain at least one lowercase letter";
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "contain at least one number";
        }

        if (!empty($errors)) {
            return "Password must " . implode(', ', $errors) . ".";
        }

        return null;
    }

    public static function validateUsername($username)
    {
        $errors = [];

        if (strlen($username) < 3 || strlen($username) > 20) {
            $errors[] = "be between 3 and 20 characters long";
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
            $errors[] = "only contain letters, numbers, dashes, and underscores";
        }

        if (!empty($errors)) {
            return "Username must " . implode(', ', $errors) . ".";
        }

        return null;
    }
}