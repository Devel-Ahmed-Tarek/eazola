<?php

namespace Modules\Appointment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;
use Illuminate\Support\Str;

class AppointmentCategory extends Model
{
    use HasTranslations;

    protected $fillable = [
        'title',
        'description',
        'slug',
        'image',
        'icon',
        'color',
        'sort_order',
        'is_featured',
        'status'
    ];

    protected $translatable = ['title', 'description'];

    protected $casts = [
        'is_featured' => 'boolean',
        'status' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get all subcategories for this category
     */
    public function subcategories(): HasMany
    {
        return $this->hasMany(AppointmentSubcategory::class, 'appointment_category_id', 'id')
                    ->where('status', 1)
                    ->orderBy('sort_order');
    }

    /**
     * Get all appointments for this category
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'appointment_category_id', 'id')
                    ->where('status', 1)
                    ->orderBy('sort_order');
    }

    /**
     * Auto-generate slug from title
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->getTranslation('title', 'en') ?? $category->title);
            }
        });

        static::updating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->getTranslation('title', 'en') ?? $category->title);
            }
        });
    }

    /**
     * Scope for featured categories
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for active categories
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope ordered by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
