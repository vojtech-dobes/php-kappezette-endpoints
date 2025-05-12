<?php declare(strict_types=1);

namespace Kappezette\Endpoints;

use Nette;


interface MethodAware
{

	/**
	 * @return (callable(ApiRequest, ApiResponse): (ArrayData|TextData|null))|null
	 */
	function getDynamicMethodHandler(
		HttpMethod $httpMethod,
		Nette\Application\Request $request,
	): callable|null;

}
