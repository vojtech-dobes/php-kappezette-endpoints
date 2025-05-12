<?php declare(strict_types=1);

namespace Kappezette\Endpoints;


interface MethodHead
{

	/**
	 * @throws ClientErrorException
	 */
	function processHead(
		ApiRequest $apiRequest,
		ApiResponse $apiResponse,
	): null;

}
