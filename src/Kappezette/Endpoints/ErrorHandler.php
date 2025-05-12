<?php declare(strict_types=1);

namespace Kappezette\Endpoints;

use Throwable;


interface ErrorHandler
{

	function handleError(Throwable $e): void;

}
