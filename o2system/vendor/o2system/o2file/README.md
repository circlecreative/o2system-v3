O2File
=====
[O2File][2] is an Open Source PHP File Workshop Libraries. It's very helpfull for reading or writing a file. 
Another amazing product from [][1], released under MIT License.
[O2File][2] is build for working more powerfull with O2System Framework, but also can be used for integrated with others as standalone version with limited features.

Features
--------
- INI File
- JSON File
- XML File
- CSV File
- Zip File

More will be coming soon, such as YAML, RAR and etc.

Installation
------------
The best way to install O2File is to use [Composer][8]
```
composer require o2system/o2file:'dev-master'
```

Usage
-----
```
use O2System\File\Factory\Ini;

// Read ini file
$ini_file_path = 'path/to/your/file.ini;
$content = Ini::read($ini_file_path, 'array'); // return as array

// Write ini file
$content = array(
  'Foo' => 'Bar'
);
Ini::write($ini_file_path, $content);
// produces file with content: Foo = "Bar"

// Read file info
use O2System\File;

$info = File::info($ini_file_path);
// returns array of usefull file info metadata
```

More details at the Wiki. (Coming Soon)

Ideas and Suggestions
---------------------
Please kindly mail us at [developer@circle-creative.com][5] or [steeven@circle-creative.com][6].

Bugs and Issues
---------------
Please kindly submit your issues at Github so we can track all the issues along development.

System Requirements
-------------------
- PHP 5.4+
- Composer
- ZZIPlib library by Guido Draheim (For Zip File Factory)
- Zip PELC extension (For Zip File Factory)

Credits
-------
* Founder and Lead Projects: [Steeven Andrian Salim (steevenz.com)][4]

[1]: http://www.circle-creative.com
[2]: http://www.circle-creative.com/products/o2file
[3]: http://www.unirest.io
[4]: http://www.steevenz.com
[5]: mailto:developer@circle-creative.com
[6]: mailto:steeven@circle-creative.com
[7]: https://packagist.org/packages/o2system/o2file
[8]: https://getcomposer.org
