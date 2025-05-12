<?php declare(strict_types=1);

namespace Kappezette\Endpoints;


final class ResponseBodyFormatter
{

	/**
	 * @param array<string, ResponseContentType> $supportedMimeTypes
	 */
	public function __construct(
		private readonly array $supportedMimeTypes,
		public readonly ?string $defaultMimeType,
	) {}



	/**
	 * @param list<string> $acceptedMimeTypes
	 */
	public function getResponseContentType(array $acceptedMimeTypes): ?ResponseContentType
	{
		foreach ($acceptedMimeTypes as $acceptedMimeType) {
			if (array_key_exists($acceptedMimeType, $this->supportedMimeTypes)) {
				return $this->supportedMimeTypes[$acceptedMimeType];
			}

			if ($acceptedMimeType === '*/*' && $this->defaultMimeType !== null) {
				return $this->supportedMimeTypes[$this->defaultMimeType];
			}

			$genericType = explode('/', $acceptedMimeType)[0];

			foreach ($this->supportedMimeTypes as $formatterMimeType => $formatter) {
				if (str_starts_with($formatterMimeType, $genericType . '/')) {
					return $formatter;
				}
			}
		}

		return ($this->defaultMimeType !== null && $acceptedMimeTypes === [])
			? $this->supportedMimeTypes[$this->defaultMimeType]
			: null;
	}



	/**
	 * @param list<string> $acceptedMimeTypes
	 */
	public function getErrorResponseContentType(array $acceptedMimeTypes): ResponseContentType
	{
		$result = $this->getResponseContentType($acceptedMimeTypes);

		if ($result !== null) {
			return $result;
		}

		if ($this->defaultMimeType !== null) {
			return $this->supportedMimeTypes[$this->defaultMimeType];
		}

		return new ResponseContentTypes\JsonResponseContentType();
	}

}
