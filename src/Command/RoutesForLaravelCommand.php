<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\RouterInterface;

class RoutesForLaravelCommand extends Command
{
    protected static $defaultName = 'app:routes-for-laravel';

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Routes converted for Laravel');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Route::prefix(\'admin\')->group(function () {');

        $routes = $this->router->getRouteCollection();
        foreach ($routes as $name => $route) {
            if ('/admin' == substr($route->getPath(), 0, 6)) {
                $getDefaults = $route->getDefaults();

                $controller = $getDefaults['_controller'];

                $method = substr($controller, strpos($controller, '::') + 2);

                $parts = explode('\\', substr($controller, 0, strpos($controller, '::')));
                $controller = $parts[count($parts) - 1];

                $path = substr($route->getPath(), 7);

                if ('cat' ==  $path || 'console' ==  $path || true == in_array($method, ['create', 'update', 'reindex', 'createAlias'])) {
                    $output->writeln('    Route::match([\'get\', \'post\'], \''.$path.'\', \''.$controller.'@'.$method.'\')->name(\''.$name.'\');');
                } else {
                    $output->writeln('    Route::get(\''.$path.'\', \''.$controller.'@'.$method.'\')->name(\''.$name.'\');');
                }
            }
        }

        $output->writeln('});');

        return 0;
    }
}
