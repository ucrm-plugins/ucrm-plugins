<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\Plugins\Commands\UPM;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UCRM\Plugins\Commands\BaseCommand;
use UCRM\Plugins\Support\FileSystem;

/**
 * UpdateCommand
 *
 * @author Ryan Spaeth <rspaeth@spaethtech.com>
 * @copyright 2022 Spaeth Technologies Inc.
 *
 * @final
 */
class UpdateCommand extends BaseCommand
{
    protected const NAMING_PATTERN = "/^[a-z][a-z\d-]*$/";
    
    /**
     * @inheritDoc
     */
    protected function configure() : void
    {
        $this
            ->setName("update")
            ->setDescription("Updates an existing Plugin's Composer dependencies")
            ->addArgument("name", InputArgument::REQUIRED, "The name of the Plugin");
            //->addOption("force", "f", InputOption::VALUE_NONE, "Forces replacement of an existing Plugin");
        
    }
   
    /**
     * @inheritDoc
     *
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        // cd $dir && vagrant ssh -c "cd /home/unms/data/ucrm/ucrm/data/plugins/$name && composer install"
        
        $owd = getcwd();
        chdir(FileSystem::path(PROJECT_PATH."/plugins/"));
        
        $name = $input->getArgument("name");
        
        if (!preg_match(self::NAMING_PATTERN, $name))
            $this->error("The Plugin's name is invalid, please adhere to ".self::NAMING_PATTERN, TRUE);
        
        if (!file_exists($existing = FileSystem::path(PROJECT_PATH."/plugins/$name")))
        {
            $this->error("A Plugin with that name does not exist at: $existing", TRUE);
        }
        
        chdir($src = FileSystem::path("$existing/src"));
        
        
        
        
        if (!file_exists("composer.json"))
        {
            $this->error("The specified Plugin does not exist at: $src", TRUE);
        }
        
        $this->io->writeln("Updating Composer dependencies...");
        $dir = PROJECT_PATH;
        $cmd = "cd $dir &&  vagrant ssh -c \"cd /home/unms/data/ucrm/ucrm/data/plugins/$name && COMPOSER_ALLOW_SUPERUSER=1 sudo composer install\"";
        print_r($cmd);
        $output = shell_exec($cmd);
        
        if (strpos($output, "Permission denied") !== FALSE)
        {
            chdir($owd);
            print_r(">>>" . $output);
            //$this->error("It appears the Plugin has not yet been installed in UCRM.", TRUE);
        }
        
        chdir($owd);
        return 0;
    }

}
