<?php

declare(strict_types=1);

namespace Marvin255\InMemoryCache;

final class InvalidArgumentException extends \Exception implements \Psr\SimpleCache\InvalidArgumentException
{
}
