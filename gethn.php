<?php require('src/inc/simple_html_dom.php');

// **************************************************************************************
// VARIABLES
// **************************************************************************************
$pages = array();
$pages['page1'] = file_get_html('https://news.ycombinator.com/');
$pages['page2'] = file_get_html('https://news.ycombinator.com/news?p=2');
$linksList = array();
$dir = dirname(__FILE__);
$file = 'data.json';
$filename = $dir . '/src/data/' . $file;

/*******************************************************************************
 * LOGIC
 ******************************************************************************/

if (file_exists($filename)) {
    getLinkList($pages, $linksList);
} else {
    createFile($dir . '/src/data/', 'data', 'json');
    getLinkList($pages, $linksList);
}

// **************************************************************************************
// FUNCTIONS
// **************************************************************************************

/**
 * [pre_r pretty arrays]
 * @param  [array] $val [takes an array]
 * @return string      [returns an array wrapped in PRE tags]
 */
function pre_r($val){
    echo '<pre>';
    print_r($val);
    echo  '</pre>';
}

function createFile($dir, $n, $xt) {
    fopen($dir . $n . '.' . $xt, "w");
}

/**
 * [makeLinkList Create list of links plus relevent details]
 * @param  [array] $pages [description]
 * @param  [array] $list  [description]
 * @return string        [HTML UL list of links]
 */
function getList($pages, $list) {
    global $dir;

    $i = 0;
    $baseUrl = 'https://news.ycombinator.com/';

    foreach ($pages as $key) {
        foreach($key->find('body table tbody tr:nth-child(3) table tbody td.title a') as $element) {
            if( preg_match('/news\?p=/', $element->href) !== 1 ) {
                if( preg_match('/item\?id=/', $element->href) !== 1 ) {
                    $list[$i]['href'] = $element->href;
                } else {
                    $list[$i]['href'] = $baseUrl . $element->href;
                }
                $list[$i]['text'] = $element->text();
                $span = $element->parent()->find('span.comhead', 0);
                if ( !is_null($span) ) {
                    $list[$i]['source'] = trim($span->plaintext);
                } else {
                    $list[$i]['source'] = '(Hacker News)';
                }
                $subtext = $element->parent()->parent()->next_sibling();
                if ( !is_null($subtext->find('span[id^="score_"]', 0)) ) {
                    $subtext->find('span[id^="score_"]', 0)->innertext = '';
                }
                if ( !is_null($subtext->find('a[href^="user?"]', 0)) ) {
                    $subtext->find('a[href^="user?"]', 0)->innertext = '';
                }
                // Get posted time/date as string
                if ( !is_null($subtext->find('a[href^="item?"]', 0)) ) {
                    $posted = $subtext->find('a[href^="item?"]', 0);
                    $list[$i]['posted'] = $posted->innertext;
                } else {
                    $posted = $subtext->innertext;
                    $list[$i]['posted'] = $posted;
                }
                // Get comments as string and link
                if ( !is_null($subtext->find('a[href^="item?"]', 1)) ) {
                    $comment = $subtext->find('a[href^="item?"]', 1);
                    if ($comment->innertext !== 'discuss') {
                        $list[$i]['commentsText'] = $comment->innertext;
                        $list[$i]['commentsHref'] = $baseUrl . $comment->href;
                    } else {
                        $list[$i]['commentsText'] = 'No comments';
                        $list[$i]['commentsHref'] = '';
                    }
                    $subtext->find('a[href^="item?"]', 0)->innertext = '';
                } else {
                    $list[$i]['commentsText'] = 'No comments';
                    $list[$i]['commentsHref'] = '';
                }

                $i++;
            }
        }
    }

    $json_data = json_encode($list);
    file_put_contents($dir . '/src/data/data.json', $json_data);

}
?>