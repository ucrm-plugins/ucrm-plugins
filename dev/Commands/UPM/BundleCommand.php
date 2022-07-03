<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\Plugins\Commands\UPM;

//use Composer\CaBundle\CaBundle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UCRM\Plugins\Commands\BaseCommand;

//use GuzzleHttp\Client;
//use GuzzleHttp\Exception\GuzzleException;
//use GuzzleHttp\RequestOptions;

//use Symfony\Component\Console\Input\InputArgument;

/**
 * BundleCommand
 *
 * @author Ryan Spaeth <rspaeth@spaethtech.com>
 * @copyright 2022 Spaeth Technologies Inc.
 *
 * @final
 */
final class BundleCommand extends BaseCommand
{
    

    /**
     * @inheritDoc
     */
    protected function configure() : void
    {
        $this
            ->setName("bundle")
            ->setDescription("Bundles the specified UCRM plugin")
            ->addArgument("dir", InputArgument::OPTIONAL, "The path in which the plugin resides", getcwd());

   }

   protected function initialize(InputInterface $input, OutputInterface $output)
   {

   }

    /**
     * @inheritDoc
     *
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        echo $input->getArgument("dir") . "\n";

        echo plugin_exists("ucrm-client-signup") ? "T" : "F";
        
        return self::SUCCESS;
    }

}
