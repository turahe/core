<?php

namespace Turahe\Core\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Turahe\Core\Tests\Feature\Factories\TaxonomyFactory;

class Taxonomy extends \Turahe\Core\Models\Taxonomy
{
    use HasFactory;

    protected static function newFactory()
    {
        return TaxonomyFactory::new();
    }
}
