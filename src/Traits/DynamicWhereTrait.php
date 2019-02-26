<?php namespace Ddup\Models\Traits;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

Trait DynamicWhereTrait
{

    private function fillDateRange(Builder $query, Collection $collection, $field_name = 'created_at')
    {
        $sdate = $collection->get('sdate');
        $edate = $collection->get('edate');

        switch (true) {
            case $sdate && $edate:
                $query = $query->whereBetween($field_name, [$sdate, $edate]);
                break;
            case $sdate:
                $query = $query->where($field_name, '>=', $sdate);
                break;
            case $edate:
                $query = $query->where($field_name, '<=', $edate);
                break;
        }

        return $query;
    }

    private function fillWhere(Builder $query, array $definds, Collection $collection)
    {
        $where = self::combinWhereArr($definds, $collection);

        foreach ($where as $row) {
            if ($row[1] == 'in') {
                $query = $query->whereIn($row[0], $row[2]);
            } else {
                $query = $query->where($row[0], $row[1], $row[2]);
            }
        }

        return $query;
    }

    private function combinWhereArr($definds, Collection $collection)
    {
        $result = [];
        foreach ($definds as $defindRow) {
            $key = $defindRow[0];

            if (count($defindRow) < 3) {
                $con     = '=';
                $val_key = $defindRow[1];
            } else {
                $con     = $defindRow[1];
                $val_key = $defindRow[2];
            }

            if ($con == 'in') {
                $result[] = [$key, $con, $val_key];
                continue;
            }

            if ($collection->get(self::getRealyValKey($val_key)) === null) {
                continue;
            }

            if ($con == 'like') {
                $val = self::likeWhere($val_key, $collection);
            } else {
                $val = self::eqWhere($val_key, $collection);
            }

            $result[] = [$key, $con, $val];
        }

        return $result;
    }

    private function eqWhere($val_key, Collection $collection)
    {
        return $collection->get($val_key);
    }

    private function getRealyValKey($val_key)
    {
        return self::likeValKey($val_key);
    }

    private function likeValKey($val_key)
    {
        return trim($val_key, '%');
    }

    public function likeWhere($val_key, Collection $collection)
    {
        if (strchr($val_key, '%') !== false) {
            $old_key = $val_key;
            $val_key = self::likeValKey($val_key);
            $val     = str_replace($val_key, $collection->get($val_key), $old_key);
        } else {
            $val = '%' . $collection->get($val_key) . '%';
        }

        return $val;
    }

}