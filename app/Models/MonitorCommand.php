<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class MonitorCommand extends Model
{
    use Uuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'command_id', 'bamboo_build_key','complete',
    ];

    public function command(){
        return $this->belongsTo(Command::class);
    }
}
