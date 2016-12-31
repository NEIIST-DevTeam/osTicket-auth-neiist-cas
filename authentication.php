<?php

require_once(INCLUDE_DIR.'class.plugin.php');

class CasAuthPlugin extends Plugin {
    function bootstrap() {
        require_once('cas.php');
        StaffAuthenticationBackend::register(new CasNEIISTAuthBackend());
    }
}

require_once(INCLUDE_DIR.'UniversalClassLoader.php');
use Symfony\Component\ClassLoader\UniversalClassLoader_osTicket;
$loader = new UniversalClassLoader_osTicket();
$loader->registerNamespaceFallbacks(array(
    dirname(__file__).'/lib'));
$loader->register();
