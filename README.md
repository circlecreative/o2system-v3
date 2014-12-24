O2System
========

[O2System Framework][2] is a new Dynamic Open Source PHP Framework by [PT. Lingkar Kreasi (Circle Creative)][1], based on [CodeIgniter Framework][3]. [CodeIgniter Framework][3] was created by [EllisLab, Inc][4], and is now a project of the [British Columbia Institute of Technology][5].

History
-------
[O2System Framework][2] first created by [PT. Lingkar Kreasi (Circle Creative)][1] in 2009 as closed source for O2CMS v1.0 development purposed. 
The project was stopped in 2011 and turned into Third Party HMVC for [CodeIgniter Framework][3] in 2012, but in November 2014 we decided to turn it back into a framework with HMVC concept and we published it as Open Source PHP Framework under MIT License.

Behind The Scenes
-----------------
Why based on [CodeIgniter Framework][3]?
[CodeIgniter Framework][3] is the most popular Open Source PHP Framework and has very good documentations, forum, community, and contributors. [CodeIgniter Framework][3] has unique concept with Super Global Object and Get Instance that really need by [O2System Framework][2].
We know that shifting into another framework is really painful, need new adaptation, reading many documentations, got to learn from many tutorials and the big problems is if you had a bunch of codes that you still need to use it and it's all writen for CodeIgniter Framework, but with [O2System Framework][2] is very easy for integration.

We create this new framework to fullfill our and your needs for building best web-based application with HMVC (Hierarchical model–view–controller) and Modular concept. With [O2System Framework][2] you can build it in the right way.

Released Scheduled
------------------
* 24 November 2014 (Prototype Version):
  Has many bugs, and still using many class from [CodeIgniter Framework][3]

* 1 December 2014 (Beta Version):
  O2System is now release in Beta Version. If you find any bugs, please kindly report it to us as an issued on Github so we can track it and we'll fixed it as soon as possible.

* January 2015 (Alpha Version):
  [O2System Framework][2]  version 2.0 Alpha will be published on January 2015 under MIT License. You can freely use it on your next web-based application project.

Ideas and Suggestions
---------------------
Please kindly mail us at [developer@circle-creative.com][6] or [steeven@circle-creative.com][7].

Bugs and Issues
---------------
Please kindly submit your issues at Github so we can track all the issues along development.

System Requirements
-------------------
- PHP 5.2+
- Composer

Special Features
----------------
- Easy [CodeIgniter Framework][3] Integration.
- Smart Loader, Load across app or modules.
- Compatible with Composer Autoload.
- Template with Theming Support, Powered with [Smarty][9] and [Twig][10] (Optional) Templating Engine.
- Overriding Module View with Template Theme Module View.
- Developer Console, Helpful Developer Function for Debuging Purpose.
- Inheritance Libraries, Controllers and Drivers.

Download It Right Now!
----------------------
[O2System Framework][2] 2.0 Beta is Packed with Site and CMS App as example, for creating Website with separated Content Management System.
Now Avaliable with [Composer][11] for more information please visit the [Packagist Page][12]

Coming Soon
-----------
- Smart Loader for Loading Controllers and Modules.
- Shortcode Parser based on Wordpress Shortcode Concept.
- Developer Console integrated with Firebug.
- Access Control Library.

Bugs
----
- Template Assets not Compatible with Twig but can be solved by called as raw: example {{ metadata|raw }}

Credits
-------
* Founder and Lead Projects: [Steeven Andrian Salim (steevenz.com)][8]
* Developer Team:
  - Wahyu Primadi
  - [Dadang Nurjaman (Mello Bruchst)][13]
* Beta Released Testing Team:
 - Tim Brownlaw (CodeIgniter Forum Admin on Facebook)
 - Hobrt Lhbib (hobrt-programming.com)

Special Thanks
--------------
* My Lovely Wife zHa,My Little Princess Angie, My Little Prince Neal - Thanks for all your supports, i love you all
* James Parry (CodeIgniter Project Lead) - Thanks for all your supports, assistance and advices
* Viktor Iwan Kristanda (PT. Doxadigital Indonesia)
* Arthur Purnama (CGI Deutschland - Germany)
* Alfi Rizka (Dedicated IT - Indonesia)
* Wahyu Primadi (LittleOrange - Indonesia)
* Sachin Pandey (Ecurser Technologies - India)
* Ariza Novisa (eClouds Center - Indonesia)
* Don Lafferty (CodeIgniter Forum) - Thanks for suggesting the right tagline for [O2System Framework][2]

Change Logs
-----------
* 07 November 2014
 - Fixed working controllers
* 27 November 2014
 - Licensing all source code
 - Combined libraries, helpers with CodeIgniter
 - Fixed Core Classes and Bootstrap
 - Simulation for loading apps, models, controllers
* 30 November 2014
 - Fixed Loader
 - Fixed Template Driver
 - Add Smarty and Twig Vendor
 - Example Site, CMS, Welcome Page, Error Page, Example Page

Things to Do
------------
- Testing loading controllers, drivers, helpers
- Testing database connection

[1]: http://www.circle-creative.com
[2]: http://www.circle-creative.com/products/o2system
[3]: http://www.codeigniter.com
[4]: http://www.ellislab.com
[5]: http://www.bcit.ca/cas/computing/
[6]: mailto:developer@circle-creative.com
[7]: mailto:steeven@circle-creative.com
[8]: http://cv.steevenz.com
[9]: http://www.smarty.net/
[10]: http://twig.sensiolabs.org/
[11]: https://getcomposer.org
[12]: https://packagist.org/packages/o2system/o2system
[13]: http://jawaxa.com
