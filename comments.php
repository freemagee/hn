<?php
/*******************************************************************************
* INCLUDES
 ******************************************************************************/

include_once(realpath(__DIR__) . '/src/inc/common_functions.php');

/*******************************************************************************
* VARIABLES
 ******************************************************************************/

$_GET = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
$article_id = $_GET['id'];

/*******************************************************************************
 * LOGIC
 ******************************************************************************/

if (preg_match("/^[0-9]{8}$/", $article_id)) {
    $this_articles_comments = get_article_comments($article_id);
    $html = generate_comments_html($this_articles_comments);
} else {
    $html = '<h3>No comments!</h3><p>Unfortunately there has been an error with this articles comments.</p>';
}

/*******************************************************************************
* FUNCTIONS
 ******************************************************************************/

function get_article_comments($id) {
    $dir = dirname(__FILE__);
    $source_file = file_get_contents($dir . '/src/data/comments.json');
    $source_obj = json_decode($source_file);
    $source = object_to_array($source_obj);
    $output = $source[$id]['comments'];

    return $output;
}

function generate_comments_html($comments) {
    $output = '';

    foreach ($comments as $key => $value) {
        if (is_array($comments[$key])) {
            $output .= '<div class="comment comment--level-' . $comments[$key]['level'] . '">';
            if (!empty($comments[$key]['user'])) {
                $output .= '<span class="comment__user">' . $comments[$key]['user'] . '</span>';
            }
            $output .= '<span class="comment__time-ago">' . $comments[$key]['time_ago'] . '</span>';
            if ($comments[$key]['content'] === '[deleted]') {
                $output .= '<p>' . $comments[$key]['content'] . '</p>';
            } else {
                $output .= $comments[$key]['content'];
            }
            $output .= '</div>';
            if (array_key_exists('comments', $comments[$key]) && !empty($comments[$key]['comments'])) {
                $output .= generate_comments_html($comments[$key]['comments']);
            }
        }
    }

    return $output;
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

            <header class="page-title">
                <h1 class="h1"><a href="./"><img src="src/img/hn-logo.svg" class="hn-logo" />Responsive Hacker News</a></h1>
            </header>
            <?php echo '<div class="comments">' . $html . '</div>'; ?>
            <footer>
                <div class="footer-inner">
                    <p>Hacker News Responsive - <a href="http://neilmagee.com">Neil Magee</a><br />
                    Original Hacker News - <a href="https://news.ycombinator.com">https://news.ycombinator.com</a></p>
                </div>
            </footer>

        </div>
    </body>
</html>