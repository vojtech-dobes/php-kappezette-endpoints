<?php declare(strict_types=1);

namespace Kappezette\Endpoints;


final class ErrorData implements ArrayData
{

	/**
	 * @param array<string, mixed> $errorContext
	 */
	public function __construct(
		private readonly string $errorCode,
		private readonly ?string $errorMessage,
		private readonly array $errorContext,
	) {}



	public function format(ResponseContentType $responseContentType): string
	{
		$payload = [
			'code' => $this->errorCode,
		];

		if ($this->errorMessage !== null) {
			$payload['message'] = $this->errorMessage;
		}

		if ($this->errorContext !== []) {
			$payload['context'] = $this->errorContext;
		}

		return $responseContentType->formatBody($payload);
	}

}
