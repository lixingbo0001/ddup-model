<?php
/**
 * Created by PhpStorm.
 * User: root1
 * Date: 2018/7/27
 * Time: 下午2:15
 */

namespace Ddup\Models\Traits;


use Ddup\Part\Libs\Str;

trait CallTableAble
{
    public function table($as = null)
    {
        $table = $this->getTable();

        $table = Str::first($table, ' as ');

        if (!is_null($as)) {
            $table .= ' as ' . trim($as);
        }

        return $table;
    }

    public function f($name, $as = null)
    {
        return self::table() . '.' . $name . (is_null($as) ? '' : " as {$as}");
    }

    public function mf($names)
    {
        $fields = [];

        foreach ($names as $name) {
            $fields[] = self::f($name);
        }

        return $fields;
    }
}