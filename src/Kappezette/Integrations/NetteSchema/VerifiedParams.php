<?php declare(strict_types=1);

namespace Kappezette\Integrations\NetteSchema;

use Kappezette;
use Nette;


trait VerifiedParams
{

	abstract public static function getParametersSchema(): Nette\Schema\Schema;



	/**
	 * @throws Kappezette\Endpoints\InvalidRequestParamsException
	 */
	public function getParameters(Kappezette\Endpoints\ApiRequest $apiRequest): array
	{
		$processor = new Nette\Schema\Processor();

		try {
			return $processor->process(
				self::getParametersSchema(),
				$apiRequest->getUnverifiedParameters(),
			);
		} catch (Nette\Schema\ValidationException $e) {
			throw new Kappezette\Endpoints\InvalidRequestParamsException(
				$e->getMessages(),
			);
		}
	}

}
