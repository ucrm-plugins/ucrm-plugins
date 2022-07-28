<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\Plugins\Commands\UPM;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UCRM\Plugins\Commands\PluginSpecificCommand;
use UCRM\Plugins\Support\FileSystem;
use UCRM\Plugins\Support\Git;
use UCRM\Plugins\Support\Templater;

/**
 * CreateCommand
 *
 * @author Ryan Spaeth <rspaeth@spaethtech.com>
 * @copyright 2022 Spaeth Technologies Inc.
 *
 * @final
 */
final class CreateCommand extends PluginSpecificCommand
{
    protected string $template;
    protected bool $submodule;
    protected bool $map;
    protected bool $force;
    
    /**
     * @inheritDoc
     */
    protected function configure() : void
    {
        $this
            ->setName("create")
            ->setDescription("Creates a new UCRM Plugin")
            ->addArgument("name", InputArgument::REQUIRED, "The name of the plugin")
            ->addArgument("template", InputArgument::REQUIRED, "The name of a template from templates/ or a git repo")
            //->addOption("submodule", "s", InputOption::VALUE_NONE, "When used with --git, adds the Plugin as a submodule")
            ->addOption("map", "m", InputOption::VALUE_NONE, "Also creates the server mappings, using 'upm map'")
            ->addOption("force", "f", InputOption::VALUE_NONE, "Forces replacement of an existing Plugin");
        
    }
    
    /**
     * @inheritDoc
     *
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $this->beforeExecute($input, $output);
    
        $this->template = $input->getArgument("template");
        //$this->submodule = $input->getOption("submodule");
        $this->map = $input->getOption("map");
        $this->force = $input->getOption("force");
        
        if (file_exists($existing = FileSystem::path(PROJECT_PATH."/plugins/$this->name")))
        {
            if (!$this->force)
                $this->error("A Plugin with that name already exists at: $existing", TRUE);
            else
            {
                $this->io->writeln("Removing existing Plugin...");
                FileSystem::rmdir($existing, [], TRUE);
                print_r("\n");
            }
        }
        
        if ($path = realpath(PROJECT_PATH."/templates/$this->template"))
        {
            $this->io->writeln("Template found, locally, duplicating...");
            FileSystem::copyDir($path, FileSystem::path(PROJECT_PATH."/plugins/$this->name"), TRUE, TRUE);
            
        }
        else
        {
            $this->io->writeln("Template not found locally, trying repositories:");
            
            $repo = filter_var($this->template, FILTER_VALIDATE_URL)
                ? $this->template
                : "https://github.com/ucrm-plugins/$this->template";
            
            $this->io->writeln("> $repo");
    
            FileSystem::gitClone($repo, $this->name, FALSE, TRUE);
        }
        
        if (!file_exists($this->name))
            $this->error("The specified template could not be found: $this->template", TRUE);
        
        chdir("$this->name/src");
        
    
        $this->io->writeln("Replacing template variables and executing commands...");
        
        $modified = Templater::replace(FileSystem::path(PROJECT_PATH."/plugins/$this->name/src/"), [
            "UCRM_PLUGIN_NAME" => $input->getArgument("name"),
            "UCRM_PLUGIN_AUTHOR" => Git::getAuthor(),
        ]);
    
        $this->io->writeln("Modified $modified template files!");
        
        
        if(file_exists("composer.json"))
        {
            exec("composer install");
            exec("composer archive --file $this->name");
        
        }
        
        chdir("..");
        
        mkdir("www");
        file_put_contents("www/public.php", <<<EOF
            <?php /** @noinspection PhpIncludeInspection */
            chdir(dirname('/usr/src/ucrm/app/data/plugins/$this->name/public.php'));
            require_once '/usr/src/ucrm/app/data/plugins/$this->name/public.php';
            
            EOF
        );
        
        if (file_exists($box = FileSystem::path(PROJECT_PATH."/box/vagrant/env/box.conf")))
        {
            $ini = parse_ini_file($box);
            $host = array_key_exists("HOSTNAME", $ini) ? $ini["HOSTNAME"] : "localhost";
            $ip = array_key_exists("IP", $ini) ? $ini["IP"] : "127.0.0.1";
            
            
            
            
            
            
        }
        else
        {
            $host = "localhost";
            $ip = "127.0.0.1";
        }
        
        $zip = dirname(str_replace("\\", "/", FileSystem::path(PROJECT_PATH."/plugins/$this->name/$this->name.zip")));
        $dir = FileSystem::path(PROJECT_PATH);
        $doc = str_replace("\\", "/", FileSystem::path(PROJECT_PATH."/docs/vagrant.md"));
    
        if ($this->map)
            exec("upm map $this->name");
        
        $this->io->writeln(<<<EOF
            
            Your newly created Plugin should now be ready for development.
            
            Next Steps:
            - Login to your local development UISP installation and complete setup if necessary.
              > https://$host
            
            - Install, configure and enable the Plugin using the included ZIP file:
              > file:///$zip
            
            - See file:///$doc for more information!
            
            EOF
        );
        
        $this->afterExecute($input, $output);
        return 0;
    }

}
