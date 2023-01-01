<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\Plugins\Commands\UPM;

use Exception;
use SimpleXMLElement;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UCRM\Plugins\Commands\BaseCommand;
use UCRM\Plugins\Commands\IdeCommands;
use UCRM\Plugins\Commands\PluginCommands;
use UCRM\Plugins\Commands\PluginRequiredCommand;
use UCRM\Plugins\PhpStorm\XmlConfigManager;
use UCRM\Plugins\Support\FileSystem;

/**
 * MapDebugCommand
 *
 * @author Ryan Spaeth <rspaeth@spaethtech.com>
 * @copyright 2022 Spaeth Technologies Inc.
 *
 * @final
 */
class MapDebugCommand extends BaseCommand
{
    use IdeCommands;
    use PluginCommands;

    /**
     * @inheritDoc
     */
    protected function configure() : void
    {
        //parent::configure();

        $this
            ->setName("map:debug")
            ->setDescription("Creates server path mappings for Plugin debugging");
            //->addOption("ide", "i", InputOption::VALUE_REQUIRED, "Any supported IDE (i.e. phpstorm, vscode)", "phpstorm");

        $this->withIdeOptions(["phpstorm"]);
        $this->withPluginArgument();
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        //$this->validatePluginArgument($input, $output);

        // Get the hostname of the VM from the config file.
        if (file_exists($box = FileSystem::path(PROJECT_DIR."/vagrant/env/box.conf")))
        {
            $ini = parse_ini_file($box);
            $host = array_key_exists("HOSTNAME", $ini) ? $ini["HOSTNAME"] : "localhost";
            $ip = array_key_exists("IP", $ini) ? $ini["IP"] : "127.0.0.1";
        }
        else
        {
            $host = "localhost";
            $ip = "127.0.0.1";
        }

        if (!file_exists($php = FileSystem::path(PROJECT_DIR."/.idea/php.xml")))
            $this->error("File .idea/php.xml could not be found!", TRUE);

        $project = simplexml_load_file($php);


        $ide = $input->getOption("ide");




        if (!($server = $project->xpath("component[@name='PhpProjectServersManager']/servers/server[@host='$host' or @host='$ip']")))
        {
            // Need to add a new server!
            $server = $project->xpath("component[@name='PhpProjectServersManager']/servers")[0]->addChild("server");
            $server->addAttribute("host", "$host"); // uisp.dev (set by PHP_IDE_CONFIG "serverName=uisp")
            $server->addAttribute("id", XmlConfigManager::guid());
            $server->addAttribute("name", "$host"); // uisp
            $server->addAttribute("use_path_mappings", "true");
        }
        else
        {
            $server = $server[0];

            // Force to HOSTNAME?
            //if ($server["host"] == $ip)
            //    $server["host"] = $host;

        }

        if (!$server->xpath("path_mappings"))
            $server->addChild("path_mappings");

        $hostPluginsDir = "\$PROJECT_DIR\$/plugins";
        $ucrmPluginsSrc = "/data/ucrm/data/plugins";
        $ucrmPluginsWeb = "/usr/src/ucrm/web/_plugins";

        self::addPathMapping($server, "$hostPluginsDir/$this->plugin/src", "$ucrmPluginsSrc/$this->plugin");
        self::addPathMapping($server, "$hostPluginsDir/$this->plugin/www", "$ucrmPluginsWeb/$this->plugin");

        // Create any missing www/ files, as this command can be run on any of the plugins???

        if (!file_exists($www = FileSystem::path(PROJECT_DIR."/plugins/$this->plugin/www")))
            mkdir($www);

        if (!file_exists($public = FileSystem::path("$www/public.php")))
        {
            file_put_contents($public, <<<EOF
            <?php /** @noinspection PhpIncludeInspection */
            chdir(dirname('/usr/src/ucrm/app/data/plugins/$this->plugin/public.php'));
            require_once '/usr/src/ucrm/app/data/plugins/$this->plugin/public.php';

            EOF
            );
        }

        // Clean up the formatting!
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($project->asXML());
        file_put_contents($php, $dom->saveXML());


        //chdir($owd);
        return self::SUCCESS;
    }



    private static function addPathMapping(SimpleXMLElement &$server, string $local, string $remote): void
    {
        //$local = "\$PROJECT_DIR\$/plugins/$name/www";
        //$remote = "/usr/src/ucrm/web/_plugins/$name";

        if (!($mappings = $server->xpath("path_mappings/mapping[@local-root='$local']")))
        {
            $mapping = $server->xpath("path_mappings")[0]->addChild("mapping");
            $mapping->addAttribute("local-root", $local);
            $mapping->addAttribute("remote-root", $remote);
        }
        else
        {
            $mapping = $mappings[0];

            // Fix possible bad remote mapping!
            if($mapping["remote-root"] != $remote)
                $mapping["remote-root"] = $remote;
        }
    }


}
