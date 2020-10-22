<?php
namespace laozhang\campus;

use laozhang\campus\utils\ParameterError;
use laozhang\campus\utils\QyApiError;
use laozhang\campus\utils\HttpUtils;
use laozhang\campus\utils\Utils;

abstract class API
{
    public $rspJson = null;
    public $rspRawStr = null;

    //第三方应用
    const GET_PERMANENT_CODE       = '/service/get_permanent_code?suite_access_token=SUITE_ACCESS_TOKEN';
    const GET_AUTH_INFO          = '/service/get_auth_info?suite_access_token=SUITE_ACCESS_TOKEN';

    //部门/成员管理
    const DEPARTMENT_LIST       = '/department/list?access_token=ACCESS_TOKEN';
    const USER_GET       = '/user/get?access_token=ACCESS_TOKEN';
    const USER_LIST       = '/user/list?access_token=ACCESS_TOKEN';
    const USER_SIMPLELIST       = '/user/simplelist?access_token=ACCESS_TOKEN';

    //机构管理
    const CORP_TITLE_LIST       = '/corp/title/list?access_token=ACCESS_TOKEN';
    const CORP_INFO       = '/corp/info?access_token=ACCESS_TOKEN';
    const CORP_ADMIN_LIST       = '/corp/admin/list?access_token=ACCESS_TOKEN';
    const CORP_LIST       = '/corp/office/corplist?access_token=ACCESS_TOKEN';

    //通用组织/成员管理
    const COMMON_DEPARTMENT_GET       = '/common/department/get?access_token=ACCESS_TOKEN';
    const COMMON_DEPARTMENT_LIST       = '/common/department/list?access_token=ACCESS_TOKEN';
    const COMMON_USER_SIMPLELIST       = '/common/user/simplelist?access_token=ACCESS_TOKEN';
    const COMMON_TEACHER_GET_GROUP       = '/common/teacher/get_group?access_token=ACCESS_TOKEN';
    const COMMON_USER_GET_BY_USER_NUMBER       = '/common/user/get_by_user_number?access_token=ACCESS_TOKEN';
    const COMMON_DEPARTMENT_LISTUSER       = '/common/department/listuser?access_token=ACCESS_TOKEN';
    const COMMON_DEPARTMENT_SEARCHUSER       = '/common/department/searchuser?access_token=ACCESS_TOKEN';

    //家校沟通--部门管理
    const SCHOOL_DEPARTMENT_LIST       = '/school/department/list?access_token=ACCESS_TOKEN';
    const SCHOOL_DEPARTMENT_LIST_BY_TAG       = '/school/department/list_by_tag?access_token=ACCESS_TOKEN';
    //家校沟通--成员管理
    const SCHOOL_USER_LIST       = '/school/user/list?access_token=ACCESS_TOKEN';
    const SCHOOL_USER_GET       = '/school/user/get?access_token=ACCESS_TOKEN';
    //家校沟通--关系链管理
    const USER_CLASS_GET       = '/user/class/get?access_token=ACCESS_TOKEN';
    const CLASS_LIST       = '/class/list?access_token=ACCESS_TOKEN';
    const TEACHER_CLASS_LIST       = '/teacher/class/list?access_token=ACCESS_TOKEN';
    const CLASS_TEACHER_LIST       = '/class/teacher/list?access_token=ACCESS_TOKEN';

    protected function GetAccessToken() { }
    protected function RefreshAccessToken() { }

    protected function GetSuiteAccessToken() { }
    protected function RefreshSuiteAccessToken() { }

    protected function GetProviderAccessToken() { }
    protected function RefreshProviderAccessToken() { }

    protected function _HttpCall($url, $method, $args)
    {
        if ('POST' == $method) { 
            $url = HttpUtils::MakeUrl($url);
            $this->_HttpPostParseToJson($url, $args);
            $this->_CheckErrCode();
        } else if ('GET' == $method) { 
            if (count($args) > 0) { 
                foreach ($args as $key => $value) {
                    if ($value == null) continue;
                    if (strpos($url, '?')) {
                        $url .= ('&'.$key.'='.$value);
                    } else { 
                        $url .= ('?'.$key.'='.$value);
                    }
                }
            }
            $url = HttpUtils::MakeUrl($url);
            $this->_HttpGetParseToJson($url);
            $this->_CheckErrCode();
        } else { 
            throw new QyApiError('wrong method');
        }
    }

    protected function _HttpGetParseToJson($url, $refreshTokenWhenExpired=true)
    {
        $retryCnt = 0;
        $this->rspJson = null;
        $this->rspRawStr = null;

        while ($retryCnt < 2) {
            $tokenType = null;
            $realUrl = $url;

            if (strpos($url, "SUITE_ACCESS_TOKEN")) {
                $token = $this->GetSuiteAccessToken();
                $realUrl = str_replace("SUITE_ACCESS_TOKEN", $token, $url);
                $tokenType = "SUITE_ACCESS_TOKEN";
            } else if (strpos($url, "PROVIDER_ACCESS_TOKEN")) {
                $token = $this->GetProviderAccessToken();
                $realUrl = str_replace("PROVIDER_ACCESS_TOKEN", $token, $url);
                $tokenType = "PROVIDER_ACCESS_TOKEN";
            } else if (strpos($url, "ACCESS_TOKEN")) {
                $token = $this->GetAccessToken();
                $realUrl = str_replace("ACCESS_TOKEN", $token, $url);
                $tokenType = "ACCESS_TOKEN";
            } else { 
                $tokenType = "NO_TOKEN";
            }

            $this->rspRawStr = HttpUtils::httpGet($realUrl);

            if ( ! Utils::notEmptyStr($this->rspRawStr)) throw new QyApiError("empty response"); 
            //
            $this->rspJson = json_decode($this->rspRawStr, true/*to array*/);
            if (strpos($this->rspRawStr, "errcode") !== false) {
                $errCode = Utils::arrayGet($this->rspJson, "errcode");
                if ($errCode == 40014 || $errCode == 42001 || $errCode == 42007 || $errCode == 42009) { // token expired
                    if ("NO_TOKEN" != $tokenType && true == $refreshTokenWhenExpired) {
                        if ("ACCESS_TOKEN" == $tokenType) { 
                            $this->RefreshAccessToken();
                        } else if ("SUITE_ACCESS_TOKEN" == $tokenType) {
                            $this->RefreshSuiteAccessToken();
                        } else if ("PROVIDER_ACCESS_TOKEN" == $tokenType) {
                            $this->RefreshProviderAccessToken();
                        } 
                        $retryCnt += 1;
                        continue;
                    }
                }
            }
            return $this->rspRawStr;
        }
    }

    protected function _HttpPostParseToJson($url, $args, $refreshTokenWhenExpired=true, $isPostFile=false)
    {
        $postData = $args;
        if (!$isPostFile) {
            if (!is_string($args)) {
                $postData = HttpUtils::Array2Json($args);
            }
        }
        $this->rspJson = null; $this->rspRawStr = null;

        $retryCnt = 0;
        while ($retryCnt < 2) {
            $tokenType = null;
            $realUrl = $url;

            if (strpos($url, "SUITE_ACCESS_TOKEN")) {
                $token = $this->GetSuiteAccessToken();
                $realUrl = str_replace("SUITE_ACCESS_TOKEN", $token, $url);
                $tokenType = "SUITE_ACCESS_TOKEN";
            } else if (strpos($url, "PROVIDER_ACCESS_TOKEN")) {
                $token = $this->GetProviderAccessToken();
                $realUrl = str_replace("PROVIDER_ACCESS_TOKEN", $token, $url);
                $tokenType = "PROVIDER_ACCESS_TOKEN";
            } else if (strpos($url, "ACCESS_TOKEN")) {
                $token = $this->GetAccessToken();
                $realUrl = str_replace("ACCESS_TOKEN", $token, $url);
                $tokenType = "ACCESS_TOKEN";
            } else { 
                $tokenType = "NO_TOKEN";
            }


            $this->rspRawStr = HttpUtils::httpPost($realUrl, $postData);

            if ( ! Utils::notEmptyStr($this->rspRawStr)) throw new QyApiError("empty response"); 

            $json = json_decode($this->rspRawStr, true/*to array*/);
            $this->rspJson = $json;

            $errCode = Utils::arrayGet($this->rspJson, "errcode");
            if ($errCode == 40014 || $errCode == 42001 || $errCode == 42007 || $errCode == 42009) { // token expired
                if ("NO_TOKEN" != $tokenType && true == $refreshTokenWhenExpired) {
                    if ("ACCESS_TOKEN" == $tokenType) { 
                        $this->RefreshAccessToken();
                    } else if ("SUITE_ACCESS_TOKEN" == $tokenType) {
                        $this->RefreshSuiteAccessToken();
                    } else if ("PROVIDER_ACCESS_TOKEN" == $tokenType) { 
                        $this->RefreshProviderAccessToken();
                    }
                    $retryCnt += 1;
                    continue;
                }
            }

            return $json;
        }
    } 


    protected function _CheckErrCode()
    {
        $rsp = $this->rspJson;
        $raw = $this->rspRawStr;
        if (is_null($rsp))
            return;

        if (!is_array($rsp))
            throw new ParameterError("invalid type " . gettype($rsp));
        if (!array_key_exists("errcode", $rsp)) {
            return;
        }
        $errCode = $rsp["errcode"];
        if (!is_int($errCode))
            throw new QyApiError(
                "invalid errcode type " . gettype($errCode) . ":" . $raw);
        if ($errCode != 0)
            throw new QyApiError("response error:" . $raw);
    }
}
