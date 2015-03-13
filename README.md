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

#### get selenium 
`(it will download to /opt/selenium/selenium-server-standalone.jar by default so you may need sudo)`

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

You can even set the firefox profile to use or set how much you are willing to wait for selenium to start (in seconds)
```
bin/selenium start --firefox-profile /someDir/toFirefoxProfile
```
```
bin/selenium start --timeout 60
```


#### stop selenium
```
bin/selenium stop
```

##### Tips & Tricks

* You can start selenium in verbose mode with ```-v```
This will tail the selenium log.

