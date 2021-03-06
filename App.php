<?php

namespace HbgStyleGuide;

class App
{
    protected $default = 'home';
    protected $documentation = null;
    protected $page = null;

    public function __construct()
    {
        $this->page = isset($_GET['p']) ? $_GET['p'] : 'home';
        $this->documentation = json_decode(file_get_contents(DOCUMENTATION_SASS_PATH));

        $this->createCacheDir();
        $this->loadPage();
    }

    /**
     * Automatically creates a cache dir for blade
     * @return string Theme
     */
    public function createCacheDir()
    {
        if (!is_dir(BASEPATH . "cache")) {
            mkdir(BASEPATH . "cache");
        }
    }

    /**
     * Gets theme from cookie or querystring
     * @return string Theme
     */
    public function getTheme()
    {
        if (!isset($_COOKIE['theme']) && !isset($_GET['theme'])) {
            return 'red';
        }

        if (!isset($_GET['theme'])) {
            return $_COOKIE['theme'];
        }

        $this->setTheme($_GET['theme']);
        return $_GET['theme'];
    }

    /**
     * Sets theme cookie
     * @param string $theme Theme to set
     */
    public function setTheme($theme)
    {
        setcookie('theme', $theme, time() + (86400 * 30), "/");
    }

    /**
     * Loads a page and it's navigation
     * @return bool Returns true when the page is loaded
     */
    public function loadPage()
    {
        // Navigation
        $data['nav'] = $this->loadNavigation($this->page);
        $data['pageNow'] = $this->page;
        $data['theme'] = $this->getTheme();

        // Home
        if ($this->page == 'home') {
            \HbgStyleGuide\View::show('home', $data);
            return true;
        }

        // Sections
        $data['docs'] = $this->loadPageDocumentation($this->page);
        \HbgStyleGuide\View::show('sections', $data);
        return true;
    }

    /**
     * Reads the navigation from the json
     * @return object Navigation
     */
    public function loadNavigation()
    {
        $nav = (array)$this->documentation->nav;
        ksort($nav);

        return (object)$nav;
    }

    /**
     * Loads the documentation of a specific page
     * @param  string $page The page
     * @return array        The documentation
     */
    public function loadPageDocumentation()
    {
        // If not set, return false
        if (!isset($this->documentation->pages->{$this->page})) {
            return false;
        }

        // If all good, return as it is
        return $this->documentation->pages->{$this->page};
    }
}
