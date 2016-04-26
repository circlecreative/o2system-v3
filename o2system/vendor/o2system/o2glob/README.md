# Welcome to O2GLOB #
---------------------
[O2Glob][2] is a Magical Singleton and Anti-Pattern Framework, a sets of core framework of O2System Framework since v3.0.0 which is distributed as a standalone mini core framework. Its goal is to enable you to develop your own framework or to make your library class more powerful, make your class methods and properties can be called in various ways and can get various results.

**Usage Example**
For example you build a small class called Config

```php
class Config
{
    use \O2Glob\Factory\Basics;
    
    public $items = array(
        'path' => 'my_path'
    );
    
    /**
     * Class constructor
     * if you trait the O2Glob factory class then you cannot use __construct() anymore
     * you must use __reconstruct() as your class constructor
     *
     */
    protected function __reconstruct()
    {
        \\ your logic
    }
    
    public function item($name)
    {
        
    }
    
    public static function load($item)
    {
        \\ your logic
    }
}

// let's try it
$CFG = new Config();

// to get from singleton
$CFG =& Config::_init();

// by default PHP doesn't allowed you to call non static method in static way
// but with the magic of O2Glob make it can be called with prefix '_'

// try to call non static method in static way
$CFG::item(); // will throw PHP error

// O2Glob Way
$CFG::_item('name'); // same result with $CFG->item('name') or Config::_item('name')

// The otherwise

// the load function is already in static but you want to call as a non static function
$CFG->load('some_item'); // same result with $CFG::load('some_item') or Config::load('some_item')
```
> Note
At example above you call the constructor or init the class at the first

**Other example**
```php
// at this example after you created your Config class you doesn't call _init or call 'new Config()'
// let's try directly call the method in static way
Config::_item('name'); // getting the same result
```
> Note
The Glob is Magically convert your non static class

**Property example**

```php
// try to called class property in method way
$items = Config::items(); // it will return the Config items properties
 
print_r($items);

// will produce
Array([path] => my_path)
```
> Note
With O2Glob you can get the value of your property class in many return type such as original value, object, array, serialize array or json on the fly 

Is there is more magic?? more information at the [wiki page][3].

Ideas and Suggestions
---------------------
Please kindly mail us at [developer@circle-creative.com][6] or [steeven@circle-creative.com][7].

Bugs and Issues
---------------
Please kindly submit your issues at Github so we can track all the issues along development.

System Requirements
-------------------
- PHP 5.4+
- Composer

Credits
-------
* Founder and Lead Projects: [Steeven Andrian Salim][7] - [steevenz.com][6]
 
Special Thanks
--------------
* My Lovely Wife zHa,My Little Princess Angie, My Little Prince Neal - Thanks for all your supports, i love you all
* Viktor Iwan Kristanda (PT. Doxadigital Indonesia)
* Yudi Primaputra (PT. Yuk Bisnis Indonesia)

[1]: http://circle-creative.com
[2]: http://circle-creative.com/products/o2glob
[3]: http://github.com/circlecreative/o2glob/wiki
[4]: mailto:developer@circle-creative.com
[5]: mailto:steeven@circle-creative.com
[6]: http://steevenz.com
[7]: http://cv.steevenz.com
[8]: https://getcomposer.org
[9]: https://packagist.org/packages/o2system/o2glob

