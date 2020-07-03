<?php

namespace App\Command;

use App\Manager\CallManager;
use App\Model\CallRequestModel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Response;

class GenerateFaviconsCommand extends Command
{
    protected static $defaultName = 'app:generate-favicons';

    public function __construct(CallManager $callManager)
    {
        $this->callManager = $callManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Generate favicons');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->colors as $name => $code) {
            $this->generateFavicon($name, $this->size);
        }

        return Command::SUCCESS;
    }

    private $size = 64;

    private $colors = [
        'red' => 'dc3545',
        'yellow' => 'ffc107',
        'green' => '28a745',
    ];

    private function generateFavicon($color, $size)
    {
        $file = __DIR__.'/../../public/favicon-'.$color.'.png';

        $image = imagecreate($size, $size);

        $split = str_split($this->colors[$color], 2);
        $r = hexdec($split[0]);
        $g = hexdec($split[1]);
        $b = hexdec($split[2]);

        $color = imagecolorallocate($image, $r, $g, $b);

        imagerectangle($image, 0, 0, $size, $size, $color);

        imagepng($image, $file);
    }
}
