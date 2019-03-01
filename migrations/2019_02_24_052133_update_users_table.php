<?php

use Flarum\Database\Migration;

return Migration::addColumns('users', [
    'has_pwned_password' => ['boolean', 'default' => false]
]);