<?php

/* Hook into the 'init' action so that the function
	* @pagekage 
	* Shin
	*/

add_action('init', 'create_new_custom_post_type');
add_action('init', 'create_custom_taxonomies');

/*
* Creating a function to create our CPT
*/


function shin_create_post_type($args)
{
    if (!is_array($args) || !$args['post_type'] || !$args['name'] || !$args['single'] || !$args['slug']) return;
    $post_type = $args['post_type'];
    $name = $args['name'];
    $single = $args['single'];
    $slug = $args['slug'];
    $rewrite = (isset($args['rewrite'])) ? $args['rewrite'] : $args['slug'];
    $icon = $args['icon'] ? $args['icon'] : "dashicons-star-filled";
    $supports = isset($args['supports']) ? $args['supports'] : array('title', 'editor', 'revisions', 'thumbnail', 'author', 'excerpt', 'custom-fields');
    $public = isset($args['public']) ? $args['public'] : true;
    $capabilities = isset($args['capabilities']) ? $args['capabilities'] : array();
    $hierarchical = isset($args['hierarchical']) ? $args['hierarchical'] : array();
    $menu_position = isset($args['menu_position']) ? $args['menu_position'] : 5;
    $show_ui = isset($args['show_ui']) ? $args['show_ui'] : true;


    register_post_type($post_type, array(
        'label'               => __($post_type, 'Shin'),
        'labels' => array(
            'name' => __($name, 'shin'),
            'singular_name' => __($single, 'shin'),
            'add_new' => __('Add New ' . $single, 'shin'),
            'add_new_item' => __('Add New ' . $single, 'shin'),
            'edit_item' => __('Edit ' . $single, 'shin'),
            'new_item' => __('New' . $single, 'shin'),
            'all_items' => __('All ' . $name, 'shin'),
            'view_item' => __('View ' . $single, 'shin'),
            'search_items' => __('Filter By ' . $name, 'shin'),
            'not_found' => __('Not Found ' . $single, 'shin'),
            'not_found_in_trash' => __('Not Found ' . $single . ' In Trash', 'shin'),
            'parent_item_colon' => '',
            'menu_name' => __($name, 'shin')
        ),
        'public' => $public,
        'menu_icon' => $icon,
        'exclude_from_search' => false,
        'menu_position'       => $menu_position,
        'has_archive' => true,
        'taxonomies' => array($post_type),
        'rewrite' => array('slug' => $rewrite),
        'publicly_queryable' => $public,
        'supports' => $supports,
        'capabilities' => $capabilities,
        'hierarchical'        => $hierarchical,
        'show_ui'             => $show_ui,
        'show_in_menu'        => $show_ui,
        'show_in_nav_menus'   => $show_ui,
        'show_in_admin_bar'   => $show_ui,
    ));
}

function shin_create_taxonomy($args)
{
    if (!is_array($args) || !$args['post_type'] || !$args['name'] || !$args['single'] || !$args['taxonomy']) return;

    $post_type = $args['post_type'];
    $name = $args['name'];
    $single = $args['single'];
    $taxonomy = $args['taxonomy'];
    $rewrite = (isset($args['rewrite'])) ? $args['rewrite'] : $taxonomy;
    $hierarchical = (isset($args['hierarchical'])) ? $args['hierarchical'] : true;
    $labels = array(
        'name' => __($name, 'shin'),
        'singular_name' => __($single, 'shin'),
        'search_items' => __('Filter By ' . $name, 'shin'),
        'popular_items' => __('Popular ' . $name, 'shin'),
        'all_items' => __('All ' . $name, 'shin'),
        'parent_item' => null,
        'parent_item_colon' => null,
        'edit_item' => __('Edit ' . $single, 'shin'),
        'update_item' => __('Update ' . $single, 'shin'),
        'add_new_item' => __('Add New ' . $single, 'shin'),
        'new_item_name' => __('Add New ' . $single, 'shin'),
        'menu_name' => __($name, 'shin'),

    );
    $args = array(
        'hierarchical' => $hierarchical,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => $rewrite),
    );
    register_taxonomy($taxonomy, $post_type, $args);
}

function create_new_custom_post_type()
{
    $args = array(
        array(
            "post_type" => 'services',
            "name" => "Services",
            "single" => "Services",
            "slug" => "services",
            "hierarchical" => true,
            "show_ui" => true,
            'menu_position'       => 4,
            "icon" => "dashicons-lightbulb",
        ),
        array(
            "post_type" => 'require_form',
            "name" => "Require Forms",
            "single" => "Require Form",
            "slug" => "require-form",
            "hierarchical" => true,
            "show_ui" => true,
            'menu_position'       => 4,
            "icon" => "dashicons-lightbulb",
        ),
        array(
            "post_type" => 'teams',
            "name" => "Teams",
            "single" => "Teams",
            "slug" => "our-teams",
            "hierarchical" => true,
            "show_ui" => true,
            'menu_position'       => 4,
            "icon" => "dashicons-lightbulb",
        ),

    );
    foreach ($args as $arg) {
        if ($arg['post_type']) {
            shin_create_post_type($arg);
        }
    }
}



function create_custom_taxonomies()
{
    $args = array(
        array(
            "post_type" => array('services'),
            "name" => "Service Categories",
            "single" => "Service Categories",
            "taxonomy" => "service_categories",
            'taxonomies' => array('category', 'post_tag'),
            "hierarchical" => true,
        ),
        array(
            "post_type" => array('require_form'),
            "name" => "Require Form Categories",
            "single" => "Require Form Categories",
            "taxonomy" => "require-form-categories",
            'taxonomies' => array('category', 'post_tag'),
            "hierarchical" => true,
        ),
        array(
            "post_type" => array('teams'),
            "name" => "Teams Categories",
            "single" => "Teams Categories",
            "taxonomy" => "teams-categories",
            'taxonomies' => array('category', 'post_tag'),
            "hierarchical" => true,
        ),


    );
    foreach ($args as $arg) {
        if (!empty($arg['post_type'])) {
            shin_create_taxonomy($arg);
        }
    }
}

// filter by Taxonolomy 
add_action('restrict_manage_posts', 'filter_backend_by_taxonomies', 99, 2);
function filter_backend_by_taxonomies($post_type, $which)
{

    if ('projects' !== $post_type)
        return;

    $taxonomies = array('categories_projects');

    foreach ($taxonomies as $taxonomy_slug) {
        $taxonomy_obj = get_taxonomy($taxonomy_slug);
        $taxonomy_name = $taxonomy_obj->labels->name;
        $terms = get_terms($taxonomy_slug);
        echo "<select name='{$taxonomy_slug}' id='{$taxonomy_slug}' class='postform'>";
        echo '<option value="">' . sprintf(esc_html__('Show All %s', 'text_domain'), $taxonomy_name) . '</option>';
        foreach ($terms as $term) {
            printf(
                '<option value="%1$s" %2$s>%3$s (%4$s)</option>',
                $term->slug,
                ((isset($_GET[$taxonomy_slug]) && ($_GET[$taxonomy_slug] == $term->slug)) ? ' selected="selected"' : ''),
                $term->name,
                $term->count
            );
        }
        echo '</select>';
    }
}
