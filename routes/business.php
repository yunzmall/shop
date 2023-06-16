<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/9/22
 * Time: 13:37
 */

const BASE_ROUTE = 'business';

Route::group(['middleware' => ['business:', 'businessLogin:']], function () {  //插件路由
    $base_route = BASE_ROUTE . '/{uniacid}/plugin/';

    /*芸客服路由*/
    Route::group(['prefix' => $base_route . 'YunChat/'], function () {
        $yun_chat_path = '\Yunshop\YunChat\manage\\';
        Route::any('groupGetList', $yun_chat_path . 'GroupController@getList');//客服组列表
        Route::any('groupSave', $yun_chat_path . 'GroupController@save');//添加编辑客服组
        Route::any('groupDelete', $yun_chat_path . 'GroupController@delete');//删除客服组
        Route::any('groupQuery', $yun_chat_path . 'GroupController@query');//检索客服组

        Route::any('employeeGetList', $yun_chat_path . 'EmployeeController@getList');//客服列表
        Route::any('employeeGetSummary', $yun_chat_path . 'EmployeeController@getSummary');//客服列表统计信息
        Route::any('employeeCreate', $yun_chat_path . 'EmployeeController@create');//添加客服
        Route::any('employeeUpdate', $yun_chat_path . 'EmployeeController@update');//编辑客服
        Route::any('employeeGetDetail', $yun_chat_path . 'EmployeeController@getDetail');//客服详情
        Route::any('employeeChangeStatus', $yun_chat_path . 'EmployeeController@changeStatus');//修改客服状态
        Route::any('employeeDelete', $yun_chat_path . 'EmployeeController@delete');//删除客服
        Route::any('employeeSetAgent', $yun_chat_path . 'EmployeeController@setAgent');//分配客服坐席
        Route::any('employeeRemoveAgent', $yun_chat_path . 'EmployeeController@removeAgent');//释放客服坐席

        Route::any('chatGetHistoryList', $yun_chat_path . 'ChatController@getHistoryList');//会话列表
        Route::any('chatGetChatList', $yun_chat_path . 'ChatController@getChatList');//会话消息列表
        Route::any('chatDeleteChat', $yun_chat_path . 'ChatController@deleteChat');//会话消息删除

        Route::any('commonReplyGetList', $yun_chat_path . 'CommonReplyController@getList');//快捷回复列表
        Route::any('commonReplyGetDetail', $yun_chat_path . 'CommonReplyController@getDetail');//快捷回复详情
        Route::any('commonReplySave', $yun_chat_path . 'CommonReplyController@save');//添加编辑快捷回复
        Route::any('commonReplyDelete', $yun_chat_path . 'CommonReplyController@delete');//删除快捷回复

        Route::any('setBasic', $yun_chat_path . 'SetController@basic');//获取设置信息
        Route::any('setBasicPost', $yun_chat_path . 'SetController@basicPost');//保存芸客服设置信息

        Route::any('wordsReplyGetList', $yun_chat_path . 'WordsReplyController@getList');//关键词回复列表
        Route::any('wordsReplyGetDetail', $yun_chat_path . 'WordsReplyController@getDetail');//获取关键词回复详情
        Route::any('wordsReplySave', $yun_chat_path . 'WordsReplyController@save');//添加编辑关键词回复
        Route::any('wordsReplyDelete', $yun_chat_path . 'WordsReplyController@delete');//删除关键词回复
        Route::any('wordsReplyQueryKeyWords', $yun_chat_path . 'WordsReplyController@queryKeyWords');//检索快捷回复关键词

        Route::any('toolbarGetList', $yun_chat_path . 'ToolBarController@getList');//聊天工具栏列表
        Route::any('toolbarSave', $yun_chat_path . 'ToolBarController@save');//聊天工具栏添加编辑
        Route::any('toolbarGetDetail', $yun_chat_path . 'ToolBarController@getDetail');//获取工具栏详情
        Route::any('toolbarChangeStatus', $yun_chat_path . 'ToolBarController@changeStatus');//修改聊天工具栏状态
        Route::any('toolbarDelete', $yun_chat_path . 'ToolBarController@delete');//删除聊天工具栏


        Route::any('getWechatKfSet', $yun_chat_path . 'SetController@getWechatKfSet');//设置微信客服设置
        Route::any('saveWechatKfSet', $yun_chat_path . 'SetController@saveWechatKfSet');//保存微信客服设置
        Route::any('getWechatKfAccount', $yun_chat_path . 'GroupController@getWechatKfAccount');//获取微信客服账号列表
        Route::any('bindWhatKf', $yun_chat_path . 'GroupController@bindWhatKf');//绑定微信客服账号
        Route::any('changeWechatKfStatus', $yun_chat_path . 'GroupController@changeWechatKfStatus');//开启或关闭微信客服功能
        Route::any('syncWechatKfEmployee', $yun_chat_path . 'EmployeeController@syncWechatKfEmployee');//同步微信客服接待员
        Route::any('changeWeChatKfUser', $yun_chat_path . 'EmployeeController@changeWeChatKfUser');//添加或删除客服帐号接待人员
        Route::post('getDepartmentMember', '\business\admin\controllers\DepartmentMemberController@getDepartmentMember');// 选择部门和成员


    });
    /*芸客服路由*/

    /*律师平台路由*/
    Route::group(['prefix' => $base_route . 'LawyerPlatform/'], function () {
        $lawyer_platform_path = '\Yunshop\LawyerPlatform\manage\controllers\\';
        Route::any('lawyerSearch', $lawyer_platform_path . 'LawyerController@search');
        Route::any('lawyerDetail', $lawyer_platform_path . 'LawyerController@detail');
        Route::any('lawyerEdit', $lawyer_platform_path . 'LawyerController@edit');
        Route::any('lawyerApply', $lawyer_platform_path . 'LawyerController@apply');
        Route::any('lawyerBlack', $lawyer_platform_path . 'LawyerController@black');
        Route::any('getCategory', $lawyer_platform_path . 'LawyerController@getCategory');
        Route::any('lawyerApplySearch', $lawyer_platform_path . 'LawyerApplyController@search');
        Route::any('lawyerApplyDetail', $lawyer_platform_path . 'LawyerApplyController@detail');
        Route::any('lawyerFirmEdit', $lawyer_platform_path . 'LawyerFirmController@edit');
        Route::any('lawyerFirmDetail', $lawyer_platform_path . 'LawyerFirmController@detail');
        Route::any('getOrderList', $lawyer_platform_path . 'OrderListController@getList');
        Route::any('orderExport', $lawyer_platform_path . 'OrderListController@export');
        Route::any('getOrderButton', $lawyer_platform_path . 'OrderListController@getOrderButton');
        Route::any('getOrderDetail', $lawyer_platform_path . 'OrderListController@getDetail');
        Route::any('getDividend', $lawyer_platform_path . 'DividendController@search');
    });
    /*律师平台路由*/

    /*企业客户路由*/
    Route::group(['prefix' => $base_route . 'WechatCustomers/'], function () {
        $wechat_cunstomers_path = '\Yunshop\WechatCustomers\manage\\';
        Route::any('getList', $wechat_cunstomers_path . 'customer\IndexController@getList');//列表
        Route::any('synchBaseData', $wechat_cunstomers_path . 'customer\IndexController@synchBaseData');//同步企业微信
        Route::any('synchMemberBaseData', $wechat_cunstomers_path . 'customer\IndexController@synchMemberBaseData');//同步会员
        Route::any('getDetails', $wechat_cunstomers_path . 'customer\IndexController@getDetails');//同步会员
        Route::any('myCustomerList', $wechat_cunstomers_path . 'customer\MyCustomerController@index');//我的客户
        Route::any('myLossCustomerList', $wechat_cunstomers_path . 'customer\MyLossCustomerController@index');//我的流失客户
        Route::any('receive', '\Yunshop\WorkWechat\wevent\ChangeExtContactController@receive');//同步会员
    });
    /*企业客户路由*/


    /*群拓客路由*/
    Route::group(['prefix' => $base_route . 'GroupDevelopUser/'], function () {
        $this_path = '\Yunshop\GroupDevelopUser\manage\\';
        Route::post('getSetting', $this_path . 'SettingController@getSetting');//获取设置
        Route::post('setSetting', $this_path . 'SettingController@setSetting');//编辑设置
        Route::post('getGroupSetting', $this_path . 'SettingController@getGroupSetting');//获取群设置
        Route::post('setGroupSetting', $this_path . 'SettingController@setGroupSetting');//编辑群设置
        Route::any('getList', $this_path . 'GroupChatController@getList');//列表
        Route::any('synch', $this_path . 'GroupChatController@synch');//同步
        Route::any('listExport', $this_path . 'GroupChatController@listExport');//导出
        Route::any('getGroupMembers', $this_path . 'GroupChatController@getGroupMembers');//获取群会员
        Route::any('saveQrcodeImg', $this_path . 'GroupChatController@saveQrcodeImg');//上传二维码
        Route::any('getNewPost', $this_path . 'PosterController@getPoster');//生成海报
        Route::any('posterList', $this_path . 'PosterController@posterList');//海报列表
        Route::any('deletePost', $this_path . 'PosterController@deletePoster');//删除海报
        Route::any('deleteAllPoster', $this_path . 'PosterController@deleteAllPoster');//删除所有满足搜索条件的海报
        Route::any('refreshPost', $this_path . 'PosterController@refreshPoster');//重新生成海报
        Route::any('bindUser', $this_path . 'GroupChatController@bindUser');//生成海报
        Route::any('getGroupEmployees', $this_path . 'GroupChatController@getGroupEmployees');//生成海报
        Route::any('groupMemberListExport', $this_path . 'GroupChatController@groupMemberListExport');//生成海报
        Route::any('groupEmployeeListExport', $this_path . 'GroupChatController@groupEmployeeListExport');//生成海报
        Route::any('bindExternalContacts', $this_path . 'GroupChatController@bindExternalContacts');//绑定外部联系人
        Route::any('getMemberList', $this_path . 'GroupChatController@getMemberList');//获取商城会员

        // 群标签
        Route::post('saveGroupChatTag', $this_path . 'GroupChatController@saveGroupChatTag');// 群聊绑定标签
        Route::post('getGroupList', $this_path . 'GroupChatTagController@getGroupList');// 获取标签组列表
        Route::post('getTagList', $this_path . 'GroupChatTagController@getTagList');// 获取标签列表
        Route::post('getAllTag', $this_path . 'GroupChatTagController@getAllTag');// 获取所有标签
        Route::post('getAllGroupTag', $this_path . 'GroupChatTagController@getAllGroupTag');// 获取整合信息
        Route::post('getGroupDetail', $this_path . 'GroupChatTagController@getGroupDetail');// 获取标签组详情
        Route::post('getTagDetail', $this_path . 'GroupChatTagController@getTagDetail');// 获取标签详情
        Route::post('saveTag', $this_path . 'GroupChatTagController@saveTag');// 保存标签
        Route::post('saveGroup', $this_path . 'GroupChatTagController@saveGroup');// 保存标签组
        Route::post('delGroup', $this_path . 'GroupChatTagController@delGroup');// 删除标签组
        Route::post('delTag', $this_path . 'GroupChatTagController@delTag');// 删除标签
    });
    /*群拓客路由*/

    /*群拓客奖励路由*/
    Route::group(['prefix' => $base_route . 'GroupReward/'], function () {
        $this_path = '\Yunshop\GroupReward\manage\\';
        Route::post('getActivityList', $this_path . 'GroupRewardController@getActivityList');// 活动管理数据接口
        Route::post('getRewardDetail', $this_path . 'GroupRewardController@getRewardDetail');// 活动群聊数据接口
        Route::post('getActivityDetail', $this_path . 'GroupRewardController@getActivityDetail');// 活动详细信息接口
        Route::post('setGroupActivity', $this_path . 'GroupRewardController@setGroupActivity');// 保存活动
        Route::post('setActivityStatus', $this_path . 'GroupRewardController@setActivityStatus');// 结束活动
        Route::post('searchGroup', $this_path . 'GroupRewardController@searchGroup');// 搜索活动
        Route::post('searchCoupon', $this_path . 'GroupRewardController@searchCoupon');// 搜索优惠券
        Route::any('rewardExport', $this_path . 'GroupRewardController@export');// 导出
    });
    /*群拓客奖励路由*/

    /*侧边栏路由*/
    Route::group(['prefix' => $base_route . 'WechatChatSidebar/'], function () {
        $this_path = '\Yunshop\WechatChatSidebar\manage\\';
        Route::any('getDepartmentMember', '\business\admin\controllers\DepartmentMemberController@getDepartmentMember'); //选择部门和成员
        Route::any('getData', $this_path . 'ListController@getData');//列表
        Route::any('getLinkPower', $this_path . 'ListController@getLinkPower');//获取链接权限角色
        Route::any('editPower', $this_path . 'ListController@editPower');//修改链接权限
    });
    /*侧边栏路由*/

    /*会话存档路由*/
    Route::group(['prefix' => $base_route . 'WorkSession/'], function () {
        $work_session_path = '\Yunshop\WorkSession\manage\\';
        Route::get('basicInfo', $work_session_path . 'SetController@basicInfo');//员工列表
        Route::get('basicSave', $work_session_path . 'SetController@basicSave');//员工列表
        Route::get('getList', $work_session_path . 'MessageController@getList');//员工列表
        Route::get('switchStaff', $work_session_path . 'MessageController@switchStaff');//切换员工、消息类型，搜索群聊或者私聊
        Route::post('getChatMsg', $work_session_path . 'MessageController@getChatMsg');//获取群聊消息列表
        Route::post('getPrivateChatMsg', $work_session_path . 'MessageController@getPrivateChatMsg');//获取私聊消息列表
    });
    /*会话存档路由*/

    /*欢迎语路由*/
    Route::group(['prefix' => $base_route . 'WelcomeWords/'], function () {
        $this_path = '\Yunshop\WelcomeWords\manage\\';
        Route::any('getDepartmentMember', '\business\admin\controllers\DepartmentMemberController@getDepartmentMember'); //选择部门和成员
        Route::any('getList', $this_path . 'ListController@listData');//欢迎语列表
        Route::any('detail', $this_path . 'ListController@detail');//欢迎语详情
        Route::any('save', $this_path . 'ListController@save');//欢迎语保存
        Route::any('search', $this_path . 'ListController@search');//部门成员搜索
        Route::any('delete', $this_path . 'ListController@delete');//欢迎语删除
    });
    /*欢迎语路由*/


    /*企业客户标签路由*/
    Route::group(['prefix' => $base_route . 'WorkWechatTag/'], function () {
        $this_path = '\Yunshop\WorkWechatTag\business\\';

        Route::any('getTagSetting', $this_path . 'SettingController@getSetting');//获取标签设置
        Route::any('setTagSetting', $this_path . 'SettingController@setSetting');//编辑标签设置

        Route::any('tagList', $this_path . 'TagController@tagList');//标签列表
        Route::any('tagDetail', $this_path . 'TagController@tagDetail');//标签详情
        Route::any('addTag', $this_path . 'TagController@addTag');//添加标签
        Route::any('editTag', $this_path . 'TagController@editTag');//编辑标签
        Route::any('deleteTag', $this_path . 'TagController@deleteTag');//删除标签

        Route::any('tagGroupList', $this_path . 'TagGroupController@tagGroupList');//标签组列表
        Route::any('tagGroupDetail', $this_path . 'TagGroupController@tagGroupDetail');//标签组详情
        Route::any('addTagGroup', $this_path . 'TagGroupController@addTagGroup');//添加标签组
        Route::any('editTagGroup', $this_path . 'TagGroupController@editTagGroup');//编辑标签组
        Route::any('deleteTagGroup', $this_path . 'TagGroupController@deleteTagGroup');//删除标签组
        Route::any('groupChooseList', $this_path . 'TagGroupController@groupChooseList');//标签组选择栏

        Route::any('refreshShopTag', $this_path . 'RefreshController@refreshShopTag');//同步商城标签
        Route::any('refreshWechatTag', $this_path . 'RefreshController@refreshWechatTag');//同步企业微信客户标签

    });
    /*企业客户标签路由*/

    /*企业客户好友裂变路由*/
    Route::group(['prefix' => $base_route . 'CustomerIncrease/'], function () {
        $this_path = '\Yunshop\CustomerIncrease\business\\';

        Route::any('activityList', $this_path . 'ActivityController@activityList');//活动列表
        Route::any('activityDetail', $this_path . 'ActivityController@activityDetail');//活动详情
        Route::any('activityAdd', $this_path . 'ActivityController@activityAdd');//创建活动
        Route::any('activityEdit', $this_path . 'ActivityController@activityEdit');//编辑活动
        Route::any('activityClose', $this_path . 'ActivityController@activityClose');//结束活动
        Route::any('activityCount', $this_path . 'ActivityController@activityCount');//活动助力统计
        Route::any('activityCode', $this_path . 'ActivityController@activityCode');//活动推广码
        Route::any('searchTag', $this_path . 'ActivityController@searchTag');//查询企业微信标签
        Route::any('searchCoupon', $this_path . 'ActivityController@searchCoupon');//查询优惠券

        Route::any('memberList', $this_path . 'CountController@memberList');//参与记录列表
        Route::any('activityAnalysis', $this_path . 'CountController@activityAnalysis');//统计概况

        Route::any('posterList', $this_path . 'PosterController@posterList');//海报生成记录列表
        Route::any('posterDelete', $this_path . 'PosterController@posterDelete');//删除海报
        Route::any('posterRefresh', $this_path . 'PosterController@posterRefresh');//重新生成海报
        Route::any('deleteManyPoster', $this_path . 'PosterController@deleteManyPoster');//批量删除海报

        Route::any('rewardList', $this_path . 'RewardController@rewardList');//奖励记录列表
        Route::any('rewardByPeople', $this_path . 'RewardController@rewardByPeople');//手动发放奖励

        Route::any('searchStaff', '\business\admin\controllers\StaffController@searchStaff');//查询企业微信关联员工

    });
    /*企业客户好友裂变路由*/

    /*sop任务*/
    Route::group(['prefix' => $base_route . 'SopTask/'], function () {
        $this_path = '\Yunshop\SopTask\business\controller\\';

        Route::post('getTaskList', $this_path . 'TaskListController@getTaskList');// 任务列表
        Route::post('getLogList', $this_path . 'TaskListController@getLogList');// 推送列表
        Route::post('getTaskDetail', $this_path . 'TaskListController@getTaskDetail');// 任务详情
        Route::post('saveTask', $this_path . 'TaskListController@saveTask');// 保存任务
        Route::post('setTaskStatus', $this_path . 'TaskListController@setTaskStatus');// 修改任务状态
        Route::post('delTask', $this_path . 'TaskListController@delTask');// 删除任务
        Route::post('getTagList', $this_path . 'TaskListController@getTagList');// 获取企业标签
        Route::get('getSet', $this_path . 'TaskListController@getSet');// 获取基础配置
        Route::any('taskExport', $this_path . 'TaskListController@taskExport');// 导出任务
        Route::any('logExport', $this_path . 'TaskListController@logExport');// 导出推送日志
        Route::post('checkGroupChat', $this_path . 'GroupChatController@checkGroupChat');// 查看群数量

        Route::post('searchStaff', $this_path . 'StaffController@searchStaff');// 查询企业微信关联员工
        Route::post('searchGroupLeader', $this_path . 'GroupChatController@searchGroupLeader');// 获取群主列表
        Route::post('getAllTagGroup', $this_path . 'GroupChatController@getAllTagGroup');// 获取所有标签组
        Route::post('searchGroupChat', $this_path . 'GroupChatController@searchGroupChat');// 查询群聊
        Route::post('getAllTag', $this_path . 'GroupChatTagController@getAllTag');// 获取所有标签
    });
    /*sop任务*/

    /*话术库路由*/
    Route::group(['prefix' => $base_route . 'SpeechcraftLibrary/'], function () {
        $this_path = '\Yunshop\SpeechcraftLibrary\business\controller\\';

        Route::post('getDepartmentMember', '\business\admin\controllers\DepartmentMemberController@getDepartmentMember'); //选择部门和成员

        Route::post('getGroupList', $this_path . 'MaterialGroupController@getGroupList');// 获取分组列表
        Route::post('getGroupInfo', $this_path . 'MaterialGroupController@getGroupInfo');// 获取分组信息
        Route::post('saveGroup', $this_path . 'MaterialGroupController@saveGroup');// 保存分组信息
        Route::post('delGroup', $this_path . 'MaterialGroupController@delGroup');// 删除分组

        Route::post('getMaterialList', $this_path . 'MaterialController@getMaterialList');// 获取素材列表
        Route::post('getMaterialInfo', $this_path . 'MaterialController@getMaterialInfo');// 获取素材信息
        Route::post('saveMaterial', $this_path . 'MaterialController@saveMaterial');// 保存素材
        Route::post('delMaterial', $this_path . 'MaterialController@delMaterial');// 删除素材
        Route::post('editMaterialStatus', $this_path . 'MaterialController@editMaterialStatus');// 修改素材状态

        Route::post('getSet', $this_path . 'SetController@index');// 获取基础设置
        Route::post('editSet', $this_path . 'SetController@editSet');// 修改基础设置
    });
    /*话术库路由*/

    /*让利涨粉*/
    Route::group(['prefix' => $base_route . 'DiscountHarvestFans/'], function () {
        $this_path = '\Yunshop\DiscountHarvestFans\business\controller\\';

        Route::post('getSet', $this_path . 'SetController@getSet');// 获取设置
        Route::post('saveSet', $this_path . 'SetController@saveSet');// 保存设置
        Route::post('getStaffList', $this_path . 'StaffController@getStaffList');// 获取员工列表
        Route::post('getTagList', $this_path . 'TagController@getTagList');// 获取标签列表
    });
    /*让利涨粉*/

    /*pos收银*/
    Route::group(['prefix' => $base_route . 'ShopPos/'], function () {
        $this_path = '\Yunshop\ShopPos\business\\';
        Route::post('getSetting', $this_path . 'SettingController@index');//获取设置
        Route::post('editSetting', $this_path . 'SettingController@edit');//编辑设置
        Route::post('searchMember', '\business\admin\controllers\StaffController@businessGetMemberByMobile');//查询会员
    });
    /*pos收银*/

    /*门店pos收银*/
    Route::group(['prefix' => $base_route . 'StorePos/'], function () {
        $this_path = '\Yunshop\StorePos\business\\';
        Route::post('getSetting', $this_path . 'SettingController@index');//获取设置
        Route::post('editSetting', $this_path . 'SettingController@edit');//编辑设置
        Route::any('clerkSumLog', $this_path . 'ClerkLogController@index');//收银统计
        Route::any('clerkDayLog', $this_path . 'ClerkLogController@dayLog');//收银日志
        Route::post('departmentSelect', $this_path . 'ClerkLogController@department');//部门选择栏
    });
    /*门店pos收银*/

    /*众筹活动*/
    Route::group(['prefix' => $base_route . 'Crowdfunding/'], function () {
        $this_path = '\Yunshop\Crowdfunding\business\\';
        Route::any('activityChannelIndex', $this_path . 'ActivityChannelController@index');// 活动列表
        Route::any('activityChannelPromotes', $this_path . 'ActivityChannelController@promotes');// 推广活动
        Route::any('activityMemberIndex', $this_path . 'ActivityMemberController@index');// 报名活动数据
        Route::any('activityOrderIndex', $this_path . 'ActivityOrderController@index');// 订单管理
    });
    /*众筹活动*/

    /*拓客雷达*/
    Route::group(['prefix' => $base_route . 'CustomerRadar/'], function () {
        $this_path = '\Yunshop\CustomerRadar\business\controller\\';

        Route::post('getSet', $this_path . 'SetController@getSet');// 获取设置
        Route::post('saveSet', $this_path . 'SetController@saveSet');// 保存设置
        Route::post('getMemberList', $this_path . 'SetController@getMemberList');// 会员列表
        Route::post('getDepartmentMember', '\business\admin\controllers\DepartmentMemberController@getDepartmentMember');// 选择部门和成员
    });
    /*拓客雷达*/

    /*商城电子合同2.0*/
    Route::group(['prefix' => $base_route . 'ShopEsignV2/'], function () {
        $this_path = '\Yunshop\ShopEsignV2\business\controllers\\';

        Route::any('getSet', $this_path . 'SetController@getSet');
        Route::any('storeSet', $this_path . 'SetController@storeSet');
        Route::any('getLevel', $this_path . 'SceneController@getLevel');
        Route::any('getScene', $this_path . 'SceneController@getScene');
        Route::any('getTemplateList', $this_path . 'SceneController@getTemplateList');
        Route::any('getByTid', $this_path . 'SceneController@getByTid');
        Route::any('addScene', $this_path . 'SceneController@addScene');
        Route::any('editShow', $this_path . 'SceneController@editShow');
        Route::any('editScene', $this_path . 'SceneController@editScene');
        Route::any('searchContract', $this_path . 'ContractController@searchContract');
        Route::any('downloadContract', $this_path . 'ContractController@downloadContract');
    });
    /*拓客雷达*/

    /*客户管理*/
    Route::group(['prefix' => $base_route . 'CustomerManage/'], function () {
        $this_path = '\Yunshop\CustomerManage\business\controller\\';


        //在plugins/customer-manage/src/menus文件夹下的所有类都有一个getRoutes方法
        if (app('plugins')->isEnabled('customer-manage')) {

            $routes = \app\common\modules\shop\ShopConfig::current()->get('business_plugin_routes.CustomerManage');

            foreach ($routes as $prefix => $route) {
                $class = $route['class'];
                $function = $route['function'];

                if (class_exists($class) && method_exists($class, $function) && is_callable([$class, $function])) {

                    $routes_arr = $class::$function();

                    if (!$routes_arr || empty($routes_arr) || count($routes_arr) <= 0) {
                        continue;
                    } else {
                        Route::group(['prefix' => "{$prefix}/"], function () use ($this_path, $routes_arr) {
                            foreach ($routes_arr as $item) {
                                $type = strtolower($item['type']);
                                switch ($type) {
                                    case 'post':
                                        Route::post($item['route'], $this_path . $item['controller']);
                                        break;
                                    case 'get':
                                        Route::get($item['route'], $this_path . $item['controller']);
                                        break;
                                    default :
                                        Route::any($item['route'], $this_path . $item['controller']);
                                }
                            }
                        });
                    }
                }
            }
        }


        Route::any('importTemplate', $this_path . 'CustomerController@importTemplate');// 导入模板


        Route::post('getIndustryList', $this_path . 'IndustryController@getIndustryList');// 获取行业列表
        Route::post('saveIndustry', $this_path . 'IndustryController@saveIndustry');// 保存行业
        Route::post('delIndustry', $this_path . 'IndustryController@delIndustry');// 删除行业

        Route::post('getProgressList', $this_path . 'ProgressController@getProgressList');// 获取进展列表
        Route::post('saveProgress', $this_path . 'ProgressController@saveProgress');// 保存进展
        Route::post('delProgress', $this_path . 'ProgressController@delProgress');// 删除进展

        Route::post('getSourceList', $this_path . 'SourceController@getSourceList');// 获取来源列表
        Route::post('saveSource', $this_path . 'SourceController@saveSource');// 保存来源
        Route::post('delSource', $this_path . 'SourceController@delSource');// 删除来源

        Route::post('getSet', $this_path . 'SetController@getSet');// 获取基本设置菜单所有信息
        Route::post('getBasicSet', $this_path . 'SetController@getBasicSet');// 获取基本设置
        Route::post('saveSet', $this_path . 'SetController@saveSet');// 保存设置
        Route::post('saveStrategy', $this_path . 'SetController@saveStrategy');// 保存回收策略

        Route::post('getTagList', $this_path . 'TagController@getTagList');// 获取标签列表

        Route::any('getDepartmentMember', '\business\admin\controllers\DepartmentMemberController@getDepartmentMember'); //选择部门和成员
        Route::post('getStaffList', $this_path . 'StaffController@getStaffList'); //根据部门id获取员工列表

        Route::post('getCustomerAllList', $this_path . 'CustomerAllController@index');// 获取全部客户列表

        Route::post('getStatisticsList', $this_path . 'CustomerStatisticsController@index'); //客户统计
        Route::any('getDepartmentList', $this_path . 'CustomerStatisticsController@getDepartmentList'); //部门列表
    });
    /*客户管理*/

    /*外呼电销系统*/
    Route::group(['prefix' => $base_route . 'OutboundSystem/'], function () {
        $this_path = '\Yunshop\OutboundSystem\business\controllers\\';

        Route::post('getRecordList', $this_path . 'CallRecordController@getRecordList');// 获取通话记录列表
        Route::post('getStatisticsList', $this_path . 'TrafficStatisticsController@getStatisticsList');// 获取话务统计列表
        Route::post('getSet', $this_path . 'SetController@getSet');// 获取基础设置
        Route::post('saveSet', $this_path . 'SetController@saveSet');// 保存设置
        Route::post('getDepartmentList', $this_path . 'DepartmentController@getDepartmentList');// 保存设置

        Route::any('getMyRecordList', $this_path . 'CallRecordController@getMyRecordList');// 获取我的通话记录列表

        Route::any('staffAgent/agentList', $this_path . 'StaffAgentController@agentList');//获取员工列表
        Route::post('staffAgent/setAgent', $this_path . 'StaffAgentController@setAgent');// 保存员工工号
    });
    /*外呼电销系统*/

    /*爆客码*/
    Route::group(['prefix' => $base_route . 'DrainageCode/'], function () {
        $this_path = '\Yunshop\DrainageCode\business\controllers\\';

        Route::post('saveCode', $this_path . 'CodeController@saveCode');// 保存爆客码
        Route::post('copyCode', $this_path . 'CodeController@copyCode');// 复制爆客码
        Route::post('delCode', $this_path . 'CodeController@delCode');// 删除爆客码
        Route::post('getDetail', $this_path . 'CodeController@getDetail');// 获取爆客码详情
        Route::post('getList', $this_path . 'CodeController@getList');// 获取爆客码列表
        Route::post('getSceneTypeList', $this_path . 'CodeController@getSceneTypeList');// 获取爆客场景
        Route::post('getQrCode', $this_path . 'CodeController@getQrCode');// 获取二维码
        Route::post('getMiniQrCode', $this_path . 'CodeController@getMiniQrCode');// 获取小程序码
        Route::post('getTagList', $this_path . 'TagController@getTagList');// 获取标签列表
        Route::post('getDepartmentMember', '\business\admin\controllers\DepartmentMemberController@getDepartmentMember');// 选择部门和成员
    });
    /*爆客码*/

    /*员工审批路由*/
    Route::group(['prefix' => $base_route . 'StaffAudit/'], function () {
        $this_path = '\Yunshop\StaffAudit\business\controllers\\';
        //基础设置
        Route::post('getSetting', $this_path . 'SettingController@getSetting');// 获取设置
        Route::post('saveSetting', $this_path . 'SettingController@saveSetting');// 编辑设置
        Route::any('getDepartmentMember', '\business\admin\controllers\DepartmentMemberController@getDepartmentMember');// 选择部门和成员

        //审批记录
        Route::post('getAuditLog', $this_path . 'AuditLogController@getList');// 获取审批记录
        Route::post('getAuditLogDepartmentList', $this_path . 'AuditLogController@getDepartmentList');// 获取部门列表

        //奖励记录
        Route::post('getRewardLog', $this_path . 'RewardLogController@getList');// 获取审批记录
        Route::post('getRewardLogDepartmentList', $this_path . 'RewardLogController@getDepartmentList');// 获取部门列表

    });
    /*员工审批路由*/

    /*商机管理*/
    Route::group(['prefix' => $base_route . 'opportunityManagement/'], function () {
        $this_path = '\Yunshop\OpportunityManagement\manage\\';
        Route::any('getStatus', $this_path . 'BasicSettingsController@getStatus');// 获取商机状态
        Route::any('addStatus', $this_path . 'BasicSettingsController@AddStatus');// 添加商机状态
        Route::any('getType', $this_path . 'BasicSettingsController@getType');// 获取商机类型
        Route::any('addType', $this_path . 'BasicSettingsController@AddType');// 添加商机类型
        Route::any('delStatus', $this_path . 'BasicSettingsController@delStatus');// 删除商机状态
        Route::any('delType', $this_path . 'BasicSettingsController@delType');// 删除商机类型
        Route::any('upload', $this_path . 'OpportunityManagementController@upload');// 上传附件
        Route::any('addOpportunity', $this_path . 'OpportunityManagementController@addOpportunity');// 新建商机
        Route::any('getOpportunity', $this_path . 'OpportunityManagementController@getOpportunity');// 获取商机信息
        Route::any('editOpportunity', $this_path . 'OpportunityManagementController@editOpportunity');// 编辑商机
        Route::any('delOpportunity', $this_path . 'BasicSettingsController@delOpportunity');// 删除商机
        Route::any('myOpportunityList', $this_path . 'OpportunityManagementController@myOpportunityList');// 我的商机列表
        Route::any('departmentOpportunityList', $this_path . 'OpportunityManagementController@DepartmentOpportunityList');// 部门商机列表
        Route::any('allOpportunityList', $this_path . 'OpportunityManagementController@allOpportunityList');// 全部商机列表
        Route::any('editOpportunityType', $this_path . 'OpportunityManagementController@editOpportunityType');// 修改商机状态
        Route::any('editOpportunityStatus', $this_path . 'OpportunityManagementController@editOpportunityStatus');// 修改商机类型
        Route::any('getCustomerList', $this_path . 'OpportunityManagementController@getCustomerList');// 获取客户列表
        Route::any('delAnnex', $this_path . 'OpportunityManagementController@delAnnex');// 删除附件
        Route::any('getStatisticsList', $this_path . 'OpportunityManagementController@getStatisticsList');// 商机统计
        Route::any('getDepartmentList', $this_path . 'OpportunityManagementController@getDepartmentList');// 部门列表
        Route::any('opportunityTransfer', $this_path . 'OpportunityOperateController@opportunityTransfer');// 部门列表
        Route::any('getDepartmentMember', '\business\admin\controllers\DepartmentMemberController@getDepartmentMember');//部门员工列表
    });
    /*商机管理*/

    //<editor-fold desc="企业微信视频课程">
    Route::group(['prefix' => $base_route . 'wechatVideoCourses/'], function () {
        $this_path = '\Yunshop\WechatVideoCourses\business\controller\\';

        Route::any('getSet', $this_path . 'set\SetController@index');// 获取设置数据
        Route::any('saveSet', $this_path . 'set\SetController@save');// 保存设置数据

        Route::any('saveCourse', $this_path . 'course\CoursesManageController@save');// 保存课程
        Route::any('getCourseList', $this_path . 'course\CoursesManageController@index');// 获取课程列表
        Route::any('getCourseDetail', $this_path . 'course\CoursesManageController@detail');// 获取课程详情
        Route::any('deleteCourse', $this_path . 'course\CoursesManageController@delete');// 删除课程

        Route::any('saveCourseCategory', $this_path . 'course\CourseCategoryManageController@save');// 保存课程分类
        Route::any('getCourseCategoryList', $this_path . 'course\CourseCategoryManageController@index');// 获取课程分类列表
        Route::any('getCourseCategoryDetail', $this_path . 'course\CourseCategoryManageController@detail');// 获取课程分类详情
        Route::any('deleteCourseCategory', $this_path . 'course\CourseCategoryManageController@delete');// 删除课程分类

        Route::any('saveRole', $this_path . 'role\RoleManageController@save');// 保存角色
        Route::any('getRoleList', $this_path . 'role\RoleManageController@index');// 获取角色列表
        Route::any('getRoleDetail', $this_path . 'role\RoleManageController@detail');// 获取角色详情
        Route::any('deleteRole', $this_path . 'role\RoleManageController@delete');// 删除角色
        Route::any('exportRole', $this_path . 'role\RoleManageController@exportRole');// 导出角色

        Route::any('saveTopic', $this_path . 'questionBank\QuestionBankManageController@save');// 保存题目
        Route::any('getTopicList', $this_path . 'questionBank\QuestionBankManageController@index');// 获取题目列表
        Route::any('getTopicDetail', $this_path . 'questionBank\QuestionBankManageController@detail');// 获取题目详情
        Route::any('deleteTopic', $this_path . 'questionBank\QuestionBankManageController@delete');// 删除题目

        Route::any('lockRecord', $this_path . 'record\LockRecordController@index');//锁定关系统计
        Route::any('rewardRecord', $this_path . 'record\RewardRecordController@index');// 奖励红包统计
        Route::any('exportRewardRecord', $this_path . 'record\RewardRecordController@export');// 导出红包统计
        Route::any('watchRecord', $this_path . 'record\WatchRecordController@index');// 观看统计
        Route::any('exportWatchRecord', $this_path . 'record\WatchRecordController@export');// 导出观看统计
        Route::any('statistics', $this_path . 'record\StatisticsController@index');// 统计
        Route::any('topData', $this_path . 'record\StatisticsController@topData');// 统计(头部数据)

        //公共接口无需权限
        Route::any('getMemberList', $this_path . 'role\RoleManageController@getMemberList');// 获取会员列表
        Route::any('getGroupChatList', $this_path . 'role\RoleManageController@getGroupChatList');// 获取群组列表
    });
    //</editor-fold>

});


Route::group(['namespace' => 'admin\controllers'], function () {


    Route::group(['prefix' => BASE_ROUTE . '/{uniacid}/admin/', 'middleware' => ['business:']], function () {
        Route::any('manageBusiness', 'BusinessController@manageBusiness'); //管理企业
        Route::any('businessList', 'BusinessController@businessList'); //企业管理列表
        Route::any('addBussiness', 'BusinessController@addBussiness'); //创建企业
        Route::any('getBusinessCommonData', 'BusinessController@getBusinessCommonData'); //获取公共参数
        Route::any('pluginEnabled', 'BusinessController@pluginEnabled'); //获取企业开启的插件参数

        Route::any('managerList', 'ManagerController@managerList'); //管理员列表
        Route::any('changeBusinessOwner', 'ManagerController@changeBusinessOwner'); //企业转让
        Route::any('addManager', 'ManagerController@addManager'); //添加管理员
        Route::any('deleteManager', 'ManagerController@deleteManager'); //删除管理员

        Route::any('cleanMemberCache', 'SettingController@cleanMemberCache'); //清除会员缓存
        Route::any('uploadPic', 'UploadController@uploadPic'); //上传附件
        Route::any('downloadFile', 'UploadController@downloadFile'); //下载文件
        Route::any('getAddressList', 'AddressController@getAddressList'); //地址列表
    });


    Route::get(BASE_ROUTE . '/{uniacid}/admin/message/test', 'NoticeController@ttt'); //消息通知测试
    Route::group(['prefix' => BASE_ROUTE . '/{uniacid}/admin/', 'middleware' => ['business:', 'businessLogin:']], function () {
        Route::any('getBusinessSurvey', 'SettingController@getBusinessSurvey'); //企业概况
        Route::any('cleanBusinessCache', 'SettingController@cleanBusinessCache'); //清除企业缓存
        Route::any('businessQyWxSetting', 'SettingController@businessQyWxSetting'); //企业微信设置
        Route::any('editBussiness', 'SettingController@editBussiness'); //编辑企业


        //企业消息通知
        Route::get('message/unread', 'NoticeController@unread'); //未读
        Route::get('message/read', 'NoticeController@read'); //已读
        Route::get('message/waitHandle', 'NoticeController@waitHandle'); //待处理列表
        Route::get('message/allAppModule', 'NoticeController@allAppModule'); //消息应用模块
        Route::any('message/markRead', 'NoticeController@markRead'); //操作已读
        Route::any('message/batchMarkRead', 'NoticeController@batchMarkRead'); //操作全部已读
        Route::any('message/alreadyHandle', 'NoticeController@alreadyHandle'); //已处理
        Route::any('message/laterHandle', 'NoticeController@laterHandle'); //加入待处理


        Route::any('getDepatmemtList', 'DepartmentController@getDepatmemtList'); //获取部门列表
        Route::any('createDepartment', 'DepartmentController@createDepartment'); //创建部门
        Route::any('updateDepartment', 'DepartmentController@updateDepartment'); //编辑部门
        Route::any('deleteDepartment', 'DepartmentController@deleteDepartment'); //删除部门
        Route::any('refreshDepartmentList', 'DepartmentController@refreshDepartmentList'); //从企业微信同步部门列表到本地
        Route::any('pushDepartment', 'DepartmentController@pushDepartment'); //推送部门列表到企业微信

        Route::any('getStaffList', 'StaffController@getStaffList'); //根据部门id获取员工列表
        Route::any('refreshStaffList', 'StaffController@refreshStaffList'); //从企业微信同步员工列表
//        Route::any($base_route . 'pushStaff', 'StaffController@pushStaff'); //推送员工到企业微信
        Route::any('setDepartmentLeader', 'StaffController@setDepartmentLeader'); //设置部门领导
        Route::any('createStaff', 'StaffController@createStaff'); //创建员工
        Route::any('updateStaff', 'StaffController@updateStaff'); //编辑员工
        Route::any('deleteStaff', 'StaffController@deleteStaff'); //禁用员工
        Route::any('businessGetMemberByMobile', 'StaffController@businessGetMemberByMobile'); //根据手机号精确查找会员
        Route::any('searchStaff', 'StaffController@searchStaff'); //查找企业员工

        Route::any('getAuthList', 'AuthController@getAuthList'); //获取权限列表
        Route::any('setAuth', 'AuthController@setAuth'); //设置权限

        Route::any('getApplicationList', 'ApplicationController@getApplicationList'); //应用中心

        Route::any('getArea', 'AreaController@index'); //获取地址
        Route::any('streetSet', 'AreaController@openStreet'); //街道设置
        Route::any('intArea', 'AreaController@init'); //初始化地址

    });
});


Route::group(['namespace' => 'frontend\controllers'], function () {
    Route::any('business/{uniacid}/frontend/qyWxCallback', 'WxCallbackController@qyWxCallback'); //企业微信通知
    Route::any('/{uniacid}/frontend/qyWxCallback', 'WxCallbackController@qyWxCallback'); //企业微信通知
});


//企业微信客服，接收事件服务器回调地址
Route::any('business/{uniacid}/plugin/YunChat/wechatKfCallback', '\Yunshop\YunChat\manage\WechatKfCallbackController@wechatKfCallback');

//芸签短链
Route::group(['prefix' => 'sign/'], function () {
    Route::any('s', '\Yunshop\YunSign\frontend\ShortUrlController@index');
});

