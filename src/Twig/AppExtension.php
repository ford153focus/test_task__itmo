<?php


namespace App\Twig;


use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('bookCover', [$this, 'getBookCover']),
        ];
    }

    public function getBookCover(int $id): string
    {
        $projectDir = dirname(__DIR__, 2);

        if (file_exists("$projectDir/public/assets/images/book-covers/$id.jpg")) {
            return "/assets/images/book-covers/$id.jpg";
        }

        return "/assets/images/book-covers/0.jpg";
    }
}
