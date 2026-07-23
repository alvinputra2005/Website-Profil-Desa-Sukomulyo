<?php

namespace App\Support;

final class ImageUploadRules
{
    public const MAX_KILOBYTES = 5120;
    public const MAX_WIDTH = 4000;
    public const MAX_HEIGHT = 4000;

    /**
     * Rules shared by every image upload endpoint.
     *
     * Keeping the dimension limit in one place prevents a large image from
     * reaching GD after a caller forgets to add the dimensions rule.
     */
    public static function optional(): array
    {
        return array_merge(['nullable'], self::fileRules());
    }

    public static function required(): array
    {
        return array_merge(['required'], self::fileRules());
    }

    private static function fileRules(): array
    {
        return [
            'file',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:'.self::MAX_KILOBYTES,
            'dimensions:max_width='.self::MAX_WIDTH.',max_height='.self::MAX_HEIGHT,
        ];
    }
}
