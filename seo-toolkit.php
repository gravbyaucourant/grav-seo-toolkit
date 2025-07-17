<?php
namespace Grav\Plugin;

use Composer\Autoload\ClassLoader;
use Grav\Common\Plugin;
use Grav\Plugin\SeoToolkit\SeoToolkitClass;
use RocketTheme\Toolbox\Event\Event;
use Grav\Common\Data\Blueprint;

class SeoToolkitPlugin extends Plugin
{
    protected $seotoolkit;

    public static function getSubscribedEvents(): array
    {
        return [
            'onPluginsInitialized' => [['onPluginsInitialized', 0]],
        ];
    }
    public function autoload(): ClassLoader
    {
        return require __DIR__ . '/vendor/autoload.php';
    }
    public function onPluginsInitialized(): void
    {
        // Initialize SeoToolkitClass
        $this->seotoolkit = new SeoToolkitClass($this->grav, $this->config);

        if (!$this->config->get('plugins.seo-toolkit.enabled')) {
            return;
        }

        // ✅ Auto-generate robots.txt when plugin is enabled
        $this->seotoolkit->generateRobotsTxt(
            [
                'User-agent' => '*',
                'Disallow' => ['/cache/', '/logs/', '/tmp/', '/admin/'],
            ],
            rtrim($this->grav['uri']->rootUrl(true), '/') . '/sitemap.xml'
        );


        if ($this->isAdmin()) {
            $this->enable([
                'onAdminMenu' => ['onAdminMenu', 0],
                'onBlueprintCreated' => ['onBlueprintCreated', 0],
                'onAssetsInitialized' => ['onAssetsInitialized', 0],
            ]);
        } else {
            $this->enable([
                'onPageInitialized' => ['onPageInitialized', 0],
                'onOutputGenerated' => ['onOutputGenerated', 0],
            ]);
        }
    }

    public function onAdminMenu(): void
    {
        $this->grav['twig']->plugins_hooked_nav['SEO Toolkit'] = [
            'route' => '/plugins/seo-toolkit',
            'icon' => 'fa-cogs'
        ];
    }
    public function onAssetsInitialized(): void
    {
        if ($this->isAdmin()) {
            // Delegate asset injection to SeoToolkitClass
            $this->seotoolkit->injectAssets();
        }
    }
    public function onBlueprintCreated(Event $event): void
    {
        $blueprint = $event['blueprint'];

        // Only extend blueprints that already use the tab layout.
        if (!$blueprint->get('form/fields/tabs')) {
            return;
        }

        /** ---------- Extended SEO Tab ---------- */
        $blueprint->extend([
            'form' => [
                'fields' => [
                    'tabs' => [
                        'fields' => [

                            /* TOP‑LEVEL SEO TAB */
                            'seo' => [
                                'type' => 'tab',
                                'title' => 'SEO',

                                /* ───────────────────── fieldsets within the tab ───────────────────── */
                                'fields' => [

                                    /** 1. SEO SETTINGS fieldset */
                                    'seo_settings' => [
                                        'type' => 'section',
                                        'title' => 'SEO Settings',
                                        'underline' => true
                                    ],
                                    'header.seo.title' => [
                                        'type' => 'text',
                                        'label' => 'SEO Title',
                                        'help' => 'Recommended: 50‑70 characters',
                                        'class' => 'seo-title-field',
                                        'validate' => ['type' => 'string', 'max' => 70]
                                    ],
                                    'header.seo.description' => [
                                        'type' => 'textarea',
                                        'label' => 'Meta Description',
                                        'help' => 'Recommended: 150‑160 characters',
                                        'class' => 'seo-description-field',
                                        'validate' => ['type' => 'string', 'max' => 320]
                                    ],
                                    'header.seo.keyword' => [
                                        'type' => 'text',
                                        'label' => 'Focus Keyword',
                                        'class' => 'seo-focus-keyword',
                                        'help' => 'Primary keyword for this page'
                                    ],

                                    /* ▸ NEW – placeholder the JS will hide & replace */
                                    'header.seo.snippet' => [
                                        'type' => 'textarea',      // matches JS selector
                                        'label' => 'Snippet Preview',   // shows a heading in the UI
                                        'rows' => 3,
                                        'class' => 'seo-snippet-textarea',
                                        'markdown' => false,
                                        'help' => 'Google‑style preview (updates live below).',
                                        'attributes' => [
                                            'readonly' => true           // user can’t edit, JS hides it anyway
                                        ],
                                        'validate' => ['type' => 'ignore']
                                    ],

                                    /** 2. OPEN GRAPH SETTINGS fieldset */
                                    'og_settings' => [
                                        'type' => 'section',
                                        'title' => 'Open Graph Settings',
                                        'underline' => true
                                    ],
                                    'header.og.title' => [
                                        'type' => 'text',
                                        'label' => 'OG Title'
                                    ],
                                    'header.og.description' => [
                                        'type' => 'textarea',
                                        'label' => 'OG Description'
                                    ],
                                    'header.og.type' => [
                                        'type' => 'select',
                                        'label' => 'OG Type',
                                        'default' => 'article',
                                        'help' => 'Select or enter a custom Open Graph object type. Example: article, website, product, video.movie, etc.',
                                        'options' => [
                                            'article' => 'Article',
                                            'website' => 'Website',
                                            'book' => 'Book',
                                            'profile' => 'Profile',
                                            'video.movie' => 'Video Movie',
                                            'video.episode' => 'Video Episode',
                                            'video.tv_show' => 'Video TV Show',
                                            'video.other' => 'Video Other',
                                            'music.song' => 'Music Song',
                                            'music.album' => 'Music Album',
                                            'music.playlist' => 'Music Playlist'
                                        ],
                                        'validate' => [
                                            'type' => 'string'
                                        ],
                                        'selectize' => [
                                            'create' => true,
                                            'allowEmptyOption' => true,
                                            'maxItems' => 1,
                                            'placeholder' => 'Select or enter a custom type'
                                        ]
                                    ],
                                    'header.og.image' => [
                                        'type' => 'file',
                                        'label' => 'OG Image',
                                        'destination' => 'self@',
                                        'accept' => ['image/*'],
                                        'multiple' => false
                                    ],


                                    /** 3. ADVANCED fieldset */
                                    'adv_settings' => [
                                        'type' => 'section',
                                        'title' => 'Advanced',
                                        'underline' => true
                                    ],
                                    'header.seo.canonical' => [
                                        'type' => 'url',
                                        'label' => 'Canonical URL'
                                    ],
                                    'header.seo.robots.index' => [
                                        'type' => 'toggle',
                                        'label' => 'Robots: Index',
                                        'highlight' => 1,
                                        'options' => [1 => 'Index', 0 => 'Noindex'],
                                        'help' => 'Whether search engines may index this page.',
                                        'default' => 1
                                    ],
                                    'header.seo.robots.follow' => [
                                        'type' => 'toggle',
                                        'label' => 'Robots: Follow',
                                        'highlight' => 1,
                                        'options' => [1 => 'Follow', 0 => 'Nofollow'],
                                        'help' => 'Whether search engines may Follow this page.',
                                        'default' => 1
                                    ],
                                    'header.seo.schema_editor' => [
                                        'type' => 'textarea',
                                        'label' => 'Page Schema (JSON‑LD)',
                                        'rows' => 10,
                                        'attributes' => ['class' => 'schema-editor']
                                    ],
                                    'header.sitemap.include' => [
                                        'type' => 'toggle',
                                        'label' => 'Include in Sitemap',
                                        'options' => [1 => 'Yes', 0 => 'No'],
                                        'default' => 1
                                    ],
                                    'header.sitemap.changefreq' => [
                                        'type' => 'select',
                                        'label' => 'Change Frequency',
                                        'default' => 'weekly',
                                        'options' => [
                                            'always' => 'Always',
                                            'hourly' => 'Hourly',
                                            'daily' => 'Daily',
                                            'weekly' => 'Weekly',
                                            'monthly' => 'Monthly',
                                            'yearly' => 'Yearly',
                                            'never' => 'Never'
                                        ]
                                    ],
                                    'header.sitemap.priority' => [
                                        'type' => 'text',
                                        'label' => 'Priority (0.0 – 1.0)',
                                        'default' => '0.5',
                                        'validate' => ['type' => 'float', 'min' => 0, 'max' => 1]
                                    ],
                                ]
                            ]

                        ] // tabs.fields
                    ]   // tabs
                ]       // form.fields
            ]           // form
        ], true);       // extend
    }

    /* ------------------------------------------------------------
     *  A.  PAGE INITIALISED  (collect + merge meta data)
     * -----------------------------------------------------------*/
    public function onPageInitialized(): void
    {
        $uri = $this->grav['uri'];
        $path = ltrim($uri->path(), '/');
        $ext = $uri->extension();

        $this->grav['log']->info('SEO Toolkit: onPageInitialized for path: "' . $path . '", extension: "' . $ext . '"');

        if ($path === 'sitemap' && $ext === 'xml') {
            // $this->grav['log']->info('SEO Toolkit: Processing sitemap.xml request');

            $sitemap = $this->seotoolkit->generateSitemapXml();

            if ($sitemap) {
                header('Content-Type: application/xml; charset=UTF-8');
                echo $sitemap;
                exit;
            } else {
                // $this->grav['log']->error('SEO Toolkit: Failed to generate sitemap XML');
                header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
                exit;
            }
        }



        /* ---------- 1. Grab configs & page headers -------------- */
        $page = $this->grav['page'];
        $header = $page->header();

        $globalSeo = $this->config->get('plugins.seo-toolkit.seo') ?? [];
        $globalAdvanced = $this->config->get('plugins.seo-toolkit.advanced') ?? [];
        $globalSocial = $this->config->get('plugins.seo-toolkit.social') ?? [];

        $pageSeo = isset($header->seo) ? (array) $header->seo : [];
        $pageOg = isset($header->og) ? (array) $header->og : [];

        /* ---------- 2. Canonical & robots ----------------------- */
        $canonical = $pageSeo['canonical'] ?? ($globalAdvanced['canonical'] ?? '');

        $robotsIndex = $pageSeo['robots']['index'] ?? null;
        $robotsFollow = $pageSeo['robots']['follow'] ?? null;

        if ($robotsIndex === null || $robotsFollow === null) {
            $robotsStr = $globalAdvanced['robots'] ?? 'index,follow';
            $robotsIndex = !str_contains($robotsStr, 'noindex');
            $robotsFollow = !str_contains($robotsStr, 'nofollow');
        }

        if (!empty($globalAdvanced['noindex'])) {
            $robotsIndex = false;
        }

        /* ---------- 3. SEO array saved to container ------------- */
        $seo = [
            'title' => $pageSeo['title'] ?? ($globalSeo['title'] ?? ''),
            'description' => $pageSeo['description'] ?? ($globalSeo['description'] ?? ''),
            'keyword' => $pageSeo['keyword'] ?? ($globalSeo['keyword'] ?? ''),
            'canonical' => $canonical,
            'robots' => [
                'index' => (bool) $robotsIndex,
                'follow' => (bool) $robotsFollow,
            ],
        ];
        $this->grav['seo_meta'] = $seo;

        /* page‑level JSON‑LD override (unchanged) */
        if (!empty($pageSeo['schema_editor']) && is_string($pageSeo['schema_editor'])) {
            $this->grav['seo_schema_custom'] = trim($pageSeo['schema_editor']);
        }

        /* ---------- 4. Resolve OG image (page > global) --------- */
        $imageUrl = '';

        if (!empty($pageOg['image'])) {
            if (is_string($pageOg['image'])) {
                $imageUrl = str_contains($pageOg['image'], '://')
                    ? $pageOg['image']
                    : ($page->media()->get($pageOg['image'])->url(true) ?? $pageOg['image']);
            } elseif (is_array($pageOg['image'])) {
                $first = reset($pageOg['image']);
                $file = isset($first['name']) ? $page->media()->get($first['name']) : null;
                $imageUrl = $file ? $file->url(true) : '';
            }
        }

        if (!$imageUrl && !empty($globalSocial['og_image'])) {
            $ogImage = $globalSocial['og_image'];

            if (is_array($ogImage)) {
                $first = reset($ogImage);
                $ogImage = (is_array($first) && isset($first['name'])) ? $first['name'] : null;
            }

            if (is_string($ogImage)) {
                $imageUrl = rtrim($this->grav['base_url_absolute'], '/')
                    . '/user/data/ogimages/' . ltrim($ogImage, '/');
            }
        }

        /* absolute + encode spaces */
        if ($imageUrl && !str_contains($imageUrl, '://')) {
            $imageUrl = rtrim($this->grav['base_url_absolute'], '/') . '/' . ltrim($imageUrl, '/');
        }
        $imageUrl = str_replace(' ', '%20', $imageUrl);

        /* ---------- 5. Open‑Graph array saved to container ------ */
        $this->grav['og_meta'] = [
            'title' => $pageOg['title'] ?? ($globalSocial['og_title'] ?? $seo['title']),
            'description' => $pageOg['description'] ?? ($globalSocial['og_description'] ?? $seo['description']),
            'image' => $imageUrl,
            'type' => $pageOg['type'] ?? ($globalSocial['og_type'] ?? 'article'),
            'url' => $seo['canonical'] ?: $page->url(true),
        ];

        /* ---------- 6. Clean up auto‑meta ----------------------- */
        $page->metadata([]);   // drop Grav defaults to prevent duplicates
    }
    /* ------------------------------------------------------------
     *  B.  OUTPUT GENERATED  (inject meta into <head>)
     * -----------------------------------------------------------*/
    public function onOutputGenerated(Event $event): void
    {
        $output = $event['output'];
        $page = $this->grav['page'];

        $seo = array_merge([
            'title' => '',
            'description' => '',
            'keywords' => '',
            'canonical' => '',
            'robots' => ['index' => true, 'follow' => true],
        ], $this->grav['seo_meta'] ?? []);

        $og = array_merge([
            'title' => '',
            'description' => '',
            'type' => 'article',
            'url' => '',
            'image' => '',
        ], $this->grav['og_meta'] ?? []);

        $e = static fn($v) => is_string($v) ? htmlspecialchars($v, ENT_QUOTES, 'UTF-8') : '';

        /* ---------- Build meta block ---------------------------- */
        $meta = '<title>' . $e($seo['title'] ?: $page->title()) . "</title>\n";
        if ($seo['description'])
            $meta .= '<meta name="description" content="' . $e($seo['description']) . "\">\n";
        if ($seo['keywords'])
            $meta .= '<meta name="keywords"    content="' . $e($seo['keywords']) . "\">\n";
        $meta .= '<link rel="canonical" href="' . $e($seo['canonical'] ?: $page->url(true)) . "\">\n";
        $meta .= '<meta name="robots" content="' .
            ($seo['robots']['index'] ? 'index' : 'noindex') . ',' .
            ($seo['robots']['follow'] ? 'follow' : 'nofollow') . "\">\n";

        if ($og['title'])
            $meta .= '<meta property="og:title" content="' . $e($og['title']) . "\">\n";
        if ($og['description'])
            $meta .= '<meta property="og:description" content="' . $e($og['description']) . "\">\n";
        if ($og['type'])
            $meta .= '<meta property="og:type" content="' . $e($og['type']) . "\">\n";
        if ($og['url'])
            $meta .= '<meta property="og:url" content="' . $e($og['url']) . "\">\n";
        if ($og['image'])
            $meta .= '<meta property="og:image" content="' . $e($og['image']) . "\">\n";

        /* ---------- JSON‑LD ------------------------------------- */
        $schema = '';
        if (!empty($this->grav['seo_schema_custom'])) {
            $schema .= '<script type="application/ld+json">' . $this->grav['seo_schema_custom'] . "</script>\n";
        }
        if ($this->config->get('plugins.seo-toolkit.schema.enabled', true)) {
            $cfg = $this->config->get('plugins.seo-toolkit.schema.site.json');
            $schema .= '<script type="application/ld+json">' .
                ($cfg ?: $this->seotoolkit->generateSiteWideSchema()) .
                "</script>\n";
        }

        /* ---------- Inject into <head> -------------------------- */
        $output = preg_replace('~<title>.*?</title>\s*~i', '', $output);
        $output = preg_replace('~<meta\s+name=["\']description["\'].*?>\s*~i', '', $output);
        $output = preg_replace('~<link\s+rel=["\']canonical["\'].*?>\s*~i', '', $output);

        if (preg_match('~(<meta\s+charset=[^>]+>)~i', $output, $m)) {
            $output = preg_replace('~(<meta\s+charset=[^>]+>)~i', $m[1] . "\n" . $meta, $output, 1);
        } else {
            $output = str_replace('</head>', $meta . '</head>', $output);
        }

        $output = str_replace('</head>', $schema . '</head>', $output);

        $event['output'] = $output;
    }
}