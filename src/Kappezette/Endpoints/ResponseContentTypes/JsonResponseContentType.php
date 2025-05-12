<?php declare(strict_types=1);

namespace Kappezette\Endpoints\ResponseContentTypes;

use Kappezette\Endpoints;
use Nette;


final class JsonResponseContentType implements Endpoints\ResponseContentType
{

	public function getContentType(): string
	{
		return 'application/json';
	}



	/**
	 * @throws Nette\Utils\JsonException
	 */
	public function formatBody(mixed $payload): string
	{
		return Nette\Utils\Json::encode($payload);
	}

}
