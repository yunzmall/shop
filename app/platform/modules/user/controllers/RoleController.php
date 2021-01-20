<?php
/**
 * Created by PhpStorm.
 * User: dingran
 * Date: 2019/3/10
 * Time: 下午12:37
 */

namespace app\platform\modules\user\controllers;


use app\platform\controllers\BaseController;
use app\platform\modules\user\models\Permission;
use app\platform\modules\user\models\Role;
use app\platform\modules\user\requests\RoleCreateRequest;
use app\platform\modules\user\requests\RoleUpdateRequest;
use Illuminate\Http\Request;

class RoleController extends BaseController
{
    protected $fields = [
        'name' => '',
        'description' => '',
        'permissions' => [],
    ];


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $datas['data'] = Role::select(['id', 'name', 'description', 'created_at', 'updated_at'])->orderBy('id', 'desc')->get();
        return view('admin.role.index', $datas);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = [];
        foreach ($this->fields as $field => $default) {
            $data[$field] = old($field, $default);
        }
        $arr = Permission::all()->toArray();
        foreach ($arr as $v) {
            $data['permissionAll'][$v['parent_id']][] = $v;
        }
        return view('admin.role.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param RoleCreateRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(RoleCreateRequest $request)
    {
        // dd($request->get('permission'));
        $role = new Role();
        foreach (array_keys($this->fields) as $field) {
            $role->$field = $request->get($field);
        }
        unset($role->permissions);
        // dd($request->get('permission'));
        $role->save();
        if (is_array($request->get('permissions'))) {
            $role->permissions()->sync($request->get('permissions',[]));
        }

        return $this->successJson('添加成功', []);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $role = Role::find((int)$id);
        if (!$role) return redirect('/admin/role')->withErrors("找不到该角色!");
        $permissions = [];
        if ($role->permissions) {
            foreach ($role->permissions as $v) {
                $permissions[] = $v->id;
            }
        }
        $role->permissions = $permissions;
        foreach (array_keys($this->fields) as $field) {
            $data[$field] = old($field, $role->$field);
        }
        $arr = Permission::all()->toArray();
        foreach ($arr as $v) {
            $data['permissionAll'][$v['parent_id']][] = $v;
        }
        $data['id'] = (int)$id;
        return view('admin.role.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param PermissionUpdateRequest|Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(RoleUpdateRequest $request, $id)
    {
        $role = Role::find((int)$id);
        foreach (array_keys($this->fields) as $field) {
            $role->$field = $request->get($field);
        }
        unset($role->permissions);
        $role->save();

        $role->permissions()->sync($request->get('permissions',[]));

        return $this->successJson('添加成功', []);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $role = Role::find((int)$id);
        foreach ($role->users as $v){
            $role->users()->detach($v);
        }

        foreach ($role->permissions as $v){
            $role->permissions()->detach($v);
        }

        if ($role) {
            /*********************用户被删除BUG-log*********************/
            $find = base_path().'\storage\logs\user_admin_delete_log.log';
            if(!file_exists($find)){
                fopen($find,'a');
            } 
            $array = [];
            $array['deleteid'] = $id;
            $array['uid'] = \YunShop::app()->uid;
            $array['uniacid'] = \YunShop::app()->uniacid;
            $array['acid'] = \YunShop::app()->acid;
            $array['username'] = \YunShop::app()->username;
            $array['siteurl'] = \YunShop::app()->siteurl;
            $array['time'] = date('Y-m-d H:i:s',time());
            $txt = "app\platform\modules\user\controllers\RoleController.php\n";
            $txt .= json_encode($array,true)."\n\n";
            file_put_contents($find,$txt, FILE_APPEND);
            \Log::debug("====用户被删除BUG-log===",$array);
            /*********************用户被删除BUG-log*********************/
            $role->delete();
        } else {
            return redirect()->back()
                ->withErrors("删除失败");
        }

        return redirect()->back()
            ->withSuccess("删除成功");
    }
}
