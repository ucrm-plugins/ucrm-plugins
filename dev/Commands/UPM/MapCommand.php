<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\Plugins\Commands\UPM;

use SimpleXMLElement;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UCRM\Plugins\Commands\BaseCommand;
use UCRM\Plugins\Support\FileSystem;
use UCRM\Plugins\Support\Git;
use UCRM\Plugins\Support\Templater;

/**
 * MapCommand
 *
 * @author Ryan Spaeth <rspaeth@spaethtech.com>
 * @copyright 2022 Spaeth Technologies Inc.
 *
 * @final
 */
class MapCommand extends BaseCommand
{
    protected const NAMING_PATTERN = "/^[a-z][a-z\d-]*$/";
    
    /**
     * @inheritDoc
     */
    protected function configure() : void
    {
        $this
            ->setName("map")
            ->setDescription("Creates server path mappings for Plugin debugging")
            ->addArgument("name", InputArgument::REQUIRED, "The name of the plugin");
            //->addArgument("template", InputArgument::REQUIRED, "The name of a template from templates/ or a git repo")
            //->addOption("submodule", "s", InputOption::VALUE_NONE, "When used with --git, adds the Plugin as a submodule")
            //->addOption("force", "f", InputOption::VALUE_NONE, "Forces replacement of an existing Plugin");
        
    }
   
    /**
     * @inheritDoc
     *
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $owd = getcwd();
        chdir(FileSystem::path(PROJECT_DIR."/plugins/"));
        
        $name = $input->getArgument("name");
        
        if (!preg_match(self::NAMING_PATTERN, $name))
            $this->error("The Plugin's name is invalid, please adhere to ".self::NAMING_PATTERN, TRUE);
        
        if (!file_exists($existing = FileSystem::path(PROJECT_DIR."/plugins/$name")))
        {
            $this->error("A Plugin with that name could not be found at: $existing", TRUE);
        }
        
        if (file_exists($box = FileSystem::path(PROJECT_DIR."/box/vagrant/env/box.conf")))
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
        
        
        
        
        if (!($server = $project->xpath("component[@name='PhpProjectServersManager']/servers/server[@host='$host' or @host='$ip']")))
        {
            // Need to add a new server!
            $server = $project->xpath("component[@name='PhpProjectServersManager']/servers")[0]->addChild("server");
            $server->addAttribute("host", "$host");
            $server->addAttribute("id", self::guid());
            $server->addAttribute("name", "$host");
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
        
        
        
        self::addPathMapping($server, "\$PROJECT_DIR\$/plugins/$name/src", "/data/ucrm/data/plugins/$name");
        self::addPathMapping($server, "\$PROJECT_DIR\$/plugins/$name/www", "/usr/src/ucrm/web/_plugins/$name");
    
    
    
        
        
        //print_r($server);
        //print_r($project->xpath("component[@name='PhpProjectServersManager']/servers")[0]->asXml());
        //print_r($dom->);
    
    
        
        
//        {
//            // Update Server
//            foreach($servers as $server)
//            {
//                foreach($server->xpath("path_mappings/mapping") as $mapping)
//                {
//                    print_r($mapping["local-root"]);
//
//                }
//
//            }
//
//
//        }
//        else
//        {
//            // Create Server
//
//        }
//
            
            
            
            
            
            
            
            
            /*
            <component name="PhpProjectServersManager">
                <servers>
                  <server host="uisp-dev" id="c785963a-b7ef-4eeb-82b6-2847458309ca" name="vagrant" use_path_mappings="true">
                    <path_mappings>
                      <mapping local-root="$PROJECT_DIR$/plugins/testing/src" remote-root="/data/ucrm/data/plugins/testing" />
                      <mapping local-root="$PROJECT_DIR$/plugins/testing/www" remote-root="/usr/src/ucrm/web/_plugins/testing" />
                    </path_mappings>
                  </server>
                </servers>
            </component>
            */
    
    
        // TODO: Create any missing www/ files, as this command can be run on any of the plugins???
    
        if (!file_exists($www = FileSystem::path(PROJECT_DIR."/plugins/$name/www")))
            mkdir($www);
            
        if (!file_exists($public = FileSystem::path("$www/public.php")))
        {
            file_put_contents($public, <<<EOF
            <?php /** @noinspection PhpIncludeInspection */
            chdir(dirname('/usr/src/ucrm/app/data/plugins/$name/public.php'));
            require_once '/usr/src/ucrm/app/data/plugins/$name/public.php';
            
            EOF
            );
        }
    
    
        
        
        
    
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($project->asXML());
        file_put_contents($php, $dom->saveXML());
    
        
        chdir($owd);
        return 0;
    }
    
    private static function guid(): string
    {
        if (function_exists('com_create_guid') === true)
            return trim(com_create_guid(), '{}');
        
        $data = random_bytes(16); // openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
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

