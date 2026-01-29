<?php

namespace Modules\Appointment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;
use Illuminate\Support\Str;

class AppointmentSubcategory extends Model
{
    use HasTranslations;

    protected $fillable = [
        'appointment_category_id',
        'title',
        'description',
        'slug',
        'image',
        'icon',
        'sort_order',
        'status'
    ];

    protected $translatable = ['title', 'description'];

    protected $casts = [
        'status' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the parent category
     */
    public function appointment_category(): BelongsTo
    {
        return $this->belongsTo(AppointmentCategory::class, 'appointment_category_id', 'id');
    }

    /**
     * Alias for appointment_category
     */
    public function category(): BelongsTo
    {
        return $this->appointment_category();
    }

    /**
     * Get all appointments for this subcategory
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'appointment_subcategory_id', 'id')
                    ->where('status', 1)
                    ->orderBy('sort_order');
    }

    /**
     * Auto-generate slug from title
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($subcategory) {
            if (empty($subcategory->slug)) {
                $subcategory->slug = Str::slug($subcategory->getTranslation('title', 'en') ?? $subcategory->title);
            }
        });

        static::updating(function ($subcategory) {
            if (empty($subcategory->slug)) {
                $subcategory->slug = Str::slug($subcategory->getTranslation('title', 'en') ?? $subcategory->title);
            }
        });
    }

    /**
     * Scope for active subcategories
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
