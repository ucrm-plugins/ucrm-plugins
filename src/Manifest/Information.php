<?php /** @noinspection PhpUnused, SpellCheckingInspection */
declare(strict_types=1);

namespace UCRM\Plugins\Manifest;

use UCRM\Plugins\Manifest\Information\UcrmVersionCompliancy;
use UCRM\Plugins\Manifest\Information\UnmsVersionCompliancy;

/**
 * Class Information
 *
 * @author Ryan Spaeth <rspaeth@spaethtech.com>
 * @copyright 2022 - Spaeth Technologies Inc.
 *
 * @generated 09/27/2022 @ 02:47:00 by JsonParser::generate()
 */
class Information
{
	public string $name;
	public string $displayName;
	public string $description;
	public string $url;
	public string $version;
	public UcrmVersionCompliancy $ucrmVersionCompliancy;
	public UnmsVersionCompliancy $unmsVersionCompliancy;
	public string $author;
}

