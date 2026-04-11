<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Brand extends Model
{
    use HasFactory, HasTranslations;

    protected $with = ['getImage'];
    protected $table = 'brands';
    protected $fillable = ['url', 'image', 'status'];

    protected $translatable = ['url'];

    public function getImage()
    {
        return $this->hasOne(MediaUploader::class, 'id', 'image')->select(['id', 'path', 'alt']);
    }
}
