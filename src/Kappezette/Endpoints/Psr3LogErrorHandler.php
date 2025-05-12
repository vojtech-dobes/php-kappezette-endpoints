<?php declare(strict_types=1);

namespace Kappezette\Endpoints;

use Psr;
use Throwable;


final class Psr3LogErrorHandler implements ErrorHandler
{

	/**
	 * @param Psr\Log\LogLevel::* $level
	 */
	public function __construct(
		private readonly Psr\Log\LoggerInterface $logger,
		private readonly string $level = Psr\Log\LogLevel::ERROR,
	) {}



	public function handleError(Throwable $e): void
	{
		$this->logger->log($this->level, 'Unexpected error occurred', [
			'exception' => $e,
		]);
	}

}
