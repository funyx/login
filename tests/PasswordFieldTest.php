<?php

declare(strict_types=1);

namespace Atk4\Login\Tests;

use Atk4\Data\Model;
use Atk4\Data\Persistence;
use Atk4\Login\Field\Password;

class PasswordFieldTest extends Generic
{
    public function testPasswordField()
    {
        $m = new Model();
        $m->addField('p', [Password::class]);

        $m = $m->createEntity();
        $m->set('p', 'mypass');

        // when setting password, you can retrieve it back while it's not yet saved
        $this->assertSame('mypass', $m->get('p'));

        // password changed, so it's dirty.
        $this->assertTrue($m->isDirty('p'));

        $this->assertFalse($m->compare('p', 'badpass'));
        $this->assertTrue($m->compare('p', 'mypass'));
    }

    public function testPasswordPersistence()
    {
        $a = [];
        $p = new Persistence\Array_($a);
        $m = new ModelWithPassword($p);

        // making sure cloning does not break things
        $m = (clone $m)->createEntity();

        // when setting password, you can retrieve it back while it's not yet saved
        $m->set('password', 'mypass');
        $this->assertSame('mypass', $m->get('password'));
        $m->save();

        // stored encoded password
        $enc = $this->getProtected($p, 'data')['data']->getRowById($m, 1)->getValue('password');
        $this->assertTrue(is_string($enc));
        $this->assertNotSame('mypass', $enc);

        // should have reloaded also
        $this->assertNull($m->get('password'));

        // password value after load is null, but it still should validate/verify
        $this->assertFalse($m->getField('password')->verify('badpass'));
        $this->assertTrue($m->getField('password')->verify('mypass'));

        // password shouldn't be dirty here
        $this->assertFalse($m->isDirty('password'));

        $dbg = $m->getPasswordHash();
        $m = $m->set('password', 'newpass');
        $dbg = $m->getPasswordHash();
        $this->assertTrue($m->isDirty('password'));
        $this->assertFalse($m->getField('password')->verify('mypass'));
        $this->assertTrue($m->getField('password')->verify('newpass'));

        $m->save();
        $this->assertFalse($m->isDirty('password'));
        $this->assertFalse($m->getField('password')->verify('mypass'));
        $this->assertTrue($m->getField('password')->verify('newpass'));

        // will have new hash
        $this->assertNotSame($enc, $this->getProtected($p, 'data')['data']->getRowById($m, 1)->getValue('password'));
    }

    public function testCanNotCompareEmptyException()
    {
        $a = [];
        $p = new Persistence\Array_($a);
        $m = new ModelWithPassword($p);

        $this->expectException(\Atk4\Data\Exception::class);
        $m->getField('password')->verify('mypass'); // tries to compare empty password field value with value 'mypass'
    }

    public function testSuggestPassword()
    {
        $field = new Password();
        $pwd = $field->suggestPassword(6);
        $this->assertIsString($pwd);
        $this->assertGreaterThanOrEqual(6, strlen($pwd));
    }
}
