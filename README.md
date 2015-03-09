# Composer-selenium
Start &amp; Stop selenium with a composer package

You can use this package to get, start and stop selenium server.

### Installation

With Composer:
```
  composer require 'fonsecas72/composer-selenium'
```

### Usage

#### get selenium 
`(this will download to /opt/selenium-server-standalone.jar)`
```
bin/selenium get (-s 2.44)
```

#### start selenium
```
bin/selenium start
```

#### stop selenium
```
bin/selenium stop
```

##### Tips & Tricks

* You can start selenium in verbose mode with ```-v```
This will tail the selenium log.

