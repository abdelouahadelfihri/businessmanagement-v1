<?php

namespace App\Models;

use App\Support\Permissions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_super',
        'permissions',
    ];

    protected $casts = [
        'is_super' => 'boolean',
        'permissions' => 'array',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function grants(string $permission): bool
    {
        return $this->is_super || in_array($permission, $this->permissions ?? [], true);
    }

    /** Keep only keys that exist in config/permissions.php. */
    public function setPermissionsAttribute($value): void
    {
        $valid = array_values(array_intersect((array) $value, Permissions::all()));
        $this->attributes['permissions'] = json_encode($valid);
    }

    public function permissionCount(): int
    {
        return $this->is_super ? count(Permissions::all()) : count($this->permissions ?? []);
    }
}
