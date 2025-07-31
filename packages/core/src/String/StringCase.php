<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\String;

enum StringCase
{
    case Upper;
    case Lower;
    case Title;
    case Camel;
    case Pascal;
    case Snake;
    case Kabob;
    case Screaming;
    case Dot;

    private const array TOKEN_PATTERN = [
        '/[_\.\-\s]+/',
        '/(?<=(?:\p{Lu}))(\p{Lu}\p{Ll})/',
        '/(?<=(?:\p{Ll}|\p{Nd}))(\p{Lu})/',
    ];

    private const array TOKEN_REPLACEMENT = ['_', '_\1', '_\1'];

    public function from(string $string): string
    {
        return match ($this->name) {
            'Upper' => \strtoupper($string),
            'Lower' => \strtolower($string),
            'Title' => \implode(' ', \array_map(\ucfirst(...), self::tokenize($string))),
            'Camel' => \lcfirst(\implode('', \array_map(\ucfirst(...), self::tokenize($string)))),
            'Pascal' => \implode('', \array_map(\ucfirst(...), self::tokenize($string))),
            'Snake' => \implode('_', self::tokenize($string)),
            'Kabob' => \implode('-', self::tokenize($string)),
            'Screaming' => \strtoupper(\implode('_', self::tokenize($string))),
            'Dot' => \implode('.', self::tokenize($string)),
        };
    }

    /**
     * @return array<int, string>
     */
    private static function tokenize(string $string): array
    {
        $string = \trim($string, "-_. \t\n\r\0\x0B");
        $string = \preg_replace(self::TOKEN_PATTERN, self::TOKEN_REPLACEMENT, $string);
        $string = \strtolower((string)$string);

        return \explode('_', $string);
    }
}
