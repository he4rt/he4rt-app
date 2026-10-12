<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A key/value row in the `auth_tokens` table — see App\Support\AuthTokenStore.
 */
class AuthToken extends Model
{
    public $incrementing = false;
    protected $primaryKey = 'key';

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];
}
