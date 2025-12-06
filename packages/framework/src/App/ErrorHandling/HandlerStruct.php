<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\App\ErrorHandling;

final class HandlerStruct
{
    public function __construct(
        public ErrorHandler|null $error = null,
        public ExceptionHandler|null $exception = null,
    ){
    }
}
