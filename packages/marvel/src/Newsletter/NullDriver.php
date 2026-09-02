<?php

namespace Marvel\Newsletter;

use Spatie\Newsletter\Drivers\Driver;
use Spatie\Newsletter\Support\Lists;

class NullDriver implements Driver
{
    public static function make(array $arguments, Lists $lists): self
    {
        return new self($arguments, $lists);
    }

    public function __construct(array $arguments, Lists $lists)
    {
    }

    public function getApi(): null
    {
        return null;
    }

    public function subscribe(
        string $email,
        array $properties = [],
        string $listName = '',
        array $options = []
    ) {
        return false;
    }

    public function subscribeOrUpdate(
        string $email,
        array $properties = [],
        string $listName = '',
        array $options = []
    ) {
        return false;
    }

    public function unsubscribe(string $email, string $listName = '')
    {
        return false;
    }

    public function delete(string $email, string $listName = '')
    {
        return false;
    }

    public function getMember(string $email, string $listName = '')
    {
        return null;
    }

    public function hasMember(string $email, string $listName = ''): bool
    {
        return false;
    }

    public function isSubscribed(string $email, string $listName = ''): bool
    {
        return false;
    }
}
