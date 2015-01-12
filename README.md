Horus CSession
===============
Client-Side secure session store .   
- Manage the lifetime easily .  
- Choose whether to encrypt with a secret key or not .
- Config the cookie.domain easily .
- Config its path easily .
- Manage _SESSION as normal .

Requirements
============
- Horus Framework >= 9 .  

Usage
======

```php
<?php

    // load Horus & it
    require('/path/to/Horus.php');
    require('path/to/CSession.php');

    // start Horus
    $app = new Horus;

    // start and config it
    new CSession
    (
        'session_cookie_name',
        time() + 3600, // lifetime
        null, // cookie-domain
        '/', // cookie-path 
        'secret-key' // key to encrypt it, null means no encryptioin
    );

    // use it normally

    $_SESSION['key1'] = 'value1';

    $app->res->sendr($_SESSION);

    session_destroy();

    $app->run();
?>
```
