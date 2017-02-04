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

// Check id matched pattern, this may need to be more adaptable for the future
if (preg_match("/^[0-9]{8}$/", $article_id)) {
    $source = format_source();
    $the_title = get_the_title($article_id, $source);
    $the_comments = get_article_comments($article_id, $source);

    if (!empty($the_comments)) {
        $html = '<div class="comments">' . $the_title . generate_comments_html($the_comments) . '</div>';
    } else {
        $html = '<div class="comments">' . $the_title . '<h3>No comments!</h3><p>Unfortunately there has been an error with this articles comments.</p></div>';
    }
} else {
    $html = '<div class="comments"><h3>No comments!</h3><p>Unfortunately there has been an error with this articles comments.</p></div>';
}

/*******************************************************************************
* FUNCTIONS
 ******************************************************************************/

function format_source() {
    $dir = dirname(__FILE__);
    $source_file = file_get_contents($dir . '/src/data/comments.json');
    $source_obj = json_decode($source_file);
    $output = object_to_array($source_obj);

    return $output;
}

function get_the_title($id, $source) {
    if (isset($source[$id])) {
        $url = $source[$id]['url'];
        $title = $source[$id]['title'];
        if (isset($source[$id]['domain'])) {
            $domain = $source[$id]['domain'];
        } else {
            $domain = 'news.ycombinator.com';
        }

        $output = '<a href="' . $url . '" class="article-title__link">' . $title . '</a><span class="article-title__source">' . $domain . '</span>';
    } else {
        $output = 'Article title unavailable';
    }

    return '<h2 class="article-title">' . $output . '</h2>';
}

function get_article_comments($id, $source) {
    if (isset($source[$id])) {
        // ID is in the $source array
        $output = $source[$id]['comments'];
    } else {
        // ID is not in $source array, need to get comments from API
        // This could be because the comments.json is ahead of the article being viewed
        $new_comment_json = get_comment('http://node-hnapi.herokuapp.com/item/' .$id);
        $new_comments = object_to_array($new_comment_json);
        $output = $new_comments['comments'];
    }

    return $output;
}

function generate_comments_html($comments) {
    $output = '';

    foreach ($comments as $key => $value) {
        if (is_array($comments[$key])) {
            $output .= '<div class="comment" data-level="' . $comments[$key]['level'] . '">';
            $output .= '<div class="comment__meta">';
            if (!empty($comments[$key]['user'])) {
                $output .= '<span class="comment__user">' . $comments[$key]['user'] . '</span>';
            } else {
                $output .= '<span class="comment__user">[anonymous]</span>';
            }
            $output .= '<span class="comment__time-ago">' . $comments[$key]['time_ago'] . '</span>';
            $output .= '</div>';
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

function get_comment($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    $data_obj = json_decode($data);
    $comment = object_to_array($data_obj);

    return $comment;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Responsive Hacker News Comments | Mobile optimised Hacker News</title>
        <meta name="description" content="Just the links from Hacker News, optimised for small screens and mobile devices.">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="favicon.ico">
        <link rel="stylesheet" href="src/css/main.css">
    </head>
    <body>
        <div class="container">
            <header>
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