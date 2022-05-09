<?php

if (!function_exists('dd_sql')) {
    /**
     * 透過 Query Builder 取得 sql and dd output it
     */
    function dd_sql($builder)
    {
        $sql = $builder->toSql();
        foreach ($builder->getBindings() as $binding) {
            $value = is_numeric($binding) ? $binding : "'" . $binding . "'";
            $sql = preg_replace('/\?/', $value, $sql, 1);
        }
        print_r(\Pharaoh\BaseModelRepository\Helpers\SqlFormatterSupport::format($sql));
        exit;
    }
}

if (!function_exists('dump_sql')) {
    /**
     * 透過 Query Builder 取得 sql and dd output it
     */
    function dump_sql($builder)
    {
        $sql = $builder->toSql();
        foreach ($builder->getBindings() as $binding) {
            $value = is_numeric($binding) ? $binding : "'" . $binding . "'";
            $sql = preg_replace('/\?/', $value, $sql, 1);
        }
        dump($sql);
    }
}
