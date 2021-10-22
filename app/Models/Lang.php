<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Lang extends Eloquent
{
    use HasFactory;
    protected $connection = 'mongodb';
    protected $collection = 'languages';
    protected $guarded = [];
}
