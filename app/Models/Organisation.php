<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organisation extends Model
{
    use HasFactory, HasUlids;

    protected $primaryKey = 'orgId';
    protected $fillable = ['name', 'description', 'owner_id', 'member_id'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
