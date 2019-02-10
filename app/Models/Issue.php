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
        'jira_id', 'jira_key', 'project_id'
    ];

    public function comments(){
        return $this->hasMany(Comment::class);
    }

    public function project(){
        return $this->belongsTo(Project::class);
    }
}
