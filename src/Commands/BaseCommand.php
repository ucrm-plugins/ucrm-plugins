<?php
declare(strict_types=1);

// cspell:ignore phpstorm

namespace UCRM\Plugins\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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

    /** @var callable[] $validators */
    protected array $validators = [];

    // The following hide the parent Command fields, as they are needed for our run() override.
    private $processTitle;
    private $code;
    private $ignoreValidationErrors;

    /**
     * @inheritDoc
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

        if ($die)
            die();
    }

    /**
     * Changes to the specified directory and assigns it as the current working directory.
     *
     * @param string $dir The directory
     * @return void
     */
    protected function chdir(string $dir)
    {
        chdir($this->cwd = realpath($dir));
    }

    /**
     * Called immediately before execute().
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     *
     * @see execute()
     */
    protected function beforeExecute(InputInterface $input, OutputInterface $output): void
    {
        $this->owd = getcwd();
        $this->chdir(PROJECT_DIR . "/plugins/");
    }

    /**
     * Called immediately after execute().
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param int $result The result from onExecute()
     *
     * @return void
     *
     * @see execute()
     */
    protected function afterExecute(InputInterface $input, OutputInterface $output, int&$result = self::SUCCESS): void
    {
        chdir($this->owd);
    }

    protected function validate(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->validators as $validator)
            $validator($input, $output);
    }

    /**
     * {@inheritDoc}
     * @suppress PHP0417
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        // add the application arguments and options
        $this->mergeApplicationDefinition();

        // bind the input against the command specific arguments/options
        try {
            $input->bind($this->getDefinition());
        }
        catch (ExceptionInterface $e) {
            if (!$this->ignoreValidationErrors) {
                throw $e;
            }
        }

        $this->initialize($input, $output);

        if (null !== $this->processTitle) {
            if (\function_exists('cli_set_process_title')) {
                if (!@cli_set_process_title($this->processTitle)) {
                    if ('Darwin' === \PHP_OS) {
                        $output->writeln('<comment>Running "cli_set_process_title" as an unprivileged user is not supported on MacOS.</comment>', OutputInterface::VERBOSITY_VERY_VERBOSE);
                    }
                    else {
                        cli_set_process_title($this->processTitle);
                    }
                }
            }

            elseif (\function_exists('setproctitle')) {

                setproctitle($this->processTitle);
            }
            elseif (OutputInterface::VERBOSITY_VERY_VERBOSE === $output->getVerbosity()) {
                $output->writeln('<comment>Install the proctitle PECL to be able to change the process title.</comment>');
            }
        }

        if ($input->isInteractive()) {
            $this->interact($input, $output);
        }

        // The command name argument is often omitted when a command is executed directly with its run() method.
        // It would fail the validation if we didn't make sure the command argument is present,
        // since it's required by the application.
        if ($input->hasArgument('command') && null === $input->getArgument('command')) {
            $input->setArgument('command', $this->getName());
        }

        $input->validate();

        if ($this->code) {
            $statusCode = ($this->code)($input, $output);
        }
        else {
            $this->validate($input, $output);

            $this->beforeExecute($input, $output);

            $statusCode = $this->execute($input, $output);

            $this->afterExecute($input, $output, $statusCode);

            if (!\is_int($statusCode)) {
                throw new \TypeError(sprintf('Return value of "%s::execute()" must be of the type int, "%s" returned.', static::class , get_debug_type($statusCode)));
            }
        }

        return is_numeric($statusCode) ? (int)$statusCode : 0;
    }


    private const DEFAULT_SUPPORTED_IDES = ["phpstorm", "vscode"];

    protected function withIdeOptions(array $supportedIdes = self::DEFAULT_SUPPORTED_IDES): void
    {
        $ides = implode(", ", $supportedIdes);
        $description = "Any supported IDE ($ides)";
        $this->addOption("ide", "i", InputOption::VALUE_REQUIRED, $description, "phpstorm");
    }

}
