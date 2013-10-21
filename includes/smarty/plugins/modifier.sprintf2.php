<?php

function smarty_modifier_sprintf2($string, $arg1, $arg2='', $arg3='', $arg4='')
{
    return sprintf($string, $arg1, $arg2, $arg3, $arg4);
}


?>