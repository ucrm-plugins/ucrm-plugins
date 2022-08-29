<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\Plugins\Commands\UPM\PhpStorm;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UCRM\Plugins\Commands\PluginSpecificCommand;
use UCRM\Plugins\PhpStorm\Deployment;
use UCRM\Plugins\PhpStorm\XmlConfigManager;
use UCRM\Plugins\Support\FileSystem;

/**
 * PhpStormDeploymentMapCommand
 *
 * @author Ryan Spaeth <rspaeth@spaethtech.com>
 * @copyright 2022 Spaeth Technologies Inc.
 *
 * @final
 */
final class PhpStormDeploymentMapCommand extends PluginSpecificCommand
{
    
    /**
     * @inheritDoc
     */
    protected function configure() : void
    {
        $this
            ->setName("phpstorm:deployment:map")
            ->setDescription("Maps a deployment path")
            ->addArgument("name", InputArgument::REQUIRED, "The name of the plugin")
            ->addArgument("server", InputArgument::OPTIONAL, "The name of the server, if omitted then the first available is used", "vagrant");

   }

    /**
     * @inheritDoc
     *
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $deployment = Deployment::load("deployment");
        
        //$server = $deployment->getServerNamedOrFirst("vagrant");
        
        //print_r($server);
        $deployment->addPathMapping("testing");
        
        return self::SUCCESS;
    }
    
    
}
