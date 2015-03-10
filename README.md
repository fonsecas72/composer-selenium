# Selenium Handler

Get, Start &amp; Stop selenium with a composer package

You can use this package to get, start and stop selenium server.

### Installation

With Composer:
```
  composer require 'fonsecas72/composer-selenium'
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
bin/selenium start -l /someDir/selenium-server-standalone.jar
```

Optionaly, you may choose whether or not you want to use xvfb
```
bin/selenium start -xvfb
```


#### stop selenium
```
bin/selenium stop
```

##### Tips & Tricks

* You can start selenium in verbose mode with ```-v```
This will tail the selenium log.

