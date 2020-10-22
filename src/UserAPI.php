<?php
namespace laozhang\campus;


class UserAPI
{
    public $baseUrl = 'https://sso.qq.com/open';
    private $appid = null;
    private $secret = null;
    protected $redirect_uri = "https://luohudck.tencentschool.cn/";
    protected $accessToken = null;

    /**
     * @brief __construct : 构造函数，
     */
    public function __construct($appid=null, $secret=null)
    {
        Utils::checkNotEmptyStr($appid, "appid");
        Utils::checkNotEmptyStr($secret, "secret");

        $this->appid = $appid;
        $this->secret = $secret;
    }

    public function GetAccessToken($code)
    {
        $redirect_uri = 'https://luohudck.tencentschool.cn/';
        $url = $this->baseUrl.'/access_token';
        $param = array(
            'appid'=>$this->appid,
            'secret'=>$this->secret,
            'code'=>$code,
            'redirect_uri'=>$this->redirect_uri,
            'grant_type'=>'authorization_code'
        );
        $url .= '?'.http_build_query($param);
        $tokenInfo = $this->httpGet($url);
        return $tokenInfo;
    }

    public function GetUserInfo($token)
    {
        $url = $this->baseUrl.'/userinfo';
        $param = array(
            'access_token'=>$token
        );
        $url .= '?'.http_build_query($param);
        $userInfo = $this->httpGet($url);
        return $userInfo;
    }

    public function GetDeparts($token)
    {
        $url = $this->baseUrl.'/get_can_see_departments';
        $param = array(
            'access_token'=>$token
        );
        $url .= '?'.http_build_query($param);
        $departsInfo = $this->httpGet($url);
        return $departsInfo;
    }
     /**
     * http get
     * @param string $strUrl  要访问的连接URL
     * @return string   返回内容
     */
    public function httpGet($strUrl,Array $headers = []){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $strUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        // https协议
        curl_setopt($ch, CURLOPT_SSLVERSION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        $ret = curl_exec($ch);
        $rinfo=curl_getinfo($ch);
        curl_close($ch);
        if (intval($rinfo["http_code"]) == 200) {
            $json_ret = json_decode($ret,true);
            if($json_ret) {
                return $json_ret;
            } else {
                return $ret;
            }
        } else {
            return false;
        }
    }

   
}
