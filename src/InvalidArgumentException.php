<?php

declare(strict_types=1);

namespace Marvin255\InMemoryCache;

/**
 * Specific cache related exception.
 *
 * @internal
 */
final class InvalidArgumentException extends \Exception implements \Psr\SimpleCache\InvalidArgumentException
{
}
