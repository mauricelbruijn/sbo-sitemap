<?php
/*
* Plugin Name: Simply Bruijn Online Sitemap
* Plugin URI: https://simplybruijnonline.nl/
* Description: Simply Bruijn Online create sitemap
* Version: 1.0
* Author: Maurice Bruijn
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
        $sitemap_content .= '
        <url>
            <loc>'.home_url().'/'. $page->post_name.'</loc>
            <lastmod>'.$page->post_modified.'</lastmod>
        </url>
        ';


//                echo $test->guid.'<br/>';
//                echo $test->post_title.'<br/>';
//                echo $test->post_name.'<br/>';
//                echo $test->post_modified.'<br/>';
    }


    $sitemap_content .= '</urlset>';

    $sitemap = fopen(SIMPLYBRUIJNONLINEROOT.'/sitemap.xml', 'w') or die('Unable to open file!');

    fwrite($sitemap, $sitemap_content);
    fclose($sitemap);

    header('location: /sitemap.xml');
}