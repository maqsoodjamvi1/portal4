<?php

use App\Models\Frontend\AuthModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class PortalPasswordTest extends CIUnitTestCase
{
    private AuthModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new AuthModel();
    }

    public function testRejectsEmptyHash(): void
    {
        $this->assertFalse($this->model->verifyPortalPassword('secret', null));
        $this->assertFalse($this->model->verifyPortalPassword('secret', ''));
    }

    public function testAcceptsBcryptHash(): void
    {
        $hash = password_hash('portal-pass', PASSWORD_BCRYPT);
        $this->assertTrue($this->model->verifyPortalPassword('portal-pass', $hash));
        $this->assertFalse($this->model->verifyPortalPassword('wrong', $hash));
    }

    public function testAcceptsLegacyMd5(): void
    {
        $plain = 'legacy123';
        $hash  = md5($plain);
        $this->assertTrue($this->model->verifyPortalPassword($plain, $hash));
    }
}
