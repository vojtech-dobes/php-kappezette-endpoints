<?php declare(strict_types=1);

namespace Kappezette\Endpoints;

use Nette;
use RuntimeException;


final class ClientErrorException extends RuntimeException
{

	/**
	 * @param array<string, mixed> $errorContext
	 * @param array<string, list<string>> $httpHeaders
	 * @param Nette\Http\IResponse::S1*|Nette\Http\IResponse::S2*|Nette\Http\IResponse::S3*|Nette\Http\IResponse::S4*|Nette\Http\IResponse::S5*|null $statusCode
	 */
	private function __construct(
		public readonly string $errorCode,
		public readonly ?string $errorMessage = null,
		public readonly array $errorContext = [],
		public readonly array $httpHeaders = [],
		public readonly ?int $statusCode = null,
	)
	{
		parent::__construct($errorCode);
	}



	public static function create(string $errorCode): self
	{
		return new self(errorCode: $errorCode);
	}



	public function withErrorMessage(string $errorMessage): self
	{
		return new self(
			errorCode: $this->errorCode,
			errorContext: $this->errorContext,
			errorMessage: $errorMessage,
			httpHeaders: $this->httpHeaders,
			statusCode: $this->statusCode,
		);
	}



	public function withErrorContext(string $name, mixed $details): self
	{
		return new self(
			errorCode: $this->errorCode,
			errorContext: [
				...$this->errorContext,
				$name => $details,
			],
			errorMessage: $this->errorMessage,
			httpHeaders: $this->httpHeaders,
			statusCode: $this->statusCode,
		);
	}



	/**
	 * @param list<string>|string $headerValue
	 */
	public function withHttpHeader(string $headerName, array|string $headerValue): self
	{
		$httpHeaders = $this->httpHeaders;

		if (is_array($headerValue)) {
			if (array_key_exists($headerName, $httpHeaders)) {
				$httpHeaders[$headerName] = array_merge($httpHeaders[$headerName], $headerValue);
			} else {
				$httpHeaders[$headerName] = $headerValue;
			}
		} else {
			if (array_key_exists($headerName, $httpHeaders)) {
				$httpHeaders[$headerName][] = $headerValue;
			} else {
				$httpHeaders[$headerName] = [$headerValue];
			}
		}

		return new self(
			errorCode: $this->errorCode,
			errorContext: $this->errorContext,
			errorMessage: $this->errorMessage,
			httpHeaders: $httpHeaders,
			statusCode: $this->statusCode,
		);
	}



	/**
	 * @param Nette\Http\IResponse::S1*|Nette\Http\IResponse::S2*|Nette\Http\IResponse::S3*|Nette\Http\IResponse::S4*|Nette\Http\IResponse::S5* $statusCode
	 */
	public function withStatusCode(int $statusCode): self
	{
		return new self(
			errorCode: $this->errorCode,
			errorContext: $this->errorContext,
			errorMessage: $this->errorMessage,
			httpHeaders: $this->httpHeaders,
			statusCode: $statusCode,
		);
	}



	public function getData(): ErrorData
	{
		return new ErrorData(
			$this->errorCode,
			$this->errorMessage,
			$this->errorContext,
		);
	}

}
