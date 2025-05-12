<?php declare(strict_types=1);

namespace Kappezette\Endpoints;

use Nette;


interface RequestContentType
{

	/**
	 * @throws InvalidRequestContentException
	 */
	function parseBody(Nette\Http\IRequest $httpRequest): mixed;

}
