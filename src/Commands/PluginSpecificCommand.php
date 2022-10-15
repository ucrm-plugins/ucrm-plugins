<?php

declare(strict_types=1);

namespace UCRM\Plugins\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use UCRM\Plugins\Commands\Exceptions\PluginInvalidNameException;
use UCRM\Plugins\Support\FileSystem;

/**
 * PluginSpecificCommand
 *
 * @author Ryan Spaeth <rspaeth@spaethtech.com>
 * @copyright 2022 Spaeth Technologies Inc.
 *
 */
abstract class PluginSpecificCommand extends BaseCommand
{
    protected const NAMING_PATTERN = "/^[a-z][_a-z\d-]*$/";

    protected string $plugin;

    protected function configure(): void
    {
        $this->addArgument("plugin", InputArgument::REQUIRED, "The name of the plugin");
    }

    /**
     * @inheritDoc
     *
     */
    protected function beforeExecute(InputInterface $input, OutputInterface $output): void
    {
        parent::beforeExecute($input, $output);

        $this->plugin = $input->getArgument("plugin");

        if (!preg_match(self::NAMING_PATTERN, $this->plugin))
            //throw new PluginInvalidNameException("The Plugin's name should adhere to ".self::NAMING_PATTERN);
            $this->error("The Plugin's name should adhere to " . self::NAMING_PATTERN, TRUE);
    }
}
