<?php

namespace Phalcon\Translate\Adapter;

use Phalcon\Translate\Adapter;


abstract class Base extends Adapter
{

    public static function setPlaceholders($translation, $placeholders)
    {
        if (is_array($placeholders)) {
            foreach ($placeholders as $key => $value) {
                $translation = str_replace('%' . $key . '%', $value, $translation);
            }
        }
        return $translation;
    }
}