<?php

class PluginTest extends TestCase
{
    public function test_plugin_installed() {
        activate_plugin( 'disciple-tools-data-top-off/disciple-tools-data-top-off.php' );

        $this->assertContains(
            'disciple-tools-data-top-off/disciple-tools-data-top-off.php',
            get_option( 'active_plugins' )
        );
    }
}
