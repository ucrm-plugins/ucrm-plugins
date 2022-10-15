<?php /** @noinspection PhpUnused, PhpUnusedParameterInspection */
declare(strict_types=1);

namespace UCRM\Plugins\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * PluginCommands
 *
 * @author Ryan Spaeth <rspaeth@spaethtech.com>
 * @copyright 2022 Spaeth Technologies Inc.
 *
 */
trait PluginCommands
{

    protected string $plugin;

    /**
     * @return void
     */
    protected function withPluginArgument()
    {
        $this->addArgument("plugin", InputArgument::REQUIRED, "The name of the plugin");

        $this->validators[] = [ $this, "validatePluginArgument" ];

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function validatePluginArgument(InputInterface $input, OutputInterface $output)
    {
        $this->plugin = $input->getArgument("plugin");

        $namingPattern = "/^[a-z][_a-z\d-]*$/";

        if (!preg_match($namingPattern, $this->plugin))
            //throw new PluginInvalidNameException("The Plugin's name should adhere to ".self::NAMING_PATTERN);
            $this->error("The Plugin's name should adhere to " . $namingPattern, TRUE);
    }

}
