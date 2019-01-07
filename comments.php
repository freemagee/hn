<?php
/*
 * INCLUDES
 */

require_once realpath(__DIR__).'/src/inc/common_functions.php';

/*
 * VARIABLES
 */

$_GET       = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
$article_id = $_GET['id'];

/*
 * LOGIC
 */

// Check id matched pattern, this may need to be more adaptable for the future. Also check the source data and format it.
try {
    if (validate_id($article_id) === false) {
        throw new Exception('Article ID is not valid');
    }

    $the_source = validate_source($article_id);

    if (is_null($the_source) === true) {
        throw new Exception('Unable to retrieve comments');
    }

    $the_title    = process_article_title($the_source);
    $the_comments = $the_source['comments'];

    if (empty($the_comments) === true) {
        throw new Exception('Article has no comments yet');
    }

    $html  = '<div class="comments">';
    $html .= $the_title.generate_comments_html($the_comments);
    $html .= '</div>';
} catch (Exception $e) {
    $html  = '<div class="no-comments">';
    $html .= '<h3>No comments available</h3>';
    $html .= '<p>Unfortunately there has been an error with this articles comments.</p>';
    $html .= '</div>';
}//end try

/*
 * FUNCTIONS
 */


/**
 * Run a regex against the ID parameter
 *
 * @param  string $id
 * @return boolean
 */
function validate_id($id)
{
    if (preg_match('/^[0-9]{8}$/', $id)) {
        return true;
    }

    return false;

}//end validate_id()


/**
 * Either uses local JSON or requests the articles comments from the API
 *
 * The return value from the JSON or the array should be identical. If the API fails, then NULL is returned and the application should show an error state.
 *
 * @param  string $id
 * @return mixed     Either an array or NULL
 */
function validate_source($id)
{
    // First look at local file system for json. JSON is typically already there because of a cron job on /cron/getcomments.php
    $dir              = dirname(__FILE__);
    $source_file      = file_get_contents($dir.'/src/data/comments.json');
    $id_is_undefined  = false;
    $article_comments = null;

    if ($source_file !== false) {
        // $source_file will contain all current article comments, so the id is used to get this articles comments. Will be null if no articles are found
        $article_comments = find_article_comments($source_file, $id);
    }

    if ($source_file !== false && is_null($article_comments) !== true) {
        return $article_comments;
    }

    // Should only get to here if comments json does not exist or $id can not be found inside json file. The result is the same, an API call.
    $new_source = get_new_source('http://node-hnapi.herokuapp.com/item/'.$id);

    if (is_null($new_source) === true) {
        return null;
    } else {
        return $new_source;
    }

}//end validate_source()


/**
 * Find the specified child node in an array
 *
 * @param  string $source_file
 * @param  string $id
 * @return mixed     Either an array or NULL
 */
function find_article_comments($source_file, $id)
{
    $output = transform_source($source_file);

    if (isset($output[$id]) === false) {
        return null;
    }

    if (is_null($output[$id]) === true) {
        return null;
    }

    return $output[$id];

}//end find_article_comments()


/**
 * Transforms string of json into and array
 *
 * @param  string $source
 * @return array
 */
function transform_source($source)
{
    $source_obj = json_decode($source);
    $output     = object_to_array($source_obj);

    return $output;

}//end transform_source()


/**
 * Combines data from the source to create an article title
 *
 * @param  array $source
 * @return string         The html of the title
 */
function process_article_title($source)
{
    $url   = $source['url'];
    $title = $source['title'];

    if (isset($source['domain'])) {
        $domain = $source['domain'];
    } else {
        $domain = 'news.ycombinator.com';
    }

    $output  = '<h2 class="article-title">';
    $output .= '<a href="'.$url.'" class="article-title__link">'.$title.'</a><span class="article-title__source">'.$domain.'</span>';
    $output .= '</h2>';

    return $output;

}//end process_article_title()


/**
 * Generate comments html
 *
 * @param  array $comments
 * @return string          The html for all the comments
 */
function generate_comments_html($comments)
{
    $output = '';

    foreach ($comments as $key => $value) {
        if (is_array($comments[$key])) {
            $output .= '<div class="comment" data-id="'.$comments[$key]['id'].'" data-level="'.$comments[$key]['level'].'">';
            $output .= '<div class="comment__meta">';
            if (!empty($comments[$key]['user'])) {
                $output .= '<span class="comment__user">'.$comments[$key]['user'].'</span>';
            } else {
                $output .= '<span class="comment__user">[anonymous]</span>';
            }

            $output .= '<span class="comment__time-ago">'.$comments[$key]['time_ago'].'</span>';
            $output .= '</div>';
            if ($comments[$key]['content'] === '[deleted]') {
                $output .= '<p>'.$comments[$key]['content'].'</p>';
            } else {
                $output .= $comments[$key]['content'];
            }

            $output .= '</div>';
            if (array_key_exists('comments', $comments[$key]) && !empty($comments[$key]['comments'])) {
                $output .= generate_comments_html($comments[$key]['comments']);
            }
        }//end if
    }//end foreach

    return $output;

}//end generate_comments_html()


/**
 * Contact API via curl
 *
 * @param  string $url url of the API
 * @return string
 */
function get_new_source($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    $data = curl_exec($ch);

    if (curl_errno($ch) !== 0) {
        $output = null;
    } else {
        $output = transform_source($data);
    }

    curl_close($ch);

    return $output;

}//end get_new_source()


?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Responsive Hacker News | Comments</title>
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
