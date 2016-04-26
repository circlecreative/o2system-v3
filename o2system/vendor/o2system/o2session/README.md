O2Session
=====
[![Latest Stable Version](https://poser.pugx.org/o2system/o2session/v/stable)](https://packagist.org/packages/o2system/o2session) [![Total Downloads](https://poser.pugx.org/o2system/o2session/downloads)](https://packagist.org/packages/o2system/o2session) [![Latest Unstable Version](https://poser.pugx.org/o2system/o2session/v/unstable)](https://packagist.org/packages/o2system/o2session) [![License](https://poser.pugx.org/o2system/o2session/license)](https://packagist.org/packages/o2system/o2session)

[O2Session][3] is an Open Source Session Management Driver Library. 
Allows different storage engines to be used. 
All but file-based storage require specific server requirements, and a Fatal Exception will be thrown if server requirements are not met. 
Another amazing product from [][1], released under [MIT License][4].

[O2Session][3] is build for working more powerfull with O2System Framework, but also can be used for integrated with others as standalone version with limited features.

[O2Session][3] is based on [CodeIgniter][10] Session Driver, so [O2Session][3] is has also functionality similar with them, but a little bit different at the syntax.

Installation
------------
The best way to install O2Session is to use [Composer][7]
```
composer require o2system/o2session:"dev-master"
```

Usage
-----
```php
use O2System\Session;

// Starting Session
$session = new Session(array(
    'storage' => array(
        'name' => 'session_name',
        'driver' => 'files',
        'save_path' => 'path/to/session/storage',
        'lifetime' => 3600,
        'regenerate_time' => 600,
        'regenerate_id' => FALSE,
        'match_ip' => TRUE
    ),
    'cookie' => array(
        'prefix' => 'cookie_',
        'lifetime' => 7200,
        'domain' => '',
        'path' => '/',
        'secure' => FALSE,
        'httponly' => TRUE
    )
));

// Set session userdata
$session->set_userdata('foo', ['bar' => 'something']);

// Get session userdata
$foo = $session->userdata('foo');

// Get all session data
$userdata = $session->get_userdata();
```

More details at the [Wiki][6].

Ideas and Suggestions
---------------------
Please kindly mail us at [developer@o2system.in][9].

Bugs and Issues
---------------
Please kindly submit your [issues at Github][5] so we can track all the issues along development.

System Requirements
-------------------
- PHP 5.4+
- [Composer][10]
- O2System Glob (O2Glob)
- O2System Database (O2DB)

Credits
-------
* Founder and Lead Projects: [Steeven Andrian Salim (steevenz.com)](http://steevenz.com)
* Github Pages Designer and Writer: [Teguh Rianto](http://teguhrianto.tk)
* Wiki Writer: [Steeven Andrian Salim](http://steevenz.com) (EN), Aradea Hind (ID)
* Special Thanks To: Yudi Primaputra (CTO - PT. YukBisnis Indonesia)

[1]: http://circle-creative.com
[2]: http://o2system.in
[3]: http://o2system.in/features/o2session
[4]: http://o2system.in/features/o2session/license
[5]: http://github.com/circlecreative/o2session/issues
[6]: http://github.com/circlecreative/o2session/wiki
[7]: https://packagist.org/packages/o2system/o2session
[8]: http://steevenz.com
[9]: mailto:developer@o2system.in
[10]: https://getcomposer.org
[11]: http://codeigniter.com
