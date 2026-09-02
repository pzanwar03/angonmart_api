<?php

namespace Marvel\Enums;

use BenSampo\Enum\Enum;

/**
 * Class WithdrawStatus
 *
 * Kept only so the historical migration that creates (and immediately drops,
 * via the multi-vendor removal migration) the `withdraws` table remains
 * valid for fresh installs. The withdraw feature itself has been removed
 * as part of the single-vendor conversion.
 *
 * @package Marvel\Enums
 */
final class WithdrawStatus extends Enum
{
    public const APPROVED = 'APPROVED';
    public const PENDING = 'PENDING';
    public const ON_HOLD = 'ON_HOLD';
    public const REJECTED = 'REJECTED';
    public const PROCESSING = 'PROCESSING';
}
