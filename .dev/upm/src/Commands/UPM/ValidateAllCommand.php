<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\Plugins\Commands\UPM;

use Exception;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UCRM\Plugins\Commands\BaseCommand;
use UCRM\Plugins\Support\FileSystem;

/**
 * ValidateAllCommand
 *
 * @author Ryan Spaeth <rspaeth@spaethtech.com>
 * @copyright 2022 Spaeth Technologies Inc.
 *
 * @final
 */
final class ValidateAllCommand extends BaseCommand
{
    protected array $errors = [];

    /**
     * @inheritDoc
     */
    protected function configure() : void
    {
        $this
            ->setName("validate-all")
            ->setDescription("Validates all UCRM Plugins");
            //->addArgument("name", InputArgument::REQUIRED, "The name of the plugin, or ALL Plugins if omitted");
            //->addOption("verbose", "v", InputOption::VALUE_NONE, "Show verbose output");

   }
    
    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $this->beforeExecute($input, $output);
    
        foreach(scandir($this->cwd) as $file)
        {
            if ($file === "." || $file === ".." || !is_dir($file))
                continue;
            
            $command = $this->getApplication()->find("validate");
    
            $greetInput = new ArrayInput([ "name" => $file ]);
            //$returnCode =
            $command->run($greetInput, $output);
            
            
        }
        
        
        
        
        
        $this->afterExecute($input, $output);
        
        return self::SUCCESS;
    }
    
    
    
    
}
