<?php

namespace Uuu9\PhpApiLog\Util;
class ExclusionJsonUtil
{
    const ASTERISK = "********";

    /**
     * 去除$field中对 excludeFields() 的无效字符 - _ 左右空格
     * @param string $field
     * @return mixed|string
     */
    public static function normalizeExcludeField(string $field)
    {
        $field = trim($field);
        $field = str_replace("_", '', $field);
        $field = str_replace("-", '', $field);
        $field = strtolower($field);
        return $field;
    }

    /**
     * 递归替换数组中的指定key对应的value为 ********
     * @param array $fields //原数据
     * @param array $excludeFieldSet //需要剔除的key集合
     * @return array
     */
    public static function excludeFields(array $fields, array $excludeFieldSet)
    {
        foreach ($fields as $key => $value) {
            if (in_array(self::normalizeExcludeField($key), $excludeFieldSet)) {
                $fields[$key] = self::ASTERISK;
            }

            if (is_array($value)) {
                $fields[$key] = self::excludeFields($value, $excludeFieldSet);
            }
        }
        return $fields;
    }
}