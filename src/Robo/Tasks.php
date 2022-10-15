<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace UCRM\Plugins\Robo;

use Robo\Collection\CollectionBuilder;
use UCRM\Plugins\Robo\Tasks\Plugin\Bundle;

trait Tasks
{
    /**
     * Bundles a Plugin
     *
     * @param string $plugin
     *
     * @return CollectionBuilder
     */
    protected function taskPluginBundle(string $plugin): CollectionBuilder
    {
        return $this->task(Bundle::class, $plugin);
    }
}
