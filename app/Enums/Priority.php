<?php
namespace App\Enums;

use App\Traits\EnumHelper;

enum Priority: string
{
    use EnumHelper;
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case URGENT = 'urgent';

    public function name(): string
    {
        return match($this) {
            self::LOW => 'Low',
            self::MEDIUM => 'Medium',
            self::HIGH => 'High',
            self::URGENT => 'Urgent',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::LOW => 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-950 dark:text-blue-300 dark:border-blue-700',
            self::MEDIUM => 'bg-yellow-100 text-yellow-800 border-yellow-200 dark:bg-yellow-950 dark:text-yellow-200 dark:border-yellow-900',
            self::HIGH => 'bg-orange-100 text-orange-800 border-orange-200 dark:bg-orange-950 dark:text-orange-200 dark:border-orange-800',
            self::URGENT => 'bg-red-100 text-red-800 border-red-200 dark:bg-red-950 dark:text-red-300 dark:border-red-900',
        };
    }
}
