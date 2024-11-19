<?php

namespace Turahe\Core\Tests\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Turahe\Core\Concerns\HasTags;

class DummyTag extends Model
{
    use HasTags;
    use HasUlids;

    protected $table = 'dummies';

    public $timestamps = false;

    protected $guarded = [];
}
