General MODX Revolution PHP commandline based install tool.
With additional options to customize and extend for your own needs.

*Install installers*

```no-highlight
To configure this installers to work:
- copy "includes/config.example.conf" to "includes/config.conf"
- modify values in config.conf for your needs
```

*Commandline examples*

```no-highlight
- cd /to/your/path/with/the/installers/
- php install-core.php
- php install-packages.php

And if you have any other hooks created;
- php install-any.php
```

***

Installer hooks
=====================

You can install/create hooks for several purposes. Below a list of
possibilities to hook in;
Note: all hooks are stored into the hooks/ folder.


**OVERALL**

Anywhere you can use a readQuestion() method to 'ask'
things commandline based. Usage:

```no-highlight
$bool = readQuestion('This is a basic YES/NO question and returns boolean true or false');
$str = readQuestion('This way you can ask to input something', 'any');
```

**INSTALL CORE**

You can hook in at the end of the CORE installer, create PHP files
that are prefixed with "install-core." and always ends with ".php"

*For example;*
When you want to create some extra assets folders, you can add a file inside hooks/ like:

install-core.CreateAssets.php
This script can create the directories you want to create.

```no-highlight
Available variables here;
- $projecthost = Hostname of the project
- $projectalis = Alternate alias hostname of the project
- $projectpath = The absolute path the projects root folder
- $adminUser = The MODX admin username
- $adminPassword = The MODX admin password
- $adminEmail = The MODX admin emailaddress
```

**INSTALL PACKAGES**

You can hook in at the end of the packages installer. Create PHP files
that are prefixed with "install-packages." and always ends with ".php"

*For example;*
The package installer asks to install a couple of package, but maybe you
want to install some defaults your own.. You can do that with a hook!

install-packages.MyPackages.php

```no-highlight
Available variables here;
- $modx = The well known MODX instance
- $projectpath = The absolute path the projects root folder
- $defaultProvider = The default MODX core package provider
- $productVersion = The version of your installed MODX

Available methods here;

downloadAndInstallPackage(
    $packageName, /* The name of the package to install, like "getResources" */
    $installOptions, /* Extra options like a custom provider setting. In the future setup options will be supported too! */
);

It will return true or false if install is successful
```

**INSTALL ANYTHING ELSE!**

For everything else you want to install inside your project, you can create
your own hooks to load when running this installer.

*For example;*
If you always have the same MODX elements (such as categories,
chunks, templates etc.), you can create one or multiple hooks to install
that elements for you all the time.

```no-highlight
Available variables here;
- $modx = The well known MODX instance
- $projectpath = The absolute path the projects root folder
```
