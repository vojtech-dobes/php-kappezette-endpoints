<?php declare(strict_types=1);

namespace Kappezette\Integrations\VojtechdobesConformance;

use Kappezette;
use Vojtechdobes;


/**
 * @template TParamsPayload
 */
trait VerifiedParams
{

	/**
	 * @return Vojtechdobes\Conformance\Constraint<mixed, covariant TParamsPayload>
	 */
	abstract public static function getParametersConstraint(): Vojtechdobes\Conformance\Constraint;



	/**
	 * @return TParamsPayload
	 * @throws Kappezette\Endpoints\InvalidRequestParamsException
	 */
	public function getParameters(Kappezette\Endpoints\ApiRequest $apiRequest): array
	{
		try {
			return Vojtechdobes\Conformance\Validator::validate(
				self::getParametersConstraint(),
				$apiRequest->getUnverifiedParameters(),
			);
		} catch (Vojtechdobes\Conformance\InvalidValueException $e) {
			throw new Kappezette\Endpoints\InvalidRequestParamsException(
				array_map(
					static fn ($error) => $error->toArray(),
					$e->errors,
				),
			);
		}
	}

}
