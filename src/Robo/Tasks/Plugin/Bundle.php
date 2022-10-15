<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\Plugins\Robo\Tasks\Bundler;

use Robo\Contract\TaskInterface;
use Robo\Result;
use UCRM\Plugins\Support\FileSystem;

class Bundle implements TaskInterface
{
    protected string $plugin;
    protected string $output;

    public function __construct(string $plugin, ?string $output = null)
    {
        $this->plugin = FileSystem::path($plugin);
        $this->output = FileSystem::path($output);
    }

    public function run(): Result
    {
        $src = FileSystem::path(PROJECT_DIR."/plugins/$this->plugin/src");

        if (!file_exists($src) || !is_dir($src))
            return Result::error($this, "The specified Plugin does not seem to have a src folder!");

        chdir($src);

        if (file_exists("composer.json"))
        {
            exec("composer install --ansi");
            exec("composer archive --ansi --file $this->plugin");
        }

        echo $this->output . "\n";
        $uri = FileSystem::uri("$this->output/$this->plugin.zip");
        $this->io->writeln($uri);
        //$this->io->writeln(getcwd()."/$this->plugin.zip");

        //$this->afterExecute($input, $output);

        return self::SUCCESS;
    }
}
