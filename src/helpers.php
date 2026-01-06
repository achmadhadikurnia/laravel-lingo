<?php

use Kanekescom\Lingo\Lingo;
use Kanekescom\Lingo\LingoBuilder;

/*
|--------------------------------------------------------------------------
| Translation Helper Functions
|--------------------------------------------------------------------------
|
| These functions are wrappers around the Lingo class for convenience.
|
*/

if (! function_exists('lingo')) {
    /**
     * Create a new Lingo builder instance for chainable operations.
     *
     * @param  array<string, string>  $translations
     * @param  string|null  $locale  Optional locale for auto-save path
     *
     * @example
     * lingo(['Hello' => 'Halo'], 'id')->sortKeys()->save();
     * lingo(['Hello' => 'Halo'])->to('id')->save();
     * lingo($translations)->clean()->toJson();
     */
    function lingo(array $translations = [], ?string $locale = null): LingoBuilder
    {
        return Lingo::make($translations, $locale);
    }
}
