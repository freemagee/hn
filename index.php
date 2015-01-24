<?php require('src/inc/simple_html_dom.php');

// **************************************************************************************
// VARIABLES
// **************************************************************************************
$pages = array();
$pages['page1'] = file_get_html('https://news.ycombinator.com/');
$pages['page2'] = file_get_html('https://news.ycombinator.com/news?p=2');
$linksList = array();

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

/**
 * [makeLinkList Create list of links plus relevent details]
 * @param  [array] $pages [description]
 * @param  [array] $list  [description]
 * @return string        [HTML UL list of links]
 */
function makeLinkList($pages, $list) {
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
                if ( !is_null($subtext->find('a[href^="item?"]', 0)) ) {
                    $comment = $subtext->find('a[href^="item?"]', 0);
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

                $posted = $subtext->plaintext;
                $posted = preg_replace('/(by)/', '', $posted);
                $posted = preg_replace('/(\s\|)/', '', $posted);
                $list[$i]['posted'] = trim($posted);
                $i++;
            }
        }
    }

    if (!empty($list)) {
        $html = '<ul id="links-list">';
        $limit = count($list);

        for ($i = 0; $i < $limit; $i++) {
            $link = $list[$i]['href'];
            $text = $list[$i]['text'];
            $source = $list[$i]['source'];
            $posted = $list[$i]['posted'];
            $commentsText = $list[$i]['commentsText'];
            $commentsHref = $list[$i]['commentsHref'];
            $number = $i + 1;

            // Build news list item
            $html .= '<li>';
            $html .= '<a class="hn-link" href="' . $link . '">';
            $html .= '<span class="count">' . $number . '</span>';
            $html .= '<div class="link-content">';
            $html .= '<span class="link-text">' . $text . '</span>';
            $html .= '<span class="source">' . $source . '</span>';
            $html .= '<span class="posted">Posted: ' . $posted . '</span>';
            $html .= '</div>';
            $html .= '</a>';
            if ($commentsHref !== '') {
                $html .= '<span class="comments"><a class="hn-comment" href="' . $commentsHref . '">' . $commentsText . '</a></span>';
            } else {
                $html .= '<span class="comments">' . $commentsText . '</span>';
            }
            $html .= '</li>';
        }

        $html .= '</ul>';
    } else {
        $html = '<h3>No news!</h3><p>Unfortunately there has been an error. It will be sorted out shortly.</p>';
    }

    return $html;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Responsive Hacker News | Just the links, mobile device friendly</title>
        <meta name="description" content="Just the links from Hacker News, optimised for small screens and mobile devices.">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="favicon.ico">
        <link rel="stylesheet" href="src/css/main.css">
    </head>
    <body>
        <div class="container">

            <header class="title">
                <h1><img src="src/img/hn-logo.svg" class="hn-logo" />Responsive Hacker News</h1>
            </header>
            <?php
            echo makeLinkList($pages, $linksList);
            ?>
            <footer>
                <div class="footer-inner">
                    <p>Hacker News Responsive - <a href="http://neilmagee.com">Neil Magee</a><br />
                    Original Hacker News - <a href="https://news.ycombinator.com">https://news.ycombinator.com</a></p>
                </div>
            </footer>

        </div>
    </body>
</html>