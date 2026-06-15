<?php

use CodeIgniter\Test\CIUnitTestCase;
use Config\Hifz;

/**
 * @internal
 */
final class HifzConfigTest extends CIUnitTestCase
{
    public function testAutoMigrateOffWhenProduction(): void
    {
        $config = new Hifz();
        $expected = ENVIRONMENT !== 'production';
        $this->assertSame($expected, $config->autoMigrate);
    }
}
