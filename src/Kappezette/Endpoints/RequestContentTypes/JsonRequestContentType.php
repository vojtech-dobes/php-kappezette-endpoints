<?php declare(strict_types=1);

namespace Kappezette\Endpoints\RequestContentTypes;

use Kappezette\Endpoints;
use Nette;


final class JsonRequestContentType implements Endpoints\RequestContentType
{

	public function parseBody(Nette\Http\IRequest $httpRequest): mixed
	{
		try {
			return Nette\Utils\Json::decode(
				$httpRequest->getRawBody() ?? '',
				forceArrays: true,
			);
		} catch (Nette\Utils\JsonException $e) {
			throw new Endpoints\InvalidRequestContentException("Json body can't be parsed");
		}
	}

}
