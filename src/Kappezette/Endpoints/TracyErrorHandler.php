<?php declare(strict_types=1);

namespace Kappezette\Endpoints;

use Throwable;
use Tracy;


final class TracyErrorHandler implements ErrorHandler
{

	public function __construct(
		private readonly Tracy\ILogger $logger,
	) {}



	public function handleError(Throwable $e): void
	{
		$this->logger->log($e, Tracy\ILogger::EXCEPTION);
	}

}
