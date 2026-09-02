<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Documents the product stock decision table for pre-order checkout.
 * Mirrors CheckoutRepository::isInStock behavior without a database.
 */
class PreorderStockLogicTest extends TestCase
{
    private function isUnavailable(
        bool $isPreorder,
        int $quantity,
        int $orderQuantity
    ): bool {
        if ($isPreorder && $quantity === 0) {
            return false;
        }

        return $orderQuantity > $quantity;
    }

    public function test_preorder_with_zero_stock_allows_any_quantity(): void
    {
        $this->assertFalse($this->isUnavailable(true, 0, 1));
        $this->assertFalse($this->isUnavailable(true, 0, 25));
    }

    public function test_preorder_with_positive_stock_enforces_cap(): void
    {
        $this->assertFalse($this->isUnavailable(true, 5, 5));
        $this->assertTrue($this->isUnavailable(true, 5, 6));
    }

    public function test_non_preorder_zero_stock_is_unavailable(): void
    {
        $this->assertTrue($this->isUnavailable(false, 0, 1));
    }

    public function test_non_preorder_respects_stock(): void
    {
        $this->assertFalse($this->isUnavailable(false, 3, 2));
        $this->assertTrue($this->isUnavailable(false, 3, 4));
    }
}
