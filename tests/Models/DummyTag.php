<?php

namespace Turahe\Core\Tests\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Turahe\Core\Concerns\HasTags;
use Turahe\Core\Concerns\HasTaxonomies;

class DummyTag extends Model
{

    use HasUlids;
    use HasTags;
    protected $table = 'dummies';

    public $timestamps = false;

    protected $guarded = [];
}