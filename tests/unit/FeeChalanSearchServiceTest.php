<?php

use App\Libraries\FeeChalanSearchService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class FeeChalanSearchServiceTest extends CIUnitTestCase
{
    public function testExactRegNoRanksFirst(): void
    {
        $service = new FeeChalanSearchService();
        $rows    = [
            ['student_name' => 'Other Child', 'reg_no' => '100'],
            ['student_name' => 'Target', 'reg_no' => 'ABC-99'],
        ];

        $ranked = $service->rankResults($rows, 'ABC-99');

        $this->assertSame('ABC-99', $ranked[0]['reg_no']);
    }
}
