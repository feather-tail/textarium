<?php
declare(strict_types=1);

namespace App\Lib;

use Decoda\Decoda;
use Decoda\Filter\TableFilter;

class BbcodeParser
{
    public function toHtml(string $text): string
    {

        $decoda = new Decoda($text, [
            'xhtmlOutput'    => true,
            'strictMode'     => false,
            'escapeHtml'     => true,
            'shorthandLinks' => false,
            'lineBreaks'     => Decoda::NL_REMOVE,
        ]);
        $decoda->setLineBreaks(false);
        $decoda->defaults();
        $decoda->addFilter(new TableFilter());

        $html = $decoda->parse();

        return $html;
    }
}
