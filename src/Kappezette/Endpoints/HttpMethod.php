<?php declare(strict_types=1);

namespace Kappezette\Endpoints;


enum HttpMethod: string
{

	case Delete = 'DELETE';
	case Get = 'GET';
	case Head = 'HEAD';
	case Options = 'OPTIONS';
	case Patch = 'PATCH';
	case Post = 'POST';
	case Put = 'PUT';



	public function canHaveRequestBody(): bool
	{
		return match ($this) {
			self::Delete, self::Patch, self::Post, self::Put => true,
			default => false,
		};
	}

}
