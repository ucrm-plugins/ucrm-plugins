<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\Plugins\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use UCRM\Plugins\Commands\Exceptions\PluginInvalidNameException;
use UCRM\Plugins\Commands\Exceptions\PluginNotFoundException;
use UCRM\Plugins\Support\Diff;
use UCRM\Plugins\Support\FileSystem;
use UCRM\Plugins\Support\JSON;

/**
 * PluginRequiredCommand
 *
 * @author Ryan Spaeth <rspaeth@spaethtech.com>
 * @copyright 2022 Spaeth Technologies Inc.
 *
 */
abstract class PluginRequiredCommand extends PluginSpecificCommand
{

    /**
     * @inheritDoc
     */
    protected function beforeExecute(InputInterface $input, OutputInterface $output): void
    {
        parent::beforeExecute($input, $output);

        if (!file_exists($this->name) || !is_dir($this->name))
            //throw new Exceptions\PluginNotFoundException("The plugin '$this->name' could not be found!");
            $this->error("The plugin '$this->name' could not be found!", TRUE);

        $this->chdir("$this->cwd/$this->name");

    }



    public static function createJsonDiffTable(OutputInterface $output, string $json1, string $json2, string $title, array $headers): Table
    {
        $style = new TableStyle();
        $style->setHeaderTitleFormat("<fg=blue;bg=black;options=bold> %s </>");

        $table = new Table($output);
        $table->setStyle($style);

        $table->setHeaderTitle($title);

        // Check header count!

        $table->setHeaders($headers);

        //$diff = Diff::json(JSON::decode($json1), JSON::decode($json2));
        //$diff = Diff::json($json1, $json2);

        //$table = self::_createDiffTable($table, $diff, $json1, $json2);

    }

    /**
     * @param Table $table
     * @param array $diff
     * @param array $array1
     * @param array $array2
     * @param int $depth
     * @param string $prefix
     *
     * @return void
     */
    public static function _createDiffTable(Table &$table, array $diff, array $array1, array $array2,
        int $depth = 0, string $prefix = ""): Table
    {
        ++$depth;
        if ($depth > 50)
            return $table;

        foreach ($diff as $key => $value)
        {
            if (array_key_exists($key, $array1) && array_key_exists($key, $array2) && is_array($array1[$key]))
            {
                $table = self::createDiffTable(
                    $table,
                    $headers,
                    Diff::array($array1[$key], $array2[$key]),
                    $array1[$key],
                    $array2[$key],
                    $depth,
                    $key . '.'
                );
            }
            else
            {
                $table->addRow([
                    "$prefix$key",
                    array_key_exists($key, $array1) ? (is_array($array1[$key]) ? 'Array' : $array1[$key]) : '(none)',
                    array_key_exists($key, $array2) ? (is_array($array2[$key]) ? 'Array' : $array2[$key]) : '(none)'
                ]);
//                printf(
//                    "    %s%s%s        file: %s%s        zip : %s%s",
//                    $keyPrefix,
//                    $key,
//                    PHP_EOL,
//                    array_key_exists($key, $array1) ? (is_array($array1[$key]) ? 'Array' : $array1[$key]) : '(none)',
//                    PHP_EOL,
//                    array_key_exists($key, $array2) ? (is_array($array2[$key]) ? 'Array' : $array2[$key]) : '(none)',
//                    PHP_EOL
//                );
            }
        }

        return $table;
    }


}
