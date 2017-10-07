<?php
namespace app\common\helper;

class ArrayHelper
{
    public static function elements($items, array $array, $default = NULL)
    {
        $return = array();

        is_array($items) or $items = array($items);

        foreach ($items as $item)  {
            $return[$item] = array_key_exists($item, $array) ? $array[$item] : $default;
        }

        return $return;
    }

    public static function element($item, array $array, $default = NULL)
    {
        return array_key_exists($item, $array) ? $array[$item] : $default;
    }

    public static function multisort($arrays, $sort_key, $sort_order = SORT_ASC, $sort_type = SORT_NUMERIC)
    {
        if (!count($arrays))
            return false;

        $key_arrays = [];

        foreach ($arrays as $array) {
            if (!count($array))
                continue;
            $key_arrays[] = $array[$sort_key];
        }

        array_multisort($key_arrays, $sort_order, $sort_type, $arrays);

        return $arrays;   
    }

    public static function isOneDimensional(array $arr)
    {
        return count($arr) == count($arr, true);
    }
    
}