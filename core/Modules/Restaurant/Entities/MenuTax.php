<?php

namespace Modules\Restaurant\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;

class MenuTax extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [];
    protected $table = 'menu_taxes';

    protected $translatable = ['name','description'];

    protected static function newFactory()
    {
        return \Modules\Restaurant\Database\factories\MenuTaxFactory::new();
    }
}
