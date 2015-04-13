<?php

namespace Phalcon\Translate\Adapter;

require('Base.php');

use \Phalcon\Translate\Adapter;
use \Phalcon\Translate\Exception;


class Json extends Base implements \Phalcon\Translate\AdapterInterface
{

    public function __construct($options){

        if (!isset($options['path'])) {
            throw new Exception("Parameter 'path' is required");
        }

        $this->options = $options;

    }

    public function _($translateKey, $placeholders=null)
    {
        return $this->query($translateKey, $placeholders);
    }

    public function query($index, $placeholders=null)
    {
        $options = $this->options;

        $translation = json_decode(file_get_contents($options['path']));

        return self::setPlaceholders($translation->$index, $placeholders);
    }

    public function exists($index)
    {

    }


}