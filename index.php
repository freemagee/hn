<?php
/*******************************************************************************
* INCLUDES
 ******************************************************************************/

include_once(realpath(__DIR__) . '/src/inc/common_functions.php');

/*******************************************************************************
* VARIABLES
 ******************************************************************************/
$dir = dirname(__FILE__);
$source_file = file_get_contents($dir . '/src/data/data.json');
$source_obj = json_decode($source_file);
$source = object_to_array($source_obj);
$html = make_hn_list($source);

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
    $url =  "//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
    $escaped_url = htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' );

    if (!empty($source)) {
        $limit = count($source);
        $html = '<ul class="links-list">';

        for ($i = 0; $i < $limit; $i++) {
            if ($source[$i]['type'] !== 'job') {
                $id = $source[$i]['id'];
                $title = $source[$i]['title'];
                if (preg_match("/Ask HN:/", $title)
                || preg_match("/Show HN:/", $title)
                || preg_match("/Apply HN:/", $title)) {
                    $link = $escaped_url . 'comments.php?id=' . $id;
                    $host_domain_short = 'news.ycombinator.com';
                } else {
                    $link = $source[$i]['url'];
                    $source_url = parse_url($link);
                    $host_domain = $source_url['host'];
                    $host_domain_short = str_replace('www.', '', $host_domain);
                }
                $delay = $source[$i]['time'];
                if (!empty($source[$i]['descendants'])) {
                    $comments_count = $source[$i]['descendants'];

                    $comments = '<a class="links-list__comments links-list__comments--has-comments" href="comments.php?id=' . $id . '">' . $comments_count . ' comments</a>';
                } else {
                    $comments = '<span class="links-list__comments links-list__comments--no-comments">No comments</span>';
                }
                $posted = time_elapsed($today-$delay);
                $number = $i + 1;

                // Build news list item
                $html .= '<li class="links-list__item">';
                $html .= '<a class="links-list__link" href="' . $link . '">';
                $html .= '<div class="links-list__count">' . $number . '</div>';
                $html .= '<div class="links-list__content">';
                $html .= '<span class="links-list__title">' . $title . '</span>';
                $html .= '<span class="links-list__source">' . $host_domain_short . '</span>';
                $html .= '</div>';
                $html .= '</a>';
                $html .= '<span class="links-list__posted">Posted: ' . $posted . ' ago</span>';
                $html .= $comments;
                $html .= '</li>';
            }
        }

        $html .= '</ul>';
    } else {
        $html = '<div class="no-news">';
        $html .= '<h3>No news!</h3>';
        $html .= '<p>Unfortunately there has been an error displaying articles.</p>';
        $html .= '</div>';
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
            <header style="display: none;">
                <h1 class="primary-title">
                    <a href="./" class="primary-title__link">
                        <img src="src/img/hn-logo.svg" class="primary-title__logo" />
                        <span class="primary-title__text">Responsive Hacker News</span>
                    </a>
                </h1>
            </header>
            <?php echo $html; ?>
            <footer>
                <div class="attributions">
                    <p>Responsive Hacker News created by <a href="http://neilmagee.com">Neil Magee</a> | This site is based on <a href="https://news.ycombinator.com">Hacker News</a></p>
                </div>
            </footer>
        </div>
    </body>
</html>