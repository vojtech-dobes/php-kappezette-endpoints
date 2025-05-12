<?php declare(strict_types=1);

namespace Kappezette\Endpoints;


final class AccessControl
{

	public function __construct(
		private readonly AccessControlConfiguration $configuration,
	) {}



	/**
	 * @return array<string, list<string>>
	 */
	public function createMainHeaders(string $origin): array
	{
		$result = [];

		if ($this->configuration->isOriginAllowed($origin)) {
			$result['Access-Control-Allow-Origin'] = [$origin];

			if ($this->configuration->areCredentialsAllowed()) {
				$result['Access-Control-Allow-Credentials'] = ['true'];
			}

			$exposeHeaders = $this->configuration->listExposeHeaders();

			if ($exposeHeaders !== []) {
				$result['Access-Control-Expose-Headers'] = [join(', ', $exposeHeaders)];
			}

			$result['Vary'] = ['Origin'];
		}

		return $result;
	}



	/**
	 * @param list<HttpMethod> $supportedMethods
	 * @return array<string, list<string>>
	 */
	public function createPreflightHeaders(
		array $supportedMethods,
		string $requestedMethod,
		?string $requestedHeaders,
	): array
	{
		$requestedMethod = HttpMethod::tryFrom($requestedMethod);

		if ($requestedMethod === null) {
			return [];
		}

		$result = [
			'Vary' => [
				'Access-Control-Request-Headers',
				'Access-Control-Request-Method',
			],
		];

		$allowedMethods = $this->configuration->listAllowMethods(
			$supportedMethods,
			$requestedMethod,
		);

		if ($allowedMethods !== []) {
			$result['Access-Control-Allow-Methods'] = [join(', ', array_map(
				static fn ($allowedMethod) => $allowedMethod->value,
				$allowedMethods,
			))];

			if ($requestedHeaders !== null) {
				$allowedHeaders = $this->configuration->listAllowHeaders(
					array_map('trim', explode(',', $requestedHeaders)),
				);

				if ($allowedHeaders !== []) {
					$result['Access-Control-Allow-Headers'] = [join(', ', $allowedHeaders)];
				}
			}

			$result['Access-Control-Max-Age'] = [(string) $this->configuration->getMaxAge()];
		}

		return $result;
	}

}
