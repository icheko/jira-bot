<?php

namespace App\Traits;

use Webpatser\Uuid\Uuid;

trait Uuids
{
    /**
     * Boot function from laravel.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if(!$model->{$model->getKeyName()})
                $model->{$model->getKeyName()} = Uuid::generate()->string;
        });
    }

    /**
     * Override the getKeyType method. Necessary for Laravel 5.7.14+
     *
     * @return string
     */
    public function getKeyType()
    {
        return 'string';
    }

    /**
     * Override the resolveRouteBinding method to validate the parameter is a uuid
     *
     * @param \Illuminate\Database\Eloquent\Model
     * @return \Illuminate\Database\Eloquent\Model|null
     * @throws ValidationException
     */
    public function resolveRouteBinding($value)
    {
        $validator = app('validator')->make(
            ['id' => $value],
            ['id' => 'uuid']
        );
        if (! $validator->passes()) {
            throw new ValidationException($validator);
        }
        return parent::resolveRouteBinding($value);
    }

    /**
     * Override the getCasts method to cast the UUID object to a string
     *
     * @return array
     */
    public function getCasts()
    {
        $this->casts = array_unique(array_merge($this->casts, [$this->getKeyName() => 'string']));
        return parent::getCasts();
    }
}