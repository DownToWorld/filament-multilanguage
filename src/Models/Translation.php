<?php

namespace DTW\FilamentMultilanguage\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    protected $table = "filament_multilanguage_table";
    protected $fillable = ['translate_panel_id', 'translate_object', 'translate_key', 'translate_language', 'translate_default', 'translate_value'];
}
