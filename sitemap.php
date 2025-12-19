<?php
/**
 * Dynamic Sitemap Generator
 */

require_once 'includes/config.php';

header('Content-Type: application/xml; charset=utf-8');

echo generateSitemap();
