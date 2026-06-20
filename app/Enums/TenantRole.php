<?php

namespace App\Enums;

enum TenantRole: string
{
    case Owner = 'owner';
    case Manager = 'manager';
    case Cashier = 'cashier';

    public function canManageProducts(): bool
    {
        return $this !== self::Cashier;
    }

    public function canManageCategories(): bool
    {
        return $this !== self::Cashier;
    }

    public function canViewReports(): bool
    {
        return $this !== self::Cashier;
    }

    public function canManageStaff(): bool
    {
        return $this === self::Owner;
    }

    public function canManageTenantSettings(): bool
    {
        return $this === self::Owner;
    }

    public function canManageBilling(): bool
    {
        return $this === self::Owner;
    }
}
