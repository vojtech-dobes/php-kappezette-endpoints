<?php declare(strict_types=1);

namespace Kappezette\Endpoints;

use Nette;


final class ApiResponse
{

	/** @var Nette\Http\IResponse::S1*|Nette\Http\IResponse::S2*|Nette\Http\IResponse::S3*|Nette\Http\IResponse::S4*|Nette\Http\IResponse::S5*|null */
	private ?int $statusCode = null;

	/** @var array<string, list<string>> */
	private array $httpHeaders = [];



	/**
	 * @param Nette\Http\IResponse::S1*|Nette\Http\IResponse::S2*|Nette\Http\IResponse::S3*|Nette\Http\IResponse::S4*|Nette\Http\IResponse::S5* $statusCode
	 */
	public function setStatusCode(int $statusCode): void
	{
		$this->statusCode = $statusCode;
	}



	/**
	 * @param array<string, list<string>|string> $headers
	 */
	public function addHttpHeaders(array $headers): void
	{
		foreach ($headers as $headerName => $headerValue) {
			$this->addHttpHeader($headerName, $headerValue);
		}
	}



	/**
	 * @param list<string>|string $headerValue
	 */
	public function addHttpHeader(string $headerName, array|string $headerValue): void
	{
		if (array_key_exists($headerName, $this->httpHeaders)) {
			if (is_array($headerValue)) {
				$this->httpHeaders[$headerName] = array_merge($this->httpHeaders[$headerName], $headerValue);
			} else {
				$this->httpHeaders[$headerName][] = $headerValue;
			}
		} else {
			$this->setHttpHeader($headerName, $headerValue);
		}
	}



	/**
	 * @param list<string>|string $headerValue
	 */
	public function setHttpHeader(string $headerName, array|string $headerValue): void
	{
		$this->httpHeaders[$headerName] = is_array($headerValue) ? $headerValue : [$headerValue];
	}



	/**
	 * @return Nette\Http\IResponse::S1*|Nette\Http\IResponse::S2*|Nette\Http\IResponse::S3*|Nette\Http\IResponse::S4*|Nette\Http\IResponse::S5*|null
	 */
	public function getStatusCode(): ?int
	{
		return $this->statusCode;
	}



	/**
	 * @return array<string, list<string>>
	 */
	public function getHttpHeaders(): array
	{
		return $this->httpHeaders;
	}

}
