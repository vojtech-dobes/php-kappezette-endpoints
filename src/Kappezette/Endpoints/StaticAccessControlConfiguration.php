<?php declare(strict_types=1);

namespace Kappezette\Endpoints;

use Nette;


final class StaticAccessControlConfiguration implements AccessControlConfiguration
{

	/**
	 * @param list<string> $allowedHeaderPatterns
	 * @param list<string> $allowedHeaders
	 * @param list<HttpMethod>|null $allowedMethods
	 * @param list<string> $allowedOriginPatterns
	 * @param list<string> $allowedOrigins
	 * @param list<string> $exposedHeaders
	 * @param int<1, max> $maxAge
	 */
	public function __construct(
		public readonly array $allowedHeaderPatterns = [],
		public readonly array $allowedHeaders = [],
		public readonly ?array $allowedMethods = null,
		public readonly array $allowedOriginPatterns = [],
		public readonly array $allowedOrigins = [],
		public readonly bool $credentialsAllowed = false,
		public readonly array $exposedHeaders = [],
		public readonly int $maxAge = 5,
	) {}



	public function isOriginAllowed(string $origin): bool
	{
		return (
			in_array($origin, $this->allowedOrigins, true)
			|| array_any(
				$this->allowedOriginPatterns,
				static fn ($allowedOriginPattern) => Nette\Utils\Strings::match($origin, $allowedOriginPattern) !== null,
			)
		);
	}



	public function areCredentialsAllowed(): bool
	{
		return $this->credentialsAllowed;
	}



	public function listExposeHeaders(): array
	{
		return $this->exposedHeaders;
	}



	public function listAllowMethods(
		array $supportedMethods,
		HttpMethod $requestedMethod,
	): array
	{
		if ($this->allowedMethods === null) {
			return $supportedMethods;
		}

		$result = [];

		foreach ($supportedMethods as $supportedMethod) {
			if (in_array($supportedMethod, $this->allowedMethods, true)) {
				$result[] = $supportedMethod;
			}
		}

		return $result;
	}



	public function listAllowHeaders(
		array $requestedHeaders,
	): array
	{
		return array_filter(
			$requestedHeaders,
			fn ($requestedHeader) => (
				in_array($requestedHeader, $this->allowedHeaders, true)
				|| array_any(
					$this->allowedHeaderPatterns,
					static fn ($allowedHeaderPattern) => Nette\Utils\Strings::match($requestedHeader, $allowedHeaderPattern) !== null,
				)
			),
		);
	}



	public function getMaxAge(): int
	{
		return $this->maxAge;
	}

}
