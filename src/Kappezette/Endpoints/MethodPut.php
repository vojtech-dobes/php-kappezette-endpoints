<?php declare(strict_types=1);

namespace Kappezette\Endpoints;


interface MethodPut
{

	/**
	 * @throws ClientErrorException
	 * @throws InvalidRequestBodyException
	 * @throws InvalidRequestParamsException
	 */
	function processPut(
		ApiRequest $apiRequest,
		ApiResponse $apiResponse,
	): ArrayData|TextData|null;

}
