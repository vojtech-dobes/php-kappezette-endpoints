<?php declare(strict_types=1);

namespace Kappezette\Integrations\NetteSchema;

use Kappezette;
use Nette;


trait VerifiedBody
{

	abstract public static function getBodySchema(): Nette\Schema\Schema;



	/**
	 * @throws Kappezette\Endpoints\InvalidRequestBodyException
	 */
	public function getBodyPayload(Kappezette\Endpoints\ApiRequest $apiRequest): mixed
	{
		$processor = new Nette\Schema\Processor();

		try {
			return $processor->process(
				self::getBodySchema(),
				$apiRequest->getUnverifiedBodyPayload(),
			);
		} catch (Nette\Schema\ValidationException $e) {
			throw new Kappezette\Endpoints\InvalidRequestBodyException(
				$e->getMessages(),
			);
		}
	}

}
