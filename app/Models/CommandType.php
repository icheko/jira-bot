<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class CommandType extends Model
{
    use Uuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'command_name', 'alias',
    ];

    public function command(){
        return $this->hasMany(Command::class);
    }
}
