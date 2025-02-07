<?php

namespace App\Enums;

enum UserRole: string
{
    case COMPANY = 'company';
    case STUDENT = 'student';
    case ADMIN = 'admin';
    case COLLEGE = 'college';
} 