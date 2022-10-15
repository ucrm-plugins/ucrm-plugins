<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\Plugins\Support\Json;

use DateTime;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use UCRM\Plugins\Support\FileSystem;

/**
 * JsonObject
 *
 * @author Ryan Spaeth <rspaeth@spaethtech.com>
 * @copyright 2022 Spaeth Technologies Inc.
 *
 */
class JsonObject
{
    private PhpNamespace $namespace;
    private ClassType $class;

    /**
     * @param array|null $json
     */
    public function __construct(?array $json = null, ?string $namespace = null, ?string $class = null, ?string $dir = null, callable $typeCallback = null)
    {
        if ($json !== null)
            $this->set($json, $namespace, $class, $dir, $typeCallback);
    }

    private function set(array $data, ?string $namespace = null, ?string $class = null, ?string $dir = null, callable $typeCallback = null)
    {
        if ($namespace !== null)
        {
            $this->namespace = new PhpNamespace($namespace);
        }

        if ($class !== null)
        {
            $timestamp = (new DateTime())->format("m/d/Y @ H:i:s");

            $this->class = ($namespace !== null) ? $this->namespace->addClass($class) : new ClassType($class);

            $this->class
                ->addComment("Class $class")
                ->addComment("")
                ->addComment("@author Ryan Spaeth <rspaeth@spaethtech.com>")
                ->addComment("@copyright 2022 - Spaeth Technologies Inc.")
                ->addComment("")
                ->addComment("@generated $timestamp by JsonParser::generate()");
        }

        $objects = [];

        foreach ($data as $key => $value)
        {
            $property = $this->class
                ->addProperty($key)
                ->setPublic();

            if (is_array($value))
            {
                $sub = new JsonObject($value, "$namespace\\$class", ucfirst($key), FileSystem::path("$dir/$class"), $typeCallback);
                //$sub->set($value);
                $value = $sub;

                    $property->setType("$namespace\\$class\\".ucfirst($key));

                $objects[] = "$namespace\\$class\\".ucfirst($key);
            }
            else
            {
                $resolved = false;

                if ($typeCallback !== null)
                {
                    $resolved = $typeCallback($property, "$namespace\\$class", $key, $value);
                }

                if (!$resolved)
                {
                    if ($value !== null)
                        $property->setType(gettype($value));
                }

            }

            $this->{$key} = $value;


        }

        foreach($objects as $object)
        {
            if ($namespace != null)
                $this->namespace->addUse($object);
        }


        if ($dir !== null)
        {
            $block = ($namespace !== NULL) ? $this->namespace : $this->class;

            $code = <<<EOF
            <?php /** @noinspection PhpUnused, SpellCheckingInspection */
            declare(strict_types=1);

            $block

            EOF;

            print_r($code);

            if (!file_exists($path = FileSystem::path($dir)))
            {
               mkdir($path, 0775, true);
            }

            if (file_exists($path = FileSystem::path("$dir/$class.php")))
            {
                unlink($path);
            }

            file_put_contents($path, $code);


        }

    }

}
