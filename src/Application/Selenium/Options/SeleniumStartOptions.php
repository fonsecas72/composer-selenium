<?php

namespace BeubiQA\Application\Selenium\Options;

class SeleniumStartOptions extends SeleniumOptions
{
    protected $seleniumExtraArguments = [];
    protected $isXvfbEnabled = false;

    /**
     * @param array $args
     */
    public function setSeleniumExtraArguments($args)
    {
        foreach ($args as $argName => $argValue) {
            $this->seleniumExtraArguments[$argName] = $argValue;
        }
    }
    /**
     * @return array
     */
    public function getSeleniumExtraArguments()
    {
        return $this->seleniumExtraArguments;
    }
    /**
     * @param string $path
     */
    public function enabledXvfb()
    {
        $this->isXvfbEnabled = true;
    }
    /**
     * @return bool
     */
    public function isXvfbEnabled()
    {
        return $this->isXvfbEnabled;
    }
}
