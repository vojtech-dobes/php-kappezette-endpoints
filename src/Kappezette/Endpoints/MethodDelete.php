<?php declare(strict_types=1);

namespace Kappezette\Endpoints;


interface MethodDelete
{

	/**
	 * @throws ClientErrorException
	 * @throws InvalidRequestBodyException
	 * @throws InvalidRequestParamsException
	 */
	function processDelete(
		ApiRequest $apiRequest,
		ApiResponse $apiResponse,
	): ArrayData|TextData|null;

}
