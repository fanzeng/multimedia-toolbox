<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VideoRecipe extends Model
{
    protected $guarded = [];

    public function frames()
    {
        return $this->hasMany(Frame::class)->orderBy('order','ASC');
    }
}
