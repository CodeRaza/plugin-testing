<?php
function sizeSort($a, $b)
{
    $a = $a['data-size'];
    $b = $b['data-size'];
    if ($a == $b)
        return 0;
    return ($a > $b) ? -1 : 1;
}


function mkdv_remove_duplicates_from_array($arr, $key)
{
    $tempArr = array();
    $usedVals = array();
    foreach ($arr as $k => $val) {
        if (!in_array($val[$key], $usedVals)) {
            $usedVals[] = $val[$key];
            array_push($tempArr, $val);
        }
    }
    return $tempArr;
}



function dv_custom_pagination($pages = 1, $range = 2)
{
    $return = '';
    $showitems = ($range * 2) + 1;

    $paged = $_GET['pg'];
    if (empty($paged)) $paged = 1;

    if (1 != $pages) {
        $return .= "<div class='pagination'>";
        if ($paged > 2 && $paged > $range + 1 && $showitems < $pages) $return .= "<a href='?filter=" . $_GET['filter'] . "&pg=1" . "&type=" . $_SESSION['type']
            . "'>&laquo;</a>";
        if ($paged > 1 && $showitems < $pages) $return .= "<a href='?filter=" . $_GET['filter'] . "&pg=" . ($_GET['pg'] - 1) . "&type=" . $_SESSION['type']
            . "'>&lsaquo;</a>";

        for ($i = 1; $i <= $pages; $i++) {
            if (1 != $pages && (!($i >= $paged + $range + 1 || $i <= $paged - $range - 1) || $pages <= $showitems)) {
                $return .= ($paged == $i) ? "<span class='current' style='padding:10px'>" . $i . "</span>" : "<a href='?filter=" . $_GET['filter'] . "&pg=" . $i . "&type=" . $_SESSION['type']
                    . "' class='inactive' >" . $i . "</a>";
            }
        }

        if ($paged < $pages && $showitems < $pages) $return .= "<a href='?filter=" . $_GET['filter'] . "&pg=" . ($_GET['pg'] + 1) . "&type=" . $_SESSION['type']
            . "'>&rsaquo;</a>";
        if ($paged < $pages - 1 &&  $paged + $range - 1 < $pages && $showitems < $pages) $return .= "<a href='?filter=" . $_GET['filter'] . "&pg=" . ($pages) . "&type=" . $_SESSION['type']
            . "'>&raquo;</a>";
        $return .= "</div>\n";
    }

    return $return;
}

function dpdv_get_template_part($path, $name, $args)
{
    $base_paths = array(
        // child-theme
        get_stylesheet_directory(),

        // design-pact-parent plugin
        WP_PLUGIN_DIR . '/design-pact-parent',
        WP_PLUGIN_DIR . '/design-pact-parent/admin',
        WP_PLUGIN_DIR . '/design-pact-parent/includes',
        WP_PLUGIN_DIR . '/design-pact-parent/public',

        // design-pact-child plugin
        WP_PLUGIN_DIR . '/design-pact-child',
        WP_PLUGIN_DIR . '/design-pact-child/admin',
        WP_PLUGIN_DIR . '/design-pact-child/includes',
        WP_PLUGIN_DIR . '/design-pact-child/public',

        // design-pact plugin
        WP_PLUGIN_DIR . '/design-pact',
        WP_PLUGIN_DIR . '/design-pact/admin',
        WP_PLUGIN_DIR . '/design-pact/includes',
        WP_PLUGIN_DIR . '/design-pact/public',
    );

    $full_path = null;
    foreach ($base_paths as $b_p) {
        $temp_path = $b_p . "/" . $path . ".php";

        if (file_exists($temp_path)) {
            $full_path = $temp_path;
            break;
        }
    }

    if (!empty($full_path)) {
        require($full_path);
    }
}
function mkdv_get_template_part($path, $name, $args)
{
    $args = $args;
    require($path . ".php");
}

function node_api_domain()
{
    $dpdv_options = get_option('dpdv_options');
    if (!isset($dpdv_options['design_pact_node_api_domain'])) {
        wp_die('DP Core Plugin is not configured correctly.');
        return "https://node-api.designpact.one";
    }
    $domain = $dpdv_options['design_pact_node_api_domain'];
    return $domain;
}

function node_api_base_url()
{
    $url = node_api_domain() . '/api/v4';
    return $url;
}

function correct_node_api_base_url($url)
{
    $modified_url = $url;

    $modified_url = str_replace('https://us2.matchkicks.com', node_api_domain(), $modified_url);
    $modified_url = str_replace('https://us21.matchkicks.com', node_api_domain(), $modified_url);
    $modified_url = str_replace('https://us31.matchkicks.com', node_api_domain(), $modified_url);

    $modified_url = str_replace('https://us2.staging.matchkicks.com', node_api_domain(), $modified_url);
    $modified_url = str_replace('https://us21.staging.matchkicks.com', node_api_domain(), $modified_url);
    $modified_url = str_replace('https://us31.staging.matchkicks.com', node_api_domain(), $modified_url);

    $modified_url = str_replace('/api/v1', '/api/v4', $modified_url);
    $modified_url = str_replace('/api/v2', '/api/v4', $modified_url);
    $modified_url = str_replace('/api/v3', '/api/v4', $modified_url);

    return $modified_url;
}

function remove_wp_upload_base_url($url)
{
    $modified_url = $url;
    $modified_url = str_replace('https://dev.matchkicks.com/', home_url() . '/', $modified_url);
    $modified_url = str_replace('https://dev2.matchkicks.com/', home_url() . '/', $modified_url);
    $modified_url = str_replace('https://staging.matchkicks.com/', home_url() . '/', $modified_url);
    $modified_url = str_replace('https://matchkicks.local/', home_url() . '/', $modified_url);
    $modified_url = str_replace('https://matchkicks1.wpengine.com/', home_url() . '/', $modified_url);

    $modified_url = str_replace('https://nyc3.digitaloceanspaces.com/matchkicks-s3/', '', $modified_url);
    $modified_url = str_replace('https://nyc3.digitaloceanspaces.com/kidsneakertees/', '', $modified_url);
    $modified_url = str_replace(home_url() . '/wp-content/uploads/', '', $modified_url);

    $modified_url = correct_node_api_base_url($modified_url);


    return $modified_url;
}

function merge_querystring($url = null, $query = null, $recursive = false)
{

    // if there's a URL missing, set it equal to the current URL
    if ($url == null || $url == '')
        $url = home_url() . $_SERVER['REQUEST_URI'];

    // if no query string, return
    if ($query == null)
        return $url;

    // split the url into it's components
    $url_components = parse_url($url);

    // if we have the query string but no query on the original url
    // just return the URL + query string
    if (empty($url_components['query']))
        return $url . '?' . ltrim($query, '?');

    // turn the url's query string into an array
    parse_str($url_components['query'], $original_query_string);

    // turn the query string into an array
    parse_str(parse_url($query, PHP_URL_QUERY), $merged_query_string);
    // merge the query string

    if ($recursive == true)
        $merged_result = array_merge_recursive($original_query_string, $merged_query_string);
    else
        $merged_result = array_merge($original_query_string, $merged_query_string);

    // Find the original query string in the URL and replace it with the new one
    return str_replace($url_components['query'], http_build_query($merged_result), $url);
}

function dpdv_table_exists($table_name)
{
    global $wpdb;
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        return false;
    } else {
        return true;
    }
}

function dpdv_object_to_array($d)
{
    if (is_object($d)) {
        // Gets the properties of the given object
        // with get_object_vars function
        $d = get_object_vars($d);
    }

    if (is_array($d)) {
        /*
        * Return array converted to object
        * Using __FUNCTION__ (Magic constant)
        * for recursive call
        */
        return array_map(__FUNCTION__, $d);
    } else {
        // Return array
        return $d;
    }
}

function dpdv_get_viewer_ip_address()
{
    $fields = array(
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_SUCURI_CLIENTIP',
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR',
    );

    foreach ($fields as $ip_field) {
        if (!empty($_SERVER[$ip_field])) {
            return $_SERVER[$ip_field];
        }
    }

    return null;
}

function dpdv_prepare_single_collection_link($args)
{
    $url = '/single-collection';
    $url .= '?sneaker_slug=' . $args['product']['sneaker']['post_name'];
    $url .= '&product_type=' . $args['product']['product'];

    $url = '/sneaker';
    $url .= '/' . $args['product']['sneaker']['post_name'];
    $url .= '?product_type=' . $args['product']['product'];

    return $url;
}

function dpdv_prepare_single_product_link($sneaker, $design, $product_type, $options = array())
{
    if (!is_array($options)) {
        $options = array();
    }
    if (empty($options['scheme'])) {
        $options['scheme'] = '2';
    }

    if (is_string($sneaker)) {
        $sneaker = get_post($sneaker);
    }
    if (is_string($design)) {
        $design = get_post($design);
    }

    $query_params = array(
        'sneakerSlug' => is_object($sneaker) ? $sneaker->post_name : $sneaker['post_name'],
        'designSlug' => is_object($design) ? $design->post_name : $design['post_name'],
        'productType' => $product_type,
    );

    if (str_contains(get_site_url(), 'matchkicks.')) {
        $query_params = array(
            'sneaker' => is_object($sneaker) ? $sneaker->post_name : $sneaker['post_name'],
            'design' => is_object($design) ? $design->post_name : $design['post_name'],
            'product' => $product_type,
        );
    }

    if (isset($_GET['productColor'])) {
        $query_params['productColor'] = $_GET['productColor'];
    }

    $url = "/single-product-page-dv"; // test-url
    if (str_contains(get_site_url(), 'matchkicks.')) {
        $url = "/product/match";
    } else if ($options['scheme'] == '2') {
        $url = "/wproduct/custom-match";
    }

    $i = 0;
    foreach ($query_params as $param_key => $param_val) {
        if ($i == 0) {
            $url .= '?' . $param_key . '=' . $param_val;
        } else {
            $url .= '&' . $param_key . '=' . $param_val;
        }
        $i++;
    }

    return $url;
}

function shuffle_assoc(&$array)
{
    $keys = array_keys($array);

    shuffle($keys);

    foreach ($keys as $key) {
        $new[$key] = $array[$key];
    }

    $array = $new;

    return true;
}
