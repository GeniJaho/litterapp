<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property PhotoItem|null $pivot
 */
class Item extends Model
{
    use HasFactory;
}
