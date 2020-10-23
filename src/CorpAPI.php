<?php
namespace jaycezheng\campus;

use jaycezheng\campus\utils\ParameterError;
use jaycezheng\campus\utils\QyApiError;
use jaycezheng\campus\utils\SysError;
use jaycezheng\campus\utils\HttpUtils;
use jaycezheng\campus\utils\Utils;

class CorpAPI extends API
{
    private $corpId = null;
    private $secret = null;
    protected $accessToken = null;

    /**
     * @brief __construct : 构造函数，
     * @note 企业进行自定义开发调用, 请传参 corpid + secret, 不用关心accesstoken，本类会自动获取并刷新
     */
    public function __construct($corpId=null, $secret=null)
    {
        Utils::checkNotEmptyStr($corpId, "corpid");
        Utils::checkNotEmptyStr($secret, "secret");

        $this->corpId = $corpId;
        $this->secret = $secret;
    }


    // ------------------------- access token ---------------------------------
    /**
     * @brief GetAccessToken : 获取 accesstoken，不用主动调用
     *
     * @return : string accessToken
     */
    protected function GetAccessToken()
    {
        if ( ! Utils::notEmptyStr($this->accessToken)) { 
            $this->RefreshAccessToken();
        } 
        return $this->accessToken;
    }

    protected function RefreshAccessToken()
    {
        if (!Utils::notEmptyStr($this->corpId) || !Utils::notEmptyStr($this->secret))
            throw new ParameterError("invalid corpid or secret");

        $url = HttpUtils::MakeUrl(
            "/gettoken?corpid={$this->corpId}&corpsecret={$this->secret}");
        $this->_HttpGetParseToJson($url, false);
        $this->_CheckErrCode();

        $this->accessToken = $this->rspJson["access_token"];
    }
    /**
     * @brief DepartmentList : 获取部门列表
     *
     *
     * @return : department
     */
    public function DepartmentList($id = '')
    {
        self::_HttpCall(self::DEPARTMENT_LIST, 'GET', array('id'=>$id)); 
        return $this->rspJson;
    }
    /**
     * @brief UserGet : 获取教职工详情信息
     * userid:教职工的用户id
     * fetch_admin_type:是否拉取管理员类型：1-拉取, 0-不拉取
     */
    public function UserGet($userid, $fetch_admin_type = 1)
    {
        self::_HttpCall(self::USER_GET, 'GET', array('userid'=>$userid,'fetch_admin_type'=>$fetch_admin_type)); 
        return $this->rspJson;
    }
    /**
     * @brief UserList : 获取部门教职工列表详情
     * department_id:获取的部门id
     * page_index:  分页号， 从1开始
     * page_size:每页数量
     * fetch_child:1/0：是否递归获取子部门下面的教职工
     * fetch_admin_type:是否拉取管理员类型：1-拉取, 0-不拉取
     *
     */
    public function UserList($department_id,$page_index,$page_size,$fetch_child = 1,$fetch_admin_type = 1)
    {
        self::_HttpCall(self::USER_LIST, 'GET', array('department_id'=>$department_id,'page_index'=>$page_index,'page_size'=>$page_size,'fetch_child'=>$fetch_child,'fetch_admin_type'=>$fetch_admin_type)); 
        return $this->rspJson;
    }
    /**
     * @brief UserSimpleList : 获取部门教职工列表详情
     * department_id:获取的部门id
     * page_index:  分页号， 从1开始
     * page_size:每页数量
     * fetch_child:1/0：是否递归获取子部门下面的教职工
     *
     */
    public function UserSimpleList($department_id,$page_index,$page_size,$fetch_child = 1)
    {
        self::_HttpCall(self::USER_SIMPLELIST, 'GET', array('department_id'=>$department_id,'page_index'=>$page_index,'page_size'=>$page_size,'fetch_child'=>$fetch_child)); 
        return $this->rspJson;
    }

    /**
     * @brief CorpInfo : 获取企业信息
     *
     *
     * @return : CorpInfo
     */
    public function CorpInfo()
    {
        self::_HttpCall(self::CORP_INFO, 'GET', array()); 
        return $this->rspJson;
    }

    /**
     * @brief CorpTitleList : 获取职位列表
     *
     * @return : CorpTitleList
     */
    public function CorpTitleList()
    {
        self::_HttpCall(self::CORP_TITLE_LIST, 'GET', array()); 
        return $this->rspJson;
    }

    /**
     * @brief CorpAdminList : 企业管理员列表
     *
     * @return : CorpAdminList
     */
    public function CorpAdminList()
    {
        self::_HttpCall(self::CORP_ADMIN_LIST, 'GET', array()); 
        return $this->rspJson;
    }

    /**
     * @brief CorpList : 获取上级单位下属的单位或学校列表
     * type:  获取类型: 2:上级单位, 4: 学校
     *
     * @return : CorpList
     */
    public function CorpList($type = 4)
    {
        self::_HttpCall(self::CORP_LIST, 'GET', array('type'=>$type)); 
        return $this->rspJson;
    }


    /**
     * @brief DepartmentGet : 获取组织架构详情
     * department_id:部门ID
     *
     * @return : departmentInfo
     */
    public function DepartmentGet($department_id)
    {
        self::_HttpCall(self::COMMON_DEPARTMENT_GET, 'GET', array('department_id'=>$department_id)); 
        return $this->rspJson;
    }
    /**
     * @brief CommonDepartmentList : 批量获取组织架构详情
     * department:部门ID数组
     *
     * @return : CommonDepartmentList
     */
    public function CommonDepartmentList($department)
    {
        self::_HttpCall(self::COMMON_DEPARTMENT_LIST, 'POST', array('department'=>$department)); 
        return $this->rspJson;
    }

    /**
     * @brief CommonUserSimpleList : 获取用户基础信息
     * userids:用户id列表
     *
     * @return : CommonUserSimpleList
     */
    public function CommonUserSimpleList($userids)
    {
        self::_HttpCall(self::COMMON_USER_SIMPLELIST, 'POST', array('userids'=>$userids)); 
        return $this->rspJson;
    }
    /**
     * @brief TeacherGetGroup : 获取学校教职工身份
     * userid:用户id
     *
     * @return : TeacherGetGroup
     */
    public function TeacherGetGroup($userid)
    {
        self::_HttpCall(self::COMMON_TEACHER_GET_GROUP, 'GET', array('userid'=>$userid)); 
        return $this->rspJson;
    }
    /**
     * @brief UserGetByUserNumber : 根据教工号获取用户信息
     * user_number:用户id
     * role_id:用户id
     *
     * @return : userlist
     */
    public function UserGetByUserNumber($user_number,$role_id)
    {
        self::_HttpCall(self::COMMON_USER_GET_BY_USER_NUMBER, 'GET', array('user_number'=>$user_number,'role_id'=>$role_id)); 
        return $this->rspJson;
    }
    /**
     * @brief DepartmentListUser : 获取组织架构成员列表
     * department_id:部门ID
     * department_type:0 学校, 1 学生, 2 教职工, 3 家长, 4 校友, 5 退休老师, 6 临时架构组
     * fetch_child:是否递归获取部门成员，默认否
     * page_size:每一页数据条数，默认10
     * page_index:获取第几页的数据，默认1
     * @return : userlist
     */
    public function DepartmentListUser($department_id,$department_type,$page_size=10,$page_index=1,$fetch_child=1)
    {
        $args = [
            'department_id'=>$department_id,
            'department_type'=>$department_type,
            'page_size'=>$page_size,
            'page_index'=>$page_index,
            'fetch_child'=>$fetch_child
        ];
        self::_HttpCall(self::COMMON_DEPARTMENT_LISTUSER, 'GET', $args); 
        return $this->rspJson;
    }
    /**
     * @brief DepartmentSearchUser : 根据组织架构获取用户信息列表（支持搜索）
     * department_id:部门ID
     * page_size:每一页数据条数
     * page_index:获取第几页的数据
     * key:关键字
     * @return : userlist
     */
    public function DepartmentSearchUser($department_id,$page_size,$page_index,$key='')
    {
        $args = [
            'department_id'=>$department_id,
            'page_size'=>$page_size,
            'page_index'=>$page_index,
            'key'=>$key
        ];
        self::_HttpCall(self::COMMON_DEPARTMENT_SEARCHUSER, 'GET', $args); 
        return $this->rspJson;
    }


    // ---------------------- 家校沟通接口 ----------------------------------
    //
    /**
     * @brief SchoolDepartmentList : 获取部门列表
     * id:部门ID
     * @return : departments
     */
    public function SchoolDepartmentList($id = '')
    {
        self::_HttpCall(self::SCHOOL_DEPARTMENT_LIST, 'GET', array('id'=>$id)); 
        return $this->rspJson;
    }
    /**
     * @brief SchoolDepartmentList : 获取组织架构年级、班级等列表
     * tag:架构属性，1 学生, 2 学部, 3 学院, 4 年级, 5 班级, 6 系别, 7 专业, 8 学年, 9 学期, 10 校区
     * page_size:每一页数据条数，默认10
     * page_index:获取第几页的数据，默认1
     * @return : departments
     */
    public function SchoolDepartmentListByTag($tag, $page_size=10, $page_index=1)
    {
        $args = [
            'tag'=>$tag,
            'page_size'=>$page_size,
            'page_index'=>$page_index
        ];
        self::_HttpCall(self::SCHOOL_DEPARTMENT_LIST_BY_TAG, 'GET', $args); 
        return $this->rspJson;
    }
    /**
     * @brief SchoolUserList : 获取学生列表
     * department_id:获取的部门id
     * page_size:每一页数据条数
     * page_index:获取第几页的数据
     * @return : departments
     */
    public function SchoolUserList($department_id, $page_size, $page_index, $fetch_child = 1)
    {
        $args = [
            'department_id'=>$department_id,
            'page_size'=>$page_size,
            'page_index'=>$page_index,
            'fetch_child'=>$fetch_child
        ];
        self::_HttpCall(self::SCHOOL_USER_LIST, 'GET', $args); 
        return $this->rspJson;
    }
    /**
     * @brief SchoolUserGet : 获取学生或家长详情
     * userid:家校通讯录的userid，家长或者学生的userid。不区分大小写，长度为1~64个字节
     * @return : userdetail
     */
    public function SchoolUserGet($userid)
    {
        self::_HttpCall(self::SCHOOL_USER_GET, 'GET', array('userid'=>$userid)); 
        return $this->rspJson;
    }
    /**
     * @brief UserClassGet : 获取学生在教师下有权限的班级列表
     * student_userid:家校通讯录的userid，学生的userid。不区分大小写，长度为1~64个字节
     * teacher_userid:家校通讯录的userid，教师的userid。不区分大小写，长度为1~64个字节
     * @return : userdetail
     */
    public function UserClassGet($student_userid, $teacher_userid)
    {
        $args = [
            'student_userid'=>$student_userid,
            'teacher_userid'=>$teacher_userid
        ];
        self::_HttpCall(self::USER_CLASS_GET, 'GET', $args); 
        return $this->rspJson;
    }
    /**
     * @brief ClassList : 获取班级列表
     * department_type:班级类型: 0-全部, 1-行政班, 8-课程班
     * page_size:每一页数据条数
     * page_index:获取第几页的数据
     * @return : classlist
     */
    public function ClassList($department_type, $page_size, $page_index)
    {
        $args = [
            'department_type'=>$department_type,
            'page_size'=>$page_size,
            'page_index'=>$page_index
        ];
        self::_HttpCall(self::CLASS_LIST, 'GET', $args); 
        return $this->rspJson;
    }
    /**
     * @brief TeacherClassList : 通过教师获取任课班级
     * teacher_userid:每一页数据条数
     * department_type:班级类型: 0-全部, 1-行政班, 8-课程班
     * @return : classlist
     */
    public function TeacherClassList($teacher_userid, $department_type=0)
    {
        $args = [
            'teacher_userid'=>$teacher_userid,
            'department_type'=>$department_type
        ];
        self::_HttpCall(self::TEACHER_CLASS_LIST, 'GET', $args); 
        return $this->rspJson;
    }
    /**
     * @brief ClassTeacherList : 通过班级获取教师
     * department_id:班级的ID
     * @return : TeacherList
     */
    public function ClassTeacherList($department_id)
    {
        $args = [
            'department_id'=>$department_id
        ];
        self::_HttpCall(self::CLASS_TEACHER_LIST, 'GET', $args); 
        return $this->rspJson;
    }

   
}
