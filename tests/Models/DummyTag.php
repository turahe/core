<?php

namespace Turahe\Core\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Turahe\Core\Concerns\HasConfigurablePrimaryKey;
use Turahe\Core\Concerns\HasTags;

class DummyTag extends Model
{
    use HasConfigurablePrimaryKey;
    use HasTags;

    protected $table = 'dummies';

    public $timestamps = false;

    protected $guarded = [];
}
