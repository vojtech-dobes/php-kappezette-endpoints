<?php declare(strict_types=1);

namespace Kappezette\Endpoints;


interface MethodGet
{

	/**
	 * @throws ClientErrorException
	 * @throws InvalidRequestParamsException
	 */
	function processGet(
		ApiRequest $apiRequest,
		ApiResponse $apiResponse,
	): ArrayData|TextData;

}
