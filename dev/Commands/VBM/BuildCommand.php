<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\Plugins\Commands\VBM;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UCRM\Plugins\Commands\BaseCommand;
use UCRM\Plugins\Support\FileSystem;

//use Symfony\Component\Console\Input\InputArgument;

/**
 * CreateCommand
 *
 * @author Ryan Spaeth <rspaeth@spaethtech.com>
 * @copyright 2022 Spaeth Technologies Inc.
 *
 * @final
 */
final class BuildCommand extends BaseCommand
{
    protected const DEFAULT_UISP_VERSION = "1.4.4";
    
    protected const SEMVER_PATTERN = "#^(?<major>\d)\.(?<minor>\d)\.(?<patch>\d)(?:-(?<release>beta\.\d))?$#";
    protected const NAMING_PATTERN = "#^(?<name>[A-Za-z][A-Za-z0-9-_]*)$#";
    protected const UPDATE_PATTERN = "#^(UISP_VERSION\s*=\s*\".*\")\$#m";
    
    
    protected string $version;
    protected bool $development;
    protected bool $publish;
    protected bool $force;
    
    protected string $name;
    protected string $path;
    protected string $file;
    

    /**
     * @inheritDoc
     */
    protected function configure() : void
    {
        $this
            ->setName("build")
            ->setDescription("Builds the specified version of UISP as a Vagrant box")
            ->addArgument("version", InputArgument::OPTIONAL, "The version of UISP", self::DEFAULT_UISP_VERSION)
            ->addOption("dev", "d", InputOption::VALUE_NONE,
                "Specifies that the development version of UISP is to be used")
            ->addOption("publish", "p", InputOption::VALUE_NONE,
                "Also triggers a Vagrant Cloud publish after the build is complete")
            ->addOption("force", "f", InputOption::VALUE_NONE,
                "Forces the box to be built, even it it already exists locally");
        
    }
    
    /**
     * @inheritDoc
     */
    protected function validate(InputInterface $input, OutputInterface $output): bool
    {
        $this->version = $input->getArgument("version");
        $this->development = $input->getOption("dev");
        $this->publish = $input->getOption("publish");
        $this->force = $input->getOption("force");
    
        if (!preg_match(self::SEMVER_PATTERN, $this->version, $matches))
            die("Invalid Version!");
        
        $this->name = "uisp".($this->development ? "-dev" : "");
        $this->path = FileSystem::path(VAGRANT_BOX_PATH."/{$this->name}");
        $this->file = FileSystem::path("{$this->path}/{$this->name}-{$this->version}.box");
        
        // $major, $minor, $patch, $release
        // extract(array_filter($matches, fn($key) => is_string($key), ARRAY_FILTER_USE_KEY));
        
        return TRUE;
    }
   

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $return = self::SUCCESS;
        
        if (!file_exists($this->file) || $this->force)
        {
            // Build
            $return = $this->build($input, $output);
        }
        
        // Already built
        
        
        if ($this->publish)
        {
            // Publish
            $return = $this->publish($input, $output);
        }
    
        /*
        BOX_ID="<your box id>"
        vagrant destroy -f
        set -e
        vagrant up # Start the box and run any defined scripts
        vagrant package --output ${BOX_ID}.box # Store the virtual machine with all the software installed into a box file.
        vagrant box add --force ${BOX_ID} ${BOX_ID}.box # Add the box to the vagrant repo using the given id.
        vagrant destroy -f # Stop the vagrant instance
        rm ${BOX_ID}.box # Remove the file
        */
        
        
        return $return;
    }
    
    protected function build(InputInterface $input, OutputInterface $output): int
    {
        $this->io->info("[BUILD] Beginning Box build...");
        
        // Save the current working directory, and switch to the box directory.
        $owd = getcwd();
        chdir($this->path);
        
        // Get the Vagrantfile path.
        $file = FileSystem::path($this->path . "/Vagrantfile");

        $this->io->info("[BUILD] Destroying any previous build Boxes...");
        passthru("vagrant destroy --force");
    
        $this->io->info("[BUILD] Updating version in Box configuration...");
        // Update the specified version in the Vagrantfile.
        file_put_contents(
            $file,
            preg_replace(self::UPDATE_PATTERN,
                "UISP_VERSION=\"{$this->version}\"",
                file_get_contents($file)
            )
        );
    
        $this->io->info("[BUILD] Initializing the new Box...");
        passthru("vagrant up");
    
        $this->io->info("[BUILD] Packaging the newly created Box...");
        passthru("vagrant package --output {$this->name}-{$this->version}.box");
    
        passthru("vagrant box add --force ucrm-plugins/{$this->name}-{$this->version} {$this->name}-{$this->version}.box");
        
        chdir($owd);
    
        return self::SUCCESS;
    }
    
    protected function publish(InputInterface $input, OutputInterface $output): int
    {
        $this->io->info("[PUBLISH] Beginning Box publish...");
        
        // Save the current working directory, and switch to the box directory.
        $owd = getcwd();
        chdir($this->path);
        
        $user = "ucrm-plugins";
        
        // IF an optional secrets file exists, THEN attempt to publish this new package...
        if (($ini = realpath(VAGRANT_BOX_PATH."/.env")) && ($env = parse_ini_file($ini)))
        {
            // Get any provided username and authentication token.
            $user = $env["VAGRANT_CLOUD_USER"];
            $auth = $env["VAGRANT_CLOUD_AUTH"];
        
            // IF credentials have been provided, THEN proceed...
            if ($user && $auth)
            {
                $this->io->info("[PUBLISH] Logging into Vagrant Cloud...");
                passthru("vagrant cloud auth login --token $auth");
            }
        }
    
        $this->io->info("[PUBLISH] Publishing the newly created Box...");
        passthru("vagrant cloud publish $user/{$this->name} {$this->version} virtualbox ".
            "{$this->name}-{$this->version}.box --release --force ".
            "--version-description \"UISP {$this->version} running on Ubuntu 20.04\"");
        
        // Return to the previous working directory.
        chdir($owd);
        
        return self::SUCCESS;
    }

}
