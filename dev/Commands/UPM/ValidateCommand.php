<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\Plugins\Commands\UPM;

use Opis\JsonSchema\Validator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UCRM\Plugins\Commands\PluginSpecificCommand;
use UCRM\Plugins\Support\FileSystem;

/**
 * ValidateCommand
 *
 * @author Ryan Spaeth <rspaeth@spaethtech.com>
 * @copyright 2022 Spaeth Technologies Inc.
 *
 * @final
 */
final class ValidateCommand extends PluginSpecificCommand
{
    protected array $errors = [];

    /**
     * @inheritDoc
     */
    protected function configure() : void
    {
        $this
            ->setName("validate")
            ->setDescription("Validates the specified UCRM Plugin")
            ->addArgument("name", InputArgument::REQUIRED, "The name of the plugin");
            //->addOption("verbose", "v", InputOption::VALUE_NONE, "Show verbose output");

   }

    /**
     * @inheritDoc
     *
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $this->beforeExecute($input, $output);
    
        $this->io->writeln("\nValidating Plugin at: $this->cwd");
    
        $this->requiredFiles([ "README.md", "src/main.php", "src/manifest.json" ]);
        $this->validSyntax();
        $this->validManifest();
        
        $this->io->section("\nSUMMARY");
        $this->io->writeln("No issues found!\n");
        
        $this->afterExecute($input, $output);
        
        return self::SUCCESS;
    }
    
    
    protected function requiredFiles(array $files)
    {
        $this->io->section("Required Files");
    
        $missing = [];
    
        if (file_exists(FileSystem::path("$this->cwd/src/composer.json"))
            && ! file_exists($lock = FileSystem::path("$this->cwd/src/composer.lock")))
        {
            $missing[] = $lock;
            $this->io->writeln("Missing: src/composer.lock");
        }
        
        foreach($files as $file)
        {
            if(!file_exists($path = FileSystem::path("$this->cwd/$file")))
            {
                $missing[] = $path;
                $this->io->writeln("Missing: $file");
            }
        }
        
        if ($missing)
            $this->error("One or more required files are missing, see output above!", TRUE);
    
        $this->io->writeln("Required files found!");
    }
    
    protected function validSyntax()
    {
        $this->io->section("PHP Syntax");
        
        $errors = 0;
        
        FileSystem::each($this->cwd,
            function(string $file) use (&$errors)
            {
                if ((stripos($file, ".php") === strlen($file) - 4) &&
                    (strpos($file, "src/vendor/") === FALSE) &&
                    (strpos($file, "www/") === FALSE))
                {
                    $output = [];
                    $result = 0;
    
                    $this->io->writeln($file);
                    
                    exec("php -l $file", $output, $result);
    
                    if ($result !== 0)
                    {
                        $errors++;
                        $this->io->newLine();
                    }
                }
                
            },
            "/"
        );
        
        if ($errors)
            $this->error("Syntax errors detected, see output above!", TRUE);
        
    }
    
    protected function validSyntaxParallel()
    {
        $this->io->section("PHP Syntax");
        
        if (!file_exists($this->getVendorBin("parallel-lint")))
            $this->error("Missing dependency: 'php-parallel-lint/php-parallel-lint'", TRUE);
    
        $cmd = "\"" . $this->getVendorBin("parallel-lint") ."\"";
        $args = "--show-deprecated --colors";
    
        $exclusions = [
            "www",
            "src/vendor"
        ];
    
        $ignore = "";
    
        foreach($exclusions as $exclusion)
            $ignore .= "--exclude $exclusion ";
    
        $result = 0;
        passthru("$cmd $args $ignore .", $result);
    
        if ($result !== 0)
            $this->error("Syntax errors detected, see output above!", TRUE);
        
    }
    
    protected function validManifest()
    {
    
        $validator = new Validator();
        $results = $validator->validate(file_get_contents("src/manifest.json"), file_get_contents(PROJECT_PATH."/manifest.schema.json"));
        
        if ($results->hasError())
        {
            $error = $results->error();
            print_r($error->message());
            print_r($error->keyword());
            
            //foreach($results->error()->subErrors() as $error)
            //    print_r($error->message());
        }
       
    }
    
    
}
