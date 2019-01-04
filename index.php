<?php
/*
 * INCLUDES
 */

require_once realpath(__DIR__).'/src/inc/common_functions.php';

/*
 * VARIABLES
 */

$dir         = dirname(__FILE__);
$source_file = file_get_contents($dir.'/src/data/articles.json');
$source_obj  = json_decode($source_file);
$source      = object_to_array($source_obj);
$html        = make_hn_list($source);

/*
 * FUNCTIONS
 */


/**
 * [make_hn_list description]
 *
 * @param  [array] $source [list of links taken from Hacker News]
 * @return [str]         [html]
 */
function make_hn_list($source)
{
    $today       = time();
    $url         = "//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
    $escaped_url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

    if (!empty($source)) {
        $limit = count($source);
        $html  = '<ul class="links-list">';

        for ($i = 0; $i < $limit; $i++) {
            if ($source[$i]['type'] !== 'job') {
                $id    = $source[$i]['id'];
                $title = $source[$i]['title'];
                if (preg_match('/Ask HN:/', $title)
                    || preg_match('/Show HN:/', $title)
                    || preg_match('/Apply HN:/', $title)
                ) {
                    $link              = $escaped_url.'comments.php?id='.$id;
                    $host_domain_short = 'news.ycombinator.com';
                } else {
                    $link              = $source[$i]['url'];
                    $source_url        = parse_url($link);
                    $host_domain       = $source_url['host'];
                    $host_domain_short = str_replace('www.', '', $host_domain);
                }

                $delay = $source[$i]['time'];
                if (!empty($source[$i]['descendants'])) {
                    $comments_count = $source[$i]['descendants'];

                    $comments = '<a class="links-list__comments links-list__comments--has-comments" href="comments.php?id='.$id.'">'.$comments_count.' comments</a>';
                } else {
                    $comments = '<span class="links-list__comments links-list__comments--no-comments">No comments</span>';
                }

                $posted = time_elapsed($today - $delay);
                $number = ($i + 1);

                // Build news list item
                $html .= '<li class="links-list__item">';
                $html .= '<a class="links-list__link" href="'.$link.'">';
                $html .= '<div class="links-list__count">'.$number.'</div>';
                $html .= '<div class="links-list__content">';
                $html .= '<span class="links-list__title">'.$title.'</span>';
                $html .= '<span class="links-list__source">'.$host_domain_short.'</span>';
                $html .= '</div>';
                $html .= '</a>';
                $html .= '<div class="links-list__meta">';
                $html .= '<span class="links-list__posted">Posted: '.$posted.' ago</span>';
                $html .= $comments;
                $html .= '</div>';
                $html .= '</li>';
            }//end if
        }//end for

        $html .= '</ul>';
    } else {
        $html  = '<div class="no-news">';
        $html .= '<h3>No news!</h3>';
        $html .= '<p>Unfortunately there has been an error displaying articles.</p>';
        $html .= '</div>';
    }//end if

    return $html;

}//end make_hn_list()


/**
 * [time_elapsed description]
 *
 * @param  [type] $secs [unix time stamp]
 * @return [str]        [time in human readable form]
 */
function time_elapsed($secs)
{
    $bit = [
        'y' => ($secs / 31556926 % 12),
        'w' => ($secs / 604800 % 52),
        'd' => ($secs / 86400 % 7),
        'h' => ($secs / 3600 % 24),
        'm' => ($secs / 60 % 60),
    ];

    foreach ($bit as $k => $v) {
        if ($v > 0) {
            $ret[] = $v.$k;
        }
    }

    return join(' ', $ret);

}//end time_elapsed()


?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Responsive Hacker News</title>
        <meta name="description" content="Just the links from Hacker News, optimised for small screens and mobile devices.">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#ff6600">

        <link rel="shortcut icon" href="favicon.ico">
        <link rel="apple-touch-icon" sizes="180x180" href="./static/img/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="./static/img/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="./static/img/favicon-16x16.png">

        <link rel="stylesheet" href="./static/css/main.css">
    </head>
    <body>
        <div class="container">
            <?php require_once realpath(__DIR__).'/src/inc/header.php'; ?>
            <?php echo $html; ?>
            <?php require_once realpath(__DIR__).'/src/inc/footer.php'; ?>
        </div>
    </body>
</html>
