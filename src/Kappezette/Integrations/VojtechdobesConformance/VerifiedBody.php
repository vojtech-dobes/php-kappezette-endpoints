<?php declare(strict_types=1);

namespace Kappezette\Integrations\VojtechdobesConformance;

use Kappezette;
use Vojtechdobes;


/**
 * @template TBodyPayload
 */
trait VerifiedBody
{

	/**
	 * @return Vojtechdobes\Conformance\Constraint<mixed, covariant TBodyPayload>
	 */
	abstract public static function getBodyConstraint(): Vojtechdobes\Conformance\Constraint;



	/**
	 * @return TBodyPayload
	 * @throws Kappezette\Endpoints\InvalidRequestBodyException
	 */
	public function getBodyPayload(Kappezette\Endpoints\ApiRequest $apiRequest): mixed
	{
		try {
			return Vojtechdobes\Conformance\Validator::validate(
				self::getBodyConstraint(),
				$apiRequest->getUnverifiedBodyPayload(),
			);
		} catch (Vojtechdobes\Conformance\InvalidValueException $e) {
			throw new Kappezette\Endpoints\InvalidRequestBodyException(
				array_map(
					static fn ($error) => $error->toArray(),
					$e->errors,
				),
			);
		}
	}

}
