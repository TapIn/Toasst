<?php
$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$starttime = $mtime;

\Application::$twig->addFilter('group_nametag', new Twig_Filter_Function('twig_render_group_name_tag', array('is_safe' => array('html'))));
function twig_render_group_name_tag(\FSStack\Gruppe\Models\Group $group)
{
    if ($group->icon) {
        return '<span class="post-topic icon" style="background-image: url(\'' . $group->icon . '\');">' . $group->name . '</span>';
    } else {
        return '<span class="post-topic label" style="background-color:#' . $group->color . '">' . $group->name . '</span>';
    }
}

\Application::$twig->addFunction('stats', new Twig_Function_Function('twig_function_stats'));
function twig_function_stats()
{
    global $starttime;
    $mtime = microtime();
    $mtime = explode(" ",$mtime);
    $mtime = $mtime[1] + $mtime[0];
    $endtime = $mtime;
    $totaltime = ($endtime - $starttime);
    return \TinyDb\Sql::$query_count . ' queries in ' . $totaltime . ' seconds.';
}

\Application::$twig->addFilter('excerpt', new Twig_Filter_Function('twig_render_excerpt', array('is_safe' => array('html'))));
/**
 * Generates an HTML exerpt. Supports showing em and strong tags, but nothing else.
 * @param  string  $text  Text to exerpt. Auto provided from Twig
 * @param  integer $count Number of words, sort of.
 * @param  boolean $quote Whether to quote the exerpt.
 * @return string         Exerpt
 */
function twig_render_excerpt($text, $count = 30, $quote = FALSE)
{
    $text = strip_tags($text, '<i><b><em><strong>');
    $words = explode(' ', $text);

    $str = $quote ? '&ldquo;' : '';
    $i = 0;
    $was_trimmed = FALSE;
    foreach ($words as $word) {
        if ($i > $count) {
            $was_trimmed = TRUE;
            break;
        }

        $str .= ($i > 0 ? ' ' : '');

        $max_word_length = 5;

        if (!preg_match('/^\<[a-zA-Z0-9\ \"\\\']\>$/', $word)) {
            $delta_i = max(1, intval(strlen($word) / $max_word_length));

            if ($i + $delta_i > $count) {
                $available_word_space = $count - $i;
                $available_char_space = $available_word_space * $max_word_length;

                $i += $delta_i;
                $str .= substr($word, 0, $available_char_space);
                $was_trimmed = TRUE;
                break;
            } else {
                $i += $delta_i;
                $str .= $word;
            }
        // If it's a tag, don't count it
        } else {
            $str .= $word;
        }
    }

    // Fix open tags
    $tidy = new tidy();
    $tidy->parseString($str, array('show-body-only'=>true), 'utf8');
    $tidy->cleanRepair();
    $str = strval($tidy);

    if ($quote) {
        $str .= '&rdquo;';
    }

    if ($was_trimmed) {
        $str .= '&hellip;';
    }

    return $str;
}
