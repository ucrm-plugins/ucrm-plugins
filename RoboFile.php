<?php

declare(strict_types=1);
require_once __DIR__ . "/vendor/autoload.php";

use Robo\Symfony\ConsoleIO;
use Robo\Tasks;
use Symfony\Component\Console\Helper\ProgressBar;
use UCRM\Plugins\Support\FileSystem;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see https://robo.li/
 */
class RoboFile extends Tasks
{
    use \UCRM\Plugins\Robo\Tasks;


    public function hello(ConsoleIO $io, $world)
    {
        $io->say("Hello, $world");



    }

    /**
     * @command bundle
     *
     * @param string $plugin The name of the Plugin
     *
     * @return void
     */
    public function bundle(ConsoleIO $io, string $plugin)
    {
        $this->taskPluginBundle($plugin)->version()->run();



    }



    /**
     *
     *
     * @param string $plugin The name of the plugin
     * @return void
     */
    public function sftpMap(ConsoleIO $io, string $plugin, $options = ["map|m" => false])
    {
        $hostname = $this->config("HOSTNAME") ?: "uisp";

        if ($this->plugin_exists($plugin, $path))
        {
            echo $path;
        }
    }

    private ProgressBar $bar;

    public function cmder(ConsoleIO $io, $subcommand, $options = ["force|f" => false, "mini|m" => false])
    {
        $path = PROJECT_DIR."/dev/cmder";
        $mini = "https://github.com/cmderdev/cmder/releases/download/v1.3.19/cmder_mini.zip";
        $full = "https://github.com/cmderdev/cmder/releases/download/v1.3.19/cmder.zip";

        $url = $options["mini"] ? $mini : $full;

        echo "Downloading Cmder...\n";
        echo "$url\n";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, [ $this, "progress" ]);
        curl_setopt($ch, CURLOPT_NOPROGRESS, false); // needed to make progress function work
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $this->bar = new ProgressBar($io->output(), 100);
        $this->bar->start();

        $zip = curl_exec($ch);

        $this->bar->finish();

        echo "\n";

        if(!$zip)
        {
            echo "Error: " . curl_errno($ch) . "\n";
            return;
        }

        curl_close($ch);

        $file = "$path". ($options["mini"] ? "-mini" : "-full") . ".zip";

        if(file_exists($file))
            unlink($file);

        file_put_contents($file, $zip);

        $zip = new ZipArchive();
        $res = $zip->open($file);

        if ($res === true)
        {
            if(file_exists($path) && is_dir($path))
                FileSystem::rmdir($path);

            $zip->extractTo($path);
            $zip->close();

        } else {
            echo 'doh!';
        }




    }

    public function progress($resource,$download_size, $downloaded, $upload_size, $uploaded)
    {
        if($download_size > 0)
        {
            //echo ($downloaded / $download_size * 100)."\n";
            $this->bar->setProgress((int)($downloaded / $download_size * 100));
        }

        sleep(1);
    }

    private function plugin_exists(string $name, string &$path = null)
    {
        return $path = realpath(__DIR__ . "/plugins/$name");
    }

    /**
     *
     *
     * @param string $settings_file
     * @return array|string
     */
    private function settings(string $settings_file = __DIR__ . "/.vscode/settings.json")
    {
        $settings = [];

        if (file_exists($settings_file))
        {
            $settings = json_decode(file_get_contents($settings_file), true);
        }

        //if ($key && array_key_exists($key, $settings))
        //    return $settings[$key];

        return $settings;
    }

    /**
     *
     *
     * @param string $key
     * @param string $config_path
     * @return array|string
     */
    private function config(string $key = "", string $config_path = __DIR__ . "/vagrant/env")
    {
        $config = [];

        foreach (scandir($config_path) as $file)
        {
            if (pathinfo($file, PATHINFO_EXTENSION) === "conf")
            {
                $config = array_merge($config, parse_ini_file("$config_path/$file"));
            }
        }

        if ($key && array_key_exists($key, $config))
        {
            return $config[$key];
        }

        return $config;
    }



}
