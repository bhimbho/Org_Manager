<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Organisation extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'orgId';
    protected $fillable = ['name', 'description', 'owner_id', 'member_id'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organisation_user', 'orgId', 'userId');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id', 'userId');
    }
}
