<?php

declare(strict_types=1);

namespace UCRM\Plugins\Commands\UPM;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UCRM\Plugins\Commands\PluginRequiredCommand;
use UCRM\Plugins\Support\FileSystem;

/**
 * BundleCommand
 *
 * @author Ryan Spaeth <rspaeth@spaethtech.com>
 * @copyright 2022 Spaeth Technologies Inc.
 *
 * @final
 */
final class BundleCommand extends PluginRequiredCommand
{
    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName("bundle")
            ->setDescription("Bundles the specified UCRM plugin");
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //$this->beforeExecute($input, $output);
        $this->chdir("src");

        if (file_exists("composer.json")) {
            exec("composer install --ansi");
            exec("composer archive --ansi --file $this->plugin");
        }

        $this->chdir("..");

        echo $this->cwd . "\n";
        $uri = FileSystem::uri($this->cwd . "/$this->plugin.zip");
        $this->io->writeln($uri);
        //$this->io->writeln(getcwd()."/$this->plugin.zip");

        //$this->afterExecute($input, $output);

        return self::SUCCESS;
    }
}
