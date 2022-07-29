<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\Plugins\Commands\UPM;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UCRM\Plugins\Commands\PluginSpecificCommand;
use UCRM\Plugins\Support\FileSystem;

/**
 * BundleCommand
 *
 * @author Ryan Spaeth <rspaeth@spaethtech.com>
 * @copyright 2022 Spaeth Technologies Inc.
 *
 * @final
 */
final class BundleCommand extends PluginSpecificCommand
{
    

    /**
     * @inheritDoc
     */
    protected function configure() : void
    {
        $this
            ->setName("bundle")
            ->setDescription("Bundles the specified UCRM plugin")
            ->addArgument("name", InputArgument::REQUIRED, "The name of the plugin");

   }

    /**
     * @inheritDoc
     *
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $this->beforeExecute($input, $output);
        
        chdir("src");
        
        if (file_exists("composer.json"))
        {
            exec("composer install --ansi");
            exec("composer archive --ansi --file $this->name");
        }
    
        chdir("..");
        
        $uri = FileSystem::uri(getcwd()."/$this->name.zip");
        $this->io->writeln($uri);
        
        $this->afterExecute($input, $output);
        
        return self::SUCCESS;
    }

}
