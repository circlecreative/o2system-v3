O2ORM
=====
[O2ORM][2] is an Open Source Database Object Relationship Manager Tool that stores data directly in the database and creates all tables and columns required on the fly. [O2ORM][2] is currently build for MySQL only. Another amazing product from [][1], released under MIT License.

[O2ORM][2] is build for working more powerfull with O2System Framework, but also can be used for integrated with others as standalone version with limited features.

[O2ORM][2] not just created on the fly but also store the tables information schema into database, it will become very useful in the future.

[O2ORM][2] is insipired by [RedBeanPHP][4], [CodeIgniter][3] Active Records concept and Eloquent, so [O2ORM][2] is has also functionality similar with them, but a little bit different at the syntax.

Installation
------------
The best way to install O2ORM is to use [Composer][8]
```
composer require o2system/o2orm:'dev-master'
```

Usage
-----
```
use O2System\DB;
use O2System\ORM\Model;

class User extends Model
{
  public function __construct()
  {
    parent::__construct();
    
    if(! isset($this->db))
    {
      $o2db = new DB();
      $this->db = $o2db->connect('mysql://username:password@127.0.0.1:3306/database_name');
    }
    
    // do something here
  }
  
  public function profile
  {
    // example by defining table name
    return $this->has_one('profile_table_name');
  }
  
  public function articles
  {
    // example by defining namespace class name, the class must be an instance of O2ORM Model
    return $this->has_many('Models\Articles');
  }
  
  public function blogs
  {
    // example using blogs model
    $blog_model = new \Models\Blogs(); // must be an instance of O2ORM Model
    return $this->has_many($blog_model);
  }
}

$user = new User();
$user->all(); // returns all users data rows

// return a single row object
$me = $user->find(1); // find by id
$me = $user->find('steevenz', 'username'); // find by specific field
echo $me->profile; // produces json output
echo $me->profile->username // will be steevenz off course ^_^

// articles output
foreach($me->articles as $article)
{
  echo $article->title;
}

// return a bunch of row records
$users = $user->find_many(['record_status' => 1]); // find many by conditions
```
You can use table name, table_name.field, namespace model class name (psr-0, psr-4 standard naming), model object resource as a parameter when you do need an object relations.
Let O2ORM do the object mapping for you, your model class would be very simple. 

Note: 
- All your model classes must be extended into O2ORM Model (become an instanceof O2ORM Model).
- If you familiar with Eloquent, O2ORM has almost the same syntax.

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
- PDO
- O2DB

Credits
-------
* Founder and Lead Projects: [Steeven Andrian Salim (steevenz.com)][4]

[1]: http://www.circle-creative.com
[2]: http://www.circle-creative.com/products/o2orm
[4]: http://www.steevenz.com
[5]: mailto:developer@circle-creative.com
[6]: mailto:steeven@circle-creative.com
[7]: https://packagist.org/packages/o2system/o2orm
[8]: https://getcomposer.org

