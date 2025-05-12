<?php declare(strict_types=1);

namespace Kappezette\Endpoints;


interface AccessControlConfiguration
{

	function isOriginAllowed(string $origin): bool;



	function areCredentialsAllowed(): bool;



	/**
	 * @return array<string>
	 */
	function listExposeHeaders(): array;



	/**
	 * @param list<HttpMethod> $supportedMethods
	 * @return array<HttpMethod>
	 */
	function listAllowMethods(
		array $supportedMethods,
		HttpMethod $requestedMethod,
	): array;



	/**
	 * @param list<string> $requestedHeaders
	 * @return array<string>
	 */
	function listAllowHeaders(
		array $requestedHeaders,
	): array;



	/**
	 * @return int<1, max>
	 */
	function getMaxAge(): int;

}
