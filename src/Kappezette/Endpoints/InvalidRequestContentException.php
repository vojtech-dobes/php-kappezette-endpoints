<?php declare(strict_types=1);

namespace Kappezette\Endpoints;

use RuntimeException;


final class InvalidRequestContentException extends RuntimeException
{

	public function __construct(
		public readonly string $errorMessage,
	)
	{
		parent::__construct();
	}

}
