<?php

declare(strict_types=1);

namespace Marvin255\InMemoryCache;

class InvalidArgumentException extends \Exception implements \Psr\SimpleCache\InvalidArgumentException
{
}
