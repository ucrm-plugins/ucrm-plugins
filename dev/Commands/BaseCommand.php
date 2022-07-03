<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\Plugins\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
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


}
