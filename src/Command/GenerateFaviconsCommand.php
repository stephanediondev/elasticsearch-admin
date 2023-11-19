<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:generate-favicons')]
class GenerateFaviconsCommand extends Command
{
    /**
     * @var array<string> $colors
     */
    private array $colors = [
        'red' => 'dc3545',
        'orange' => 'fd7e14',
        'yellow' => 'ffc107',
        'green' => '198754',
        'purple' => '6f42c1',
        'gray' => 'adb5bd',
    ];

    protected function configure(): void
    {
        $this->setDescription('Generate favicons');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->colors as $name => $code) {
            $this->generateFavicon($name, 64);
            $this->generateFavicon($name, 144);
            $this->generateFavicon($name, 512);
        }

        return Command::SUCCESS;
    }

    private function generateFavicon(string $color, int $size): void
    {
        $file = __DIR__.'/../../public/favicon-'.$color.'-'.$size.'.png';

        if ($image = imagecreate($size, $size)) {
            $split = str_split($this->colors[$color], 2);
            $r = intval(hexdec($split[0]));
            $g = intval(hexdec($split[1]));
            $b = intval(hexdec($split[2]));

            if (false !== $color = imagecolorallocate($image, $r, $g, $b)) {
                imagerectangle($image, 0, 0, $size, $size, $color);

                imagepng($image, $file);
            }
        }
    }
}
