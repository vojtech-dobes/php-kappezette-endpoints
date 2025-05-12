<?php declare(strict_types=1);

namespace Kappezette\Endpoints;

use RuntimeException;


abstract class ConstraintViolationException extends RuntimeException
{

	/**
	 * @param list<mixed> $errors
	 */
	final public function __construct(
		public readonly array $errors,
	)
	{
		parent::__construct();
	}

}
