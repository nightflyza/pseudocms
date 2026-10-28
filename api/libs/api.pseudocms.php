<?php

/**
 * PseudoCMS - file-based pages and articles for YALF
 */
class PseudoCMS {

    /**
     * Data root under content/documents/
     *
     * @var string
     */
    protected $cmsDataPath = 'content/documents/pseudocms/';

    /**
     * Articles per feed page
     *
     * @var int
     */
    protected $articlesPerPage = 10;

    /**
     * Articles per page in pcmsmgr list
     *
     * @var int
     */
    protected $adminArticlesPerPage = 10;

    /**
     * Article date render mode from PCMS_ARTICLES_DATE_RENDER
     * 0 - hide, 1 - date only, 2 - date and time
     *
     * @var int
     */
    protected $articlesDateRender = 2;

    /**
     * Custom articles feed/module name from PCMS_ARTICLES_CUSTOM_NAMING
     *
     * @var string
     */
    protected $articlesCustomNaming = '';

    /**
     * Browser page title mode from PCMS_MUTABLE_TITLES
     * 0 - off, 1 - append to getPageTitle, 2 - replace with content title
     *
     * @var int
     */
    protected $mutableTitles = 0;

    /**
     * Default article type for create form (PCMS_ARTICLES_DEFAULT_HTML)
     * true = html, false = text
     *
     * @var bool
     */
    protected $articlesDefaultHtml = true;

    /**
     * Optional public stylesheet path from PCMS_CUSTOM_STYLES
     *
     * @var string
     */
    protected $customStylesPath = '';

    /**
     * Recent articles widget count from PCMS_RECENT_COUNT (0 = off)
     *
     * @var int
     */
    protected $recentCount = 0;

    /**
     * Public media directory relative path from PCMS_MEDIA_PATH (empty = disabled)
     *
     * @var string
     */
    protected $mediaPath = '';

    /**
     * Allowed media extensions from PCMS_MEDIA_ALLOWED (lowercase)
     *
     * @var array
     */
    protected $mediaAllowed = array();

    /**
     * Whether BBCode toolbar script was already output
     *
     * @var bool
     */
    protected $bbToolbarScriptDone = false;

    /**
     * Temporary placeholders for [code] blocks during BBCode parse
     *
     * @var array
     */
    protected $bbCodePlaceholders = array();

    /**
     * System messages helper
     *
     * @var object
     */
    protected $messages = '';

    /**
     * Last storage path error message (empty if ok)
     *
     * @var string
     */
    protected $storageError = '';

    /**
     * Per-request scandir name whitelist cache (dir path => name=>name)
     *
     * @var array
     */
    protected $dirWhitelistCache = array();

    const URL_ME = '?module=pcmsmgr';
    const URL_PAGES = '?module=pages';
    const URL_ARTICLES = '?module=articles';
    const URL_RECENT = '?module=recent';
    const URL_RSS = '?module=rss';

    const ROUTE_PAGEID = 'pageid';
    const ROUTE_ARTICLE = 'a';
    const ROUTE_PAGE = 'page';

    const ROUTE_SECTION = 'section';
    const ROUTE_EDIT_PAGE = 'editpage';
    const ROUTE_DELETE_PAGE = 'deletepage';
    const ROUTE_NEW_PAGE = 'newpage';
    const ROUTE_EDIT_ELEMENT = 'editelement';
    const ROUTE_DELETE_ELEMENT = 'deleteelement';
    const ROUTE_NEW_ELEMENT = 'newelement';
    const ROUTE_EDIT_ARTICLE = 'editarticle';
    const ROUTE_DELETE_ARTICLE = 'deletearticle';
    const ROUTE_NEW_ARTICLE = 'newarticle';
    const ROUTE_TO_DRAFT = 'todraft';
    const ROUTE_PUBLISH_DRAFT = 'publishdraft';
    const ROUTE_EDIT_DRAFT = 'editdraft';
    const ROUTE_DELETE_DRAFT = 'deletedraft';
    const ROUTE_NEW_DRAFT = 'newdraft';
    const ROUTE_DELETE_MEDIA = 'deletemedia';
    const ROUTE_DOWNLOAD_MEDIA = 'downloadmedia';

    const PROUTE_DOSAVE_PAGE = 'savepage';
    const PROUTE_DOSAVE_ELEMENT = 'saveelement';
    const PROUTE_DOSAVE_ARTICLE = 'savearticle';
    const PROUTE_DOUPLOAD_MEDIA = 'uploadmedia';
    const PROUTE_PAGE_ID = 'page_id';
    const PROUTE_PAGE_TITLE = 'page_title';
    const PROUTE_PAGE_CONTENT = 'page_content';
    const PROUTE_ELEMENT_ID = 'element_id';
    const PROUTE_ELEMENT_CONTENT = 'element_content';
    const PROUTE_ARTICLE_ID = 'article_id';
    const PROUTE_ARTICLE_TITLE = 'article_title';
    const PROUTE_ARTICLE_SHORT = 'article_short';
    const PROUTE_ARTICLE_FULL = 'article_full';
    const PROUTE_ARTICLE_DATE = 'article_date';
    const PROUTE_ARTICLE_TIME = 'article_time';
    const PROUTE_ARTICLE_TYPE = 'article_type';
    const PROUTE_IS_DRAFT = 'is_draft';
    const PROUTE_MEDIA_FILE = 'mediafile';

    const TYPE_HTML = 'html';
    const TYPE_TEXT = 'text';
    const SECTION_PAGES = 'pages';
    const SECTION_ELEMENTS = 'elements';
    const SECTION_ARTICLES = 'articles';
    const SECTION_DRAFTS = 'drafts';
    const SECTION_MEDIA = 'media';

    const DATE_RENDER_NONE = 0;
    const DATE_RENDER_DATE = 1;
    const DATE_RENDER_FULL = 2;

    const MUTABLE_TITLES_OFF = 0;
    const MUTABLE_TITLES_APPEND = 1;
    const MUTABLE_TITLES_REPLACE = 2;

    /**
     * Creates PseudoCMS instance
     */
    public function __construct() {
        $this->initMessages();
        $this->loadArticlesPerPage();
        $this->loadArticlesDateRender();
        $this->loadArticlesCustomNaming();
        $this->loadMutableTitles();
        $this->loadArticlesDefaultHtml();
        $this->loadCustomStyles();
        $this->loadRecentCount();
        $this->loadMediaPath();
        $this->loadMediaAllowed();
    }

    /**
     * Inits system messages helper
     *
     * @return void
     */
    protected function initMessages() {
        $this->messages = new UbillingMessageHelper();
    }

    /**
     * Is PseudoCMS enabled via alter.ini PCMS_ENABLED
     *
     * @global object $ubillingConfig
     *
     * @return bool
     */
    public function isEnabled() {
        global $ubillingConfig;
        $result = false;
        if (isset($ubillingConfig) and is_object($ubillingConfig)) {
            if ($ubillingConfig->getAlterParam('PCMS_ENABLED')) {
                $result = true;
            }
        }
        return ($result);
    }

    /**
     * Loads PCMS_ARTICLES_PERPAGE from alter.ini when present
     *
     * @global object $ubillingConfig
     *
     * @return void
     */
    protected function loadArticlesPerPage() {
        global $ubillingConfig;
        $perPage = 10;
        if (isset($ubillingConfig) and is_object($ubillingConfig)) {
            $altCfg = $ubillingConfig->getAlter();
            if (is_array($altCfg) and isset($altCfg['PCMS_ARTICLES_PERPAGE'])) {
                $raw = ubRouting::filters($altCfg['PCMS_ARTICLES_PERPAGE'], 'int');
                if (!empty($raw)) {
                    $perPage = (int) $raw;
                }
            }
        }
        $this->articlesPerPage = $perPage;
    }

    /**
     * Loads PCMS_ARTICLES_DATE_RENDER from alter.ini when present
     *
     * @global object $ubillingConfig
     *
     * @return void
     */
    protected function loadArticlesDateRender() {
        global $ubillingConfig;
        $mode = self::DATE_RENDER_FULL;
        if (isset($ubillingConfig) and is_object($ubillingConfig)) {
            $altCfg = $ubillingConfig->getAlter();
            if (is_array($altCfg) and isset($altCfg['PCMS_ARTICLES_DATE_RENDER'])) {
                $mode = (int) $altCfg['PCMS_ARTICLES_DATE_RENDER'];
                if ($mode != self::DATE_RENDER_NONE and $mode != self::DATE_RENDER_DATE and $mode != self::DATE_RENDER_FULL) {
                    $mode = self::DATE_RENDER_FULL;
                }
            }
        }
        $this->articlesDateRender = $mode;
    }

    /**
     * Loads PCMS_ARTICLES_CUSTOM_NAMING from alter.ini when present
     *
     * @global object $ubillingConfig
     *
     * @return void
     */
    protected function loadArticlesCustomNaming() {
        global $ubillingConfig;
        $customName = '';
        if (isset($ubillingConfig) and is_object($ubillingConfig)) {
            $altCfg = $ubillingConfig->getAlter();
            if (is_array($altCfg) and isset($altCfg['PCMS_ARTICLES_CUSTOM_NAMING'])) {
                if (!empty($altCfg['PCMS_ARTICLES_CUSTOM_NAMING'])) {
                    $customName = $altCfg['PCMS_ARTICLES_CUSTOM_NAMING'];
                }
            }
        }
        $this->articlesCustomNaming = $customName;
    }

    /**
     * Loads PCMS_MUTABLE_TITLES from alter.ini when present
     *
     * @global object $ubillingConfig
     *
     * @return void
     */
    protected function loadMutableTitles() {
        global $ubillingConfig;
        $mode = self::MUTABLE_TITLES_OFF;
        if (isset($ubillingConfig) and is_object($ubillingConfig)) {
            $altCfg = $ubillingConfig->getAlter();
            if (is_array($altCfg) and isset($altCfg['PCMS_MUTABLE_TITLES'])) {
                $mode = (int) $altCfg['PCMS_MUTABLE_TITLES'];
                if ($mode != self::MUTABLE_TITLES_OFF and $mode != self::MUTABLE_TITLES_APPEND and $mode != self::MUTABLE_TITLES_REPLACE) {
                    $mode = self::MUTABLE_TITLES_OFF;
                }
            }
        }
        $this->mutableTitles = $mode;
    }

    /**
     * Loads PCMS_ARTICLES_DEFAULT_HTML from alter.ini when present
     *
     * @global object $ubillingConfig
     *
     * @return void
     */
    protected function loadArticlesDefaultHtml() {
        global $ubillingConfig;
        $defaultHtml = true;
        if (isset($ubillingConfig) and is_object($ubillingConfig)) {
            $altCfg = $ubillingConfig->getAlter();
            if (is_array($altCfg) and isset($altCfg['PCMS_ARTICLES_DEFAULT_HTML'])) {
                if ($altCfg['PCMS_ARTICLES_DEFAULT_HTML']) {
                    $defaultHtml = true;
                } else {
                    $defaultHtml = false;
                }
            }
        }
        $this->articlesDefaultHtml = $defaultHtml;
    }

    /**
     * Loads PCMS_CUSTOM_STYLES from alter.ini when present
     *
     * @global object $ubillingConfig
     *
     * @return void
     */
    protected function loadCustomStyles() {
        global $ubillingConfig;
        $stylesPath = '';
        if (isset($ubillingConfig) and is_object($ubillingConfig)) {
            $altCfg = $ubillingConfig->getAlter();
            if (is_array($altCfg) and isset($altCfg['PCMS_CUSTOM_STYLES'])) {
                if (!empty($altCfg['PCMS_CUSTOM_STYLES'])) {
                    $rawPath = str_replace('\\', '/', $altCfg['PCMS_CUSTOM_STYLES']);
                    $rawPath = ltrim($rawPath, '/');
                    if (strpos($rawPath, '..') === false and file_exists($rawPath) and is_readable($rawPath)) {
                        $stylesPath = $rawPath;
                    }
                }
            }
        }
        $this->customStylesPath = $stylesPath;
    }

    /**
     * HTML link tag for optional public custom stylesheet
     *
     * @return string
     */
    public function renderCustomStylesLink() {
        $result = '';
        if (!empty($this->customStylesPath)) {
            $href = htmlspecialchars($this->customStylesPath, ENT_QUOTES, 'UTF-8');
            $result .= '<link rel="stylesheet" type="text/css" href="' . $href . '" />';
        }
        return ($result);
    }

    /**
     * Loads PCMS_RECENT_COUNT from alter.ini when present
     *
     * @global object $ubillingConfig
     *
     * @return void
     */
    protected function loadRecentCount() {
        global $ubillingConfig;
        $count = 0;
        if (isset($ubillingConfig) and is_object($ubillingConfig)) {
            $altCfg = $ubillingConfig->getAlter();
            if (is_array($altCfg) and isset($altCfg['PCMS_RECENT_COUNT'])) {
                $raw = ubRouting::filters($altCfg['PCMS_RECENT_COUNT'], 'int');
                if (!empty($raw) and ((int) $raw > 0)) {
                    $count = (int) $raw;
                }
            }
        }
        $this->recentCount = $count;
    }

    /**
     * Is RSS module enabled via alter.ini PCMS_RSS_ENABLED
     *
     * @global object $ubillingConfig
     *
     * @return bool
     */
    public function isRssEnabled() {
        global $ubillingConfig;
        $result = false;
        if (isset($ubillingConfig) and is_object($ubillingConfig)) {
            if ($ubillingConfig->getAlterParam('PCMS_RSS_ENABLED')) {
                $result = true;
            }
        }
        return ($result);
    }

    /**
     * Loads PCMS_MEDIA_PATH from alter.ini when present
     *
     * @global object $ubillingConfig
     *
     * @return void
     */
    protected function loadMediaPath() {
        global $ubillingConfig;
        $mediaPath = '';
        if (isset($ubillingConfig) and is_object($ubillingConfig)) {
            $altCfg = $ubillingConfig->getAlter();
            if (is_array($altCfg) and isset($altCfg['PCMS_MEDIA_PATH'])) {
                if (!empty($altCfg['PCMS_MEDIA_PATH'])) {
                    $rawPath = str_replace('\\', '/', $altCfg['PCMS_MEDIA_PATH']);
                    $rawPath = trim($rawPath);
                    $rawPath = ltrim($rawPath, '/');
                    if (!empty($rawPath) and (strpos($rawPath, '..') === false)) {
                        $mediaPath = rtrim($rawPath, '/') . '/';
                    }
                }
            }
        }
        $this->mediaPath = $mediaPath;
    }

    /**
     * Loads PCMS_MEDIA_ALLOWED from alter.ini when present
     *
     * @global object $ubillingConfig
     *
     * @return void
     */
    protected function loadMediaAllowed() {
        global $ubillingConfig;
        $allowed = array('jpeg', 'jpg', 'gif', 'png', 'pdf');
        if (isset($ubillingConfig) and is_object($ubillingConfig)) {
            $altCfg = $ubillingConfig->getAlter();
            if (is_array($altCfg) and isset($altCfg['PCMS_MEDIA_ALLOWED'])) {
                if (!empty($altCfg['PCMS_MEDIA_ALLOWED'])) {
                    $parts = explode(',', $altCfg['PCMS_MEDIA_ALLOWED']);
                    $parsed = array();
                    if (!empty($parts)) {
                        foreach ($parts as $io => $ext) {
                            $ext = strtolower(trim($ext));
                            $ext = ltrim($ext, '.');
                            if (!empty($ext) and preg_match('/^[a-z0-9]+$/', $ext)) {
                                $parsed[$ext] = $ext;
                            }
                        }
                    }
                    if (!empty($parsed)) {
                        $allowed = array_values($parsed);
                    }
                }
            }
        }
        $this->mediaAllowed = $allowed;
    }

    /**
     * Is media uploads enabled (non-empty PCMS_MEDIA_PATH)
     *
     * @return bool
     */
    public function isMediaEnabled() {
        $result = false;
        if (!empty($this->mediaPath)) {
            $result = true;
        }
        return ($result);
    }

    /**
     * Site base URL from current request (scheme + host + app path, no trailing slash)
     *
     * @return string
     */
    protected function getSiteBaseUrl() {
        $result = '';
        $https = false;
        if (!empty($_SERVER['HTTPS']) and ($_SERVER['HTTPS'] !== 'off')) {
            $https = true;
        } else {
            if (isset($_SERVER['SERVER_PORT']) and ((int) $_SERVER['SERVER_PORT'] == 443)) {
                $https = true;
            } else {
                if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) and (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https')) {
                    $https = true;
                }
            }
        }

        $host = '';
        if (!empty($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
        } else {
            if (!empty($_SERVER['SERVER_NAME'])) {
                $host = $_SERVER['SERVER_NAME'];
            }
        }

        if (!empty($host)) {
            $scheme = 'http';
            if ($https) {
                $scheme = 'https';
            }
            $basePath = '';
            if (!empty($_SERVER['SCRIPT_NAME'])) {
                $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
                if ($dir != '/' and $dir != '.' and $dir != '') {
                    $basePath = rtrim($dir, '/');
                }
            }
            $result = $scheme . '://' . $host . $basePath;
        }
        return ($result);
    }

    /**
     * Absolute public URL for a relative module path like ?module=articles&a=1
     *
     * @param string $relativeUrl
     *
     * @return string
     */
    protected function buildAbsoluteUrl($relativeUrl) {
        $result = $relativeUrl;
        $base = $this->getSiteBaseUrl();
        if (!empty($base)) {
            if (strpos($relativeUrl, '/') === 0) {
                $result = $base . $relativeUrl;
            } else {
                $result = $base . '/' . $relativeUrl;
            }
        }
        return ($result);
    }

    /**
     * Public articles module/feed title (custom naming or Articles)
     *
     * @return string
     */
    public function getArticlesModuleName() {
        $result = __('Articles');
        if (!empty($this->articlesCustomNaming)) {
            $result = __($this->articlesCustomNaming);
        }
        return ($result);
    }

    /**
     * Updates system page title by PCMS_MUTABLE_TITLES mode
     * 1 - append " - " + extraTitle to getPageTitle
     * 2 - replace page title with extraTitle
     *
     * @global object $system
     * @param string $extraTitle
     *
     * @return void
     */
    public function applyMutableTitle($extraTitle) {
        global $system;
        if ($this->mutableTitles != self::MUTABLE_TITLES_OFF and !empty($extraTitle)) {
            if (isset($system) and is_object($system)) {
                if ($this->mutableTitles == self::MUTABLE_TITLES_REPLACE) {
                    $system->setPageTitle($extraTitle);
                } else {
                    if ($this->mutableTitles == self::MUTABLE_TITLES_APPEND) {
                        $system->setPageTitle($system->getPageTitle() . ' - ' . $extraTitle);
                    }
                }
            }
        }
    }

    /**
     * Renders article datetime under content by PCMS_ARTICLES_DATE_RENDER
     *
     * @param string $datetime
     *
     * @return string
     */
    protected function renderArticleDate($datetime) {
        $result = '';
        if ($this->articlesDateRender != self::DATE_RENDER_NONE and !empty($datetime)) {
            $text = '';
            $ts = strtotime($datetime);
            if ($ts) {
                if ($this->articlesDateRender == self::DATE_RENDER_DATE) {
                    $text = date('Y-m-d', $ts);
                } else {
                    if ($this->articlesDateRender == self::DATE_RENDER_FULL) {
                        $text = date('Y-m-d H:i', $ts);
                    }
                }
            } else {
                $text = $datetime;
            }
            if (!empty($text)) {
                $result .= wf_tag('div', false, 'pcms_article_date');
                $result .= $text;
                $result .= wf_tag('div', true);
            }
        }
        return ($result);
    }

    /**
     * Public read check: data directories exist and are readable (no mkdir)
     *
     * @return bool
     */
    public function dataPathsExist() {
        $result = false;
        $pagesPath = $this->getPagesPath();
        $articlesPath = $this->getArticlesPath();
        if (file_exists($pagesPath) and is_dir($pagesPath) and is_readable($pagesPath)) {
            if (file_exists($articlesPath) and is_dir($articlesPath) and is_readable($articlesPath)) {
                $result = true;
            }
        }
        return ($result);
    }

    /**
     * Generic public message when data paths are missing
     *
     * @return string
     */
    public function getPublicStorageError() {
        $result = __('CMS data path is not writable');
        return ($result);
    }

    /**
     * Admin-only: create data directories if needed and check write access.
     * On failure sets detailed storageError with paths.
     *
     * @return void
     */
    public function ensurePaths() {
        $this->storageError = '';
        if ($this->prepareDirectory($this->cmsDataPath)) {
            $this->prepareDirectory($this->getPagesPath());
            if (empty($this->storageError)) {
                $this->prepareDirectory($this->getArticlesPath());
            }
            if (empty($this->storageError)) {
                $this->prepareDirectory($this->getDraftsPath());
            }
            if (empty($this->storageError)) {
                $this->prepareDirectory($this->getElementsPath());
            }
        }
    }

    /**
     * Creates a directory if needed and checks write access (admin)
     *
     * @param string $path
     *
     * @return bool
     */
    protected function prepareDirectory($path) {
        $result = false;
        if (file_exists($path)) {
            if (is_dir($path) and is_writable($path)) {
                $result = true;
            } else {
                $this->storageError = __('CMS data path is not writable') . ': ' . $path;
            }
        } else {
            $parentPath = dirname($path);
            $canCreate = false;
            if (file_exists($parentPath) and is_dir($parentPath) and is_writable($parentPath)) {
                $canCreate = true;
            } else {
                if (!file_exists($parentPath)) {
                    $canCreate = true;
                }
            }

            if ($canCreate) {
                $created = @mkdir($path, 0777, true);
                if ($created and is_dir($path) and is_writable($path)) {
                    $result = true;
                } else {
                    $this->storageError = __('Cannot create CMS data path') . ': ' . $path;
                }
            } else {
                $this->storageError = __('CMS data path is not writable') . ': ' . $parentPath;
            }
        }
        return ($result);
    }

    /**
     * Admin write storage ready (after ensurePaths)
     *
     * @return bool
     */
    public function isStorageReady() {
        $result = false;
        $draftsPath = $this->getDraftsPath();
        $elementsPath = $this->getElementsPath();
        if (empty($this->storageError) and $this->dataPathsExist()) {
            if (is_writable($this->getPagesPath()) and is_writable($this->getArticlesPath())) {
                if (file_exists($draftsPath) and is_dir($draftsPath) and is_writable($draftsPath)) {
                    if (file_exists($elementsPath) and is_dir($elementsPath) and is_writable($elementsPath)) {
                        $result = true;
                    }
                }
            }
        }
        return ($result);
    }

    /**
     * Detailed storage error for admin UI (may include paths)
     *
     * @return string
     */
    public function getStorageError() {
        $result = $this->storageError;
        if (empty($result) and !$this->isStorageReady()) {
            $result = __('CMS data path is not writable') . ': ' . $this->cmsDataPath;
        }
        return ($result);
    }

    /**
     * Pages storage directory
     *
     * @return string
     */
    protected function getPagesPath() {
        $result = $this->cmsDataPath . self::SECTION_PAGES . '/';
        return ($result);
    }

    /**
     * Articles storage directory
     *
     * @return string
     */
    protected function getArticlesPath() {
        $result = $this->cmsDataPath . self::SECTION_ARTICLES . '/';
        return ($result);
    }

    /**
     * Drafts storage directory
     *
     * @return string
     */
    protected function getDraftsPath() {
        $result = $this->cmsDataPath . self::SECTION_DRAFTS . '/';
        return ($result);
    }

    /**
     * Elements storage directory
     *
     * @return string
     */
    protected function getElementsPath() {
        $result = $this->cmsDataPath . self::SECTION_ELEMENTS . '/';
        return ($result);
    }

    /**
     * Filters page id to English letters and digits only
     *
     * @param string $pageId
     *
     * @return string
     */
    public function filterPageId($pageId) {
        $result = ubRouting::filters($pageId, 'gigasafe');
        if ($result === false) {
            $result = '';
        }
        $result = basename($result);
        return ($result);
    }

    /**
     * Filters element id to English letters and digits only (same as page id)
     *
     * @param string $elementId
     *
     * @return string
     */
    public function filterElementId($elementId) {
        $result = $this->filterPageId($elementId);
        return ($result);
    }

    /**
     * Filters article id to digits only
     *
     * @param mixed $articleId
     *
     * @return string
     */
    public function filterArticleId($articleId) {
        $result = ubRouting::filters($articleId, 'int');
        if ($result === false) {
            $result = '';
        }
        $result = basename($result);
        return ($result);
    }

    /**
     * Cached file names in a directory (strict basename keys)
     *
     * @param string $dirPath
     *
     * @return array
     */
    protected function getDirectoryFileNames($dirPath) {
        $result = array();
        if (!empty($dirPath)) {
            if (isset($this->dirWhitelistCache[$dirPath])) {
                $result = $this->dirWhitelistCache[$dirPath];
            } else {
                $names = array();
                if (file_exists($dirPath) and is_dir($dirPath) and is_readable($dirPath)) {
                    $files = rcms_scandir($dirPath);
                    if (!empty($files)) {
                        foreach ($files as $io => $eachFile) {
                            if (is_string($eachFile) and ($eachFile !== '') and ($eachFile !== '.') and ($eachFile !== '..')) {
                                if (is_file($dirPath . $eachFile)) {
                                    $names[$eachFile] = $eachFile;
                                }
                            }
                        }
                    }
                }
                $this->dirWhitelistCache[$dirPath] = $names;
                $result = $names;
            }
        }
        return ($result);
    }

    /**
     * Drops cached scandir whitelist for a directory
     *
     * @param string $dirPath
     *
     * @return void
     */
    protected function invalidateDirectoryWhitelist($dirPath) {
        if (!empty($dirPath) and isset($this->dirWhitelistCache[$dirPath])) {
            unset($this->dirWhitelistCache[$dirPath]);
        }
    }

    /**
     * Path to an existing file only if filtered id is in directory scandir whitelist (as-is).
     * Uses the name from the scan list for the final path segment.
     *
     * @param string $dirPath
     * @param string $filteredId
     *
     * @return string
     */
    protected function resolveWhitelistedPath($dirPath, $filteredId) {
        $result = '';
        if (!empty($dirPath) and ($filteredId !== '') and ($filteredId !== null) and ($filteredId !== false)) {
            $names = $this->getDirectoryFileNames($dirPath);
            if (isset($names[$filteredId])) {
                $result = $dirPath . $names[$filteredId];
            }
        }
        return ($result);
    }

    /**
     * Absolute path to a page file if id is valid
     *
     * @param string $pageId
     *
     * @return string
     */
    protected function getPageFilePath($pageId) {
        $result = '';
        $pageId = $this->filterPageId($pageId);
        if (!empty($pageId)) {
            $result = $this->getPagesPath() . $pageId;
        }
        return ($result);
    }

    /**
     * Absolute path to an element file if id is valid
     *
     * @param string $elementId
     *
     * @return string
     */
    protected function getElementFilePath($elementId) {
        $result = '';
        $elementId = $this->filterElementId($elementId);
        if (!empty($elementId)) {
            $result = $this->getElementsPath() . $elementId;
        }
        return ($result);
    }

    /**
     * Absolute path to an article file if id is valid
     *
     * @param mixed $articleId
     *
     * @return string
     */
    protected function getArticleFilePath($articleId) {
        $result = '';
        $articleId = $this->filterArticleId($articleId);
        if (!empty($articleId)) {
            $result = $this->getArticlesPath() . $articleId;
        }
        return ($result);
    }

    /**
     * Absolute path to a draft file if id is valid
     *
     * @param mixed $draftId
     *
     * @return string
     */
    protected function getDraftFilePath($draftId) {
        $result = '';
        $draftId = $this->filterArticleId($draftId);
        if (!empty($draftId)) {
            $result = $this->getDraftsPath() . $draftId;
        }
        return ($result);
    }

    /**
     * Encodes entity data as JSON string
     *
     * @param array $data
     *
     * @return string|false
     */
    protected function encodeData($data) {
        $result = false;
        if (is_array($data)) {
            if (defined('JSON_UNESCAPED_UNICODE')) {
                $result = json_encode($data, JSON_UNESCAPED_UNICODE);
            } else {
                $result = json_encode($data);
            }
        }
        return ($result);
    }

    /**
     * Decodes JSON entity data into array
     *
     * @param string $raw
     *
     * @return array|false
     */
    protected function decodeData($raw) {
        $result = false;
        if ($raw !== false and $raw !== null and $raw !== '') {
            $data = json_decode($raw, true);
            if (is_array($data) and isset($data['id'])) {
                $result = $data;
            }
        }
        return ($result);
    }

    /**
     * Loads page data by id (only if id is in pages/ scandir whitelist)
     *
     * @param string $pageId
     *
     * @return array|false
     */
    public function getPage($pageId) {
        $result = false;
        $pageId = $this->filterPageId($pageId);
        if (!empty($pageId)) {
            $filePath = $this->resolveWhitelistedPath($this->getPagesPath(), $pageId);
            if (!empty($filePath) and is_readable($filePath)) {
                $raw = @file_get_contents($filePath);
                $result = $this->decodeData($raw);
            }
        }
        return ($result);
    }

    /**
     * Loads element data by id (only if id is in elements/ scandir whitelist)
     *
     * @param string $elementId
     *
     * @return array|false
     */
    public function getElement($elementId) {
        $result = false;
        $elementId = $this->filterElementId($elementId);
        if (!empty($elementId)) {
            $filePath = $this->resolveWhitelistedPath($this->getElementsPath(), $elementId);
            if (!empty($filePath) and is_readable($filePath)) {
                $raw = @file_get_contents($filePath);
                $result = $this->decodeData($raw);
            }
        }
        return ($result);
    }

    /**
     * Loads article data by id (only if id is in articles/ scandir whitelist)
     *
     * @param mixed $articleId
     *
     * @return array|false
     */
    public function getArticle($articleId) {
        $result = false;
        $articleId = $this->filterArticleId($articleId);
        if (!empty($articleId)) {
            $filePath = $this->resolveWhitelistedPath($this->getArticlesPath(), $articleId);
            if (!empty($filePath) and is_readable($filePath)) {
                $raw = @file_get_contents($filePath);
                $result = $this->decodeData($raw);
            }
        }
        return ($result);
    }

    /**
     * Loads draft data by id
     *
     * @param mixed $draftId
     *
     * @return array|false
     */
    public function getDraft($draftId) {
        $result = false;
        $filePath = $this->getDraftFilePath($draftId);
        if (!empty($filePath) and file_exists($filePath)) {
            $raw = @file_get_contents($filePath);
            $result = $this->decodeData($raw);
        }
        return ($result);
    }

    /**
     * Lists all pages (id => data), sorted by id
     *
     * @return array
     */
    public function listPages() {
        $result = array();
        $pagesPath = $this->getPagesPath();
        if (file_exists($pagesPath) and is_dir($pagesPath) and is_readable($pagesPath)) {
            $files = rcms_scandir($pagesPath);
            if (!empty($files)) {
                foreach ($files as $io => $eachFile) {
                    $page = $this->getPage($eachFile);
                    if (!empty($page)) {
                        $result[$page['id']] = $page;
                    }
                }
                ksort($result);
            }
        }
        return ($result);
    }

    /**
     * Lists all elements (id => data), sorted by id
     *
     * @return array
     */
    public function listElements() {
        $result = array();
        $elementsPath = $this->getElementsPath();
        if (file_exists($elementsPath) and is_dir($elementsPath) and is_readable($elementsPath)) {
            $files = rcms_scandir($elementsPath);
            if (!empty($files)) {
                foreach ($files as $io => $eachFile) {
                    $element = $this->getElement($eachFile);
                    if (!empty($element)) {
                        $result[$element['id']] = $element;
                    }
                }
                ksort($result);
            }
        }
        return ($result);
    }

    /**
     * Lists all articles sorted by datetime descending
     *
     * @return array
     */
    public function listArticles() {
        $result = array();
        $articlesPath = $this->getArticlesPath();
        if (file_exists($articlesPath) and is_dir($articlesPath) and is_readable($articlesPath)) {
            $files = rcms_scandir($articlesPath);
            if (!empty($files)) {
                foreach ($files as $io => $eachFile) {
                    $article = $this->getArticle($eachFile);
                    if (!empty($article)) {
                        $result[$article['id']] = $article;
                    }
                }
                uasort($result, array($this, 'compareArticlesByDatetime'));
            }
        }
        return ($result);
    }

    /**
     * Lists all drafts sorted by datetime descending
     *
     * @return array
     */
    public function listDrafts() {
        $result = array();
        $draftsPath = $this->getDraftsPath();
        if (file_exists($draftsPath) and is_dir($draftsPath) and is_readable($draftsPath)) {
            $files = rcms_scandir($draftsPath);
            if (!empty($files)) {
                foreach ($files as $io => $eachFile) {
                    $draft = $this->getDraft($eachFile);
                    if (!empty($draft)) {
                        $result[$draft['id']] = $draft;
                    }
                }
                uasort($result, array($this, 'compareArticlesByDatetime'));
            }
        }
        return ($result);
    }

    /**
     * uasort callback: newer datetime first
     *
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    protected function compareArticlesByDatetime($a, $b) {
        $result = 0;
        $da = isset($a['datetime']) ? $a['datetime'] : '';
        $db = isset($b['datetime']) ? $b['datetime'] : '';
        if ($da == $db) {
            $result = 0;
        } else {
            if ($da < $db) {
                $result = 1;
            } else {
                $result = -1;
            }
        }
        return ($result);
    }

    /**
     * Next free numeric article/draft id (max across articles and drafts)
     *
     * @return int
     */
    protected function getNextArticleId() {
        $result = 1;
        $maxId = 0;
        $paths = array($this->getArticlesPath(), $this->getDraftsPath());
        foreach ($paths as $io => $scanPath) {
            if (file_exists($scanPath) and is_dir($scanPath) and is_readable($scanPath)) {
                $files = rcms_scandir($scanPath);
                if (!empty($files)) {
                    foreach ($files as $fileIo => $eachFile) {
                        $id = $this->filterArticleId($eachFile);
                        if (!empty($id) and ((int) $id > $maxId)) {
                            $maxId = (int) $id;
                        }
                    }
                }
            }
        }
        $result = $maxId + 1;
        return ($result);
    }

    /**
     * Saves page array to disk
     *
     * @param array $pageData
     * @param bool $allowOverwrite false = create only, refuse existing id
     *
     * @return string empty on success, error message otherwise
     */
    public function savePageData($pageData, $allowOverwrite = true) {
        $result = '';
        if (!$this->isStorageReady()) {
            $result = $this->getStorageError();
        } else {
            if (is_array($pageData) and isset($pageData['id'])) {
                $pageId = $this->filterPageId($pageData['id']);
                if (!empty($pageId)) {
                    $pageData['id'] = $pageId;
                    if (!isset($pageData['title'])) {
                        $pageData['title'] = '';
                    }
                    if (!isset($pageData['content'])) {
                        $pageData['content'] = '';
                    }
                    $filePath = $this->getPageFilePath($pageId);
                    if (!$allowOverwrite and file_exists($filePath)) {
                        $result = __('Page already exists');
                    } else {
                        if ($allowOverwrite and !file_exists($filePath)) {
                            $result = __('Page not found');
                        } else {
                            $jsonData = $this->encodeData($pageData);
                            if ($jsonData === false) {
                                $result = __('Empty page ID');
                            } else {
                                $written = @file_put_contents($filePath, $jsonData);
                                if ($written === false) {
                                    $result = __('CMS data path is not writable') . ': ' . $filePath;
                                } else {
                                    $this->invalidateDirectoryWhitelist($this->getPagesPath());
                                }
                            }
                        }
                    }
                } else {
                    $result = __('Empty page ID');
                }
            } else {
                $result = __('Empty page ID');
            }
        }
        return ($result);
    }

    /**
     * Saves element array to disk
     *
     * @param array $elementData
     * @param bool $allowOverwrite false = create only, refuse existing id
     *
     * @return string empty on success, error message otherwise
     */
    public function saveElementData($elementData, $allowOverwrite = true) {
        $result = '';
        if (!$this->isStorageReady()) {
            $result = $this->getStorageError();
        } else {
            if (is_array($elementData) and isset($elementData['id'])) {
                $elementId = $this->filterElementId($elementData['id']);
                if (!empty($elementId)) {
                    $elementData['id'] = $elementId;
                    if (!isset($elementData['content'])) {
                        $elementData['content'] = '';
                    }
                    $filePath = $this->getElementFilePath($elementId);
                    if (!$allowOverwrite and file_exists($filePath)) {
                        $result = __('Element already exists');
                    } else {
                        if ($allowOverwrite and !file_exists($filePath)) {
                            $result = __('Element not found');
                        } else {
                            $jsonData = $this->encodeData($elementData);
                            if ($jsonData === false) {
                                $result = __('Empty element ID');
                            } else {
                                $written = @file_put_contents($filePath, $jsonData);
                                if ($written === false) {
                                    $result = __('CMS data path is not writable') . ': ' . $filePath;
                                } else {
                                    $this->invalidateDirectoryWhitelist($this->getElementsPath());
                                }
                            }
                        }
                    }
                } else {
                    $result = __('Empty element ID');
                }
            } else {
                $result = __('Empty element ID');
            }
        }
        return ($result);
    }

    /**
     * Normalizes article/draft fields before write
     *
     * @param array $articleData
     *
     * @return array|string array on success, error string otherwise
     */
    protected function normalizeArticleData($articleData) {
        $result = '';
        if (is_array($articleData) and isset($articleData['id'])) {
            $articleId = $this->filterArticleId($articleData['id']);
            if (!empty($articleId)) {
                $articleData['id'] = $articleId;
                if (!isset($articleData['title'])) {
                    $articleData['title'] = '';
                }
                if (!isset($articleData['short'])) {
                    $articleData['short'] = '';
                }
                if (!isset($articleData['full'])) {
                    $articleData['full'] = '';
                }
                if (!isset($articleData['datetime']) or empty($articleData['datetime'])) {
                    $articleData['datetime'] = date('Y-m-d H:i:s');
                }
                if (!isset($articleData['type']) or ($articleData['type'] != self::TYPE_HTML and $articleData['type'] != self::TYPE_TEXT)) {
                    $articleData['type'] = self::TYPE_TEXT;
                }
                $result = $articleData;
            } else {
                $result = __('Empty article ID');
            }
        } else {
            $result = __('Empty article ID');
        }
        return ($result);
    }

    /**
     * Writes normalized article/draft JSON to a file path
     *
     * @param array $articleData
     * @param string $filePath
     *
     * @return string empty on success, error message otherwise
     */
    protected function writeArticleFile($articleData, $filePath) {
        $result = '';
        $jsonData = $this->encodeData($articleData);
        if ($jsonData === false) {
            $result = __('Empty article ID');
        } else {
            $written = @file_put_contents($filePath, $jsonData);
            if ($written === false) {
                $result = __('CMS data path is not writable') . ': ' . $filePath;
            }
        }
        return ($result);
    }

    /**
     * Saves article array to disk
     *
     * @param array $articleData
     *
     * @return string empty on success, error message otherwise
     */
    public function saveArticleData($articleData) {
        $result = '';
        if (!$this->isStorageReady()) {
            $result = $this->getStorageError();
        } else {
            $normalized = $this->normalizeArticleData($articleData);
            if (is_array($normalized)) {
                $filePath = $this->getArticleFilePath($normalized['id']);
                $result = $this->writeArticleFile($normalized, $filePath);
                if (empty($result)) {
                    $this->invalidateDirectoryWhitelist($this->getArticlesPath());
                }
            } else {
                $result = $normalized;
            }
        }
        return ($result);
    }

    /**
     * Saves draft array to disk
     *
     * @param array $articleData
     *
     * @return string empty on success, error message otherwise
     */
    public function saveDraftData($articleData) {
        $result = '';
        if (!$this->isStorageReady()) {
            $result = $this->getStorageError();
        } else {
            $normalized = $this->normalizeArticleData($articleData);
            if (is_array($normalized)) {
                $filePath = $this->getDraftFilePath($normalized['id']);
                $result = $this->writeArticleFile($normalized, $filePath);
            } else {
                $result = $normalized;
            }
        }
        return ($result);
    }

    /**
     * Deletes a page by id
     *
     * @param string $pageId
     *
     * @return string empty on success, error message otherwise
     */
    public function deletePage($pageId) {
        $result = '';
        if (!$this->isStorageReady()) {
            $result = $this->getStorageError();
        } else {
            $filePath = $this->getPageFilePath($pageId);
            if (!empty($filePath) and file_exists($filePath)) {
                if (is_writable($filePath) and @unlink($filePath)) {
                    $this->invalidateDirectoryWhitelist($this->getPagesPath());
                    log_register('PCMS PAGE DELETE {' . $pageId . '}');
                } else {
                    $result = __('CMS data path is not writable') . ': ' . $filePath;
                }
            }
        }
        return ($result);
    }

    /**
     * Deletes an element by id
     *
     * @param string $elementId
     *
     * @return string empty on success, error message otherwise
     */
    public function deleteElement($elementId) {
        $result = '';
        if (!$this->isStorageReady()) {
            $result = $this->getStorageError();
        } else {
            $filePath = $this->getElementFilePath($elementId);
            if (!empty($filePath) and file_exists($filePath)) {
                if (is_writable($filePath) and @unlink($filePath)) {
                    $this->invalidateDirectoryWhitelist($this->getElementsPath());
                    log_register('PCMS ELEMENT DELETE {' . $elementId . '}');
                } else {
                    $result = __('CMS data path is not writable') . ': ' . $filePath;
                }
            }
        }
        return ($result);
    }

    /**
     * Deletes an article by id
     *
     * @param mixed $articleId
     *
     * @return string empty on success, error message otherwise
     */
    public function deleteArticle($articleId) {
        $result = '';
        if (!$this->isStorageReady()) {
            $result = $this->getStorageError();
        } else {
            $filePath = $this->getArticleFilePath($articleId);
            if (!empty($filePath) and file_exists($filePath)) {
                if (is_writable($filePath) and @unlink($filePath)) {
                    $this->invalidateDirectoryWhitelist($this->getArticlesPath());
                    log_register('PCMS ARTICLE DELETE {' . $articleId . '}');
                } else {
                    $result = __('CMS data path is not writable') . ': ' . $filePath;
                }
            }
        }
        return ($result);
    }

    /**
     * Deletes a draft by id
     *
     * @param mixed $draftId
     *
     * @return string empty on success, error message otherwise
     */
    public function deleteDraft($draftId) {
        $result = '';
        if (!$this->isStorageReady()) {
            $result = $this->getStorageError();
        } else {
            $filePath = $this->getDraftFilePath($draftId);
            if (!empty($filePath) and file_exists($filePath)) {
                if (is_writable($filePath) and @unlink($filePath)) {
                    log_register('PCMS DRAFT DELETE {' . $draftId . '}');
                } else {
                    $result = __('CMS data path is not writable') . ': ' . $filePath;
                }
            }
        }
        return ($result);
    }

    /**
     * Moves published article into drafts
     *
     * @param mixed $articleId
     *
     * @return string empty on success, error message otherwise
     */
    public function moveArticleToDraft($articleId) {
        $result = '';
        $articleId = $this->filterArticleId($articleId);
        if (!empty($articleId)) {
            $article = $this->getArticle($articleId);
            if (!empty($article)) {
                $draftPath = $this->getDraftFilePath($articleId);
                if (file_exists($draftPath)) {
                    $result = __('Draft already exists');
                } else {
                    $result = $this->saveDraftData($article);
                    if (empty($result)) {
                        $result = $this->deleteArticle($articleId);
                        if (empty($result)) {
                            log_register('PCMS ARTICLE TO DRAFT {' . $articleId . '}');
                        }
                    }
                }
            } else {
                $result = __('Article not found');
            }
        } else {
            $result = __('Empty article ID');
        }
        return ($result);
    }

    /**
     * Moves draft into published articles
     *
     * @param mixed $draftId
     *
     * @return string empty on success, error message otherwise
     */
    public function moveDraftToArticle($draftId) {
        $result = '';
        $draftId = $this->filterArticleId($draftId);
        if (!empty($draftId)) {
            $draft = $this->getDraft($draftId);
            if (!empty($draft)) {
                $articlePath = $this->getArticleFilePath($draftId);
                if (file_exists($articlePath)) {
                    $result = __('Article already exists');
                } else {
                    $result = $this->saveArticleData($draft);
                    if (empty($result)) {
                        $result = $this->deleteDraft($draftId);
                        if (empty($result)) {
                            log_register('PCMS DRAFT PUBLISH {' . $draftId . '}');
                        }
                    }
                }
            } else {
                $result = __('Draft not found');
            }
        } else {
            $result = __('Empty article ID');
        }
        return ($result);
    }


    /**
     * Parses BBCode for article type=text
     * Supports: b i u s url img list [*] code hr
     *
     * @param string $text
     *
     * @return string
     */
    protected function parseBbCode($text) {
        $result = '';
        $this->bbCodePlaceholders = array();

        if ($text !== null and $text !== false) {
            $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

            $text = preg_replace_callback('#\[code\](.*?)\[/code\]#is', array($this, 'bbCodeExtractCode'), $text);
            $text = preg_replace_callback('#\[list\](.*?)\[/list\]#is', array($this, 'bbCodeParseList'), $text);

            $simpleTags = array(
                '#\[b\](.*?)\[/b\]#is' => '<strong>\\1</strong>',
                '#\[i\](.*?)\[/i\]#is' => '<em>\\1</em>',
                '#\[u\](.*?)\[/u\]#is' => '<span style="text-decoration:underline">\\1</span>',
                '#\[s\](.*?)\[/s\]#is' => '<del>\\1</del>',
                '#\[url\]\s*((https?)://[^\s<]+?)\s*\[/url\]#is' => '<a class="pcms_article_link" href="\\1" target="_blank" rel="noopener noreferrer">\\1</a>',
                '#\[url=(&quot;|"|)((https?)://[^\s<]+?)(&quot;|"|)\](.*?)\[/url\]#is' => '<a class="pcms_article_link" href="\\2" target="_blank" rel="noopener noreferrer">\\5</a>',
                '#\[img\]\s*((https?)://[^\s<]+?\.(gif|png|jpe?g|webp))\s*\[/img\]#is' => '<img class="pcms_article_image" src="\\1" alt="" />',
                '#\[hr\]#i' => '<hr class="pcms_article_hr" />'
            );

            $pass = 0;
            while ($pass < 3) {
                $text = preg_replace(array_keys($simpleTags), array_values($simpleTags), $text);
                $pass++;
            }

            $text = nl2br($text);
            $text = preg_replace('#(<br\s*/?>\s*)+(<(ul|li|pre|hr|/ul|/li|/pre)[^>]*>)#i', '\\2', $text);
            $text = preg_replace('#(<(ul|li|pre|hr|/ul|/li|/pre)[^>]*>)(\s*<br\s*/?>)+#i', '\\1', $text);

            if (!empty($this->bbCodePlaceholders)) {
                foreach ($this->bbCodePlaceholders as $placeholder => $codeHtml) {
                    $text = str_replace($placeholder, $codeHtml, $text);
                }
            }

            $result = $text;
        }

        $this->bbCodePlaceholders = array();
        return ($result);
    }

    /**
     * Extracts [code] body into a placeholder
     *
     * @param array $matches
     *
     * @return string
     */
    protected function bbCodeExtractCode($matches) {
        $result = '';
        $placeholder = '{PCMSBB:' . md5($matches[1] . sizeof($this->bbCodePlaceholders)) . '}';
        $this->bbCodePlaceholders[$placeholder] = '<pre class="pcms_article_code"><code>' . $matches[1] . '</code></pre>';
        $result = $placeholder;
        return ($result);
    }

    /**
     * Converts [list][*]item[/list] into unordered list HTML
     *
     * @param array $matches
     *
     * @return string
     */
    protected function bbCodeParseList($matches) {
        $result = '<ul class="pcms_article_list">';
        $parts = preg_split('#\[\*\]#', $matches[1]);
        if (!empty($parts)) {
            foreach ($parts as $io => $item) {
                $item = trim($item);
                if ($item !== '') {
                    $result .= '<li>' . $item . '</li>';
                }
            }
        }
        $result .= '</ul>';
        return ($result);
    }

    /**
     * Renders body by content type
     *
     * @param string $text
     * @param string $type
     *
     * @return string
     */
    public function renderBody($text, $type) {
        $result = '';
        if ($type == self::TYPE_HTML) {
            $result = $text;
        } else {
            $result = $this->parseBbCode($text);
        }
        return ($result);
    }

    /**
     * Public page view HTML
     *
     * @param string $pageId
     *
     * @return string
     */
    public function renderPage($pageId) {
        $result = '';
        $pageId = $this->filterPageId($pageId);
        if (!empty($pageId)) {
            $page = $this->getPage($pageId);
            if (!empty($page)) {
                $result .= wf_tag('div', false, 'pcms_page');
                $result .= wf_tag('div', false, 'pcms_page_body');
                $result .= $page['content'];
                $result .= wf_tag('div', true);
                $result .= wf_tag('div', true);
            } else {
                $result .= $this->messages->getStyledMessage(__('Page not found'), 'error');
            }
        } else {
            $result .= $this->messages->getStyledMessage(__('Empty page ID'), 'error');
        }
        return ($result);
    }

    /**
     * Prints element content as HTML (for modules / site template). No public GET route.
     *
     * @param string $elementId
     *
     * @return string always empty (content is printed)
     */
    public function renderElement($elementId) {
        $result = '';
        $elementId = $this->filterElementId($elementId);
        if (!empty($elementId)) {
            $element = $this->getElement($elementId);
            if (!empty($element) and isset($element['content'])) {
                print($element['content']);
            }
        }
        return ($result);
    }

    /**
     * Runs element content as PHP via eval (for modules / site template). No public GET route.
     * Content should be PHP code without opening <?php tags.
     *
     * @param string $elementId
     *
     * @return mixed eval return value, or false if element missing
     */
    public function runElement($elementId) {
        $result = false;
        $elementId = $this->filterElementId($elementId);
        if (!empty($elementId)) {
            $element = $this->getElement($elementId);
            if (!empty($element) and isset($element['content'])) {
                $result = eval($element['content']);
            }
        }
        return ($result);
    }

    /**
     * Public single article view
     *
     * @param mixed $articleId
     *
     * @return string
     */
    public function renderArticle($articleId) {
        $result = '';
        $articleId = $this->filterArticleId($articleId);
        if (!empty($articleId)) {
            $article = $this->getArticle($articleId);
            if (!empty($article)) {
                $result .= wf_tag('div', false, 'pcms_article');
                $result .= wf_tag('div', false, 'pcms_article_body');
                $result .= $this->renderBody($article['full'], $article['type']);
                $result .= wf_tag('div', true);
                $result .= $this->renderArticleDate($article['datetime']);
                $result .= wf_delimiter(0);
                $result .= wf_BackLink(self::URL_ARTICLES);
                $result .= wf_tag('div', true);
            } else {
                $result .= $this->messages->getStyledMessage(__('Article not found'), 'error');
            }
        } else {
            $result .= $this->messages->getStyledMessage(__('Empty article ID'), 'error');
        }
        return ($result);
    }

    /**
     * Public articles feed with pagination
     *
     * @param int $pageNum
     *
     * @return string
     */
    public function renderArticlesFeed($pageNum) {
        $result = '';
        $pageNum = ubRouting::filters($pageNum, 'int');
        if (empty($pageNum) or ((int) $pageNum < 1)) {
            $pageNum = 1;
        } else {
            $pageNum = (int) $pageNum;
        }

        $result .= wf_tag('div', false, 'pcms_feed');
        $allArticles = $this->listArticles();
        $total = sizeof($allArticles);
        if ($total > 0) {
            $perPage = $this->articlesPerPage;
            if (empty($perPage)) {
                $perPage = 10;
            }
            $maxPage = (int) ceil($total / $perPage);
            if ($pageNum > $maxPage) {
                $pageNum = $maxPage;
            }
            $offset = ($pageNum - 1) * $perPage;
            $slice = array_slice($allArticles, $offset, $perPage, true);
            if (!empty($slice)) {
                foreach ($slice as $io => $article) {
                    $result .= wf_tag('div', false, 'pcms_feed_item');
                    $titleLink = wf_Link(self::URL_ARTICLES . '&' . self::ROUTE_ARTICLE . '=' . $article['id'], $article['title']);
                    $result .= wf_tag('h3', false, 'pcms_feed_title') . $titleLink . wf_tag('h3', true);
                    $result .= wf_tag('div', false, 'pcms_feed_excerpt');
                    $result .= $this->renderBody($article['short'], $article['type']);
                    $result .= wf_tag('div', true);
                    $result .= $this->renderArticleDate($article['datetime']);
                    $result .= wf_tag('div', true);
                }
            } else {
                $result .= $this->messages->getStyledMessage(__('Nothing to show'), 'info');
            }
            $result .= wf_delimiter(0);
            $result .= wf_tag('div', false, 'pcms_pager');
            $result .= wf_pagination($total, $perPage, $pageNum, self::URL_ARTICLES, 'pcms_pager_link');
            $result .= wf_tag('div', true);
        } else {
            $result .= $this->messages->getStyledMessage(__('Nothing to show'), 'info');
        }
        $result .= wf_tag('div', true);
        return ($result);
    }

    /**
     * Recent module widget: latest N articles (PCMS_RECENT_COUNT or $limit)
     *
     * @param int $limit 0 = use PCMS_RECENT_COUNT; empty config = no output
     *
     * @return string
     */
    public function renderRecentArticles($limit = 0) {
        $result = '';
        $limit = ubRouting::filters($limit, 'int');
        if (empty($limit) or ((int) $limit < 1)) {
            $limit = $this->recentCount;
        } else {
            $limit = (int) $limit;
        }
        if (!empty($limit)) {
            $result .= wf_tag('div', false, 'pcms_recent');
            $allArticles = $this->listArticles();
            if (!empty($allArticles)) {
                $slice = array_slice($allArticles, 0, $limit, true);
                if (!empty($slice)) {
                    foreach ($slice as $io => $article) {
                        $result .= wf_tag('div', false, 'pcms_feed_item');
                        $titleLink = wf_Link(self::URL_ARTICLES . '&' . self::ROUTE_ARTICLE . '=' . $article['id'], $article['title']);
                        $result .= wf_tag('h3', false, 'pcms_feed_title') . $titleLink . wf_tag('h3', true);
                        $result .= wf_tag('div', false, 'pcms_feed_excerpt');
                        $result .= $this->renderBody($article['short'], $article['type']);
                        $result .= wf_tag('div', true);
                        $result .= $this->renderArticleDate($article['datetime']);
                        $result .= wf_tag('div', true);
                    }
                }
            } else {
                $result .= $this->messages->getStyledMessage(__('Nothing to show'), 'info');
            }
            $result .= wf_tag('div', false, 'pcms_recent_more');
            $result .= wf_Link(self::URL_ARTICLES, __('More').'...');
            $result .= wf_tag('div', true);
            $result .= wf_tag('div', true);
        }
        return ($result);
    }

    /**
     * Builds RSS 2.0 XML for published articles
     *
     * @return string
     */
    public function renderRssXml() {
        $result = '';
        $channelTitle = $this->getArticlesModuleName();
        $channelLink = $this->buildAbsoluteUrl(self::URL_ARTICLES);
        $channelDesc = $channelTitle;
        $base = $this->getSiteBaseUrl();
        if (!empty($base)) {
            $channelDesc = $base;
        }

        $result .= '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $result .= '<rss version="2.0">' . PHP_EOL;
        $result .= '<channel>' . PHP_EOL;
        $result .= '<title>' . $this->xmlEscape($channelTitle) . '</title>' . PHP_EOL;
        $result .= '<link>' . $this->xmlEscape($channelLink) . '</link>' . PHP_EOL;
        $result .= '<description>' . $this->xmlEscape($channelDesc) . '</description>' . PHP_EOL;

        $allArticles = $this->listArticles();
        if (!empty($allArticles)) {
            $rssLimit = $this->articlesPerPage;
            if (empty($rssLimit)) {
                $rssLimit = 10;
            }
            $slice = array_slice($allArticles, 0, $rssLimit, true);
            if (!empty($slice)) {
                foreach ($slice as $io => $article) {
                    $itemLink = $this->buildAbsoluteUrl(self::URL_ARTICLES . '&' . self::ROUTE_ARTICLE . '=' . $article['id']);
                    $itemTitle = isset($article['title']) ? $article['title'] : '';
                    $itemDesc = isset($article['short']) ? $article['short'] : '';
                    if ($article['type'] == self::TYPE_TEXT) {
                        $itemDesc = strip_tags($this->parseBbCode($itemDesc));
                    } else {
                        $itemDesc = strip_tags($itemDesc);
                    }
                    $pubDate = '';
                    if (!empty($article['datetime'])) {
                        $ts = strtotime($article['datetime']);
                        if ($ts) {
                            $pubDate = date('r', $ts);
                        }
                    }
                    $result .= '<item>' . PHP_EOL;
                    $result .= '<title>' . $this->xmlEscape($itemTitle) . '</title>' . PHP_EOL;
                    $result .= '<link>' . $this->xmlEscape($itemLink) . '</link>' . PHP_EOL;
                    $result .= '<guid isPermaLink="true">' . $this->xmlEscape($itemLink) . '</guid>' . PHP_EOL;
                    $result .= '<description>' . $this->xmlEscape($itemDesc) . '</description>' . PHP_EOL;
                    if (!empty($pubDate)) {
                        $result .= '<pubDate>' . $this->xmlEscape($pubDate) . '</pubDate>' . PHP_EOL;
                    }
                    $result .= '</item>' . PHP_EOL;
                }
            }
        }

        $result .= '</channel>' . PHP_EOL;
        $result .= '</rss>';
        return ($result);
    }

    /**
     * Escapes text for XML element content
     *
     * @param string $text
     *
     * @return string
     */
    protected function xmlEscape($text) {
        $result = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        return ($result);
    }

    /**
     * Sends RSS feed headers and body, then stops script (skips skin)
     *
     * @return void
     */
    public function flushRssFeed() {
        $xml = $this->renderRssXml();
        header('Content-Type: application/rss+xml; charset=UTF-8');
        die($xml);
    }

    /**
     * BBCode insert script (once per page)
     *
     * @return string
     */
    protected function renderBbCodeToolbarScript() {
        $result = '';
        if (!$this->bbToolbarScriptDone) {
            $this->bbToolbarScriptDone = true;
            $result .= wf_tag('script', false, '', 'type="text/javascript"');
            $result .= 'function pcmsBbInsert(id,openTag,closeTag){';
            $result .= 'var el=document.getElementById(id);if(!el){return;}el.focus();';
            $result .= 'if(typeof el.selectionStart==="undefined"){el.value=el.value+openTag+closeTag;return;}';
            $result .= 'var s=el.selectionStart,e=el.selectionEnd,sel=el.value.substring(s,e);';
            $result .= 'el.value=el.value.substring(0,s)+openTag+sel+closeTag+el.value.substring(e);';
            $result .= 'var p=s+openTag.length+sel.length;el.selectionStart=el.selectionEnd=p;';
            $result .= '}';
            $result .= wf_tag('script', true);
        }
        return ($result);
    }

    /**
     * BBCode toolbar buttons for a textarea id (admin forms)
     *
     * @param string $textareaId
     *
     * @return string
     */
    protected function renderBbCodeToolbar($textareaId) {
        $result = '';
        $textareaId = preg_replace('/[^a-zA-Z0-9_]/', '', $textareaId);
        if (!empty($textareaId)) {
            $result .= $this->renderBbCodeToolbarScript();
            $result .= wf_tag('div', false, 'pcms_bb_toolbar', 'style="margin:0.25em 0;"');
            $buttons = array(
                array('[b]', '[/b]', 'B'),
                array('[i]', '[/i]', 'I'),
                array('[u]', '[/u]', 'U'),
                array('[s]', '[/s]', 'S'),
                array('[url]', '[/url]', 'URL'),
                array('[img]', '[/img]', 'IMG'),
                array("[list]\n[*]", "\n[/list]", 'LIST'),
                array('[*]', '', '*'),
                array('[code]', '[/code]', 'CODE'),
                array('[hr]', '', 'HR')
            );
            foreach ($buttons as $io => $btn) {
                $openJs = str_replace(array('\\', "'", "\n", "\r"), array('\\\\', "\\'", '\\n', ''), $btn[0]);
                $closeJs = str_replace(array('\\', "'", "\n", "\r"), array('\\\\', "\\'", '\\n', ''), $btn[1]);
                $label = htmlspecialchars($btn[2], ENT_QUOTES, 'UTF-8');
                $result .= '<button type="button" class="pcms_bb_btn" style="margin:0 0.15em 0.15em 0;" onclick="pcmsBbInsert(\'' . $textareaId . '\',\'' . $openJs . '\',\'' . $closeJs . '\');return false;">' . $label . '</button>';
            }
            $result .= wf_tag('div', true);
        }
        return ($result);
    }

    /**
     * Creates media directory and default .htaccess when missing
     *
     * @return string empty on success, error message otherwise
     */
    public function ensureMediaPath() {
        $result = '';
        if ($this->isMediaEnabled()) {
            $path = $this->mediaPath;
            if (file_exists($path)) {
                if (!is_dir($path) or !is_writable($path)) {
                    $result = __('CMS data path is not writable') . ': ' . $path;
                }
            } else {
                $created = @mkdir($path, 0777, true);
                if (!$created or !is_dir($path) or !is_writable($path)) {
                    $result = __('Cannot create CMS data path') . ': ' . $path;
                }
            }
            if (empty($result)) {
                $this->ensureMediaHtaccess();
            }
        } else {
            $result = __('Media uploads disabled');
        }
        return ($result);
    }

    /**
     * Writes default media .htaccess (allow + -Indexes) if file is missing
     *
     * @return void
     */
    protected function ensureMediaHtaccess() {
        if ($this->isMediaEnabled()) {
            $htPath = $this->mediaPath . '.htaccess';
            if (!file_exists($htPath)) {
                $content = 'Options -Indexes' . PHP_EOL;
                $content .= 'Allow from all' . PHP_EOL;
                @file_put_contents($htPath, $content);
            }
        }
    }

    /**
     * Safe media file name (basename, allowed chars + extension)
     *
     * @param mixed $fileName
     *
     * @return string
     */
    public function filterMediaFileName($fileName) {
        $result = '';
        if (!empty($fileName) and is_string($fileName)) {
            $fileName = str_replace('\\', '/', $fileName);
            $fileName = basename($fileName);
            if (preg_match('/^[a-zA-Z0-9._-]+$/', $fileName)) {
                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                if (!empty($ext) and in_array($ext, $this->mediaAllowed)) {
                    $result = $fileName;
                }
            }
        }
        return ($result);
    }

    /**
     * Full filesystem path for a media file name
     *
     * @param string $fileName
     *
     * @return string
     */
    protected function getMediaFileFullPath($fileName) {
        $result = '';
        $fileName = $this->filterMediaFileName($fileName);
        if ($this->isMediaEnabled() and !empty($fileName)) {
            $result = $this->mediaPath . $fileName;
        }
        return ($result);
    }

    /**
     * Confirms resolved path stays inside media directory
     *
     * @param string $fullPath
     *
     * @return bool
     */
    protected function isPathInsideMedia($fullPath) {
        $result = false;
        if ($this->isMediaEnabled() and !empty($fullPath)) {
            $mediaReal = realpath($this->mediaPath);
            $fileReal = realpath($fullPath);
            if ($mediaReal and $fileReal) {
                $mediaReal = str_replace('\\', '/', $mediaReal);
                $fileReal = str_replace('\\', '/', $fileReal);
                $prefix = rtrim($mediaReal, '/') . '/';
                if (strpos($fileReal, $prefix) === 0) {
                    $result = true;
                } else {
                    if ($fileReal == $mediaReal) {
                        $result = true;
                    }
                }
            }
        }
        return ($result);
    }

    /**
     * Public absolute URL for a media file
     *
     * @param string $fileName
     *
     * @return string
     */
    public function getMediaPublicUrl($fileName) {
        $result = '';
        $fileName = $this->filterMediaFileName($fileName);
        if ($this->isMediaEnabled() and !empty($fileName)) {
            $result = $this->buildAbsoluteUrl($this->mediaPath . $fileName);
        }
        return ($result);
    }

    /**
     * Lists media files (name => meta), newest first by mtime
     *
     * @return array
     */
    public function listMediaFiles() {
        $result = array();
        if ($this->isMediaEnabled() and file_exists($this->mediaPath) and is_dir($this->mediaPath) and is_readable($this->mediaPath)) {
            $files = rcms_scandir($this->mediaPath);
            if (!empty($files)) {
                foreach ($files as $io => $eachFile) {
                    $safeName = $this->filterMediaFileName($eachFile);
                    if (!empty($safeName)) {
                        $fullPath = $this->mediaPath . $safeName;
                        if (is_file($fullPath) and is_readable($fullPath)) {
                            $result[$safeName] = array(
                                'name' => $safeName,
                                'size' => filesize($fullPath),
                                'mtime' => filemtime($fullPath),
                                'url' => $this->getMediaPublicUrl($safeName)
                            );
                        }
                    }
                }
                if (!empty($result)) {
                    uasort($result, array($this, 'compareMediaByMtime'));
                }
            }
        }
        return ($result);
    }

    /**
     * uasort callback: newer mtime first
     *
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    protected function compareMediaByMtime($a, $b) {
        $result = 0;
        $ma = isset($a['mtime']) ? (int) $a['mtime'] : 0;
        $mb = isset($b['mtime']) ? (int) $b['mtime'] : 0;
        if ($ma == $mb) {
            $result = 0;
        } else {
            if ($ma < $mb) {
                $result = 1;
            } else {
                $result = -1;
            }
        }
        return ($result);
    }

    /**
     * Human-readable file size
     *
     * @param int $bytes
     *
     * @return string
     */
    protected function formatMediaSize($bytes) {
        $result = '0 B';
        $bytes = (int) $bytes;
        if ($bytes > 0) {
            if ($bytes < 1024) {
                $result = $bytes . ' B';
            } else {
                if ($bytes < 1048576) {
                    $result = round($bytes / 1024, 1) . ' KB';
                } else {
                    $result = round($bytes / 1048576, 1) . ' MB';
                }
            }
        }
        return ($result);
    }

    /**
     * Sanitizes original upload basename before uniqueness check
     * Cyrillic via zb_TranslitString; spaces/+ and other URL-unfriendly chars become "_"
     *
     * @param string $originalName
     *
     * @return string
     */
    protected function sanitizeUploadBaseName($originalName) {
        $result = '';
        $originalName = basename(str_replace('\\', '/', $originalName));
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $base = pathinfo($originalName, PATHINFO_FILENAME);
        if (function_exists('zb_TranslitString')) {
            $base = zb_TranslitString($base);
        }
        $base = str_replace(array(' ', '+', '%', '#', '?', '&', '=', '@', '!', ',', ';', ':', '"', '\'', '(', ')', '[', ']', '{', '}', '<', '>', '|', '\\', '/', '*'), '_', $base);
        $base = preg_replace('/[^a-zA-Z0-9._-]/', '_', $base);
        $base = preg_replace('/_+/', '_', $base);
        $base = trim($base, '._-');
        if (empty($base)) {
            $base = 'file';
        }
        if (!empty($ext) and in_array($ext, $this->mediaAllowed)) {
            $result = $base . '.' . $ext;
        }
        return ($result);
    }

    /**
     * Handles media file upload from POST multipart
     *
     * @return string empty on success, error message otherwise
     */
    public function uploadMediaFromRequest() {
        $result = '';
        if (ubRouting::checkPost(self::PROUTE_DOUPLOAD_MEDIA)) {
            if ($this->isMediaEnabled()) {
                $pathError = $this->ensureMediaPath();
                if (empty($pathError)) {
                    $uploadedFile = array();
                    if (isset($_FILES[self::PROUTE_MEDIA_FILE])) {
                        $uploadedFile = $_FILES[self::PROUTE_MEDIA_FILE];
                    }
                    if (!empty($uploadedFile) and isset($uploadedFile['error']) and ($uploadedFile['error'] === UPLOAD_ERR_OK)) {
                        if (!empty($uploadedFile['tmp_name']) and is_uploaded_file($uploadedFile['tmp_name'])) {
                            $safeName = $this->sanitizeUploadBaseName($uploadedFile['name']);
                            if (!empty($safeName)) {
                                $base = pathinfo($safeName, PATHINFO_FILENAME);
                                $ext = pathinfo($safeName, PATHINFO_EXTENSION);
                                $candidate = $safeName;
                                $i = 1;
                                while (file_exists($this->mediaPath . $candidate) and ($i < 1000)) {
                                    $candidate = $base . '_' . $i . '.' . $ext;
                                    $i++;
                                }
                                $destPath = $this->mediaPath . $candidate;
                                $moved = @move_uploaded_file($uploadedFile['tmp_name'], $destPath);
                                if ($moved and file_exists($destPath)) {
                                    log_register('PCMS MEDIA UPLOAD {' . $candidate . '}');
                                } else {
                                    $result = __('Photo upload failed');
                                }
                            } else {
                                $result = __('Wrong file type');
                            }
                        } else {
                            $result = __('Photo upload failed');
                        }
                    } else {
                        $result = __('Photo upload failed');
                    }
                } else {
                    $result = $pathError;
                }
            } else {
                $result = __('Media uploads disabled');
            }
        }
        return ($result);
    }

    /**
     * Deletes a media file
     *
     * @param mixed $fileName
     *
     * @return string empty on success, error message otherwise
     */
    public function deleteMedia($fileName) {
        $result = '';
        $fileName = $this->filterMediaFileName($fileName);
        if ($this->isMediaEnabled() and !empty($fileName)) {
            $fullPath = $this->getMediaFileFullPath($fileName);
            if (!empty($fullPath) and file_exists($fullPath) and is_file($fullPath) and $this->isPathInsideMedia($fullPath)) {
                $deleted = @unlink($fullPath);
                if ($deleted) {
                    log_register('PCMS MEDIA DELETE {' . $fileName . '}');
                } else {
                    $result = __('Cannot delete file');
                }
            } else {
                $result = __('File not found');
            }
        } else {
            $result = __('File not found');
        }
        return ($result);
    }

    /**
     * Forces download of a media file and stops script
     *
     * @param mixed $fileName
     *
     * @return void
     */
    public function downloadMedia($fileName) {
        $fileName = $this->filterMediaFileName($fileName);
        if ($this->isMediaEnabled() and !empty($fileName)) {
            $fullPath = $this->getMediaFileFullPath($fileName);
            if (!empty($fullPath) and file_exists($fullPath) and is_readable($fullPath) and $this->isPathInsideMedia($fullPath)) {
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $fileName . '"');
                header('Content-Length: ' . filesize($fullPath));
                header('Cache-Control: private');
                @readfile($fullPath);
                die();
            }
        }
    }

    /**
     * Admin media upload form + file list
     *
     * @return string
     */
    public function renderMediaPanel() {
        $result = '';
        if ($this->isMediaEnabled()) {
            $acceptParts = array();
            if (!empty($this->mediaAllowed)) {
                foreach ($this->mediaAllowed as $io => $ext) {
                    $acceptParts[] = '.' . $ext;
                }
            }
            $acceptAttr = '';
            if (!empty($acceptParts)) {
                $acceptAttr = ' accept="' . implode(',', $acceptParts) . '"';
            }
            $inputs = wf_HiddenInput(self::PROUTE_DOUPLOAD_MEDIA, 'true');
            $inputs .= '<input type="file" name="' . self::PROUTE_MEDIA_FILE . '"' . $acceptAttr . ' /> ';
            $inputs .= wf_Submit(__('Upload'));
            $result .= wf_Form('', 'POST', $inputs, 'glamour', '', '', '', 'enctype="multipart/form-data"');
            $result .= wf_tag('div', false, '', 'style="opacity:0.7; margin:0.5em 0 1em;"');
            $result .= __('Allowed') . ': ' . implode(', ', $this->mediaAllowed);
            $result .= wf_tag('div', true);
            $result .= wf_delimiter(0);

            $allFiles = $this->listMediaFiles();
            if (!empty($allFiles)) {
                $result .= wf_tag('script', false, '', 'type="text/javascript"');
                $result .= 'function pcmsCopyMediaUrl(url){';
                $result .= 'if(navigator.clipboard&&navigator.clipboard.writeText){navigator.clipboard.writeText(url);alert(' . json_encode(__('URL copied')) . ');return;}';
                $result .= 'var t=document.createElement("textarea");t.value=url;document.body.appendChild(t);t.select();';
                $result .= 'try{document.execCommand("copy");alert(' . json_encode(__('URL copied')) . ');}catch(e){prompt(' . json_encode(__('Copy URL')) . ',url);}';
                $result .= 'document.body.removeChild(t);}';
                $result .= wf_tag('script', true);

                $cells = wf_TableCell(__('File'));
                $cells .= wf_TableCell(__('Size'));
                $cells .= wf_TableCell(__('Actions'));
                $rows = wf_TableRow($cells, 'row1');
                foreach ($allFiles as $io => $file) {
                    $fileUrl = $file['url'];
                    $fileUrlJs = str_replace(array('\\', "'", "\r", "\n"), array('\\\\', "\\'", '', ''), $fileUrl);
                    $actControls = wf_JSAlert(self::URL_ME . '&' . self::ROUTE_SECTION . '=' . self::SECTION_MEDIA . '&' . self::ROUTE_DELETE_MEDIA . '=' . rawurlencode($file['name']), web_delete_icon(), $this->messages->getDeleteAlert()) . ' ';
                    $actControls .= wf_Link(self::URL_ME . '&' . self::ROUTE_SECTION . '=' . self::SECTION_MEDIA . '&' . self::ROUTE_DOWNLOAD_MEDIA . '=' . rawurlencode($file['name']), web_icon_download(), false) . ' ';
                    $actControls .= '<a href="#" title="' . htmlspecialchars(__('Copy URL'), ENT_QUOTES, 'UTF-8') . '" onclick="pcmsCopyMediaUrl(\'' . $fileUrlJs . '\');return false;">' . wf_img('skins/icon_clone.png', __('Copy URL')) . '</a> ';
                    $actControls .= wf_Link($fileUrl, wf_img('skins/icon_visible.png', __('View')), false, '', 'target="_blank"');
                    $cells = wf_TableCell($file['name']);
                    $cells .= wf_TableCell($this->formatMediaSize($file['size']));
                    $cells .= wf_TableCell($actControls);
                    $rows .= wf_TableRow($cells, 'row5');
                }
                $result .= wf_TableBody($rows, '100%', 0, 'sortable');
            } else {
                $result .= $this->messages->getStyledMessage(__('Nothing to show'), 'info');
            }
        } else {
            $result .= $this->messages->getStyledMessage(__('Media uploads disabled'), 'info');
        }
        return ($result);
    }

    /**
     * Manager section switcher and create buttons
     *
     * @param string $section current section pages|elements|articles|drafts|media
     *
     * @return string
     */
    public function renderMgrMenu($section = '') {
        $result = '';
        if (empty($section)) {
            $section = self::SECTION_ARTICLES;
        }
        if ($section == self::SECTION_PAGES) {
            $result .= wf_Link(self::URL_ME . '&' . self::ROUTE_SECTION . '=' . self::SECTION_PAGES . '&' . self::ROUTE_NEW_PAGE . '=true', web_add_icon() . ' ' . __('Create page'), false, 'ubButton') . ' ';
        } else {
            if ($section == self::SECTION_ELEMENTS) {
                $result .= wf_Link(self::URL_ME . '&' . self::ROUTE_SECTION . '=' . self::SECTION_ELEMENTS . '&' . self::ROUTE_NEW_ELEMENT . '=true', web_add_icon() . ' ' . __('Create element'), false, 'ubButton') . ' ';
            } else {
                if ($section == self::SECTION_MEDIA) {
                    $result .= '';
                } else {
                    if ($section == self::SECTION_DRAFTS) {
                        $result .= wf_Link(self::URL_ME . '&' . self::ROUTE_SECTION . '=' . self::SECTION_DRAFTS . '&' . self::ROUTE_NEW_DRAFT . '=true', web_add_icon() . ' ' . __('Create draft'), false, 'ubButton') . ' ';
                    } else {
                        $result .= wf_Link(self::URL_ME . '&' . self::ROUTE_SECTION . '=' . self::SECTION_ARTICLES . '&' . self::ROUTE_NEW_ARTICLE . '=true', web_add_icon() . ' ' . __('Create article'), false, 'ubButton') . ' ';
                    }
                }
            }
        }
        $result .= wf_Link(self::URL_ME . '&' . self::ROUTE_SECTION . '=' . self::SECTION_ARTICLES, wf_img('skins/icon_time_small.png') . ' ' . __('Articles'), false, 'ubButton') . ' ';
        $result .= wf_Link(self::URL_ME . '&' . self::ROUTE_SECTION . '=' . self::SECTION_DRAFTS, wf_img('skins/icon_hidden.png') . ' ' . __('Drafts'), false, 'ubButton') . ' ';
        $result .= wf_Link(self::URL_ME . '&' . self::ROUTE_SECTION . '=' . self::SECTION_PAGES, wf_img('skins/icon_note.gif') . ' ' . __('Pages'), false, 'ubButton') . ' ';
        $result .= wf_Link(self::URL_ME . '&' . self::ROUTE_SECTION . '=' . self::SECTION_ELEMENTS, wf_img('skins/icon_extended.png') . ' ' . __('Elements'), false, 'ubButton');
        if ($this->isMediaEnabled()) {
            $result .= ' ' . wf_Link(self::URL_ME . '&' . self::ROUTE_SECTION . '=' . self::SECTION_MEDIA, wf_img('skins/icon_download.png') . ' ' . __('Media'), false, 'ubButton');
        }
        $result .= wf_delimiter();
        return ($result);
    }

    /**
     * Admin pages list
     *
     * @return string
     */
    public function renderPagesList() {
        $result = '';
        $allPages = $this->listPages();
        if (!empty($allPages)) {
            $cells = wf_TableCell(__('ID'));
            $cells .= wf_TableCell(__('Title'));
            $cells .= wf_TableCell(__('Actions'));
            $rows = wf_TableRow($cells, 'row1');
            foreach ($allPages as $io => $page) {
                $cells = wf_TableCell($page['id']);
                $cells .= wf_TableCell($page['title']);
                $actControls = wf_JSAlert(self::URL_ME . '&' . self::ROUTE_SECTION . '=' . self::SECTION_PAGES . '&' . self::ROUTE_DELETE_PAGE . '=' . $page['id'], web_delete_icon(), $this->messages->getDeleteAlert()) . ' ';
                $actControls .= wf_Link(self::URL_ME . '&' . self::ROUTE_SECTION . '=' . self::SECTION_PAGES . '&' . self::ROUTE_EDIT_PAGE . '=' . $page['id'], web_edit_icon(__('Edit')), false) . ' ';
                $actControls .= wf_Link(self::URL_PAGES . '&' . self::ROUTE_PAGEID . '=' . $page['id'], wf_img('skins/icon_search_small.gif', __('View')), false, '', 'target="_blank"');
                $cells .= wf_TableCell($actControls);
                $rows .= wf_TableRow($cells, 'row5');
            }
            $result .= wf_TableBody($rows, '100%', 0, 'sortable');
        } else {
            $result .= $this->messages->getStyledMessage(__('Nothing to show'), 'info');
        }
        return ($result);
    }

    /**
     * Admin elements list
     *
     * @return string
     */
    public function renderElementsList() {
        $result = '';
        $allElements = $this->listElements();
        if (!empty($allElements)) {
            $cells = wf_TableCell(__('ID'));
            $cells .= wf_TableCell(__('Actions'));
            $rows = wf_TableRow($cells, 'row1');
            foreach ($allElements as $io => $element) {
                $cells = wf_TableCell($element['id']);
                $actControls = wf_JSAlert(self::URL_ME . '&' . self::ROUTE_SECTION . '=' . self::SECTION_ELEMENTS . '&' . self::ROUTE_DELETE_ELEMENT . '=' . $element['id'], web_delete_icon(), $this->messages->getDeleteAlert()) . ' ';
                $actControls .= wf_Link(self::URL_ME . '&' . self::ROUTE_SECTION . '=' . self::SECTION_ELEMENTS . '&' . self::ROUTE_EDIT_ELEMENT . '=' . $element['id'], web_edit_icon(__('Edit')), false);
                $cells .= wf_TableCell($actControls);
                $rows .= wf_TableRow($cells, 'row5');
            }
            $result .= wf_TableBody($rows, '100%', 0, 'sortable');
        } else {
            $result .= $this->messages->getStyledMessage(__('Nothing to show'), 'info');
        }
        return ($result);
    }

    /**
     * Admin articles list with pagination
     *
     * @param int $pageNum
     *
     * @return string
     */
    public function renderArticlesList($pageNum = 1) {
        $result = '';
        $pageNum = ubRouting::filters($pageNum, 'int');
        if (empty($pageNum) or ((int) $pageNum < 1)) {
            $pageNum = 1;
        } else {
            $pageNum = (int) $pageNum;
        }

        $allArticles = $this->listArticles();
        $total = sizeof($allArticles);
        if ($total > 0) {
            $perPage = $this->adminArticlesPerPage;
            if (empty($perPage)) {
                $perPage = 10;
            }
            $maxPage = (int) ceil($total / $perPage);
            if ($pageNum > $maxPage) {
                $pageNum = $maxPage;
            }
            $offset = ($pageNum - 1) * $perPage;
            $slice = array_slice($allArticles, $offset, $perPage, true);
            if (!empty($slice)) {
                $cells = wf_TableCell(__('ID'));
                $cells .= wf_TableCell(__('Title'));
                $cells .= wf_TableCell(__('Date'));
                $cells .= wf_TableCell(__('Type'));
                $cells .= wf_TableCell(__('Actions'));
                $rows = wf_TableRow($cells, 'row1');
                foreach ($slice as $io => $article) {
                    $cells = wf_TableCell($article['id']);
                    $cells .= wf_TableCell($article['title']);
                    $cells .= wf_TableCell($article['datetime']);
                    $cells .= wf_TableCell($article['type']);
                    $actControls = wf_JSAlert(self::URL_ME . '&' . self::ROUTE_SECTION . '=' . self::SECTION_ARTICLES . '&' . self::ROUTE_DELETE_ARTICLE . '=' . $article['id'], web_delete_icon(), $this->messages->getDeleteAlert()) . ' ';
                    $actControls .= wf_Link(self::URL_ME . '&' . self::ROUTE_SECTION . '=' . self::SECTION_ARTICLES . '&' . self::ROUTE_EDIT_ARTICLE . '=' . $article['id'], web_edit_icon(__('Edit')), false) . ' ';
                    $actControls .= wf_JSAlert(self::URL_ME . '&' . self::ROUTE_SECTION . '=' . self::SECTION_ARTICLES . '&' . self::ROUTE_TO_DRAFT . '=' . $article['id'], wf_img('skins/icon_hidden.png', __('Move to drafts')), __('Move to drafts') . '?') . ' ';
                    $actControls .= wf_Link(self::URL_ARTICLES . '&' . self::ROUTE_ARTICLE . '=' . $article['id'], wf_img('skins/icon_search_small.gif', __('View')), false, '', 'target="_blank"');
                    $cells .= wf_TableCell($actControls);
                    $rows .= wf_TableRow($cells, 'row5');
                }
                $result .= wf_TableBody($rows, '100%', 0, 'sortable');
            } else {
                $result .= $this->messages->getStyledMessage(__('Nothing to show'), 'info');
            }
            $result .= wf_delimiter();
            $pagerLink = self::URL_ME . '&' . self::ROUTE_SECTION . '=' . self::SECTION_ARTICLES;
            $result .= wf_pagination($total, $perPage, $pageNum, $pagerLink);
        } else {
            $result .= $this->messages->getStyledMessage(__('Nothing to show'), 'info');
        }
        return ($result);
    }

    /**
     * Admin drafts list with pagination
     *
     * @param int $pageNum
     *
     * @return string
     */
    public function renderDraftsList($pageNum = 1) {
        $result = '';
        $pageNum = ubRouting::filters($pageNum, 'int');
        if (empty($pageNum) or ((int) $pageNum < 1)) {
            $pageNum = 1;
        } else {
            $pageNum = (int) $pageNum;
        }

        $allDrafts = $this->listDrafts();
        $total = sizeof($allDrafts);
        if ($total > 0) {
            $perPage = $this->adminArticlesPerPage;
            if (empty($perPage)) {
                $perPage = 10;
            }
            $maxPage = (int) ceil($total / $perPage);
            if ($pageNum > $maxPage) {
                $pageNum = $maxPage;
            }
            $offset = ($pageNum - 1) * $perPage;
            $slice = array_slice($allDrafts, $offset, $perPage, true);
            if (!empty($slice)) {
                $cells = wf_TableCell(__('ID'));
                $cells .= wf_TableCell(__('Title'));
                $cells .= wf_TableCell(__('Date'));
                $cells .= wf_TableCell(__('Type'));
                $cells .= wf_TableCell(__('Actions'));
                $rows = wf_TableRow($cells, 'row1');
                foreach ($slice as $io => $draft) {
                    $cells = wf_TableCell($draft['id']);
                    $cells .= wf_TableCell($draft['title']);
                    $cells .= wf_TableCell($draft['datetime']);
                    $cells .= wf_TableCell($draft['type']);
                    $actControls = wf_JSAlert(self::URL_ME . '&' . self::ROUTE_SECTION . '=' . self::SECTION_DRAFTS . '&' . self::ROUTE_DELETE_DRAFT . '=' . $draft['id'], web_delete_icon(), $this->messages->getDeleteAlert()) . ' ';
                    $actControls .= wf_Link(self::URL_ME . '&' . self::ROUTE_SECTION . '=' . self::SECTION_DRAFTS . '&' . self::ROUTE_EDIT_DRAFT . '=' . $draft['id'], web_edit_icon(__('Edit')), false) . ' ';
                    $actControls .= wf_JSAlert(self::URL_ME . '&' . self::ROUTE_SECTION . '=' . self::SECTION_DRAFTS . '&' . self::ROUTE_PUBLISH_DRAFT . '=' . $draft['id'], wf_img('skins/icon_visible.png', __('Publish')), __('Publish') . '?');
                    $cells .= wf_TableCell($actControls);
                    $rows .= wf_TableRow($cells, 'row5');
                }
                $result .= wf_TableBody($rows, '100%', 0, 'sortable');
            } else {
                $result .= $this->messages->getStyledMessage(__('Nothing to show'), 'info');
            }
            $result .= wf_delimiter();
            $pagerLink = self::URL_ME . '&' . self::ROUTE_SECTION . '=' . self::SECTION_DRAFTS;
            $result .= wf_pagination($total, $perPage, $pageNum, $pagerLink);
        } else {
            $result .= $this->messages->getStyledMessage(__('Nothing to show'), 'info');
        }
        return ($result);
    }

    /**
     * Page create/edit form
     *
     * @param string $pageId empty for create
     *
     * @return string
     */
    public function renderPageForm($pageId = '') {
        $result = '';
        $pageId = $this->filterPageId($pageId);
        $title = '';
        $content = '';
        $isEdit = false;

        if (!empty($pageId)) {
            $page = $this->getPage($pageId);
            if (!empty($page)) {
                $isEdit = true;
                $title = $page['title'];
                $content = $page['content'];
            } else {
                $result .= $this->messages->getStyledMessage(__('Page not found'), 'error');
            }
        }

        if (empty($result)) {
            $inputs = wf_HiddenInput(self::PROUTE_DOSAVE_PAGE, 'true');
            if ($isEdit) {
                $inputs .= wf_HiddenInput(self::PROUTE_PAGE_ID, $pageId);
                $inputs .= wf_tag('div') . __('ID') . ': ' . $pageId . wf_tag('div', true);
            } else {
                $inputs .= wf_TextInput(self::PROUTE_PAGE_ID, __('Page ID') . ' (a-z, 0-9)', '', true, 20, 'alphanumeric');
            }
            $inputs .= wf_TextInput(self::PROUTE_PAGE_TITLE, __('Title'), $title, true, 40);
            $inputs .= __('Content').' (HTML)'.wf_tag('br');
            $inputs .= wf_TextArea(self::PROUTE_PAGE_CONTENT,'', $content, true, '60x20');
            $inputs .= wf_Submit(__('Save'));
            $result .= wf_Form('', 'POST', $inputs, 'glamour');
        }
        return ($result);
    }

    /**
     * Element create/edit form
     *
     * @param string $elementId empty for create
     *
     * @return string
     */
    public function renderElementForm($elementId = '') {
        $result = '';
        $elementId = $this->filterElementId($elementId);
        $content = '';
        $isEdit = false;

        if (!empty($elementId)) {
            $element = $this->getElement($elementId);
            if (!empty($element)) {
                $isEdit = true;
                $content = $element['content'];
            } else {
                $result .= $this->messages->getStyledMessage(__('Element not found'), 'error');
            }
        }

        if (empty($result)) {
            $inputs = wf_HiddenInput(self::PROUTE_DOSAVE_ELEMENT, 'true');
            if ($isEdit) {
                $inputs .= wf_HiddenInput(self::PROUTE_ELEMENT_ID, $elementId);
                $inputs .= wf_tag('div') . __('ID') . ': ' . $elementId . wf_tag('div', true);
            } else {
                $inputs .= wf_TextInput(self::PROUTE_ELEMENT_ID, __('Element ID') . ' (a-z, 0-9)', '', true, 20, 'alphanumeric');
            }
            $inputs .= __('Content') . ' (HTML / PHP)' . wf_tag('br');
            $inputs .= wf_TextArea(self::PROUTE_ELEMENT_CONTENT, '', $content, true, '60x20');
            $inputs .= wf_Submit(__('Save'));
            $result .= wf_Form('', 'POST', $inputs, 'glamour');
        }
        return ($result);
    }

    /**
     * Article or draft create/edit form
     *
     * @param mixed $articleId empty for create
     * @param bool $asDraft
     *
     * @return string
     */
    public function renderArticleForm($articleId = '', $asDraft = false) {
        $result = '';
        $articleId = $this->filterArticleId($articleId);
        $title = '';
        $short = '';
        $full = '';
        $datePart = date('Y-m-d');
        $timePart = date('H:i');
        $type = self::TYPE_TEXT;
        if ($this->articlesDefaultHtml) {
            $type = self::TYPE_HTML;
        }
        $isEdit = false;

        if (!empty($articleId)) {
            $article = false;
            if ($asDraft) {
                $article = $this->getDraft($articleId);
            } else {
                $article = $this->getArticle($articleId);
            }
            if (!empty($article)) {
                $isEdit = true;
                $title = $article['title'];
                $short = $article['short'];
                $full = $article['full'];
                $type = $article['type'];
                if (!empty($article['datetime'])) {
                    $ts = strtotime($article['datetime']);
                    if ($ts) {
                        $datePart = date('Y-m-d', $ts);
                        $timePart = date('H:i', $ts);
                    }
                }
            } else {
                if ($asDraft) {
                    $result .= $this->messages->getStyledMessage(__('Draft not found'), 'error');
                } else {
                    $result .= $this->messages->getStyledMessage(__('Article not found'), 'error');
                }
            }
        }

        if (empty($result)) {
            $typeParams = array(
                self::TYPE_HTML => 'HTML',
                self::TYPE_TEXT => __('Text')
            );
            $inputs = wf_HiddenInput(self::PROUTE_DOSAVE_ARTICLE, 'true');
            if ($asDraft) {
                $inputs .= wf_HiddenInput(self::PROUTE_IS_DRAFT, '1');
            }
            if ($isEdit) {
                $inputs .= wf_HiddenInput(self::PROUTE_ARTICLE_ID, $articleId);
                $inputs .= wf_tag('div') . __('ID') . ': ' . $articleId . wf_tag('div', true);
            }
            $shortAreaId = 'pcms_article_short';
            $fullAreaId = 'pcms_article_full';
            $inputs .= wf_TextInput(self::PROUTE_ARTICLE_TITLE, __('Title'), $title, true, 40);
            $inputs .= __('Short text') . wf_tag('br');
            if (!$this->articlesDefaultHtml) {
                $inputs .= $this->renderBbCodeToolbar($shortAreaId);
            }
            $inputs .= wf_TextArea(self::PROUTE_ARTICLE_SHORT, '', $short, true, '60x8', $shortAreaId);
            $inputs .= __('Full text') . wf_tag('br');
            if (!$this->articlesDefaultHtml) {
                $inputs .= $this->renderBbCodeToolbar($fullAreaId);
            }
            $inputs .= wf_TextArea(self::PROUTE_ARTICLE_FULL, '', $full, true, '60x20', $fullAreaId);
            $inputs .= __('Date') . ' ' . wf_DatePickerPreset(self::PROUTE_ARTICLE_DATE, $datePart, true) . ' ';
            $inputs .= wf_TimePickerPreset(self::PROUTE_ARTICLE_TIME, $timePart, __('Time'), false) . ' ';
            $inputs .= wf_Selector(self::PROUTE_ARTICLE_TYPE, $typeParams, __('Type'), $type, false);
            $inputs .= wf_Submit(__('Save'));
            $result .= wf_Form('', 'POST', $inputs, 'glamour');
        }
        return ($result);
    }

    /**
     * Creates or updates a page from POST
     *
     * @return string empty on success, error message otherwise
     */
    public function savePageFromRequest() {
        $result = '';
        if (ubRouting::checkPost(self::PROUTE_DOSAVE_PAGE)) {
            $pageId = $this->filterPageId(ubRouting::post(self::PROUTE_PAGE_ID, 'gigasafe'));
            $title = ubRouting::post(self::PROUTE_PAGE_TITLE, 'raw');
            $content = ubRouting::post(self::PROUTE_PAGE_CONTENT, 'raw');
            if (!empty($pageId)) {
                $pageData = array(
                    'id' => $pageId,
                    'title' => $title,
                    'content' => $content
                );
                $allowOverwrite = false;
                if (ubRouting::checkGet(self::ROUTE_EDIT_PAGE)) {
                    $allowOverwrite = true;
                }
                $result = $this->savePageData($pageData, $allowOverwrite);
                if (empty($result)) {
                    log_register('PCMS PAGE SAVE {' . $pageId . '}');
                }
            } else {
                $result = __('Empty page ID');
            }
        }
        return ($result);
    }

    /**
     * Creates or updates an element from POST
     *
     * @return string empty on success, error message otherwise
     */
    public function saveElementFromRequest() {
        $result = '';
        if (ubRouting::checkPost(self::PROUTE_DOSAVE_ELEMENT)) {
            $elementId = $this->filterElementId(ubRouting::post(self::PROUTE_ELEMENT_ID, 'gigasafe'));
            $content = ubRouting::post(self::PROUTE_ELEMENT_CONTENT, 'raw');
            if (!empty($elementId)) {
                $elementData = array(
                    'id' => $elementId,
                    'content' => $content
                );
                $allowOverwrite = false;
                if (ubRouting::checkGet(self::ROUTE_EDIT_ELEMENT)) {
                    $allowOverwrite = true;
                }
                $result = $this->saveElementData($elementData, $allowOverwrite);
                if (empty($result)) {
                    log_register('PCMS ELEMENT SAVE {' . $elementId . '}');
                }
            } else {
                $result = __('Empty element ID');
            }
        }
        return ($result);
    }

    /**
     * Creates or updates an article or draft from POST
     *
     * @return string empty on success, error message otherwise
     */
    public function saveArticleFromRequest() {
        $result = '';
        if (ubRouting::checkPost(self::PROUTE_DOSAVE_ARTICLE)) {
            $asDraft = false;
            if (ubRouting::checkPost(self::PROUTE_IS_DRAFT)) {
                $asDraft = true;
            }
            $articleId = $this->filterArticleId(ubRouting::post(self::PROUTE_ARTICLE_ID, 'int'));
            $isNew = false;
            if (empty($articleId)) {
                $articleId = (string) $this->getNextArticleId();
                $isNew = true;
            }
            $title = ubRouting::post(self::PROUTE_ARTICLE_TITLE, 'raw');
            $short = ubRouting::post(self::PROUTE_ARTICLE_SHORT, 'raw');
            $full = ubRouting::post(self::PROUTE_ARTICLE_FULL, 'raw');
            $datePart = ubRouting::post(self::PROUTE_ARTICLE_DATE, 'safe');
            $timePart = ubRouting::post(self::PROUTE_ARTICLE_TIME, 'safe');
            $datetime = '';
            if (!empty($datePart)) {
                if (empty($timePart)) {
                    $timePart = '00:00';
                }
                $datetime = $datePart . ' ' . $timePart;
                if (strlen($timePart) == 5) {
                    $datetime .= ':00';
                }
            }
            $type = ubRouting::post(self::PROUTE_ARTICLE_TYPE, 'gigasafe');
            if ($type != self::TYPE_HTML and $type != self::TYPE_TEXT) {
                $type = self::TYPE_TEXT;
            }
            if (!empty($title)) {
                $articleData = array(
                    'id' => $articleId,
                    'title' => $title,
                    'short' => $short,
                    'full' => $full,
                    'datetime' => $datetime,
                    'type' => $type
                );
                if ($asDraft) {
                    $result = $this->saveDraftData($articleData);
                    if (empty($result)) {
                        if ($isNew) {
                            log_register('PCMS DRAFT CREATE {' . $articleId . '}');
                        } else {
                            log_register('PCMS DRAFT SAVE {' . $articleId . '}');
                        }
                    }
                } else {
                    $result = $this->saveArticleData($articleData);
                    if (empty($result)) {
                        if ($isNew) {
                            log_register('PCMS ARTICLE CREATE {' . $articleId . '}');
                        } else {
                            log_register('PCMS ARTICLE SAVE {' . $articleId . '}');
                        }
                    }
                }
            } else {
                $result = __('Empty title');
            }
        }
        return ($result);
    }

    /**
     * Was last article save targeting drafts?
     *
     * @return bool
     */
    public function isDraftSaveRequest() {
        $result = false;
        if (ubRouting::checkPost(self::PROUTE_IS_DRAFT)) {
            $result = true;
        }
        return ($result);
    }

}
