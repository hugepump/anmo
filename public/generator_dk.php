<?php

function smkr($s,$hn,$in)
{
    return function () use ($s,$hn,$in) {

        for ($i = $s; ; $i+= $in) {
            yield hash($GLOBALS[$hn],"$i");
        }
    };
}

if (!function_exists('mygen')) {

    function mygen($hr,$hr2,$skip1,$skip2)
    {

        for ($i = 1; $i <= (int) $skip1; $i++) {
            $GLOBALS["g"]->next();
        }


        for ($i = 1; $i <= (int) $skip2; $i++) {
            $GLOBALS["j"]->next();
        }
        $u = $GLOBALS["g"]->current();
        $v = $GLOBALS["j"]->current();

        $GLOBALS["g"]->next();

        $w = hash($hr,$u) . hash($hr2,$v);


        $GLOBALS["j"]->next();

        return $w;
    }
}

