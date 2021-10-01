<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    use HasFactory;

    public function facilities() {
        return $this->belongsToMany(Facility::class);
    }

    public function photos() {
        return $this->belongsToMany(Photo::class);
    }

    public function owner() {
        return $this->belongsTo(User::class);
    }
}
