<?php

use App\Libraries\HifzManzilCalculator;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class HifzManzilCalculatorTest extends CIUnitTestCase
{
    public function testManzilPoolEmptyWhenCursorZero(): void
    {
        $calc = new HifzManzilCalculator();
        $this->assertSame([], $calc->manzilPoolFromCursor(0));
    }

    public function testParaProgressSnapshotDefaultsWhenCursorZero(): void
    {
        $calc = new HifzManzilCalculator();
        $snap = $calc->paraProgressSnapshot(0);

        $this->assertSame(1, $snap['juz_no']);
        $this->assertSame(0.0, $snap['progress_pct']);
        $this->assertSame(range(2, 30), $snap['pool']);
    }
}
