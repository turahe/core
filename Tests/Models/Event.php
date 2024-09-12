<?php

namespace Turahe\Core\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Turahe\Media\HasMedia;

class Event extends Model
{
    use HasFactory, HasMedia;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['title', 'description', 'start', 'end', 'date', 'total_guests', 'is_all_day', 'user_id', 'status'];

    public function status()
    {
        return $this->belongsTo(EventStatus::class, 'status_id');
    }

    public function locations()
    {
        return $this->morphMany(Location::class, 'locationable')->orderBy('locations.created_at');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function calendars()
    {
        return $this->morphedByMany(Calendar::class, 'eventable', 'eventables');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->orderBy('created_at');
    }
}
