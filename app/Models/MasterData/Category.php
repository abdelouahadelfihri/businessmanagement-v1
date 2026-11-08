<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    /** Bootstrap Icons offered in the form. */
    public const ICONS = [
        'bi-tag', 'bi-box-seam', 'bi-basket', 'bi-bag', 'bi-cart', 'bi-cup-hot', 'bi-egg-fried',
        'bi-apple', 'bi-droplet', 'bi-fire', 'bi-lightning', 'bi-plug', 'bi-laptop', 'bi-phone',
        'bi-tv', 'bi-printer', 'bi-tools', 'bi-hammer', 'bi-wrench', 'bi-brush', 'bi-palette',
        'bi-scissors', 'bi-car-front', 'bi-truck', 'bi-house', 'bi-building', 'bi-tree',
        'bi-flower1', 'bi-heart-pulse', 'bi-capsule', 'bi-book', 'bi-pencil', 'bi-briefcase',
        'bi-gift', 'bi-gem', 'bi-watch', 'bi-headphones', 'bi-controller', 'bi-person', 'bi-gear',
    ];

    protected $fillable = [
        'code', 'parent_id', 'name', 'description',
        'color', 'icon', 'default_tax_rate', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'default_tax_rate' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        // Auto-assign a code (CAT-001) when none was typed in
        static::created(function (Category $category) {
            if (blank($category->code)) {
                $category->code = sprintf('CAT-%03d', $category->id);
                $category->saveQuietly();
            }
        });
    }

    /* ---------------- Relations ---------------- */

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /* ---------------- Scopes ---------------- */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $like = '%' . $term . '%';
            $q->where('name', 'like', $like)
                ->orWhere('code', 'like', $like)
                ->orWhere('description', 'like', $like);
        });
    }

    /* ---------------- Helpers ---------------- */

    /** "Parent › Child" path. */
    public function getFullNameAttribute(): string
    {
        $names = [$this->name];
        $node = $this;
        $guard = 0;

        while ($node->parent && $guard++ < 10) {
            $node = $node->parent;
            array_unshift($names, $node->name);
        }

        return implode(' › ', $names);
    }

    /** IDs of this category and every category below it. */
    public function descendantIds(): array
    {
        $ids = [$this->id];
        $level = [$this->id];

        for ($depth = 0; $level && $depth < 10; $depth++) {
            $level = static::whereIn('parent_id', $level)->pluck('id')->all();
            $ids = array_merge($ids, $level);
        }

        return array_values(array_unique($ids));
    }
}
