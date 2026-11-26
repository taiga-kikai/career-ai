<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/** Laravelを起動せず、小さなPHP処理を単独確認する最小例。 */
class ExampleTest extends TestCase
{
    /**
     * PHPUnitの基本的な真偽値アサーションの例。
     */
    public function test_that_true_is_true(): void
    {
        $this->assertTrue(true);
    }
}
