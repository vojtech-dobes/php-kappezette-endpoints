<?php declare(strict_types=1);

namespace Kappezette\Endpoints;


interface MethodPatch
{

	/**
	 * @throws ClientErrorException
	 * @throws InvalidRequestBodyException
	 * @throws InvalidRequestParamsException
	 */
	function processPatch(
		ApiRequest $apiRequest,
		ApiResponse $apiResponse,
	): ArrayData|TextData|null;

}
