<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Department extends Model
{
    protected $guarded = [];

    // JSON column ko automatically array mein convert karne ke liye
    protected $casts = [
        'responsibilities' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // one department has multiple employee
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
    
    public function responsibilities()
    {
        return $this->hasMany(JobResponsibility::class);
    }
}
