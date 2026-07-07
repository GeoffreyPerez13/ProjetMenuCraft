<?php
class SitemapController extends BaseController
{
    public function generate(): void
    {
        header('Content-Type: application/xml; charset=utf-8');

        $restaurantModel = new Restaurant($this->pdo);
        $restaurants = $restaurantModel->getAllOnline();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // Page landing
        $xml .= '<url><loc>' . SITE_URL . '</loc><changefreq>weekly</changefreq><priority>1.0</priority></url>';

        // Pages légales
        foreach (['cgu', 'privacy', 'cookies', 'legal'] as $section) {
            $xml .= '<url><loc>' . SITE_URL . '?page=legal&amp;section=' . $section . '</loc><changefreq>monthly</changefreq><priority>0.3</priority></url>';
        }

        // Restaurants
        foreach ($restaurants as $r) {
            $xml .= '<url><loc>' . SITE_URL . '?page=display&amp;slug=' . htmlspecialchars($r->slug) . '</loc>';
            $xml .= '<lastmod>' . date('Y-m-d', strtotime($r->updated_at ?? $r->created_at)) . '</lastmod>';
            $xml .= '<changefreq>weekly</changefreq><priority>0.8</priority></url>';
        }

        $xml .= '</urlset>';
        echo $xml;
        exit;
    }
}
