<?php

declare( strict_types=1 );

namespace Atk4\Login\Tests;

use Atk4\Data\Model;
use Atk4\Login\Feature\PasswordManagement;
use Atk4\Login\Field\Password;

/**
 * Example user data model.
 */
class ModelWithPassword extends Model
{
    use PasswordManagement;

    protected function init(): void
    {
        parent::init();
        $this->addField('password', [Password::class]);

        // traits
        $this->initPasswordManagement();
    }
}
