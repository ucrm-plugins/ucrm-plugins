<?php

declare(strict_types=1);

namespace UCRM\Plugins\Commands\UPM;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UCRM\Plugins\Commands\PluginRequiredCommand;
use UCRM\Plugins\Support\FileSystem;
use UCRM\Plugins\Support\Json\JsonParser;

/**
 * BundleCommand
 *
 * @author Ryan Spaeth <rspaeth@spaethtech.com>
 * @copyright 2022 Spaeth Technologies Inc.
 *
 * @final
 */
class BundleCommand extends PluginRequiredCommand
{

    protected const DEFAULT_EXCLUSIONS = [
        "data/plugin.log",
        "ucrm.json",
        ".ucrm-*",
    ];


    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName("bundle")
            ->setDescription("Bundles the specified UCRM plugin")
            ->addOption("no-dev", null, InputOption::VALUE_NONE, "Skip installing packages listed in require-dev");
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //$this->beforeExecute($input, $output);
        $this->chdir("src");



        if (file_exists("composer.json")) {
            $composer = json_decode(file_get_contents("composer.json"), true);

            $exclusions = $composer["archive"]["exclude"] ?? [];
            $exclusions = array_unique(array_merge($exclusions, self::DEFAULT_EXCLUSIONS));

            $composer["archive"]["exclude"] = $exclusions;
        } else {
            $composer = [];
            $exclusions = self::DEFAULT_EXCLUSIONS;
            $composer["archive"]["exclude"] = $exclusions;
        }

        if (array_key_exists("require", $composer) || array_key_exists("require-dev", $composer)) {
            passthru("composer install --ansi" . ($input->getOption("no-dev") ? " --no-dev" : ""));
        }

        file_put_contents("composer-temp.json", json_encode($composer));

        print_r($composer);

        exit;


        exec("composer archive --ansi --file $this->plugin --format=zip --dir ../");


        if ($temp)
            unlink("composer.json");

        $this->chdir("..");

        echo $this->cwd . "\n";
        $uri = FileSystem::uri($this->cwd . "/$this->plugin.zip");
        $this->io->writeln($uri);
        //$this->io->writeln(getcwd()."/$this->plugin.zip");

        //$this->afterExecute($input, $output);

        return self::SUCCESS;
    }
}
