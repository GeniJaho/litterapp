<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TagShortcutItemTag extends Pivot
{
    use HasFactory;

    protected $table = 'tag_shortcut_item_tag';

    public $incrementing = true;
}
