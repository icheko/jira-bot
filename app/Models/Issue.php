<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class Issue extends Model
{
    use Uuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'jira_id', 'jira_key',
    ];

    public function comments(){
        return $this->hasMany(Comment::class);
    }
}
