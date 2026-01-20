<?php

namespace Modules\Restaurant\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MenuAttribute extends Model
{
    use HasFactory;

    protected $fillable = ["title","terms"];
    protected $translatable = ['title'];

    protected static function newFactory()
    {
        return \Modules\Restaurant\Database\factories\MenuAttributeFactory::new();
    }
}
