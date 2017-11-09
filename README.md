yoda-portal-intake
====================

NAME
----

Intake module for Yoda.

INSTALLATION
------

You can use the Yoda install script to add modules to the Yoda environment.
In the following example, $home is used for the root of the Yoda portal (where the directories `controllers`, `models`, etc. are)
```sh
$ /bin/bash $home/tools/add-module.sh https://github.com/UtrechtUniversity/yoda-portal-intake intake
```
The module will be installed in `$home/modules/intake`.

### Installing with a different name
In the above instructions, it is assumed the module should be called _intake_. In case this name should be different, replace the argument `intake` in the above example call to `add-module.sh` to the name you wish to use. It is best practive to use lower case letters and lower dashes (`_`) only.

Next, create the file `module_local.php` in the `config` directory of the module, and copy the contents from `module.php` to this file. Change the `name` key to be the same value you used in your call to `add-module.sh` and pick any name you want for the `label`.

LICENSE
-------

Copyright (c) 2015-2017, Utrecht University. All rights reserved.

This project is licensed under the GPLv3 license.

The full license text can be found in [LICENSE](LICENSE).
