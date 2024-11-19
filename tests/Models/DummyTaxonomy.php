<?php

namespace Turahe\Core\Tests\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Turahe\Core\Concerns\HasTags;
use Turahe\Core\Concerns\HasTaxonomies;

class DummyTaxonomy extends Model
{
    use HasUlids;
    use HasTaxonomies;
    protected $table = 'dummies';

    public $timestamps = false;

    protected $guarded = [];

}