<?php declare(strict_types=1);

namespace Kappezette\Endpoints;


final class FlatData implements ArrayData
{

	/**
	 * @param list<list<scalar>> $data
	 */
	public function __construct(
		private readonly array $data,
	) {}



	public function format(ResponseContentType $responseContentType): string
	{
		return $responseContentType->formatBody($this->data);
	}

}
