<?php

namespace App\Enums;

enum ProductCategory: string
{
    case Mobile = 'mobile';
    case Case = 'case';
    case Charger = 'charger';
    case Earphone = 'earphone';
    case Cable = 'cable';
    case Other = 'other';
}
