<?php

/**
 * Class: MoovicoCSVGenerator
 *
 */
class MoovicoCSVGenerator {

    /**
     * rowFromObject
     *
     * @param mixed $include_headers
     * @param string $e
     * @param string $s
     * @param string $t
     * @param array $columns
     */
    public static function rowFromObject($obj, $include_headers = false, $e = '"', $s = ';', $t = "\n", $columns = array()) {
        $str = '';
        $cols = !empty($columns) ? $columns : array_keys(get_object_vars($obj));
        if ($include_headers == true)
        {
            $vals = array();
            foreach ($cols as $prop)
            {
                $vals[] = strtoupper(addcslashes($prop, $e));
            }

            $str.= $e.implode($e.$s.$e, $vals).$e.$t;
        }

        $vals = array();
        foreach ($cols as $prop)
        {
            $vals[] = addcslashes($obj->{$prop}, $e);
        }

        $str.= $e.implode($e.$s.$e, $vals).$e.$t;

        return $str;
    }
}
