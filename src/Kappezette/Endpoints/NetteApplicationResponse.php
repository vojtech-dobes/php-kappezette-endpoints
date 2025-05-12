<?php declare(strict_types=1);

namespace Kappezette\Endpoints;

use Nette;


final class NetteApplicationResponse implements Nette\Application\Response
{

	public function __construct(
		private readonly ApiResponse $apiResponse,
		private readonly ?string $responseBody,
	) {}



	public function send(
		Nette\Http\IRequest $httpRequest,
		Nette\Http\IResponse $httpResponse,
	): void
	{
		$statusCode = $this->apiResponse->getStatusCode();

		if ($statusCode !== null) {
			$httpResponse->setCode($statusCode);
		}

		$httpResponse->deleteHeader('Content-Type');

		foreach ($this->apiResponse->getHttpHeaders() as $headerName => $headerValues) {
			foreach ($headerValues as $headerValue) {
				$httpResponse->addHeader($headerName, $headerValue);
			}
		}

		if ($this->responseBody !== null) {
			if ($httpResponse->getCode() === Nette\Http\IResponse::S200_OK) {
				$etag = '"' . hash('sha256', $this->responseBody) . '"';

				$httpResponse->setHeader('Cache-Control', 'no-cache, must-revalidate');
				$httpResponse->setHeader('ETag', $etag);

				if ($httpRequest->getHeader('if-none-match') === $etag) {
					$httpResponse->setCode(Nette\Http\IResponse::S304_NotModified);
					$httpResponse->setHeader('Content-Length', '0');
					return;
				}
			}

			echo $this->responseBody;
		}
	}

}
