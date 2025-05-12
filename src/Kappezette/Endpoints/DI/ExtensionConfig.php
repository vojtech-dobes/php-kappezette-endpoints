<?php declare(strict_types=1);

namespace Kappezette\Endpoints\DI;

use Kappezette\Endpoints;


final class ExtensionConfig
{

	/** @var list<string>|null */
	public ?array $accessControlAllowedHeaderPatterns = null;

	/** @var list<string>|null */
	public ?array $accessControlAllowedHeaders = [
		'Content-Type',
	];

	/** @var list<string>|null */
	public ?array $accessControlAllowedMethods = null;

	/** @var list<string>|null */
	public ?array $accessControlAllowedOriginPatterns = null;

	/** @var list<string>|null */
	public ?array $accessControlAllowedOrigins = null;
	public ?bool $accessControlCredentialsAllowed = null;

	/** @var list<string>|null */
	public ?array $accessControlExposedHeaders = null;
	public ?int $accessControlMaxAge = null;

	public string $errorHandler = Endpoints\TracyErrorHandler::class;

	public ?string $requestBodyImplicitMimeType = 'application/json';

	/** @var array<string, class-string<Endpoints\RequestContentType>> */
	public array $requestBodySupportedMimeTypes = [
		'application/json' => Endpoints\RequestContentTypes\JsonRequestContentType::class,
	];

	public ?string $responseBodyDefaultMimeType = 'application/json';

	/** @var array<string, class-string<Endpoints\ResponseContentType>> */
	public array $responseBodySupportedMimeTypes = [
		'application/json' => Endpoints\ResponseContentTypes\JsonResponseContentType::class,
	];

}
