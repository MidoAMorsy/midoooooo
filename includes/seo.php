<?php
/**
 * Ahmed Ashraf Portfolio - SEO Functions
 */

/**
 * Generate meta tags
 */
function generateMetaTags($options = [])
{
    $lang = getCurrentLanguage();
    $defaults = [
        'title' => getSetting('site_name_' . $lang, SITE_NAME),
        'description' => getSetting('site_description_' . $lang, ''),
        'keywords' => '',
        'image' => SITE_URL . '/assets/images/og-image.jpg',
        'url' => getCurrentUrl(),
        'type' => 'website',
        'robots' => 'index, follow'
    ];

    $meta = array_merge($defaults, $options);
    $siteName = getSetting('site_name_' . $lang, SITE_NAME);

    $html = '';

    // Basic meta tags
    $html .= '<meta charset="UTF-8">' . "\n";
    $html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n";
    $html .= '<meta http-equiv="X-UA-Compatible" content="ie=edge">' . "\n";

    // SEO meta tags
    $html .= '<title>' . e($meta['title']) . ' | ' . e($siteName) . '</title>' . "\n";
    $html .= '<meta name="description" content="' . e($meta['description']) . '">' . "\n";

    if (!empty($meta['keywords'])) {
        $html .= '<meta name="keywords" content="' . e($meta['keywords']) . '">' . "\n";
    }

    $html .= '<meta name="robots" content="' . e($meta['robots']) . '">' . "\n";
    $html .= '<meta name="author" content="' . e($siteName) . '">' . "\n";
    $html .= '<link rel="canonical" href="' . e($meta['url']) . '">' . "\n";

    // Language alternates
    $html .= '<link rel="alternate" hreflang="en" href="' . getAlternateUrl('en') . '">' . "\n";
    $html .= '<link rel="alternate" hreflang="ar" href="' . getAlternateUrl('ar') . '">' . "\n";
    $html .= '<link rel="alternate" hreflang="x-default" href="' . SITE_URL . '">' . "\n";

    // Open Graph tags
    $html .= '<meta property="og:type" content="' . e($meta['type']) . '">' . "\n";
    $html .= '<meta property="og:title" content="' . e($meta['title']) . '">' . "\n";
    $html .= '<meta property="og:description" content="' . e($meta['description']) . '">' . "\n";
    $html .= '<meta property="og:url" content="' . e($meta['url']) . '">' . "\n";
    $html .= '<meta property="og:site_name" content="' . e($siteName) . '">' . "\n";
    $html .= '<meta property="og:image" content="' . e($meta['image']) . '">' . "\n";
    $html .= '<meta property="og:locale" content="' . ($lang === 'ar' ? 'ar_EG' : 'en_US') . '">' . "\n";
    $html .= '<meta property="og:locale:alternate" content="' . ($lang === 'ar' ? 'en_US' : 'ar_EG') . '">' . "\n";

    // Twitter Card tags
    $html .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
    $html .= '<meta name="twitter:title" content="' . e($meta['title']) . '">' . "\n";
    $html .= '<meta name="twitter:description" content="' . e($meta['description']) . '">' . "\n";
    $html .= '<meta name="twitter:image" content="' . e($meta['image']) . '">' . "\n";

    // Additional meta
    $html .= '<meta name="format-detection" content="telephone=no">' . "\n";
    $html .= '<meta name="theme-color" content="#0066cc">' . "\n";

    return $html;
}



/**
 * Get alternate language URL
 */
function getAlternateUrl($lang)
{
    $currentUrl = getCurrentUrl();

    // If URL contains language parameter, replace it
    if (preg_match('/[?&]lang=(en|ar)/', $currentUrl)) {
        return preg_replace('/([?&])lang=(en|ar)/', '$1lang=' . $lang, $currentUrl);
    }

    // Otherwise, add language parameter
    $separator = strpos($currentUrl, '?') === false ? '?' : '&';
    return $currentUrl . $separator . 'lang=' . $lang;
}

/**
 * Generate schema.org markup
 */
function generateSchemaMarkup($type = 'WebSite', $data = [])
{
    $lang = getCurrentLanguage();
    $siteName = getSetting('site_name_' . $lang, SITE_NAME);

    switch ($type) {
        case 'WebSite':
            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => $siteName,
                'url' => SITE_URL,
                'description' => getSetting('site_description_' . $lang, ''),
                'inLanguage' => $lang === 'ar' ? 'ar-EG' : 'en-US',
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => SITE_URL . '/search?q={search_term_string}',
                    'query-input' => 'required name=search_term_string'
                ]
            ];
            break;

        case 'Person':
            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'Person',
                'name' => 'Ahmed Ashraf',
                'url' => SITE_URL,
                'email' => getSetting('contact_email', 'AhmedFrost@gmail.com'),
                'telephone' => getSetting('contact_phone', '+20-112-738-8682'),
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressLocality' => 'Cairo',
                    'addressCountry' => 'Egypt'
                ],
                'jobTitle' => $lang === 'ar' ? 'متخصص تسويق' : 'Marketing Specialist',
                'alumniOf' => [
                    '@type' => 'EducationalOrganization',
                    'name' => $lang === 'ar' ? 'كلية الحقوق - جامعة عين شمس' : 'Faculty of Law, Ain Shams University'
                ],
                'sameAs' => array_filter([
                    getSetting('facebook_url'),
                    getSetting('twitter_url'),
                    getSetting('linkedin_url'),
                    getSetting('instagram_url'),
                    getSetting('github_url')
                ])
            ];
            break;

        case 'Article':
            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => $data['title'] ?? '',
                'description' => $data['description'] ?? '',
                'image' => $data['image'] ?? '',
                'datePublished' => $data['published'] ?? '',
                'dateModified' => $data['modified'] ?? $data['published'] ?? '',
                'author' => [
                    '@type' => 'Person',
                    'name' => 'Ahmed Ashraf'
                ],
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => $siteName,
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => SITE_URL . '/assets/images/logo.png'
                    ]
                ],
                'mainEntityOfPage' => [
                    '@type' => 'WebPage',
                    '@id' => $data['url'] ?? getCurrentUrl()
                ]
            ];
            break;

        case 'Service':
            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'Service',
                'name' => $data['name'] ?? '',
                'description' => $data['description'] ?? '',
                'provider' => [
                    '@type' => 'Person',
                    'name' => 'Ahmed Ashraf'
                ],
                'areaServed' => [
                    '@type' => 'Country',
                    'name' => 'Egypt'
                ]
            ];

            if (!empty($data['price'])) {
                $schema['offers'] = [
                    '@type' => 'Offer',
                    'price' => $data['price'],
                    'priceCurrency' => 'EGP'
                ];
            }
            break;

        case 'BreadcrumbList':
            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => []
            ];

            $position = 1;
            foreach ($data as $item) {
                $schema['itemListElement'][] = [
                    '@type' => 'ListItem',
                    'position' => $position,
                    'name' => $item['name'],
                    'item' => $item['url']
                ];
                $position++;
            }
            break;

        default:
            $schema = array_merge(['@context' => 'https://schema.org'], $data);
    }

    return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</script>';
}

/**
 * Render SEO head content
 */
function renderSEOHead($options = [], $schemaType = 'WebSite', $schemaData = [])
{
    $html = generateMetaTags($options);
    $html .= "\n" . generateSchemaMarkup($schemaType, $schemaData);
    return $html;
}

/**
 * Get page SEO settings from database
 */
function getPageSEO($pageType, $pageId)
{
    try {
        $stmt = db()->prepare("SELECT * FROM seo_settings WHERE page_type = ? AND page_id = ?");
        $stmt->execute([$pageType, $pageId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Save page SEO settings
 */
function savePageSEO($pageType, $pageId, $data)
{
    try {
        $stmt = db()->prepare("INSERT INTO seo_settings 
            (page_type, page_id, meta_title_en, meta_title_ar, meta_description_en, meta_description_ar, 
             meta_keywords, og_title, og_description, og_image, twitter_title, twitter_description, 
             twitter_image, canonical_url, robots, schema_markup) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            meta_title_en = VALUES(meta_title_en),
            meta_title_ar = VALUES(meta_title_ar),
            meta_description_en = VALUES(meta_description_en),
            meta_description_ar = VALUES(meta_description_ar),
            meta_keywords = VALUES(meta_keywords),
            og_title = VALUES(og_title),
            og_description = VALUES(og_description),
            og_image = VALUES(og_image),
            twitter_title = VALUES(twitter_title),
            twitter_description = VALUES(twitter_description),
            twitter_image = VALUES(twitter_image),
            canonical_url = VALUES(canonical_url),
            robots = VALUES(robots),
            schema_markup = VALUES(schema_markup)");

        return $stmt->execute([
            $pageType,
            $pageId,
            $data['meta_title_en'] ?? null,
            $data['meta_title_ar'] ?? null,
            $data['meta_description_en'] ?? null,
            $data['meta_description_ar'] ?? null,
            $data['meta_keywords'] ?? null,
            $data['og_title'] ?? null,
            $data['og_description'] ?? null,
            $data['og_image'] ?? null,
            $data['twitter_title'] ?? null,
            $data['twitter_description'] ?? null,
            $data['twitter_image'] ?? null,
            $data['canonical_url'] ?? null,
            $data['robots'] ?? 'index, follow',
            $data['schema_markup'] ?? null
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Generate XML sitemap
 */
function generateSitemap()
{
    $urls = [];

    // Static pages
    $staticPages = [
        ['loc' => SITE_URL . '/', 'priority' => '1.0', 'changefreq' => 'daily'],
        ['loc' => SITE_URL . '/about.php', 'priority' => '0.8', 'changefreq' => 'monthly'],
        ['loc' => SITE_URL . '/services.php', 'priority' => '0.8', 'changefreq' => 'weekly'],
        ['loc' => SITE_URL . '/projects.php', 'priority' => '0.8', 'changefreq' => 'weekly'],
        ['loc' => SITE_URL . '/blog.php', 'priority' => '0.9', 'changefreq' => 'daily'],
        ['loc' => SITE_URL . '/media.php', 'priority' => '0.6', 'changefreq' => 'weekly'],
        ['loc' => SITE_URL . '/contact.php', 'priority' => '0.7', 'changefreq' => 'monthly'],
    ];

    foreach ($staticPages as $page) {
        $urls[] = $page;
    }

    // Blog posts
    try {
        $stmt = db()->query("SELECT slug, updated_at FROM posts WHERE status = 'published' ORDER BY updated_at DESC");
        while ($post = $stmt->fetch()) {
            $urls[] = [
                'loc' => SITE_URL . '/post.php?slug=' . $post['slug'],
                'lastmod' => date('Y-m-d', strtotime($post['updated_at'])),
                'priority' => '0.7',
                'changefreq' => 'monthly'
            ];
        }
    } catch (PDOException $e) {
    }

    // Projects
    try {
        $stmt = db()->query("SELECT slug, updated_at FROM projects WHERE status = 'published' ORDER BY updated_at DESC");
        while ($project = $stmt->fetch()) {
            $urls[] = [
                'loc' => SITE_URL . '/project.php?slug=' . $project['slug'],
                'lastmod' => date('Y-m-d', strtotime($project['updated_at'])),
                'priority' => '0.7',
                'changefreq' => 'monthly'
            ];
        }
    } catch (PDOException $e) {
    }

    // Services
    try {
        $stmt = db()->query("SELECT slug, updated_at FROM services WHERE status = 'active' ORDER BY sort_order");
        while ($service = $stmt->fetch()) {
            $urls[] = [
                'loc' => SITE_URL . '/service.php?slug=' . $service['slug'],
                'lastmod' => date('Y-m-d', strtotime($service['updated_at'])),
                'priority' => '0.7',
                'changefreq' => 'monthly'
            ];
        }
    } catch (PDOException $e) {
    }

    // Categories
    try {
        $stmt = db()->query("SELECT slug FROM categories ORDER BY name_en");
        while ($category = $stmt->fetch()) {
            $urls[] = [
                'loc' => SITE_URL . '/blog.php?category=' . $category['slug'],
                'priority' => '0.6',
                'changefreq' => 'weekly'
            ];
        }
    } catch (PDOException $e) {
    }

    // Generate XML
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    foreach ($urls as $url) {
        $xml .= "  <url>\n";
        $xml .= "    <loc>" . e($url['loc']) . "</loc>\n";
        if (isset($url['lastmod'])) {
            $xml .= "    <lastmod>" . $url['lastmod'] . "</lastmod>\n";
        }
        if (isset($url['changefreq'])) {
            $xml .= "    <changefreq>" . $url['changefreq'] . "</changefreq>\n";
        }
        if (isset($url['priority'])) {
            $xml .= "    <priority>" . $url['priority'] . "</priority>\n";
        }
        $xml .= "  </url>\n";
    }

    $xml .= '</urlset>';

    return $xml;
}
