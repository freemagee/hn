<?php
/**
 * Generate a comments page for the given story ID
 */

/*
 * INCLUDES
 */

require_once realpath(__DIR__).'/inc/common_functions.php';

/*
 * VARIABLES
 */

$_GET      = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
$articleId = $_GET['id'];

/*
 * LOGIC
 */

// Check id matched pattern, this may need to be more adaptable for the future. Also check the source data and format it.
try {
    if (validateId($articleId) === false) {
        throw new Exception('Article ID is not valid');
    }

    $theSource = validateSource($articleId);

    if ($theSource === null) {
        throw new Exception('Unable to retrieve comments');
    }

    $theTitle    = processArticleTitle($theSource);
    $theComments = $theSource['comments'];

    if (empty($theComments) === true) {
        throw new Exception('Article has no comments yet');
    }

    $html  = '<div class="comments">';
    $html .= $theTitle.generateCommentsHtml($theComments);
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
 * @param string $id The id of the item.
 *
 * @return boolean
 */
function validateId(string $id)
{
    if (preg_match('/^[0-9]{8}$/', $id) === true) {
        return true;
    }

    return false;

}//end validateId()


/**
 * Either uses local JSON or requests the articles comments from the API
 *
 * The return value from the JSON or the array should be identical. If the API fails, then NULL is returned and the application should show an error state.
 *
 * @param string $id The id of the item.
 *
 * @return mixed     Either an array or NULL
 */
function validateSource(string $id)
{
    // First look at local file system for json. JSON is typically already there because of a cron job on /cron/getcomments.php.
    $dir           = dirname(__FILE__);
    $sourceFile    = file_get_contents($dir.'/../data/comments.json');
    $idIsUndefined = false;
    $theComments   = null;

    if ($sourceFile !== false) {
        // $sourceFile will contain all current article comments, so the id is used to get this articles comments. Will be null if no articles are found
        $theComments = findComments($sourceFile, $id);
    }

    if ($sourceFile !== false && $theComments !== null) {
        return $theComments;
    }

    // Should only get to here if comments json does not exist or $id can not be found inside json file. The result is the same, an API call.
    $newSource = getNewSource('http://node-hnapi.herokuapp.com/item/'.$id);

    if ($newSource === null) {
        return null;
    } else {
        return $newSource;
    }

}//end validateSource()


/**
 * Find the specified child node in an array
 *
 * @param string $sourceFile String of json.
 * @param string $id         The comment ID.
 *
 * @return mixed     Either an array or NULL
 */
function findComments(string $sourceFile, string $id)
{
    $output = transformSource($sourceFile);

    if (isset($output[$id]) === false) {
        return null;
    }

    if ($output[$id] === null) {
        return null;
    }

    return $output[$id];

}//end findComments()


/**
 * Transforms string of json into an array
 *
 * @param string $json The json string.
 *
 * @return array
 */
function transformSource(string $json)
{
    $sourceObj = json_decode($json);
    $output    = objectToArray($sourceObj);

    return $output;

}//end transformSource()


/**
 * Combines data from the source to create an article title
 *
 * @param array $story Story data in an array.
 *
 * @return string         The html of the title
 */
function processArticleTitle(array $story)
{
    $url           = $story['url'];
    $title         = $story['title'];
    $commentsCount = $story['comments_count'];

    if (isset($story['domain']) === true) {
        $domain = $story['domain'];
    } else {
        $domain = 'news.ycombinator.com';
    }

    $output  = '<h2 class="article-title">';
    $output .= '<a href="'.$url.'" class="article-title__link">'.$title.'</a><span class="article-title__meta"><span class="article-title__source">'.$domain.'</span><span class="article-title__comments">'.$commentsCount.' comments</span></span>';
    $output .= '</h2>';

    return $output;

}//end processArticleTitle()


/**
 * Generate comments html
 *
 * @param array $comments An array of comments.
 *
 * @return string          The html for all the comments
 */
function generateCommentsHtml(array $comments)
{
    $output = '';

    foreach ($comments as $key => $value) {
        if (is_array($comments[$key]) === true) {
            $output .= '<div class="comment" data-id="'.$comments[$key]['id'].'" data-level="'.$comments[$key]['level'].'">';
            $output .= '<div class="comment__meta">';
            if (empty($comments[$key]['user']) !== false) {
                $output .= '<span class="comment__user">'.$comments[$key]['user'].'</span>';
            } else {
                $output .= '<span class="comment__user">[anonymous]</span>';
            }

            $output .= '<span class="comment__time-ago">'.$comments[$key]['time_ago'].'</span>';
            $output .= '</div>';
            $output .= processContent($comments[$key]['content']);

            $output .= '</div>';
            if (array_key_exists('comments', $comments[$key]) === true && empty($comments[$key]['comments']) !== false) {
                $output .= generateCommentsHtml($comments[$key]['comments']);
            }
        }//end if
    }//end foreach

    return $output;

}//end generateCommentsHtml()


/**
 * Contact API via curl
 *
 * @param string $url The url of the API.
 *
 * @return string
 */
function getNewSource(string $url)
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
        $output = transformSource($data);
    }

    curl_close($ch);

    return $output;

}//end getNewSource()


/**
 * Take the provided content and parse the html and output in p tags
 *
 * @param string $content The provided content.
 *
 * @return string
 */
function processContent(string $content)
{
    $sentences = explode('<p>', $content);
    $output    = '';
    $limit     = count($sentences);

    for ($i = 1; $i < $limit; $i++) {
        $sentence = strip_tags($sentences[$i], '<pre><code><a>');
        // Sentence contains a <pre> so it should not be wrapped in a p.
        if (strpos($sentence, '<pre>') !== false) {
            $regex = '#<\s*?pre\b[^>]*>(.*?)</pre\b[^>]*>#s';
            preg_match($regex, $sentence, $matches);
            $pre    = $matches[0];
            $preEnd = strpos($sentence, '</pre>');
            // The sentence is not only a <pre>...</pre>.
            if (($preEnd + 6) !== strlen($sentence)) {
                $followingSentence = substr($sentence, $preEnd);
                $output           .= $pre;
                $output           .= '<p>'.processQuotes($followingSentence).'</p>';
            } else {
                $output .= $pre;
            }
        } else {
            $output .= '<p>'.processQuotes($sentence).'</p>';
        }
    }

    return $output;

}//end processContent()


/**
 * If text starts with a >, then wrap it in an em tag
 *
 * @param string $text The text tp be processed.
 *
 * @return string
 */
function processQuotes(string $text)
{
    if (substr($text, 0, 4) === '&gt;') {
        $output = '<em>'.$text.'</em>';
    } else {
        $output = $text;
    }

    return $output;

}//end processQuotes()


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
            <?php require_once realpath(__DIR__).'/inc/header.php'; ?>
            <?php echo $html; ?>
            <?php require_once realpath(__DIR__).'/inc/footer.php'; ?>
        </div>
    </body>
</html>
