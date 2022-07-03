<?php /** @noinspection PhpUnused */
declare(strict_types=1);

const SETUP_FILE = "composer-setup.php";
const BINARY_DIR = "/usr/local/bin";

copy("https://getcomposer.org/installer", SETUP_FILE);
$hash = "55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae";
if (hash_file("sha384", SETUP_FILE) === $hash)
{
    echo "Installer verified";
}
else
{
    echo "Installer corrupt";
    unlink(SETUP_FILE);
}

exec("php ".SETUP_FILE);
unlink(SETUP_FILE);
exec("mv composer.phar ".BINARY_DIR."/composer");
