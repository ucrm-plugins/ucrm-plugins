<?php
declare(strict_types=1);

namespace UCRM\Plugins\Robo\Tasks\Plugin;

use Robo\Contract\TaskInterface;
use Robo\Result;
use Robo\Task\BaseTask;
use UCRM\Plugins\Support\FileSystem;
use UCRM\Plugins\Support\Json\JsonParser;

class Bundle extends BaseTask implements TaskInterface
{
    protected string $folder;
    protected string $plugin;
    protected string $source;

    /**
     * Summary of __construct
     *
     * @param string $plugin
     * @author Ryan
     */
    public function __construct(string $plugin)
    {
        $this->plugin = $plugin;
        $this->folder = FileSystem::path(PROJECT_DIR . "/plugins/$this->plugin");
        $this->source = FileSystem::path("$this->folder/src");
    }

    public function version(?string $version = null): self
    {
        if (!$version) {
            //            $manifest = ($parser = new JsonParser($path = FileSystem::path("$this->source/manifest.json")))->decoded();
//            $version = $manifest->information->version;
//            print_r("$version\n");
//            //$version = $manifest->information->version = "1.0.2";
//            //print_r("$version\n");
//            $parser->save($path);

            $manifest = ($parser = new JsonParser($path = FileSystem::path("$this->source/manifest.json")))
                ->generate("UCRM\\Plugins", "Manifest", FileSystem::path(PROJECT_DIR . "/src"));

            exit;
            $version = $manifest->information->version;


        }



        return $this;
    }

    public function run(): Result
    {
        //$src = FileSystem::path(PROJECT_DIR."/plugins/$this->plugin/src");



        exit;

        if (!file_exists($this->source) || !is_dir($this->source))
            return Result::error($this, "The specified Plugin does not seem to have a src folder!");

        chdir($this->source);

        if (file_exists("composer.json")) {
            passthru("composer install --ansi");
            passthru("composer archive --ansi --file $this->plugin");
        }

        return Result::success($this);
    }
}
