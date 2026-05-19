<?php

namespace Config;

class OrderStatus
{
    public const PENDING               = 'pending';
    public const WAITING_CONFIRMATION  = 'waiting_confirmation';
    public const PROCESSING            = 'processing';
    public const SHIPPED               = 'shipped';
    public const COMPLETED             = 'completed';
    public const CANCELLED             = 'cancelled';
    public const REFUNDED              = 'refunded';
    public const PARTIALLY_REFUNDED    = 'partially_refunded';
    public const REJECTED              = 'rejected';
    public const EXPIRED               = 'expired';
}
