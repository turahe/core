<?php

namespace Turahe\Core\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Turahe\Core\Concerns\HasConfigurablePrimaryKey;
use Turahe\Core\Concerns\HasTaxonomies;

class DummyTaxonomy extends Model
{
    use HasConfigurablePrimaryKey;
    use HasTaxonomies;

    protected $table = 'dummies';

    public $timestamps = false;

    protected $guarded = [];
}
