<?php

namespace FoF\PwnedPasswords\Events;

use Flarum\User\User;

class PwnedPasswordDetected
{
    /**
     * @var User
     */
    public $user;

    public $type;

    public function __construct(User $user = null, string $type)
    {
        $this->user = $user;
        $this->type = $type;
    }
}