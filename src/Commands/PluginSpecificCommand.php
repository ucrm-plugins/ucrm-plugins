<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\Plugins\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
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

    protected string $name;


    protected function beforeExecute(InputInterface $input, OutputInterface $output): void
    {
        parent::beforeExecute($input, $output);

        $this->name = $input->getArgument("name");

        if (!preg_match(self::NAMING_PATTERN, $this->name))
            $this->error("The Plugin's name is invalid, please adhere to ".self::NAMING_PATTERN, TRUE);

        if (file_exists($this->name) && is_dir($this->name))
            $this->chdir("$this->cwd/$this->name");



    }


}
