<?php

namespace Modules\Appointment\Entities;

use App\Models\MetaInfo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Modules\Blog\Entities\BlogComment;
use Spatie\Translatable\HasTranslations;

class Appointment extends Model
{
    use HasTranslations;

    protected $fillable = [
        'appointment_category_id',
        'appointment_subcategory_id',
        'title',
        'description',
        'short_description',
        'price',
        'duration',
        'sale_price',
        'slug',
        'status',
        'is_popular',
        'is_featured',
        'image',
        'gallery',
        'video_url',
        'views',
        'person',
        'max_booking_per_slot',
        'advance_booking_days',
        'cancellation_policy',
        'requirements',
        'sort_order',
        'rating_avg',
        'rating_count',
        'sub_appointment_status',
        'tax_status',
        'key'
    ];

    protected $translatable = ['title', 'description', 'short_description', 'cancellation_policy', 'requirements'];

    protected $casts = [
        'status' => 'boolean',
        'is_popular' => 'boolean',
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'duration' => 'integer',
        'sort_order' => 'integer',
        'rating_avg' => 'decimal:2',
        'rating_count' => 'integer',
        'max_booking_per_slot' => 'integer',
        'advance_booking_days' => 'integer',
        'gallery' => 'array',
    ];

    public function metainfo()
    {
        return $this->morphOne(MetaInfo::class, 'metainfoable');
    }

    public function additional_appointments(): HasMany
    {
        return $this->hasMany(AdditionalAppointment::class, 'appointment_id', 'id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AppointmentCategory::class, 'appointment_category_id', 'id');
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(AppointmentSubcategory::class, 'appointment_subcategory_id', 'id');
    }

    public function comments()
    {
        return $this->hasMany(AppointmentComment::class, 'appointment_id', 'id');
    }

    public function sub_appointments(): HasManyThrough
    {
        return $this->hasManyThrough(SubAppointment::class, AdditionalAppointment::class, 'appointment_id', 'id', 'id', 'sub_appointment_id');
    }

    /**
     * Get the effective price (sale_price if available, otherwise price)
     */
    public function getEffectivePriceAttribute()
    {
        return $this->sale_price && $this->sale_price > 0 ? $this->sale_price : $this->price;
    }

    /**
     * Check if the appointment has a discount
     */
    public function getHasDiscountAttribute()
    {
        return $this->sale_price && $this->sale_price > 0 && $this->sale_price < $this->price;
    }

    /**
     * Get discount percentage
     */
    public function getDiscountPercentageAttribute()
    {
        if ($this->has_discount) {
            return round((($this->price - $this->sale_price) / $this->price) * 100);
        }
        return 0;
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute()
    {
        if (!$this->duration) return null;

        $hours = floor($this->duration / 60);
        $minutes = $this->duration % 60;

        if ($hours > 0 && $minutes > 0) {
            return $hours . 'h ' . $minutes . 'm';
        } elseif ($hours > 0) {
            return $hours . 'h';
        } else {
            return $minutes . 'm';
        }
    }

    /**
     * Scope for featured appointments
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for popular appointments
     */
    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    /**
     * Scope for active appointments
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
