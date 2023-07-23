<?php
/*
* Plugin Name: Simply Bruijn Online Sitemap
* Plugin URI: https://simplybruijnonline.nl/
* Description: Maak jouw sitemap via deze tool
* Version: 1.0
* Author: Simply Bruijn Online
* Author URI: https://simplybruijnonline.nl/
* License: GPLv2
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// Exit if accessed directly
if(!defined('ABSPATH'))
{
    exit;
}

if(!defined('SBO_SITEMAP_DIR'))
{
    define('SBO_SITEMAP_DIR', plugin_dir_path( __FILE__ ));
}

$sbo_sitemap = new SboSitemap();
add_action('admin_menu', array($sbo_sitemap, 'addAdminMenus'));

/**
 * Class WpPluginName
 */
class SboSitemap
{
    public function AdminPage()
    {
        ?>
        <h1>Sitemap {Google, Bing, Yahoo}</h1><hr/>
        <p style="padding-right:15px;">Een sitemap is een essentieel hulpmiddel voor het optimaliseren van de navigatie en zichtbaarheid van je website. Het is een gestructureerde lijst van alle pagina's op jouw website, die zoekmachines zoals Google helpen om de inhoud beter te begrijpen en te indexeren. Een sitemap biedt een overzicht van de hiërarchie en relatie tussen de verschillende pagina's op jouw site. Dit vergemakkelijkt het crawlen van de website door zoekmachines, waardoor ze gemakkelijker en sneller nieuwe pagina's kunnen ontdekken en indexeren. Hierdoor kunnen jouw webpagina's beter worden weergegeven in zoekresultaten. Het hebben van een sitemap is vooral handig voor websites met een uitgebreide inhoud, dynamische pagina's, of websites die regelmatig nieuwe content toevoegen. Het zorgt ervoor dat zelfs de diepste pagina's van je site gezien worden door zoekmachines en helpt om eventuele problemen met het indexeren van jouw website op te lossen. Een sitemap kan ook de SEO-prestaties van jouw website verbeteren. Door het verstrekken van duidelijke en gestructureerde informatie over jouw inhoud, kan dit de relevantie en autoriteit van jouw site in de ogen van zoekmachines vergroten. Bij het maken van een sitemap is het belangrijk om deze up-to-date te houden en eventuele wijzigingen in jouw site te reflecteren. Gelukkig zijn er veel tools beschikbaar om sitemaps automatisch te genereren en bij te werken], zoals op deze pagina. Kortom, een sitemap is een onmisbaar hulpmiddel om de vindbaarheid en prestaties van jouw website te verbeteren. Het vergemakkelijkt de indexering van jouw pagina's door zoekmachines en draagt bij aan een betere gebruikerservaring voor jouw websitebezoekers.</p>
        <form action="<?php echo get_admin_url(); ?>admin-post.php" method="post">
            <input type='hidden' name='action' value='create-sitemap' />
            <button type="submit" class="btn btn-primary" style="cursor:pointer;background: #C41630;padding: 12px;border: none;color: white;border-radius: 2px;box-shadow: 2px 3px #bebebe;font-size: 18px;">Nieuwe genereren</button>
            <a href="/sitemap.xml" target="_blank" class="btn btn-primary" style="background: #C41630;padding: 12px;border: none;color: white;border-radius: 2px;box-shadow: 2px 3px #bebebe;text-decoration:none;font-size: 18px;">Hudige sitemap bekijken</a>
        </form>
        <?php
    }

    public function addAdminMenus()
    {
        add_menu_page('Sitemap', 'Sitemap', 'manage_options', 'sbo-sitemap', array($this, 'AdminPage'));
    }
}

add_action('admin_post_create-sitemap', 'sbo_create_sitemap');
function sbo_create_sitemap() {

    $args = array(
        'sort_order' => 'asc',
        'sort_column' => 'post_title',
        'hierarchical' => 1,
        'exclude' => '',
        'include' => '',
        'meta_key' => '',
        'meta_value' => '',
        'authors' => '',
        'child_of' => 0,
        'parent' => -1,
        'exclude_tree' => '',
        'number' => '',
        'offset' => 0,
        'post_type' => 'page',
        'post_status' => 'publish'
    );

    $posts = get_pages($args);
    array_push($posts, get_posts());
    $pages = [];

    foreach($posts as $page) {
        if(is_array($page)) {
            foreach($page as $post) {
                $pages[] = $post;
            }
        }
        else {
            $pages[] = $page;
        }
    }

    if(file_exists(SIMPLYBRUIJNONLINEROOT.'/sitemap.xml')) {
        unlink(SIMPLYBRUIJNONLINEROOT.'/sitemap.xml');
    }

    sbo_generate_sitemap($pages); // pass pages
}

function sbo_generate_sitemap($pages) {
    $sitemap_content = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    foreach($pages as $page) {
        $page_url = get_permalink($page->ID);
        
        $sitemap_content .= '
        <url>
            <loc>'.$page_url.'</loc>
            <lastmod>'.$page->post_modified.'</lastmod>
        </url>
        ';
    }

    $sitemap_content .= '</urlset>';

    $sitemap = fopen(SIMPLYBRUIJNONLINEROOT.'/sitemap.xml', 'w') or die('Unable to open file!');

    fwrite($sitemap, $sitemap_content);
    fclose($sitemap);

    header('location: /sitemap.xml');
}