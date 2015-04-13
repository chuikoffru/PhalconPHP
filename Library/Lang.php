<?php

namespace Lib;

require_once('Adapter/Translate/Json.php');

class Lang extends \Phalcon\Mvc\User\Component
{


    public function getLang($module)
    {
        if (!$this->cookies->has('lang'))
        {
            $this->cookies->set('lang', $this->request->getBestLanguage());
        }

        $path = __DIR__ . "/../modules/" . $module . "/language/" . $this->cookies->get('lang') . ".json";


        if (!file_exists($path))
        {
            $path = __DIR__ . "/../modules/" . $module . "/language/ru-RU.json";
        }

        return new \Phalcon\Translate\Adapter\Json(array(
            "path" => $path
        ));
    }

    public function setLang($lang)
    {
        $this->cookies->set('lang', $lang);
    }

    public function myLang()
    {
        return $this->cookies->get('lang')->getValue();
    }

}