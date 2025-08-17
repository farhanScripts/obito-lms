<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseSection extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'course_id',
        'name',
        'position'
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id', 'id');
    }

    public function sectionContents():HasMany
    {
        return $this->hasMany(SectionContent::class, 'course_section_id', 'id');
    }
}
