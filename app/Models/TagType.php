<?php

namespace App\Models;

use Database\Factories\TagTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagType extends Model
{
    /** @use HasFactory<TagTypeFactory> */
    use HasFactory;
}
