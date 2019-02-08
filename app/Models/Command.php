<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class Command extends Model
{
    use Uuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'comment_id', 'command_type_id', 'arguments',
    ];

    public function commandType(){
        return $this->belongsTo(CommandType::class);
    }
}
