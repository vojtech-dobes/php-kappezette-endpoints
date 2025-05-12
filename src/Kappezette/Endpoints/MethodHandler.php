<?php declare(strict_types=1);

namespace Kappezette\Endpoints;


interface MethodHandler
{

	function process(
		ApiRequest $apiRequest,
		ApiResponse $apiResponse,
	): ArrayData|TextData|null;

}
