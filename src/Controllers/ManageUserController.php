<?php

namespace Imzhi\InspiniaAdmin\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Imzhi\InspiniaAdmin\Models\AdminUser;
use Imzhi\InspiniaAdmin\Requests\ManageUserCreate;
use Imzhi\InspiniaAdmin\Requests\ManageUserStatus;
use Imzhi\InspiniaAdmin\Requests\ManageUserDistribute;
use Imzhi\InspiniaAdmin\Requests\ManageUserRolesCreate;
use Imzhi\InspiniaAdmin\Repositories\ManageUserRepository;
use Imzhi\InspiniaAdmin\Requests\ManageUserRolesDistribute;
use Imzhi\InspiniaAdmin\Requests\ManageUserPermissionsGroup;

class ManageUserController extends Controller
{
    protected $request;
    protected $manageUserRepository;

    public function __construct(Request $request, ManageUserRepository $manageUserRepository)
    {
        $this->request = $request;
        $this->manageUserRepository = $manageUserRepository;
    }

    /**
     * 管理员管理-成员列表页
     */
    protected function showList()
    {
        $request_data = $this->request->input();

        $list = $this->manageUserRepository->list($request_data);

        $status_rels = AdminUser::statusRels();
        $roles = $this->manageUserRepository->allRoles();
        $nav_id = 'manageuser.list';
        return view('admin::manageuser.list', compact('request_data', 'list', 'status_rels', 'roles', 'nav_id'));
    }

    /**
     * 管理员管理-新增/编辑成员页
     */
    protected function showCreate($id = null)
    {
        $user = Auth::guard('admin_user')->user();

        $data = null;
        $title = '新增成员';
        if ($id) {
            $data = $this->manageUserRepository->get($id);
            $title = '编辑成员';
            if (!$data) {
                return redirect(url()->previous())->withErrors('参数错误');
            }

            if ($this->manageUserRepository->ifNotAdmin($id, $user->id)) {
                return redirect(url()->previous())->withErrors('无权限修改初始管理员账号');
            }
        }

        $status_rels = AdminUser::statusRels();
        $nav_id = 'manageuser.list';
        return view('admin::manageuser.create', compact('data', 'title', 'status_rels', 'nav_id'));
    }

    /**
     * 管理员管理-新增/编辑成员操作
     */
    protected function create(ManageUserCreate $request)
    {
        $user = Auth::guard('admin_user')->user();

        $id = $this->request->input('id');
        $name = $this->request->input('name');
        $request_data = $this->request->except('id');

        if ($id) {
            if ($this->manageUserRepository->ifNotAdmin($id, $user->id)) {
                return ['err' => true, 'msg' => '无权限修改初始管理员账号'];
            }

            if ($this->manageUserRepository->checkSame($name, 'name', $id)) {
                return ['err' => true, 'msg' => '用户账号已存在'];
            }

            $result = $this->manageUserRepository->edit($request_data, $id);
            if (!$result) {
                return ['err' => true, 'msg' => '编辑成员失败'];
            }
            return ['err' => false, 'msg' => '编辑成员成功', 'redirect' => true];
        } else {
            if ($this->manageUserRepository->checkSame($name, 'name')) {
                return ['err' => true, 'msg' => '用户账号已存在'];
            }

            $result = $this->manageUserRepository->create($request_data);
            if (!$result) {
                return ['err' => true, 'msg' => '新增成员失败'];
            }
            return ['err' => false, 'msg' => '新增成员成功', 'redirect' => true];
        }
    }

    /**
     * 管理员管理-成员状态操作
     */
    protected function status(ManageUserStatus $request)
    {
        $user = Auth::guard('admin_user')->user();

        $user_id = $this->request->input('user_id');
        $status = $this->request->input('status');

        if ($this->manageUserRepository->ifNotAdmin($user_id, $user->id)) {
            return ['err' => true, 'msg' => '无权限修改初始管理员账号'];
        }

        if ($this->manageUserRepository->ifDisableAdmin($status, $user_id)) {
            return ['err' => true, 'msg' => '不能禁用初始管理员账号'];
        }

        $result = $this->manageUserRepository->status($status, $user_id);
        if (!$result) {
            return ['err' => true, 'msg' => '操作失败，请重试'];
        }

        return ['err' => false, 'msg' => '操作成功', 'reload' => true];
    }

    /**
     * 管理员管理-角色列表页
     */
    protected function showRoles()
    {
        $request_data = $this->request->input();

        $list = $this->manageUserRepository->roles();

        $nav_id = 'manageuser.roles';
        return view('admin::manageuser.roles', compact('request_data', 'list', 'nav_id'));
    }

    /**
     * 管理员管理-新增/编辑角色页
     */
    protected function showRolesCreate($id = null)
    {
        $data = null;
        $title = '新增角色';
        if ($id) {
            $data = $this->manageUserRepository->getRole($id);
            $title = '编辑角色';
            if (!$data) {
                return redirect(url()->previous())->withErrors('参数错误');
            }
        }

        $nav_id = 'manageuser.roles';
        return view('admin::manageuser.roles_create', compact('data', 'title', 'nav_id'));
    }

    /**
     * 管理员管理-新增/编辑角色操作
     */
    protected function rolesCreate(ManageUserRolesCreate $request)
    {
        $id = $this->request->input('id');
        $name = $this->request->input('name');
        $request_data = $this->request->except('id');

        if ($id) {
            if ($this->manageUserRepository->checkSameRole($name, $id)) {
                return ['err' => true, 'msg' => '角色名称已存在'];
            }

            $result = $this->manageUserRepository->rolesEdit($request_data, $id);
            if (!$result) {
                return ['err' => true, 'msg' => '编辑角色失败'];
            }
            return ['err' => false, 'msg' => '编辑角色成功', 'redirect' => true];
        } else {
            if ($this->manageUserRepository->checkSameRole($name)) {
                return ['err' => true, 'msg' => '角色名称已存在'];
            }

            $result = $this->manageUserRepository->rolesCreate($request_data);
            if (!$result) {
                return ['err' => true, 'msg' => '新增角色失败'];
            }
            return ['err' => false, 'msg' => '新增角色成功', 'redirect' => true];
        }
    }

    /**
     * 管理员管理-成员分配角色页
     */
    protected function showDistribute($id)
    {
        $data = $this->manageUserRepository->get($id);
        if (!$data) {
            return redirect(url()->previous())->withErrors('参数错误');
        }

        $list = $this->manageUserRepository->allRoles();

        $user_roles = $data->roles->pluck('id')->all();
        $nav_id = 'manageuser.list';
        return view('admin::manageuser.distribute', compact('data', 'list', 'user_roles', 'nav_id'));
    }

    /**
     * 管理员管理-成员分配角色操作
     */
    protected function distribute(ManageUserDistribute $request)
    {
        $id = $this->request->input('id');
        $role_ids = $this->request->input('role_ids', []);

        $result = $this->manageUserRepository->distribute($role_ids, $id);
        if (!$result) {
            return ['err' => true, 'msg' => '操作失败'];
        }
        return ['err' => false, 'msg' => '操作成功', 'redirect' => true];
    }

    /**
     * 管理员管理-角色分配权限页
     */
    protected function showRolesDistribute($id)
    {
        $data = $this->manageUserRepository->getRole($id);
        if (!$data) {
            return redirect(url()->previous())->withErrors('参数错误');
        }

        $list = $this->manageUserRepository->groupSortPermissions($this->manageUserRepository->allPermissions());

        $role_permissions = $data->permissions->pluck('id')->all();
        $nav_id = 'manageuser.roles';
        return view('admin::manageuser.roles_distribute', compact('data', 'list', 'role_permissions', 'nav_id'));
    }

    /**
     * 管理员管理-角色分配权限操作
     */
    protected function rolesDistribute(ManageUserRolesDistribute $request)
    {
        $id = $this->request->input('id');
        $permission_ids = $this->request->input('permission_ids', []);

        $result = $this->manageUserRepository->rolesDistribute($permission_ids, $id);
        if (!$result) {
            return ['err' => true, 'msg' => '操作失败'];
        }
        return ['err' => false, 'msg' => '操作成功', 'redirect' => true];
    }

    /**
     * 管理员管理-权限列表页
     */
    protected function showPermissions()
    {
        $request_data = $this->request->input();

        $list = $this->manageUserRepository->permissions($request_data);

        $nav_id = 'manageuser.permissions';
        return view('admin::manageuser.permissions', compact('request_data', 'list', 'nav_id'));
    }

    /**
     * 管理员管理-权限批量检测操作
     */
    protected function permissionsDetect()
    {
        $result = $this->manageUserRepository->permissionsDetect();
        return $result;
    }

    /**
     * 管理员管理-权限批量分组操作
     */
    protected function permissionsGroup(ManageUserPermissionsGroup $request)
    {
        $request_data = $this->request->input();

        $result = $this->manageUserRepository->permissionsGroup($request_data);
        if ($result === false) {
            return ['err' => true, 'msg' => '分组失败'];
        }

        return ['err' => false, 'msg' => '分组成功', 'reload' => true];
    }
}