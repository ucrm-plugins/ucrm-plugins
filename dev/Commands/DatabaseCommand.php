<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\Plugins\Commands;

use DateTime;
use Exception;
use PDO;
use PDOStatement;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * BaseCommand
 *
 * @author Ryan Spaeth <rspaeth@spaethtech.com>
 * @copyright 2022 Spaeth Technologies Inc.
 *
 */
abstract class DatabaseCommand extends BaseCommand
{
    
    
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        
        $this->dbConnect();
        
        if ($this->pdo === NULL)
            $this->error("Could not establish a connection to the database", TRUE);
    }
    
    protected ?PDO $pdo = NULL;
    
    protected function dbConnect(bool $reconnect = FALSE): PDO
    {
        if ($this->pdo === NULL || $reconnect)
        {
            $nms = parse_ini_file(PROJECT_PATH."/ide/env/unms.conf");
            $box = parse_ini_file(PROJECT_PATH."/ide/env/box.conf");
            
            $dsn = "pgsql:host={$box["IP"]};port=5432;dbname={$nms["UCRM_POSTGRES_DB"]}";
            
            $this->pdo = new PDO($dsn, $nms["UCRM_POSTGRES_USER"], $nms["UCRM_POSTGRES_PASSWORD"]);
            //$this->pdo->exec("SET search_path TO ".($schema ?? $nms["UCRM_POSTGRES_SCHEMA"]));
        }
        
        return $this->pdo;
    }

    
    protected function dbSanitize(array $params): array
    {
        return array_map(
            function($value)
            {
                if ($value === "")
                    return NULL;
            
                if (is_bool($value))
                    return $value ? "true" : "false";
            
                return $value;
            },
            $params
        );
    }
    
    protected function dbExecute(PDOStatement $statement, array $params = NULL): bool
    {
        $return = $statement->execute($params);
    
        if (($result = $statement->errorCode()) !== PDO::ERR_NONE)
        {
            switch($result)
            {
                case 23505: // Duplicate
                    $this->error("The Plugin is already installed");
                    die();
                default:
                    $this->error("The database INSERT command failed");
                    print_r($statement->errorInfo());
                    die();
            }
        }
        
        return $return;
    }
    
    
    protected function dbPluginExistsByName(string $name): bool
    {
        $pdo = $this->dbConnect();
    
        $smt = $pdo->prepare("SELECT * FROM unms.ucrm.plugin WHERE name = :name");
        $this->dbExecute($smt, [ "name" => $name ]);
    
        return $smt->rowCount() === 1;
    }
    
    protected function dbPluginExistsById(int $id): bool
    {
        $pdo = $this->dbConnect();
        
        $smt = $pdo->prepare("SELECT * FROM unms.ucrm.plugin WHERE id = :id");
        $this->dbExecute($smt, [ "id" => $id ]);
        
        return $smt->rowCount() === 1;
    }
    
    protected function dbAppKeyExistsByPluginName(string $name): bool
    {
        $pdo = $this->dbConnect();
        
        $smt = $pdo->prepare("SELECT * FROM unms.ucrm.app_key WHERE name = :name");
        $this->dbExecute($smt, [ "name" => "plugin_$name" ]);
        
        return $smt->rowCount() === 1;
    }
    
    protected function dbAppKeyExistsByPluginId(int $id): bool
    {
        $pdo = $this->dbConnect();
        
        $smt = $pdo->prepare("SELECT * FROM unms.ucrm.app_key WHERE plugin_id = :id");
        $this->dbExecute($smt, [ "id" => $id ]);
        
        return $smt->rowCount() === 1;
    }
    
    
    /**
     * @param array $manifest
     * @param bool $enabled
     *
     * @return false|string
     */
    protected function dbPluginInsert(array $manifest, string $target, bool $enabled = FALSE)
    {
        $pdo = $this->dbConnect();
        
        $smt = $pdo->prepare(<<<SQL
            INSERT INTO unms.ucrm.plugin
            (
                --id,
                name,
                display_name,
                description,
                url,
                author,
                version,
                enabled,
                execution_period,
                min_unms_version,
                max_unms_version,
                has_widgets,
                has_payment_button,
                has_admin_zone_js,
                has_client_zone_js
            )
            VALUES
            (
                :name,
                :display_name,
                :description,
                :url,
                :author,
                :version,
                :enabled,
                :execution_period,
                :min_unms_version,
                :max_unms_version,
                :has_widgets,
                :has_payment_button,
                :has_admin_zone_js,
                :has_client_zone_js
            );
        SQL);
        
        $params = $this->dbSanitize(
            [
                "name"                  => $manifest["information"]["name"],
                "display_name"          => $manifest["information"]["displayName"],
                "description"           => $manifest["information"]["description"],
                "url"                   => $manifest["information"]["url"],
                "author"                => $manifest["information"]["author"],
                "version"               => $manifest["information"]["version"],
                "enabled"               => $enabled,
                "execution_period"      => "",
                "min_unms_version"      => $manifest["information"]["unmsVersionCompliancy"]["min"],
                "max_unms_version"      => $manifest["information"]["unmsVersionCompliancy"]["max"],
                "has_widgets"           => array_key_exists("widgets", $manifest),
                "has_payment_button"    => array_key_exists("paymentButton", $manifest),
                "has_admin_zone_js"     => file_exists("$target/public/admin-zone.js"),
                "has_client_zone_js"    => file_exists("$target/public/client-zone.js"),
            ]
        );
        
        $this->dbExecute($smt, $params);
        
        return $pdo->lastInsertId();
    }
    
    /**
     * @param array $manifest
     * @param string $id
     *
     * @return false|string
     */
    protected function dbAppKeyInsert(array $manifest, string $id)
    {
        $pdo = $this->dbConnect();
        
        $key = "";
        
        try
        {
            // From: /usr/src/ucrm/src/AppBundle/DataProvider/AppKeyDataProvider.php
            $key = base64_encode(random_bytes(48));
        }
        catch (Exception $e)
        {
            $this->error("Could not generate a valid Plugin Key", TRUE);
        }
        
        $smt = $pdo->prepare(<<<SQL
            INSERT INTO unms.ucrm.app_key
            (
                --id,
                name,
                key,
                type,
                created_date,
                last_used_date,
                plugin_id,
                deleted_at
            )
            VALUES
            (
                :name,
                :key,
                :type,
                :created_date,
                :last_used_date,
                :plugin_id,
                :deleted_at
            );
        SQL);
        
        $params = $this->dbSanitize(
            [
                "name"                  => "plugin_".$manifest["information"]["name"],
                "key"                   => $key,
                "type"                  => "TYPE_WRITE",
                "created_date"          => (new DateTime())->format("Y-m-d h:i:s"),
                "last_used_date"        => NULL,
                "plugin_id"             => $id,
                "deleted_at"            => NULL,
            ]
        );
        
        $this->dbExecute($smt, $params);
    
        return $pdo->lastInsertId();
    }
    
    
    
    protected function dbPluginDeleteByName(string $name): bool
    {
        $pdo = $this->dbConnect();
        
        $smt = $pdo->prepare("DELETE FROM unms.ucrm.plugin WHERE name = :name");
        $this->dbExecute($smt, [ "name" => $name ]);
        
        return $smt->rowCount() === 1;
    }
    
    protected function dbPluginDeleteById(int $id): bool
    {
        $pdo = $this->dbConnect();
        
        $smt = $pdo->prepare("DELETE FROM unms.ucrm.plugin WHERE id = :id");
        $this->dbExecute($smt, [ "id" => $id ]);
        
        return $smt->rowCount() === 1;
    }
    
    protected function dbAppKeyDeleteByPluginName(string $name): bool
    {
        $pdo = $this->dbConnect();
        
        $smt = $pdo->prepare("DELETE FROM unms.ucrm.app_key WHERE name = :name");
        $this->dbExecute($smt, [ "name" => "plugin_$name" ]);
        
        return $smt->rowCount() === 1;
    }
    
    protected function dbAppKeyDeleteByPluginId(int $id): bool
    {
        $pdo = $this->dbConnect();
        
        $smt = $pdo->prepare("DELETE FROM unms.ucrm.app_key WHERE plugin_id = :id");
        $this->dbExecute($smt, [ "id" => $id ]);
        
        return $smt->rowCount() === 1;
    }
    
    protected function dbGetOptionByCode(string $code): string
    {
        $pdo = $this->dbConnect();
    
        $smt = $pdo->prepare("SELECT * FROM unms.ucrm.option WHERE code = :code");
        $this->dbExecute($smt, [ "code" => $code ]);
    
        $result = $smt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && array_key_exists("value", $result))
            return $result["value"];
        
        return "";
    }
    
    protected function dbGetAppKeyByName(string $name): string
    {
        $pdo = $this->dbConnect();
        
        $smt = $pdo->prepare("SELECT * FROM unms.ucrm.app_key WHERE name = :name");
        $this->dbExecute($smt, [ "name" => "plugin_$name" ]);
        
        $result = $smt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && array_key_exists("key", $result))
            return $result["key"];
        
        return "";
    }
    
    protected function dbGetAppKeyById(string $id): string
    {
        $pdo = $this->dbConnect();
        
        $smt = $pdo->prepare("SELECT * FROM unms.ucrm.app_key WHERE key_id = :id");
        $this->dbExecute($smt, [ "id" => $id ]);
        
        $result = $smt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && array_key_exists("key", $result))
            return $result["key"];
        
        return "";
    }
    
}
