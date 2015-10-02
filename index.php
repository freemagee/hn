<?php
/*******************************************************************************
* VARIABLES
 ******************************************************************************/
$dir = dirname(__FILE__);
$source_file = file_get_contents($dir . '/src/data/data.json');
$source_obj = json_decode($source_file);
$source = object_to_array($source_obj);
$hn_list = make_hn_list($source);

/*******************************************************************************
* FUNCTIONS
 ******************************************************************************/

/**
 * [make_hn_list description]
 * @param  [array] $source [list of links taken from Hacker News]
 * @return [str]         [html]
 */
function make_hn_list($source) {
    $today = time();

    if (!empty($source)) {
        $limit = count($source);
        $html = '<ul id="links-list">';

        for ($i = 0; $i < $limit; $i++) {
            if ($source[$i]['type'] !== 'job') {
                $id = $source[$i]['id'];
                $title = $source[$i]['title'];
                if (preg_match("/Ask HN:/", $title)) {
                    $link = 'https://news.ycombinator.com/item?id=' . $id;
                    $host_domain = 'news.ycombinator.com';
                } else {
                    $link = $source[$i]['url'];
                    $source_url = parse_url($link);
                    $host_domain = $source_url['host'];
                    $host_domain_short = str_replace('www.', '', $host_domain);
                }
                $delay = $source[$i]['time'];
                if (!empty($source[$i]['kids'])) {
                    $comments_count = count($source[$i]['kids']);
                    $comments = '<a class="hn-comment" href="https://news.ycombinator.com/item?id=' . $id . '">' . $comments_count . ' comments</a>';
                } else {
                    $comments = 'No comments';
                }
                $posted = time_elapsed($today-$delay);
                $number = $i + 1;

                // Build news list item
                $html .= '<li>';
                $html .= '<a class="hn-link" href="' . $link . '">';
                $html .= '<span class="count">' . $number . '</span>';
                $html .= '<div class="link-content">';
                $html .= '<span class="link-title">' . $title . '</span>';
                $html .= '<span class="source">' . $host_domain_short . '</span>';
                $html .= '<span class="posted">Posted: ' . $posted . ' ago</span>';
                $html .= '</div>';
                $html .= '</a>';
                $html .= '<span class="comments">' . $comments . '</span>';
                $html .= '</li>';
            }
        }

        $html .= '</ul>';
    } else {
        $html = '<h3>No news!</h3><p>Unfortunately there has been an error. It will be sorted out shortly.</p>';
    }

    return $html;
}

/**
 * [time_elapsed description]
 * @param  [type] $secs [unix time stamp]
 * @return [str]        [time in human readable form]
 */
function time_elapsed($secs){
    $bit = array(
        'y' => $secs / 31556926 % 12,
        'w' => $secs / 604800 % 52,
        'd' => $secs / 86400 % 7,
        'h' => $secs / 3600 % 24,
        'm' => $secs / 60 % 60
        );

    foreach($bit as $k => $v) {
        if($v > 0)$ret[] = $v . $k;
    }

    return join(' ', $ret);
}

/**
 * [pre_r | a pretty version of print_r - helps with debugging arrays]
 * @param  [array] $val
 */
function pre_r($val){
    echo '<pre>';
    print_r($val);
    echo  '</pre>';
}

/**
 * [object_to_array description]
 * @param  [type] $d [description]
 * @return [type]    [description]
 */
function object_to_array($d) {
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
    }
    else {
        // Return array
        return $d;
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Responsive Hacker News | Mobile optimised Hacker News</title>
        <meta name="description" content="Just the links from Hacker News, optimised for small screens and mobile devices.">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="favicon.ico">
        <link rel="stylesheet" href="src/css/main.css">
    </head>
    <body>
        <div class="container">

            <header class="title">
                <h1><a href="./"><img src="src/img/hn-logo.svg" class="hn-logo" />Responsive Hacker News</a></h1>
            </header>
            <?php echo $hn_list; ?>
            <footer>
                <div class="footer-inner">
                    <p>Hacker News Responsive - <a href="http://neilmagee.com">Neil Magee</a><br />
                    Original Hacker News - <a href="https://news.ycombinator.com">https://news.ycombinator.com</a></p>
                </div>
            </footer>

        </div>
    </body>
</html>