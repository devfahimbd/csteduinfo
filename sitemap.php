<?php
require_once 'includes/config.php';

header('Content-Type: application/xml');
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc><?php echo SITE_URL; ?>/</loc>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc><?php echo SITE_URL; ?>/about.php</loc>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc><?php echo SITE_URL; ?>/faculty.php</loc>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc><?php echo SITE_URL; ?>/notice.php</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc><?php echo SITE_URL; ?>/gallery.php</loc>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    <url>
        <loc><?php echo SITE_URL; ?>/resources.php</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc><?php echo SITE_URL; ?>/contact.php</loc>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
    <?php
    // Dynamic URLs
    try {
        $tables = [
            ['notices', 'notice-details.php', 'slug'],
            ['gallery', 'gallery-details.php', 'slug'],
            ['resources', 'resource-details.php', 'slug'],
        ];
        foreach ($tables as $t) {
            $stmt = $pdo->query("SELECT {$t[2]} FROM {$t[0]} WHERE status = 1");
            while ($row = $stmt->fetch()) {
                echo '<url><loc>' . SITE_URL . '/' . $t[1] . '?slug=' . urlencode($row[$t[2]]) . '</loc><changefreq>monthly</changefreq><priority>0.6</priority></url>';
            }
        }
        // Teachers
        $stmt = $pdo->query("SELECT id, name FROM teachers WHERE status = 1");
        while ($row = $stmt->fetch()) {
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $row['name'])) . '-' . $row['id'];
            echo '<url><loc>' . SITE_URL . '/teacher-details.php?slug=' . urlencode($slug) . '</loc><changefreq>monthly</changefreq><priority>0.6</priority></url>';
        }
    } catch (Exception $e) {
        // Silent fail for sitemap
    }
    ?>
</urlset>
