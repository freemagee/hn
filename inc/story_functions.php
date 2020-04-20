<?php


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
        $limit  = count($source);
        $output = [];

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
                $commentsCount          = $source[$i]['descendants'];
                $output[$i]['comments'] = $commentsCount;
            } else {
                $output[$i]['comments'] = 0;
            }

            $posted = timeElapsed($today - $delay);
            $number = ($i + 1);

            // Build news list item.
            $output[$i]['id']          = $id;
            $output[$i]['link']        = $link;
            $output[$i]['number']      = $number;
            $output[$i]['title']       = $title;
            $output[$i]['host_domain'] = $hostDomainShort;
            $output[$i]['posted']      = $posted;
        }//end for
    }//end if

    return $output;

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
