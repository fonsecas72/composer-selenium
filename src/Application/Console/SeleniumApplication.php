<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BeubiQA\Application\Console;

use Symfony\Component\Console\Application;
use BeubiQA\Application\Command\StartSeleniumCommand;
use BeubiQA\Application\Command\StopSeleniumCommand;
use BeubiQA\Application\Command\GetSeleniumCommand;
use BeubiQA\Application\Command\ShowSeleniumCommand;

class SeleniumApplication extends Application
{

    public function __construct($name = 'selenium', $version = '0.1')
    {
        parent::__construct($name = 'selenium', $version = '0.1');
        $this->add(new StartSeleniumCommand());
        $this->add(new StopSeleniumCommand());
        $this->add(new GetSeleniumCommand());
        $this->add(new ShowSeleniumCommand());
    }
}
