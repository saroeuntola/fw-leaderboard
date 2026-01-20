<?php
function limitText($text, $limit = 50)
{
    return strlen($text) > $limit
        ? substr($text, 0, $limit) . '...'
        : $text;
}
