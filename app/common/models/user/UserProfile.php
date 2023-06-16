<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 02/03/2017
 * Time: 18:25
 */

namespace app\common\models\user;


use app\common\models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class UserProfile extends BaseModel
{
    public $table =   'users_profile';

    public $timestamps = false;

    //protected $guarded = [''];

    protected $guarded = [''];

    public $attributes =[
        'nickname'      => '',
        'avatar'        => '',
        'qq'            => '',
        'fakeid'        => '',
        'vip'           => 0,
        'gender'        => 0,
        'birthyear'     => 0,
        'birthmonth'    => 0,
        'birthday'      => 0,
        'constellation' => '',
        'zodiac'        => '',
        'telephone'     => '',
        'idcard'        => '',
        'studentid'     => '',
        'grade'         => '',
        'address'       => '',
        'zipcode'       => '',
        'nationality'   => '',
        'resideprovince' => '',
        'residecity'    => '',
        'residedist'    => '',
        'graduateschool' => '',
        'company'       => '',
        'education'     => '',
        'occupation'    => '',
        'position'      => '',
        'revenue'       => '',
        'affectivestatus' => '',
        'lookingfor'    => '',
        'bloodtype'     => '',
        'height'        => '',
        'weight'        => '',
        'alipay'        => '',
        'msn'           => '',
        'email'         => '',
        'taobao'        => '',
        'site'          => '',
        'bio'           => '',
        'interest'      => '',
        'workerid'      => '',
    ];

    public function __construct()
    {
        parent::__construct();
        if (config('app.framework') == 'platform') {
            $this->table = 'yz_users_profile';
            $this->attributes = [
                'avatar'        => '',
            ];
            $this->timestamps = true;
        } else {
            if(Schema::hasColumn($this->table, 'edittime')){ //用于兼容新版微擎新增的字段
                $this->attributes = array_merge($this->attributes, ['edittime' =>time()]);
            }
            if(Schema::hasColumn($this->table, 'is_send_mobile_status')){ //用于兼容新版微擎新增的字段
                $this->attributes = array_merge($this->attributes, ['is_send_mobile_status' =>0]);
            }
            if(Schema::hasColumn($this->table, 'send_expire_status')){ //用于兼容新版微擎新增的字段
                $this->attributes = array_merge($this->attributes, ['send_expire_status' =>0]);
            }
        }
    }

    /*
     * 通过uid获取单条数据
     *
     * @params int $uid
     *
     * @return object */
    public static function getProfileByUid($uid)
    {
        return static::where('uid', $uid)->first();
    }

    public function relationValidator($data)
    {
        $userProfile = new static();
        $userProfile->fill($data);
        return $userProfile->validator();

    }
    /*
     *  @parms array $data
     *  @parms boject $model
     *
     *  @return bool
     * */
    public function addUserProfile($data, $model)
    {
        static::fill($data);
        $this->uid = $model->id;
        $this->createtime = $model->starttime;

        return $this->save();
    }


    /**
     * 定义字段名
     *
     * @return array */
    public  function atributeNames() {
        return [
            'realname'=> "姓名",
            'mobile' => "手机号码"
        ];
    }
    /**
     * 字段规则
     *
     * @return array */
    public  function rules()
    {
        $rules =  [
            'realname' => 'required|max:10',
            'mobile' => ['regex:/^1\d{10}$|^(0\d{2,3}-?|\(0\d{2,3}\))?[1-9]\d{4,7}(-\d{1,8})?$/',Rule::unique($this->table)->ignore(request()->id,'uid')],
            Rule::unique($this->table)->ignore(request()->id, 'uid')
        ];

        return $rules;
    }

}