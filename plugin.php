<?php
return array(
    'id' =>             'auth:cas', # notrans
    'version' =>        '0.1.1',
    'name' =>           /* trans */ 'NEIIST CAS Authentication',
    'author' =>         'Ricardo Laranjeiro',
    'description' =>    /* trans */ 'Created from Kevin O\'Connor\'s JASIG CAS Authentication to provide IST\'s CAS Authentication to NEIIST members.',
    'url' =>            'https://neiist.tecnico.ulisboa.pt',
    'plugin' =>         'authentication.php:CasAuthPlugin',
    'requires' => array()
);

?>
