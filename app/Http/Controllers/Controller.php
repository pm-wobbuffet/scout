<?php

namespace App\Http\Controllers;

use Butschster\Head\Facades\Meta;

abstract class Controller
{
    protected function setOGTitle(string $title)
    {
        $p = Meta::getPackage('Scouter_OG');
        $p->setTitle($title);
        Meta::replacePackage($p);
    }

    protected function setOGDescription(string $description)
    {
        $p = Meta::getPackage('Scouter_OG');
        $p->setDescription($description);
        Meta::replacePackage($p);
    }

    protected function setOGImage(string $image, array $extraprops = [])
    {
        $p = Meta::getPackage('Scouter_OG');
        $p->addImage($image);
        Meta::replacePackage($p);
    }
}
