<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'cover',
        'status',
        'user_id',
        'description',
        'category'
    ];
    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function projectLikes(): HasMany
    {
        return $this->hasMany(ProjectLike::class);
    }
}
