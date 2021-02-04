<?php

error_reporting(E_ALL);
ini_set('display_startup_errors', 1);
ini_set('display_errors',   1); // only debug version

function send_head_json() {
    header('Content-Type: application/json; charset=UTF-8');
}

if( $_POST['cmd'] == 'check_text' )
{
    $pspell_ru = pspell_new('ru','','','utf-8', PSPELL_FAST);
    $pspell_en = pspell_new('en','','','utf-8', PSPELL_FAST);
    $not_righ = [];

    function spellCheckWord($word)
    {
        if( strlen($word[0]) < 3 || preg_match("/^\d/u", $word[0] ) ) {
            return '';
        }

        global $pspell_ru, $pspell_en, $not_righ;

        // Take the string match from preg_replace_callback's array
        $word = $word[0];

        if( preg_match("/[a-zA-Z]+/u", $word) ) {
            // Return dictionary words
            if (pspell_check($pspell_en, $word)) {
                return '';
            }

            // Auto-correct with the first suggestion, color green
            if ($suggestions = pspell_suggest($pspell_en, $word)) {
                //if( array_search($word, $suggestions) !== FALSE )
                {
                        $not_righ[] = $word;
                }
            }
        }
        else
        {
            // Return dictionary words
            if (pspell_check($pspell_ru, $word)) {
                return '';
            }

            // Auto-correct with the first suggestion, color green
            if ($suggestions = pspell_suggest($pspell_ru, $word)) {
                //if( array_search($word, $suggestions) !== FALSE )
                {
                    $not_righ[] = $word;
                }
            }
        }
        
        // No suggestions, color red
        return '';
    }

    function spellCheck($string) {
        return preg_replace_callback('/\b[a-zA-Zа-яА-Я\']+\b/u','spellCheckWord', $string);
    }

    spellCheck($_POST['text']);
    
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode( array_values( array_unique( $not_righ )) );
    exit;
}
else if( $_REQUEST['cmd'] == 'check_word' )
{
    $pspell = pspell_new($_REQUEST['lang'],'','','utf-8', PSPELL_FAST);

    $autocorrect = TRUE;

    header('Content-Type: application/json; charset=UTF-8');

    // Take the string match from preg_replace_callback's array
    $word = $_REQUEST['w'];

    // Return dictionary words
    if (pspell_check($pspell, $word))
    {
        echo json_encode([1, [$word]]);
        exit;
    }

    // Auto-correct with the first suggestion, color green
    if ($autocorrect && $suggestions = pspell_suggest($pspell, $word))
    {
        /*if( count($suggestions) > 0 && $suggestions[0] == $word )
        {
                echo json_encode([0, []]);
                exit;
        }*/

        echo json_encode( [2, array_slice($suggestions, 0, 7) ] );
        exit;
    }

    // No suggestions, color red
    echo json_encode([0, []]);
    exit;
}
