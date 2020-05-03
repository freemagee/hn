<?php


/**
 * Run a regex against the ID parameter
 *
 * @param string $id The id of the item.
 *
 * @return boolean
 */
function validateId(string $id)
{
    if (preg_match('/^[0-9]{8}$/', $id) === 1) {
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
 * @return array
 */
function validateSource(string $id)
{
    // First look at local file system for json. JSON is typically already there because of a cron job on /cron/getcomments.php.
    $dir           = dirname(__FILE__);
    $sourceFile    = file_get_contents($dir.'/../../data/comments.json');
    $idIsUndefined = false;
    $theComments   = [];

    if ($sourceFile !== false) {
        // $sourceFile will contain all current article comments, so the id is used to get this articles comments. Will be empty if no articles are found.
        $theComments = findComments($sourceFile, $id);
    }

    // Comments array is not empty.
    if (empty($theComments) === false) {
        return $theComments;
    }

    // Should only get to here if comments json does not exist or $id can not be found inside json file. The resulting data is the same.
    $getNewSource = getNewSource('https://node-hnapi.herokuapp.com/item/'.$id);

    if ($getNewSource !== null) {
        return $getNewSource;
    }

    return [];

}//end validateSource()


/**
 * Find the specified child node in an array
 *
 * @param string  $sourceFile String of json.
 * @param integer $id         The comment ID.
 *
 * @return array
 */
function findComments(string $sourceFile, int $id)
{
    $output = transformSource($sourceFile);
    $limit  = count($output);

    // Loop over the first level of items. Check for the provided id.
    for ($i = 0; $i < $limit; $i++) {
        if (isset($output[$i]) === true && $output[$i]['id'] === $id) {
            return $output[$i];
        }
    }

    return [];

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
 * Combines data from the source and outputs an array of the story details
 *
 * @param array $story Source story data.
 *
 * @return array
 */
function processStory(array $story)
{
    if (isset($story['domain']) === true) {
        $domain = $story['domain'];
    } else {
        $domain = 'news.ycombinator.com';
    }

    // Ask HN stories have content to go along with the comments.
    if (isset($story['content']) === true) {
        $content = processContent($story['content']);
    } else {
        $content = [];
    }

    return [
        'title'         => $story['title'],
        'url'           => $story['url'],
        'domain'        => $domain,
        'commentsCount' => $story['comments_count'],
        'content'       => $content
    ];

}//end processStory()


/**
 * Generate comments html
 *
 * @param array $comments An array of comments.
 *
 * @return array
 */
function processComments(array $comments)
{
    $output = [];
    $limit  = count($comments);

    for ($i = 0; $i < $limit; $i++) {
        $subComments = [];

        if (is_array($comments[$i]) === true) {
            $id                   = $comments[$i]['id'];
            $output[$id]['id']    = $comments[$i]['id'];
            $output[$id]['level'] = $comments[$i]['level'];

            if (empty($comments[$i]['user']) === false) {
                $output[$id]['user'] = $comments[$i]['user'];
            } else {
                $output[$id]['user'] = '[anonymous]';
            }

            $output[$id]['timeAgo'] = $comments[$i]['time_ago'];
            $output[$id]['content'] = processContent($comments[$i]['content']);
            // Recursive loop. If there are child comments, then process them and add them to the output array.
            if (array_key_exists('comments', $comments[$i]) === true && empty($comments[$i]['comments']) === false) {
                $subComments = processComments($comments[$i]['comments']);

                if (count($subComments) > 0) {
                    $output = array_merge($output, $subComments);
                }
            }
        }//end if
    }//end for

    return $output;

}//end processComments()


/**
 * Take the provided content and parse the html and output in p tags
 *
 * @param string $content The provided content.
 *
 * @return string
 */
function processContent(string $content)
{
    if ($content === '[deleted]') {
        return $content;
    }

    $sentences = explode('<p>', $content);
    $output    = [];
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
                // Now remove the HTML tags completely, they will be controlled by the template engine.
                $strippedPre = strip_tags($pre);
                $output[] = [
                    'type' => 'code',
                    'text' => $strippedPre,
                ];
                $output[]          = processQuotes($followingSentence);
            } else {
                // Now remove the HTML tags completely, they will be controlled by the template engine.
                $strippedPre = strip_tags($pre);
                $output[] = [
                    'type' => 'code',
                    'text' => $strippedPre,
                ];
            }
        } else {
            $output[] = processQuotes($sentence);
        }//end if
    }//end for

    return $output;

}//end processContent()


/**
 * If text starts with a >, then make its type 'quote'
 *
 * @param string $text The text tp be processed.
 *
 * @return array
 */
function processQuotes(string $text)
{
    if (substr($text, 0, 4) === '&gt;') {
        $output = [
            'type' => 'quote',
            'text' => $text,
        ];
    } else {
        $output = [
            'type' => 'sentence',
            'text' => $text,
        ];
    }

    return $output;

}//end processQuotes()

/**
 * Contact API via curl
 *
 * @param  string $url url of the API
 * @return string
 */
function getNewSource($url)
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
