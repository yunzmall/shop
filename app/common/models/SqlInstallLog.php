<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022-03-10
 * Time: 16:48
 */

namespace app\common\models;


class SqlInstallLog extends BaseModel
{
    public $table = 'yz_sql_install_log';
    public $timestamps = true;
    public $guarded = [''];
}