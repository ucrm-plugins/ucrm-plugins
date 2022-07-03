<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\Plugins\Commands\UPM;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UCRM\Plugins\Commands\DatabaseCommand;

/**
 * CreateCommand
 *
 * @author Ryan Spaeth <rspaeth@spaethtech.com>
 * @copyright 2022 Spaeth Technologies Inc.
 *
 * @final
 */
final class InstallCommand extends DatabaseCommand
{
    protected string $name;
    protected bool $enable;
    protected bool $force;
    protected bool $reset;
    
    /**
     * @inheritDoc
     */
    protected function configure() : void
    {
        $this
            ->setName("install")
            ->setDescription("Installs the specified Plugin from the plugins folder")
            ->addArgument("name", InputArgument::REQUIRED, "The name of the Plugin to install")
            ->addOption("enable", "e", InputOption::VALUE_NONE, "Enables the Plugin after installation")
            ->addOption("force", "f", InputOption::VALUE_NONE, "Forces installation even when the Plugin name exists")
            ->addOption("reset", "r", InputOption::VALUE_NONE, "Optionally, clears the data/ folder");
        
    }
   
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
    
        $this->name = $input->getArgument("name") ?? "";
        $this->enable = $input->getOption("enable");
        $this->force = $input->getOption("force");
        $this->reset = $input->getOption("reset");
        
    }
    
    /**
     * @inheritDoc
     *
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $owd = getcwd();
        chdir(PROJECT_PATH."/ide/src/");
        
        $target = str_replace(["\\", "/"], DIRECTORY_SEPARATOR, "../../plugins/$this->name/src");
        
        if (!file_exists($target) || !is_dir($target))
        {
            $this->error("The specified Plugin could not be found in $target");
            chdir($owd);
            return self::FAILURE;
        }
    
        $link = $this->name;
        
        if (file_exists($link) && is_link($link) && $this->force)
        {
            if (!@unlink($link))
                @rmdir($link);
            
            $this->io->writeln("Removed existing Plugin symlink");
        }
        
        if (file_exists($link) && is_link($link))
        {
            $this->io->writeln("A Plugin with the same name is already installed, use --force to replace");
        }
        else
        {
            if (!@symlink($target, $link))
            {
                $this->error(
                    "A symlink to the Plugin could not be created.  ".
                    "On Windows, be sure to run this command in a terminal with elevated privileges.");
                chdir($owd);
                return self::FAILURE;
            }
    
            $this->io->writeln("Created Plugin symlink");
        }
    
        $manifest = json_decode(file_get_contents("$target/manifest.json"), TRUE);
    
        if ($this->dbPluginExistsByName($this->name) && $this->force)
        {
            $this->dbPluginDeleteByName($this->name);
            $this->io->writeln("Existing Plugin has been removed from the database");
            $this->dbAppKeyDeleteByPluginName($this->name);
            $this->io->writeln("Existing AppKey has been removed from the database");
        }
        
        if ($this->dbPluginExistsByName($this->name))
        {
            $this->error("A Plugin with the same name is already exists in the database");
            chdir($owd);
            return self::FAILURE;
        }
        
        $pluginId = $this->dbPluginInsert($manifest, $target, $this->enable);
        $this->io->writeln("Plugin has been added to the database");
        $appKeyId = $this->dbAppKeyInsert($manifest, $pluginId);
        $this->io->writeln("AppKey has been added to the database");
        
        // ucrm.json
        
        $host = $this->dbGetOptionByCode("SERVER_HOSTNAME");
        $key  = $this->dbGetAppKeyById($appKeyId);
        $url  = file_exists("$target/public.php") ? "\"https://$host/crm/_plugins/$this->name/public.php\"" : "null";
        
        $json = json_encode(<<<JSON
            {
                "ucrmPublicUrl": "https://$host/crm/",
                "ucrmLocalUrl": "http://localhost/crm/",
                "unmsLocalUrl": "http://unms:8081/nms/",
                "pluginPublicUrl": $url,
                "pluginAppKey": "$key",
                "pluginId": $pluginId
            }
            JSON,
            JSON_UNESCAPED_SLASHES
        );
    
        // Minify/Cleanup JSON and save the file.
        $json = trim(preg_replace(['#\s*:\s*#', '#(\\\r\\\n|\\\r|\\\n)\s*#', '#\\\"#'], [':', '', '"'], $json), '"');
        file_put_contents("$target/ucrm.json", $json);
        
        
        
    
    
    
        chdir($owd);
        
        return self::SUCCESS;
    }
    
    
    
    
    
    
    
    
}
