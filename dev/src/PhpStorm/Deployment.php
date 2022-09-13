<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\Plugins\PhpStorm;

use SimpleXMLElement;

class Deployment extends XmlConfigManager
{
    public static function load(string $file): self
    {
        return new self(self::getPath($file));
    }

    /**
     * @param string $name
     *
     * @return SimpleXMLElement|FALSE
     */
    //public function getServerNamedOrFirst(string $name = ""): ?SimpleXmlElement
    //{
    //    return $this->first($this->xpathUntilFound("/project/component/serverData/paths", "[@name='$name']", "[1]"));
    //}


    /**
     * Gets the default deployment server name.
     *
     * @return string|null
     */
    public function getDefaultServerName(): ?string
    {
        $component = $this->xml->xpath("/project/component");

        if ($component === FALSE || $component === NULL)
            return NULL;

        return $component[0]->attributes()["serverName"];
    }

    /**
     * Adds a path mapping to the
     *
     * @param string $plugin
     *
     * @return void
     */
    public function addPathMapping(string $plugin)
    {
        $component = $this->xpathFirst("/project/component[@name='PublishConfigData']");

        print_r($component);

        //if ($component === NULL || !array_key_exists("serverName", $component->attributes()))
        {

        }



    }





}
