<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\Plugins\Commands\UPM;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UCRM\Plugins\Commands\BaseCommand;
use UCRM\Plugins\Support\FileSystem;
use UCRM\Plugins\Support\Templater;

//use Symfony\Component\Console\Input\InputArgument;

/**
 * CreateCommand
 *
 * @author Ryan Spaeth <rspaeth@spaethtech.com>
 * @copyright 2022 Spaeth Technologies Inc.
 *
 * @final
 */
final class CreateCommand extends BaseCommand
{
    protected const NAMING_PATTERN = "/^[a-z][a-z\d-]*$/";
    
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
            ->addOption("submodule", "s", InputOption::VALUE_NONE, "When used with --git, adds the Plugin as a submodule")
            ->addOption("force", "f", InputOption::VALUE_NONE, "Forces replacement of an existing Plugin");
        
    }
   
    /**
     * @inheritDoc
     *
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        
        $info = Templater::getAuthor();
        
        
        Templater::replace(FileSystem::path(PROJECT_PATH."/plugins/testing/src/"), [
            "NAME" => $input->getArgument("name"),
            "AUTHOR" => $info,
            
        ]);
        
        //print_r ($info);
        exit;
        
        
        $owd = getcwd();
        chdir(FileSystem::path(PROJECT_PATH."/plugins/"));
        
        $name = $input->getArgument("name");
        $template = $input->getArgument("template");
        $submodule = $input->getOption("submodule");
        $force = $input->getOption("force");
        
        if (!preg_match(self::NAMING_PATTERN, $name))
            $this->error("The Plugin's name is invalid, please adhere to ".self::NAMING_PATTERN, TRUE);
        
        if (file_exists($existing = FileSystem::path(PROJECT_PATH."/plugins/$name")))
        {
            if (!$force)
                $this->error("A Plugin with that name already exists at: $existing", TRUE);
            else
            {
                $this->io->writeln("Removing existing Plugin...");
                FileSystem::rmdir($existing, [], TRUE);
                print_r("\n");
            }
        }
        
        if ($path = realpath(PROJECT_PATH."/templates/$template"))
        {
            $this->io->writeln("Template found, locally, duplicating...");
            FileSystem::copyDir($path, FileSystem::path(PROJECT_PATH."/plugins/$name"), TRUE, TRUE);
            
        }
        else
        {
            $this->io->writeln("Template not found locally, trying repositories:");
            
            $repo = filter_var($template, FILTER_VALIDATE_URL)
                ? $template
                : "https://github.com/ucrm-plugins/$template";
            
            $this->io->writeln("> $repo");
    
            FileSystem::gitClone($repo, $name, FALSE, TRUE);
        }
        
        if (!file_exists($name))
            $this->error("The specified template could not be found: $template", TRUE);
        
        chdir("$name/src");
        
        if (file_exists("manifest.json"))
        {
            $manifest = json_decode(file_get_contents("manifest.json"), TRUE);
            $manifest["information"]["name"] = $name;
            //$manifest["information"]["author"] =
            // TODO: Improve the templating system!
            
            file_put_contents("manifest.json", json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
        
        if(file_exists("composer.json"))
        {
            exec("composer install");
            exec("composer archive --file $name");
        
        }
        
        chdir("..");
        
        mkdir("www");
        file_put_contents("www/public.php", <<<EOF
            <?php /** @noinspection PhpIncludeInspection */
            chdir(dirname('/usr/src/ucrm/app/data/plugins/$name/public.php'));
            require_once '/usr/src/ucrm/app/data/plugins/$name/public.php';
            
            EOF
        );
        
        
        $zip = FileSystem::path(PROJECT_PATH."/plugins/$name/$name.zip");
    
        $this->io->writeln(<<<EOF
            
            Your newly created Plugin should now be ready for use.
            
            Next Steps:
            - Login to your local/development UISP installation and complete setup if necessary.
              > https://localhost/
            - Install, configure and enable the Plugin using the included ZIP file:
              > $zip
            - TBC...
            
            EOF
        );
        
        
        
        
        
        
        chdir($owd);
        return 0;
    }

}
