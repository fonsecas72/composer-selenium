# Selenium Handler

Get, Start &amp; Stop selenium server

You can use this package to get, start and stop selenium server.

[![Build Status](https://travis-ci.org/fonsecas72/selenium-handler.svg)](https://travis-ci.org/fonsecas72/selenium-handler)   [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/fonsecas72/selenium-handler/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/fonsecas72/selenium-handler/?branch=master)
[![Dependency Status](https://www.versioneye.com/user/projects/5502ac704a1064db0e0004ba/badge.svg?style=flat)](https://www.versioneye.com/user/projects/5502ac704a1064db0e0004ba)

[![Latest Stable Version](https://poser.pugx.org/fonsecas72/selenium-handler/v/stable.svg)](https://packagist.org/packages/fonsecas72/selenium-handler) [![Total Downloads](https://poser.pugx.org/fonsecas72/selenium-handler/downloads.svg)](https://packagist.org/packages/fonsecas72/selenium-handler) [![Daily Downloads](https://poser.pugx.org/fonsecas72/selenium-handler/d/daily.png)](https://packagist.org/packages/fonsecas72/selenium-handler)  [![Latest Unstable Version](https://poser.pugx.org/fonsecas72/selenium-handler/v/unstable.svg)](https://packagist.org/packages/fonsecas72/selenium-handler) [![License](https://poser.pugx.org/fonsecas72/selenium-handler/license.svg)](https://packagist.org/packages/fonsecas72/selenium-handler)


### Installation

With Composer:
```
  composer require --dev fonsecas72/selenium-handler dev-master
```

### Usage


### Library

You can create a downloader, a starter, a stopper and a watcher.
E.g. to create a starter:

```
$seleniumStarterOptions = new SeleniumStartOptions();
$process = new Process('');
$exeFinder = new ExecutableFinder();
$waiter = new ResponseWaitter(new Client());
$starter = new SeleniumStarter($seleniumStarterOptions, $process, $waiter, $exeFinder);
```

Then you can call:
```
$starter->start();
```
And it will just work.

Of course, you can also change de default settings.
This is done by calling options classes that each one if this has.
E.g. to change a setting for the starter:

```
// timeout is changed in the "waitter" class:
$starter->getResponseWaitter()->setTimeout($input->getOption('timeout'));
// to set a specific selenium location you do:
$starterOptions = $starter->getStartOptions();
$starterOptions->setSeleniumJarLocation($input->getOption('selenium-location'));
// to enable xvfb:
$starterOptions->enabledXvfb();
```

**see the tests and the built-in commands for more examples, or open an issue**

You can also create a "handler" that will allow you to start, stop, download, etc. through one single class.
```
$this->handler = new SeleniumHandler($starter, $stopper, $downloader, $logWatcher);
```


### Built-in commands 

#### get selenium
`(it will download to current directory by default)`

```
bin/selenium get
```

You can set the destination directory with
```
bin/selenium get -d someDir/
```

You can set the selenium version with
```
bin/selenium get -s 2.44
```

#### start selenium
```
bin/selenium start
```

You can give the selenium location with
```
bin/selenium start --selenium-location /someDir/selenium-server-standalone.jar
```

Optionally, you may choose whether or not you want to use xvfb
```
bin/selenium start --xvfb
```

You can throw selenium options too. For example:
You can set the firefox profile to use:
```
bin/selenium start --selextra firefox-profile=/someDir/toFirefoxProfile
```

You can also set how much you are willing to wait for selenium to start (in seconds)
```
bin/selenium start --timeout 60
```


#### stop selenium
```
bin/selenium stop
```

##### Tips & Tricks

You can also tail the selenium log after start with follow option:
```
bin/selenium start --follow
```

You can even track a specific log level
```
bin/selenium start --follow ERROR
```

This can be specially useful if you start selenium in the background before running tests.

```
bin/selenium start --follow ERROR &
```

Then if some error happens you will see it in your test log / output.




