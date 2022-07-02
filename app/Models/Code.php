<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Code extends Eloquent
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'snippet_data';
    protected $guarded = [];
}
