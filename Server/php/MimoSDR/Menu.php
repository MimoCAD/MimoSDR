<?php
namespace MimoSDR;

class Menu extends \TTG\Menu
{
    public function printHeader(string $brand = 'MimoSDR', string $img = '/favicon.ico')
    {
        parent::printHeader($brand, $img);
    }

}
