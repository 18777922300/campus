<?php
namespace laozhang\campus;

use laozhang\campus\utils\HttpUtils;
use laozhang\campus\utils\Utils;

// include_once(__DIR__."/../../utils/Utils.class.php");
// include_once(__DIR__."/../../utils/HttpUtils.class.php");
// include_once(__DIR__."/../../utils/error.inc.php");

class ServiceCorpAPI extends CorpAPI 
{
    private $suite_id = null; // string 
    private $suite_secret = null; // string 
    private $suite_ticket = null; // string 

    private $authCorpId = null; // string 
    private $permanentCode = null; // string 

    private $suiteAccessToken = null; // string

    public function __construct(
        $suite_id=null, 
        $suite_secret=null, 
        $suite_ticket=null, 
        $authCorpId=null, 
        $permanentCode=null)
    {
        $this->suite_id = $suite_id;
        $this->suite_secret = $suite_secret;
        $this->suite_ticket = $suite_ticket;

        // 调用 CorpAPI 的function， 需要设置这两个参数
        $this->authCorpId = $authCorpId;
        $this->permanentCode = $permanentCode;
    }

    /**
     * @brief RefreshAccessToken : override CorpAPI的函数，使用三方服务商的get_corp_token
     *
     * @return : string
     */
    protected function RefreshAccessToken()
    {
        Utils::checkNotEmptyStr($this->authCorpId, "auth_corpid");
        Utils::checkNotEmptyStr($this->permanentCode, "permanent_code");
        $args = array(
            "auth_corpid" => $this->authCorpId, 
            "permanent_code" => $this->permanentCode
        ); 
        $url = HttpUtils::MakeUrl("/service/get_corp_token?suite_access_token=SUITE_ACCESS_TOKEN");
        $this->_HttpPostParseToJson($url, $args, false);
        $this->_CheckErrCode();

        $this->accessToken = $this->rspJson["access_token"];
    }

    /**
     * @brief GetSuiteAccessToken : 获取第三方应用凭证
     *
     *   获取第三方应用凭证
     *
     * @note 调用者不用关心，本类会自动获取、更新
     *
     * @return : string
     */
    protected function GetSuiteAccessToken()
    { 
        if ( ! Utils::notEmptyStr($this->suiteAccessToken)) { 
            $this->RefreshSuiteAccessToken();
        } 
        return $this->suiteAccessToken;
    }
    protected function RefreshSuiteAccessToken()
    {
        Utils::checkNotEmptyStr($this->suite_id, "suite_id");
        Utils::checkNotEmptyStr($this->suite_secret, "suite_secret");
        Utils::checkNotEmptyStr($this->suite_ticket, "suite_ticket");
        $args = array(
            "suite_id" => $this->suite_id, 
            "suite_secret" => $this->suite_secret,
            "suite_ticket" => $this->suite_ticket,
        ); 
        $url = HttpUtils::MakeUrl("/service/get_suite_token");
        $this->_HttpPostParseToJson($url, $args, false);
        $this->_CheckErrCode();

        $this->suiteAccessToken = $this->rspJson["suite_access_token"];
    }

    // ---------------------- 第三方开放接口 ----------------------------------
    //
    //

    /**
     * @brief GetPermanentCode : 获取企业永久授权码
     *
     * @link 获取企业永久授权码
     *
     * @param $temp_auth_code : string 临时授权码
     *
     * @return : GetPermanentCodeRsp
     */
    public function GetPermanentCode($temp_auth_code)
    { 
        $args = array("auth_code" => $temp_auth_code); 
        self::_HttpCall(self::GET_PERMANENT_CODE, 'POST', $args);
        return $this->rspJson;
    }

    /**
     * @brief GetAuthInfo : 获取企业授权信息
     *
     * @link 获取企业授权信息
     *
     * @param $auth_corpid : string
     * @param $permanent_code : 永久授权码
     *
     * @return : GetAuthInfoRsp
     */
    public function GetAuthInfo($auth_corpid, $permanent_code)
    { 
        Utils::checkNotEmptyStr($auth_corpid, "auth_corpid");
        Utils::checkNotEmptyStr($permanent_code, "permanent_code");
        $args = array(
            "auth_corpid" => $auth_corpid,
            "permanent_code" => $permanent_code
        ); 
        self::_HttpCall(self::GET_AUTH_INFO, 'POST', $args);
        return $this->rspJson;
    }

}
