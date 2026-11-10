<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    public const TYPES = [
        'event' => 'School event',
        'holiday' => 'School holidays',
        'development_day' => 'School Development Day (no students)',
    ];

    protected $fillable = ['title', 'starts_on', 'ends_on', 'time', 'type'];

    protected function casts(): array
    {
        return ['starts_on' => 'date', 'ends_on' => 'date'];
    }

    /** Shape used by the front-end calendar script (window.EPS_EVENTS). */
    public function toCalendarArray(): array
    {
        return array_filter([
            'start' => $this->starts_on->toDateString(),
            'end' => $this->ends_on?->toDateString(),
            'time' => $this->time,
            'title' => $this->title,
            'type' => ['holiday' => 'hol', 'development_day' => 'sd'][$this->type] ?? null,
        ]);
    }
}
