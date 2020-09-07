<?php
namespace tsframe;
use tsframe\Plugins;

/**
 * @deprecated
 * Plugin migrated to framework standart modules
 */

Plugins::disable('crypto');
throw new \Exception('Plugin "crypto" was deprecated! Reload this page ...');