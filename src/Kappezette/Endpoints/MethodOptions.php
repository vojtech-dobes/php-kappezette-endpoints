<?php declare(strict_types=1);

namespace Kappezette\Endpoints;


interface MethodOptions
{

	/**
	 * @throws ClientErrorException
	 */
	function processOptions(
		ApiRequest $apiRequest,
		ApiResponse $apiResponse,
	): ArrayData|TextData|null;

}
