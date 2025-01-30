<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class User extends Model implements Authenticatable
{
    public function getAuthIdentifierName()
    {
        return 'username';
    }
    public function getAuthIdentifier()
    {
        return $this->username;
    }
    public function getAuthPassword()
    {
        return $this->password;
    }
    public function getAuthPasswordName()
    {
        return 'password';
    }

    public function getRememberToken()
    {
        return $this->token;
    }

    public function setRememberToken($value)
    {
        $this->token = $value;
    }

    public function getRememberTokenName()
    {
        return 'token';
    }

    //
    protected $table = "users";
    protected $primaryKey = "id";
    protected $keyType = "int";
    public $timestamps = true;
    public $incrementing = true;
    protected $fillable = [
        'username',
        'password',
        'name',
    ];
    public function contacts()
    {
        // contact:class,kolom yang ada di dalam contact table,kolom yang ada di user table
        return $this->hasMany(Contact::class, "user_id", "id");
    }
}
