<?php declare(strict_types=1);

namespace Kappezette\Endpoints;

use Nette;


final class RequestBodyProvider
{

	/**
	 * @template TMimeType of string
	 * @template TImplicitMimeType of TMimeType
	 * @param array<TMimeType, RequestContentType> $supportedMimeTypes
	 * @param TImplicitMimeType $implicitMimeType
	 */
	public function __construct(
		private readonly array $supportedMimeTypes,
		private readonly ?string $implicitMimeType,
	) {}



	/**
	 * @return list<string>
	 */
	public function listSupportedMimeTypes(): array
	{
		return array_keys($this->supportedMimeTypes);
	}



	/**
	 * @return callable(): mixed
	 * @throws ClientErrorException
	 */
	public function getRequestBodyProvider(Nette\Http\IRequest $httpRequest, ApiResponse $apiResponse): callable
	{
		$mimeType = $this->getRequestMimeType($httpRequest) ?? $this->implicitMimeType;

		if ($mimeType === null) {
			throw ClientErrorException::create('ambiguous_content_type')
				->withErrorMessage("Request body type can't be determined")
				->withStatusCode(Nette\Http\IResponse::S415_UnsupportedMediaType)
				->withHttpHeader('Accept', $this->listSupportedMimeTypes());
		}

		if (array_key_exists($mimeType, $this->supportedMimeTypes) === false) {
			throw ClientErrorException::create('unsupported_content_type')
				->withErrorMessage("Request body type '{$mimeType}' isn't supported")
				->withStatusCode(Nette\Http\IResponse::S415_UnsupportedMediaType)
				->withHttpHeader('Accept', $this->listSupportedMimeTypes());
		}

		if ($this->doesHaveBody($httpRequest) === false) {
			return static function (): never {
				throw ClientErrorException::create('missing_body')
					->withErrorMessage('Request body is required')
					->withStatusCode(Nette\Http\IResponse::S400_BadRequest);
			};
		}

		return function () use ($httpRequest, $mimeType): mixed {
			try {
				return $this->supportedMimeTypes[$mimeType]->parseBody($httpRequest);
			} catch (InvalidRequestContentException $e) {
				throw ClientErrorException::create('malformed_body')
					->withErrorMessage($e->errorMessage)
					->withStatusCode(Nette\Http\IResponse::S400_BadRequest);
			}
		};
	}



	private function getRequestMimeType(Nette\Http\IRequest $httpRequest): ?string
	{
		$contentType = $httpRequest->getHeader('Content-Type');

		return $contentType !== null
			? Nette\Utils\Strings::match($contentType, '~([a-z]+/[a-z0-9+.-]+)~')[1] ?? null
			: null;
	}



	private function doesHaveBody(Nette\Http\IRequest $httpRequest): bool
	{
		return (
			(int) ($httpRequest->getHeader('Content-Length') ?? 0) > 0
			|| strtolower($httpRequest->getHeader('Transfer-Encoding') ?? '') === 'chunked'
		);
	}

}
