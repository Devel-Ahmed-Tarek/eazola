<?php

namespace Modules\HotelBooking\Entities;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Room extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        "name",
        "room_type_id",
        "base_cost",
        "sale_price",
        "share_value",
        "description",
        "short_description",
        "slug",
        "is_featured",
        "is_popular",
        "gallery",
        "video_url",
        "max_guests",
        "sort_order",
        "status"
    ];
    protected $with = ["room_types","room_image"];
    protected $translatable = ['name','description','short_description'];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', '1');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', 'on');
    }

    public function scopePopular($query)
    {
        return $query->where('is_popular', 'on');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    // Check if has sale price
    public function hasSalePrice(): bool
    {
        return !empty($this->sale_price) && $this->sale_price < $this->base_cost;
    }

    // Get gallery as array
    public function getGalleryImagesAttribute(): array
    {
        if (empty($this->gallery)) {
            return [];
        }
        return is_array($this->gallery) ? $this->gallery : explode(',', $this->gallery);
    }

    public function room_types(){
        return $this->belongsTo(RoomType::class,"room_type_id","id");
    }


    public function room_image(){
        return $this->hasMany(RoomImage::class,"room_id","id");
    }

    public function room_inventory(){
        return $this->hasMany(RoomInventory::class,"room_id","id");
    }

    public function reviews(){
        return $this->hasMany(HotelReview::class,"room_id","id");
    }
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('ratting');
    }

    public function reviewCount()
    {
        return $this->reviews()->count();
    }

    public function inventory()
    {
        return $this->hasManyThrough(Inventory::class, RoomInventory::class,"room_id","id","id","inventory_id");
    }

}
