<?php declare(strict_types=1);

namespace Kappezette\Endpoints;


final class TextData
{

	public function __construct(
		public readonly string $mimeType,
		public readonly string $content,
	) {}

}
