<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\Plugins\PhpStorm;

use DOMDocument;
use ErrorException;
use Exception;
use SimpleXMLElement;
use UCRM\Plugins\Support\FileSystem;

abstract class XmlConfigManager
{
    protected DOMDocument $dom;
    protected SimpleXMLElement $xml;


    public function __construct(string $file)
    {
        $this->dom = new DOMDocument("1.0");
        $this->dom->preserveWhiteSpace = FALSE;
        $this->dom->formatOutput = TRUE;

        $this->xml = simplexml_load_file($file);

        // TODO: Validation
    }

    protected function asXml()
    {
        return $this->dom->loadXML($this->xml)->saveXML();
    }

    protected static function getPath(string $file): string
    {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        return FileSystem::path(PROJECT_DIR."/.idea/$file".($ext ? "" : ".xml"));
    }

    /**
     * @throws Exception
     */
    public static function guid(): string
    {
        //if (function_exists('com_create_guid') === true)
        //    return trim(com_create_guid(), '{}');

        $data = random_bytes(16); // openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * @param string $file The configuration file to use.
     *
     * @return XmlConfigManager Returns an appropriate ConfigManager
     */
    public abstract static function load(string $file): XmlConfigManager;


    protected function enableXpathExceptions()
    {
        set_error_handler(
            /**
             * @param $code
             * @param $message
             * @param $file
             * @param $line
             *
             * @return false
             * @throws ErrorException
             */
            function($code, $message, $file, $line)
            {
                // IF the error was suppressed with the @-operator, ignore!
                if (0 === error_reporting()) {
                    return FALSE;
                }

                throw new ErrorException($message, 0, $code, $file, $line);
            }
        );
    }

    protected function disableXpathExceptions()
    {
        restore_error_handler();
    }



    /**
     * @param string $base
     * @param string ...$query
     *
     * @return SimpleXMLElement[]|NULL|FALSE
     */
    protected function xpathUntilFound(string $base = "", ...$query) : ?array
    {
        for ($i = 0; $i < count($query); $i++)
        {
            $xpath = $this->xml->xpath("$base{$query[$i]}");

            if ($xpath === NULL || (is_array($xpath) && count($xpath) === 0) || $xpath === FALSE)
                continue;

            return $xpath;
        }

        return NULL;
    }



    /**
     * @param SimpleXMLElement[]|NULL|FALSE $xpath
     *
     * @return SimpleXMLElement|NULL|FALSE
     */
    protected function first(?array $xpath): ?SimpleXMLElement
    {
        print_r($xpath);
        exit;
        return ($xpath === FALSE) ? FALSE : ((is_array($xpath) && count($xpath) > 0) ? $xpath[0] : NULL);
    }

    /**
     * @param string $expression
     *
     * @return SimpleXMLElement[]|NULL
     */
    protected function xpath(string $expression): ?array
    {
        try
        {
            $this->enableXpathExceptions();
            $xpath = $this->xml->xpath($expression);
        }
        catch(ErrorException $e)
        {
            $message = <<<ERROR
                {$e->getMessage()}
                > File: {$e->getFile()}
                > Line: {$e->getLine()}
                ERROR;

            die("\n$message\n\n");
        }
        finally
        {
            $this->disableXpathExceptions();
        }

        if ($xpath === FALSE)
            die("XPath failed to execute!");

        return $xpath;
    }

    /**
     * @param string $expression
     *
     * @return SimpleXMLElement|NULL
     */
    protected function xpathFirst(string $expression): ?SimpleXMLElement
    {
        $xpath = $this->xpath($expression);
        return (is_array($xpath) && count($xpath) > 0) ? $xpath[0] : NULL;
    }



}
