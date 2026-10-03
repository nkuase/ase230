<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Student extends Model
{
    protected $fillable = ['name', 'email', 'major', 'year', 'is_active', 'gpa'];

    protected function casts(): array
    {
        return ['year' => 'integer', 'is_active' => 'boolean', 'gpa' => 'decimal:2'];
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class)->withTimestamps();
    }

    public function scopeInYear(Builder $query, int $year): Builder
    {
        return $query->where('year', $year);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
