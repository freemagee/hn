<?php
/**
 * Display a list of hacker news stories.
 */

/*
 * INCLUDES
 */

require_once realpath(__DIR__).'/inc/common_functions.php';

/*
 * VARIABLES
 */

$dir        = dirname(__FILE__);
$sourceFile = file_get_contents($dir.'/../data/stories.json');
$sourceObj  = json_decode($sourceFile);
$source     = objectToArray($sourceObj);
$html       = makeHnList($source);

/*
 * FUNCTIONS
 */


/**
 * Take the array of HN stories and begin to process for output.
 *
 * @param array $source List of links taken from Hacker News.
 *
 * @return string
 */
function makeHnList(array $source)
{
    $today      = time();
    $url        = "//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
    $escapedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

    if (empty($source) === false) {
        $limit = count($source);
        $html  = '<ul class="links-list">';

        for ($i = 0; $i < $limit; $i++) {
            $id    = $source[$i]['id'];
            $title = $source[$i]['title'];
            if (preg_match('/Ask HN:/', $title) === 1
                || preg_match('/Show HN:/', $title) === 1
                || preg_match('/Apply HN:/', $title) === 1
                || preg_match('/Tell HN:/', $title) === 1
            ) {
                $link            = $escapedUrl.'comments.php?id='.$id;
                $hostDomainShort = 'news.ycombinator.com';
            } else {
                $link            = $source[$i]['url'];
                $sourceUrl       = parse_url($link);
                $hostDomain      = $sourceUrl['host'];
                $hostDomainShort = str_replace('www.', '', $hostDomain);
            }

            $delay = $source[$i]['time'];
            if (empty($source[$i]['descendants']) === false) {
                $commentsCount = $source[$i]['descendants'];

                $comments = '<a class="links-list__comments links-list__comments--has-comments" href="comments.php?id='.$id.'">'.$commentsCount.' comments</a>';
            } else {
                $comments = '<span class="links-list__comments links-list__comments--no-comments">No comments</span>';
            }

            $posted = timeElapsed($today - $delay);
            $number = ($i + 1);

            // Build news list item.
            $html .= '<li class="links-list__item">';
            $html .= '<a class="links-list__link" href="'.$link.'">';
            $html .= '<div class="links-list__count">'.$number.'</div>';
            $html .= '<div class="links-list__content">';
            $html .= '<span class="links-list__title">'.$title.'</span>';
            $html .= '<span class="links-list__source">'.$hostDomainShort.'</span>';
            $html .= '</div>';
            $html .= '</a>';
            $html .= '<div class="links-list__meta">';
            $html .= '<span class="links-list__posted">Posted: '.$posted.' ago</span>';
            $html .= $comments;
            $html .= '</div>';
            $html .= '</li>';
        }//end for

        $html .= '</ul>';
    } else {
        $html  = '<div class="no-news">';
        $html .= '<h3>No news!</h3>';
        $html .= '<p>Unfortunately there has been an error displaying articles.</p>';
        $html .= '</div>';
    }//end if

    return $html;

}//end makeHnList()


/**
 * Take a microtime and return a human readable string
 *
 * @param integer $secs Unix time stamp.
 *
 * @return string Time in human readable form.
 */
function timeElapsed(int $secs)
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

}//end timeElapsed()


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
            <?php require_once realpath(__DIR__).'/inc/header.php'; ?>
            <?php echo $html; ?>
            <?php require_once realpath(__DIR__).'/inc/footer.php'; ?>
        </div>
    </body>
</html>
