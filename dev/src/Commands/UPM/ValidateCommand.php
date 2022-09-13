<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\Plugins\Commands\UPM;

use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Exceptions\SchemaException;
use Opis\JsonSchema\Helper;
use Opis\JsonSchema\Validator;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UCRM\Plugins\Commands\PluginRequiredCommand;
use UCRM\Plugins\Support\Diff;
use UCRM\Plugins\Support\Diffs\JsonDiff;
use UCRM\Plugins\Support\FileSystem;
use UCRM\Plugins\Support\JSON;
use ZipArchive;

/**
 * ValidateCommand
 *
 * @author Ryan Spaeth <rspaeth@spaethtech.com>
 * @copyright 2022 Spaeth Technologies Inc.
 *
 * @final
 */
final class ValidateCommand extends PluginRequiredCommand
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

   }

    /**
     * @inheritDoc
     *
     */
    protected function onExecute(InputInterface $input, OutputInterface $output) : int
    {
        //$this->beforeExecute($input, $output);

        $this->io->writeln("\nValidating Plugin at: $this->cwd");

        $this->requiredFiles([ "README.md", "src/main.php", "src/manifest.json" ]);
        $this->validSyntax();
        $this->validManifest($output);

        $this->io->section("\nSUMMARY");
        $this->io->writeln("No issues found!\n");

        //$this->afterExecute($input, $output);

        return self::SUCCESS;
    }


    /**
     * Ensures the required files are found.
     *
     * @param array $files
     *
     * @return bool
     */
    protected function requiredFiles(array $files): bool
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
        {
            $this->error("One or more required files are missing, see output above!", TRUE);
            return FALSE;
        }

        $this->io->writeln("Required files found!");
        return TRUE;
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

    /**
     * @throws \Exception
     */
    protected function validManifest(OutputInterface $output): bool
    {

        $this->io->section("Manifest");

        if(!file_exists("src/manifest.json"))
            return FALSE;

        $validator = new Validator();
        $data = json_decode(file_get_contents("src/manifest.json"));
        $schema = file_get_contents(PROJECT_DIR."/manifest.schema.json");

        $validator->setMaxErrors(10);
        $results = $validator->validate($data, $schema);

        if ($results->hasError())
        {
            $formatter = new ErrorFormatter();
            $this->io->writeln(json_encode(
                $formatter->format($results->error()),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            ));
            $this->error("Schema errors detected, see output above!");
            //return FALSE;
        }
        else
        {
            $this->io->writeln("Schema validated!");
        }

        // TODO: Compare differences between source manifest.json and bundled manifest.json!
        if(file_exists("$this->name.zip"))
        {
            $zip = new ZipArchive();
            $zip->open("$this->name.zip");

            $zipManifest = $zip->getFromName("manifest.json");
            unset($zip);

            if(!$zipManifest)
            {
                $this->error("Plugin bundle does not include a manifest.json file!");
                return FALSE;
            }

            //$array1 = JSON::load("src/manifest.json")->getDecoded();
            //$array2 = (new JSON($zipManifest))->getDecoded();

            //$diff = Diff::json(file_get_contents("src/manifest.json"), $zipManifest);
            $diff = JsonDiff::fromFiles()

            echo $diff;

            exit;
            $diff = Diff::array($array1, $array2);

            if (count($diff) > 0)
            {
                printf(<<<EOF
                    The file "manifest.json" differs between the following:

                    %s
                    - %s
                    - %s

                    Does the Plugin need to be (re-)bundled?

                    EOF,
                    FileSystem::path(PROJECT_DIR."/plugins/$this->name"),
                    FileSystem::path("src/manifest.json"),
                    FileSystem::path("$this->name.zip")
                );

                $style = new TableStyle();
                $style->setHeaderTitleFormat("<fg=blue;bg=black;options=bold> %s </>");

                $table = new Table($output);
                $table->setStyle($style);


                $table->setHeaderTitle("manifest.json");


                //$table = self::createDiffTable($table, ["KEY", "FILE", "ZIP"], $diff, $array1, $array2);

                $table->render();

                return FALSE;
            }


        }



        return TRUE;
    }



}
