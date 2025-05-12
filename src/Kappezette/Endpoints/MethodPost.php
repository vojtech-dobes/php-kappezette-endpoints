<?php declare(strict_types=1);

namespace Kappezette\Endpoints;


interface MethodPost
{

	/**
	 * @throws ClientErrorException
	 * @throws InvalidRequestBodyException
	 * @throws InvalidRequestParamsException
	 */
	function processPost(
		ApiRequest $apiRequest,
		ApiResponse $apiResponse,
	): ArrayData|TextData;

}
