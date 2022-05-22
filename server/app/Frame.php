<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Frame extends Model
{
    protected $fillable = ['num_repetition'];
    public $timestamps = false;

    public function video_recipe() {
        return $this->belongsTo(VideoRecipe::class);
    }
}
