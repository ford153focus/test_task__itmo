<?php


namespace App\Twig;


use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('bookCover', [$this, 'getBookCover']),
        ];
    }

    public function getBookCover(int $id)
    {
        $projectDir = dirname(dirname(__DIR__));

        if (file_exists("$projectDir/public/assets/images/book-covers/$id.jpg")) {
            return "/assets/images/book-covers/$id.jpg";
        } else {
            return "/assets/images/book-covers/0.jpg";
        }
    }
}
