<?php /** @noinspection PhpUnused, PhpUnusedParameterInspection */
declare(strict_types=1);

namespace UCRM\Plugins\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use UCRM\Plugins\Support\FileSystem;

/**
 * BaseCommand
 *
 * @author Ryan Spaeth <rspaeth@spaethtech.com>
 * @copyright 2022 Spaeth Technologies Inc.
 *
 */
abstract class BaseCommand extends Command
{
    protected SymfonyStyle $io;
    protected string $owd;
    protected string $cwd;

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }


    /**
     * Fixes some formatting issues with the built-in error() function.
     *
     * @param string $message   The message to display
     * @param bool $die         Optionally, calls die() after displaying the message
     *
     * @return void
     */
    protected function error(string $message, bool $die = FALSE): void
    {
        $this->io->newLine();
        $this->io->writeln("<error> [ERROR] $message</>");
        $this->io->newLine();

        if($die)
            die();
    }

    protected function getVendorBin(string $command = ""): string
    {
        return FileSystem::path(PROJECT_DIR."/vendor/bin/$command");
    }

    protected function chdir(string $dir)
    {
        chdir($this->cwd = FileSystem::path($dir));
    }

    /**
     * Called immediately before onExecute().
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     *
     * @see onExecute()
     */
    protected function beforeExecute(InputInterface $input, OutputInterface $output): void
    {
        $this->owd = getcwd();
        $this->chdir(PROJECT_DIR."/plugins/");
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * onExecute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @return int 0 if everything went fine, or an exit code
     *
     * @throws LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function onExecute(InputInterface $input, OutputInterface $output): int
    {
        throw new LogicException('You must override the onExecute() method in the concrete command class.');
    }

    /**
     * Called immediately after onExecute().
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param int $result The result from onExecute()
     *
     * @return void
     *
     * @see onExecute()
     */
    protected function afterExecute(InputInterface $input, OutputInterface $output, int $result = self::SUCCESS): void
    {
        chdir($this->owd);
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $this->beforeExecute($input, $output);

        $result = $this->onExecute($input, $output);

        $this->afterExecute($input, $output, $result);

        return $result;
    }


}
