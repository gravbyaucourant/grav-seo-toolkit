<?php
namespace Grav\Plugin\SeoToolkit;
use Grav\Common\Config\Config;
use Grav\Common\Page\Page;

class SeoToolkitClass
{
    protected $grav;
    protected $config;

    public function __construct($grav, Config $config)
    {
        $this->grav = $grav;
        $this->config = $config;
    }
    public function injectAssets(): void
    {
        // new validation JS
        $this->grav['assets']->addJs('plugin://seo-toolkit/assets/seo-validation.js', ['group' => 'bottom']);

        // validation CSS
        $this->grav['assets']->addCss('plugin://seo-toolkit/assets/seo-validation.css');
    }
    public function generateSiteWideSchema(): string
    {
        $pluginConfig = $this->config->get('plugins.seo-toolkit');

        // ✅ Correct way to get nested keys from array
        $enabled = $pluginConfig['schema']['enabled'] ?? true;
        if (!$enabled) {
            return '';
        }

        $siteConfig = $pluginConfig['schema']['site'] ?? [];

        // ✅ If user pasted JSON, use it directly
        if (!empty($siteConfig['json']) && is_string($siteConfig['json'])) {
            return trim($siteConfig['json']);
        }

        // ❌ Avoid generating placeholder schema
        if (empty($siteConfig['name'])) {
            return '';
        }

        $siteUrl = $siteConfig['url'] ?? $this->grav['uri']->rootUrl(true);

        $organization = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $siteConfig['name'],
            'url' => $siteUrl,
        ];

        if (!empty($siteConfig['sameAs']) && is_array($siteConfig['sameAs'])) {
            $organization['sameAs'] = $siteConfig['sameAs'];
        }

        $website = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'url' => $siteUrl,
            'name' => $siteConfig['name'],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $siteConfig['name'],
            ],
        ];

        return json_encode([$organization, $website], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    public function generateSitemapXml(): string
    {
        $config = $this->config->get('plugins.seo-toolkit.sitemap') ?? [];

        if (empty($config['enabled']) || !$config['enabled']) {
            // $this->grav['log']->info('SEO Toolkit: Sitemap generation is disabled via plugin config.');
            return '';
        }

        $includeHidden = $config['include_hidden'] ?? false;
        $pages = $this->grav['pages'];
        $items = [];

        foreach ($pages->all() as $page) {
            if ((!$page->routable() || !$page->visible()) && !$includeHidden) {
                continue;
            }

            $header = $page->header();
            $sitemap = $header->sitemap ?? [];

            if (isset($sitemap['include']) && !$sitemap['include']) {
                continue;
            }

            $url = $page->url(true);
            $lastmod = date('c', $page->modified());
            $changefreq = $sitemap['changefreq'] ?? 'weekly';
            $priority = $sitemap['priority'] ?? '0.5';

            $items[] = <<<XML
<url>
<loc>{$url}</loc>
<lastmod>{$lastmod}</lastmod>
<changefreq>{$changefreq}</changefreq>
<priority>{$priority}</priority>
</url>
XML;
        }

        if (empty($items)) {
            // $this->grav['log']->warning('SEO Toolkit: No pages included in sitemap.');
            return '';
        }

        $baseUrl = rtrim($this->grav['uri']->rootUrl(true), '/');
        $xslUrl = $baseUrl . '/user/plugins/seo-toolkit/assets/sitemap.xsl';

        $sitemapXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="{$xslUrl}"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
XML;
        $sitemapXml .= PHP_EOL . implode(PHP_EOL, $items) . PHP_EOL . '</urlset>';

        return $sitemapXml;
    }
    public function pingGoogleSitemap(): void
    {
        $sitemapUrl = rtrim($this->grav['uri']->rootUrl(true), '/') . '/sitemap.xml';
        $pingUrl = 'https://www.google.com/ping?sitemap=' . urlencode($sitemapUrl);

        try {
            @file_get_contents($pingUrl);
            // $this->grav['log']->info("SEO Toolkit: Pinged Google with sitemap URL: $sitemapUrl");
        } catch (\Exception $e) {
            // $this->grav['log']->error("SEO Toolkit: Failed to ping Google: " . $e->getMessage());
        }
    }
    public function ensureSitemapInRobots(): void
    {
        $robotsPath = $this->grav['locator']->findResource('user://robots.txt', true, true);
        $sitemapUrl = rtrim($this->grav['uri']->rootUrl(true), '/') . '/sitemap.xml';
        $sitemapLine = "Sitemap: {$sitemapUrl}";

        if (file_exists($robotsPath)) {
            $contents = file_get_contents($robotsPath);
            if (strpos($contents, $sitemapLine) === false) {
                file_put_contents($robotsPath, $contents . PHP_EOL . $sitemapLine);
            }
        } else {
            file_put_contents($robotsPath, $sitemapLine . PHP_EOL);
        }

        // $this->grav['log']->info("SEO Toolkit: Ensured sitemap is in robots.txt");
    }
    public function generateRobotsTxt(array $rules, string $sitemapUrl, array $extraLines = []): void
    {
        $lines = [];
        foreach ($rules as $directive => $value) {
            if (!is_array($value)) {
                $lines[] = "{$directive}: {$value}";
                continue;
            }
            foreach ($value as $v) {
                $lines[] = "{$directive}: {$v}";
            }
        }
        foreach ($extraLines as $custom) {
            $lines[] = $custom;
        }
        $lines[] = "Sitemap: {$sitemapUrl}";
        $content = implode(PHP_EOL, $lines) . PHP_EOL;

        $robotsPath = GRAV_ROOT . '/robots.txt';
        if (!is_writable(dirname($robotsPath))) {
            // $this->grav['log']->error("SEO Toolkit: Cannot write to {$robotsPath}. Check permissions.");
            return;
        }
        if (file_put_contents($robotsPath, $content) === false) {
            // $this->grav['log']->error("SEO Toolkit: Failed to write to {$robotsPath}.");
            return;
        }

        // $this->grav['log']->info("SEO Toolkit: robots.txt generated at {$robotsPath}");
        // $this->grav['log']->info("SEO Toolkit: robots.txt content:\n" . $content);
    }

}