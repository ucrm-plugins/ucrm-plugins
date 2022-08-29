<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\Plugins\Commands\UPM;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UCRM\Plugins\Commands\PluginSpecificCommand;
use UCRM\Plugins\Support\FileSystem;

/**
 * ExecCommand
 *
 * @author Ryan Spaeth <rspaeth@spaethtech.com>
 * @copyright 2022 Spaeth Technologies Inc.
 *
 * @final
 */
final class ExecCommand extends PluginSpecificCommand
{
    protected string $command;
    protected string $args;
    
    /**
     * @inheritDoc
     */
    protected function configure() : void
    {
        $this
            ->setName("exec")
            ->setDescription("Runs a command (remotely) inside the UCRM container and from the Plugin's directory")
            ->addArgument("name", InputArgument::REQUIRED, "The name of the plugin")
            ->addArgument("exec", InputArgument::REQUIRED, "The command to execute")
            ->addArgument("args", InputArgument::IS_ARRAY, "Any optional arguments", []);
        
    }
   
    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $this->beforeExecute($input, $output);
    
        if (!file_exists($existing = FileSystem::path(PROJECT_DIR."/plugins/$this->name")))
            $this->error("A Plugin with that name could not be found locally at: $existing", TRUE);
        
        $this->command = $input->getArgument("exec");
        $this->args = implode(" ", $input->getArgument("args"));
        
        
        
        passthru("sudo docker exec -t ucrm /scripts/plugin-command.sh $this->name $this->command $this->args");
    
        $this->afterExecute($input, $output);
        
        return self::SUCCESS;
    }
    
    
}

