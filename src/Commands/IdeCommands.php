<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\Plugins\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * IdeCommands
 *
 * @author Ryan Spaeth <rspaeth@spaethtech.com>
 * @copyright 2022 Spaeth Technologies Inc.
 *
 */
trait IdeCommands
{
    protected string $ide;

    /**
     * @param array $supported An array of supported IDEs
     *
     * @return void
     */
    protected function withIdeOptions(array $supported = [ "phpstorm", "vscode" ]): void
    {
        /** @var BaseCommand $this */

        $description = "Any supported IDE (" . implode(", ", $supported) . ")";
        $this->addOption("ide", "i", InputOption::VALUE_REQUIRED, $description, "phpstorm");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function validateIdeOption(InputInterface $input, OutputInterface $output)
    {
        $this->ide = $input->getArgument("ide");

    }

}
