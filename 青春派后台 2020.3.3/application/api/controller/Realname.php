<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\admin\model\usersmanage\Users;
use app\admin\model\officialsmanage\Onepage;
use think\Db;
use vendor\AlibabaCloud\Client\AlibabaCloud;
use vendor\AlibabaCloud\Client\Exception\ClientException;
use vendor\AlibabaCloud\Client\Exception\ServerException;

class Realname extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function identity_attest(Request $user_info)
    {
        header('content-type:text/html;charset = utf-8');
        $user_info = $user_info->all();
        if($user_info['IDcard'] == null){
            return json_encode(['code'=>3,'message'=>"请填写身份证号"]);
        }
        if($user_info['username'] == null){
            return json_encode(['code'=>4,'message'=>"请填写真实姓名"]);
        }
        $repeat = DB::table('b_identity_attests')->where('IDcard',$user_info['IDcard'])->value('IDcard');
        if(!empty($repeat)){
            return json_encode(['code'=>5,'message'=>"身份证号".$user_info['IDcard']."已经认证，请不要重复认证"]);
        }

        include_once('../app/Packages/alipay/AopSdk.php');
        include_once('../app/Packages/alipay/aop/AopClient.php');
        include_once('../app/Packages/alipay/aop/request/AlipayUserCertifyOpenInitializeRequest.php');
        $aop = new \AopClient ();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = '2018121162530291';
        $aop->rsaPrivateKey = 'MIIEpQIBAAKCAQEA2JwEtLrGrHiu4VhB35z1TuCZONJZlZV6MFIVaAwJ7lK7S1psvF5hDhPgobS+iGo+bcFygSViA0Bk3VOzHkGRndeP6l7P/OnS+I2zLTF65vnHWw2IbJusD4QI4ULCKUkOcLJ9QVIgy4cyWxCkfHYYKtIGxDc82u+gakpHloHoN95X7YNOFKr8er3ZrSAxbHrbEHHbGlNWQvKBuLvLdddlKNnAk8srx01djgPazqj8MV5jtjK0kXtN3clSiBUOTg7ndz52esXFsHnx8cws/k0f4WA6HQtji3hwF3vuF0xdhhr6Tz/Kdtj0zMOaQdSH7KDjUTC/B5glicY7ICEXnd3nZwIDAQABAoIBAQDWIW4Zh+atS2R+SnRcbVqnzipKXM/Iqwsux4Z35CbRHaIfvNVvZdGGTCotUXNlgboTYEAk4WLCGh9cGMkiaOld42JW68GqoTA5HEN7ssVvno7wOTGbRE1UzU7F+OveSmVnDVv7fggDi7RBB/PDQi9j7VpZEM4tFOQ9Dw8z/1okFuyzpqMdJm4CmpMWyvpblfN6qKtXco7pIuKYxsaFv/mJKZ4F0Dl75Xbdkhudqiy8HawXqDPq1EccA2VPxFMv3CIvvrop0BxmqY1h/vaejx+zt4xbtyZfO4MtW7LiHHCZMlBT8Wx0nEFNnabXLk26KmF21BPhn/Ly09/YtxskMe2pAoGBAO33xsB3epH1vN3GW7kFhSZ3e3cHt9IX1pH4umoFt+cMYGVMUUxCbVy+i35CPTA8nyJSVbXoPeA1r3fHwCRYIF/B5xK1YwPDgIV0oOwhCphV81er8mGqmxVbFac8gvEY35DzmIQ+LiSTYPQBLy1eujvjy02NXM58Ck81+hTPwS1zAoGBAOkF65BkiBrrEwkFyirrs8WgLFbp/chQzGlYGgIx8A4Ia4ej1dlSoAjkE63JVTiBC1PAQQO5BGTZCYanHX/gXh4kmf+/MmAoJpuklTlLl5gqNjVlirBmF0K/WAPjC0JWc9n22533WwPWhnOLdBR7anZw3QuyvttEyi30yStjjOE9AoGBAKy9YQwTbukHHetK8wgS5r2um/QiqSAb58kcaY1Oy1kv1cWSqa0WxzY8pxumz631Q3rxk4AxrsDTl6T2XhbD1fRM2ATvoIl54BCAQdRGg99i/PrWOWMGKf9NmCFGvrJu4NLi0QKl9G7egNF/DpT7n2qcB9cq14SX7A/l2bxDtzHbAoGBAKEvlCuULsWX99nU5GxeEENSsXkJcJ/grBTueUctbeT3FBKGVMV4LvUXdzlOPCHSuZgcM5y0nYJ40usOrAwpklD9dnz+r4TuIQ6mgQZZ8Km0AT1cWNv+MnbcTrCZm88uaALMjEuvr4hvGx/PxeLpH9J/7ZdK9FROOCAj5AecW/XtAoGAYarHccaIltyIkpPtIi4vE2sHHwVkwdwZATiwfBtwrgM4e0qh3dAn4v2/eN7fnLI1+e5Q5ptc460pcKaKpg6/UoJIY8S0yz7tOxfpbCpu0YUh3O2bETpDe8NFOMca9v/dhw6lrBlnizbglfKP5Y3XO3BRueqRIapHy5/UO3zh58E=';


        $aop->alipayrsaPublicKey='MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAiQGQ/TGURHQxC/Ie3sFK3omZ3hXYdouOX5d/vaSaZVQki2BfAfsLk2G2l1xikmbFcjX+IFNnU95TPkAJzy//vuiPRFnpMD094d9fR7nk4sYgwx2WskDHKA0wR2XvjQY4k1mNk6Wgfgn2X+u6jFA03PbA825TzyutJ+enriKsSjXbxp0M2Ia8oggJcKyeR/HfuI6oJLHdNIq8M543COeCiKyuNXXpDvmEKGKE1JBLuvtSLzp6XFQl1MQC+q65f32mMQwvJqz67t0X6SwLj+gxGb7tfta/pgUawv6pk6X4EPrtlP6clNw2+XupoqY4IZREKrL8iT2ihP/fpWJRFjV28QIDAQAB';
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset='UTF-8';
        $aop->format='json';
        $request = new \AlipayUserCertifyOpenInitializeRequest ();
        $outer_order_no = md5(time() . mt_rand(1,1000000));
        $data = array(
            'outer_order_no'=>$outer_order_no,
            'biz_code'=>'SMART_FACE',
            'identity_param'=>array('identity_type'=>'CERT_INFO',
                'cert_type'=>'IDENTITY_CARD',
                'cert_name'=>$user_info['username'],
                'cert_no'=>$user_info['IDcard']
            ),
            'merchant_config'=>array(
                'return_url'=>"http://59.110.169.251/api/identity_attest_callback"
            ),
            'face_contrast_picture'=>null,
        );
        $data = json_encode($data);
        $request->setBizContent($data);
        $result = $aop->execute ($request);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;

        if(!empty($resultCode)&&$resultCode == 10000){
            require_once('../app/Packages/alipay/aop/request/AlipayUserCertifyOpenCertifyRequest.php');
            $request = new \AlipayUserCertifyOpenCertifyRequest ();
            $certify_id = $result->$responseNode->certify_id;

            DB::table('b_identity_attests')->insert(['certify_id'=>$certify_id,'username'=>$user_info['username'],'IDcard'=>$user_info['IDcard'],'user_id'=>$user_info['user_id']]);
            $data = array('$certify_id'=>$certify_id);
            $data = json_encode($data);
            $request->setBizContent($data);
            $result = $aop->pageExecute($request);
            return $result;
            // $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
            // $resultCode = $result->$responseNode->code;
            // if(!empty($resultCode)&&$resultCode == 10000){
            // echo "成功";
            // } else {
            // echo "失败";
            // }
        } else {
            return json_encode(['code'=>2,'message'=>"请求失败"]);
        }
    }

    public function identity_attest_callback(Request $user_info){
        // $data = $data = $user_info->all();
        // file_put_contents('./1.txt',$data);
        return 1;
    }

    public function identity_attest_query(Request $user_info){
        header('content-type:text/html;charset = utf-8');
        $user_info = $user_info->all();
        $certify_id = DB::table('b_identity_attests')->where('user_id',$user_info['user_id'])->value('certify_id');
        $data = array('$certify_id'=>$certify_id);
        $data = json_encode($data);
        require_once('../app/Packages/alipay/AopSdk.php');
        require_once('../app/Packages/alipay/aop/AopClient.php');
        require_once('../app/Packages/alipay/aop/request/AlipayUserCertifyOpenQueryRequest.php');
        $aop = new \AopClient ();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = '2018121162530291';
        $aop->rsaPrivateKey = 'MIIEpQIBAAKCAQEA2JwEtLrGrHiu4VhB35z1TuCZONJZlZV6MFIVaAwJ7lK7S1psvF5hDhPgobS+iGo+bcFygSViA0Bk3VOzHkGRndeP6l7P/OnS+I2zLTF65vnHWw2IbJusD4QI4ULCKUkOcLJ9QVIgy4cyWxCkfHYYKtIGxDc82u+gakpHloHoN95X7YNOFKr8er3ZrSAxbHrbEHHbGlNWQvKBuLvLdddlKNnAk8srx01djgPazqj8MV5jtjK0kXtN3clSiBUOTg7ndz52esXFsHnx8cws/k0f4WA6HQtji3hwF3vuF0xdhhr6Tz/Kdtj0zMOaQdSH7KDjUTC/B5glicY7ICEXnd3nZwIDAQABAoIBAQDWIW4Zh+atS2R+SnRcbVqnzipKXM/Iqwsux4Z35CbRHaIfvNVvZdGGTCotUXNlgboTYEAk4WLCGh9cGMkiaOld42JW68GqoTA5HEN7ssVvno7wOTGbRE1UzU7F+OveSmVnDVv7fggDi7RBB/PDQi9j7VpZEM4tFOQ9Dw8z/1okFuyzpqMdJm4CmpMWyvpblfN6qKtXco7pIuKYxsaFv/mJKZ4F0Dl75Xbdkhudqiy8HawXqDPq1EccA2VPxFMv3CIvvrop0BxmqY1h/vaejx+zt4xbtyZfO4MtW7LiHHCZMlBT8Wx0nEFNnabXLk26KmF21BPhn/Ly09/YtxskMe2pAoGBAO33xsB3epH1vN3GW7kFhSZ3e3cHt9IX1pH4umoFt+cMYGVMUUxCbVy+i35CPTA8nyJSVbXoPeA1r3fHwCRYIF/B5xK1YwPDgIV0oOwhCphV81er8mGqmxVbFac8gvEY35DzmIQ+LiSTYPQBLy1eujvjy02NXM58Ck81+hTPwS1zAoGBAOkF65BkiBrrEwkFyirrs8WgLFbp/chQzGlYGgIx8A4Ia4ej1dlSoAjkE63JVTiBC1PAQQO5BGTZCYanHX/gXh4kmf+/MmAoJpuklTlLl5gqNjVlirBmF0K/WAPjC0JWc9n22533WwPWhnOLdBR7anZw3QuyvttEyi30yStjjOE9AoGBAKy9YQwTbukHHetK8wgS5r2um/QiqSAb58kcaY1Oy1kv1cWSqa0WxzY8pxumz631Q3rxk4AxrsDTl6T2XhbD1fRM2ATvoIl54BCAQdRGg99i/PrWOWMGKf9NmCFGvrJu4NLi0QKl9G7egNF/DpT7n2qcB9cq14SX7A/l2bxDtzHbAoGBAKEvlCuULsWX99nU5GxeEENSsXkJcJ/grBTueUctbeT3FBKGVMV4LvUXdzlOPCHSuZgcM5y0nYJ40usOrAwpklD9dnz+r4TuIQ6mgQZZ8Km0AT1cWNv+MnbcTrCZm88uaALMjEuvr4hvGx/PxeLpH9J/7ZdK9FROOCAj5AecW/XtAoGAYarHccaIltyIkpPtIi4vE2sHHwVkwdwZATiwfBtwrgM4e0qh3dAn4v2/eN7fnLI1+e5Q5ptc460pcKaKpg6/UoJIY8S0yz7tOxfpbCpu0YUh3O2bETpDe8NFOMca9v/dhw6lrBlnizbglfKP5Y3XO3BRueqRIapHy5/UO3zh58E=';
        $aop->alipayrsaPublicKey='MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAiQGQ/TGURHQxC/Ie3sFK3omZ3hXYdouOX5d/vaSaZVQki2BfAfsLk2G2l1xikmbFcjX+IFNnU95TPkAJzy//vuiPRFnpMD094d9fR7nk4sYgwx2WskDHKA0wR2XvjQY4k1mNk6Wgfgn2X+u6jFA03PbA825TzyutJ+enriKsSjXbxp0M2Ia8oggJcKyeR/HfuI6oJLHdNIq8M543COeCiKyuNXXpDvmEKGKE1JBLuvtSLzp6XFQl1MQC+q65f32mMQwvJqz67t0X6SwLj+gxGb7tfta/pgUawv6pk6X4EPrtlP6clNw2+XupoqY4IZREKrL8iT2ihP/fpWJRFjV28QIDAQAB';
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset='UTF-8';
        $aop->format='json';
        $request = new \AlipayUserCertifyOpenQueryRequest ();
        $request->setBizContent($data);
        $result = $aop->execute ( $request);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        dd($result);
        if(!empty($resultCode)&&$resultCode == 10000){
            echo "成功";
        } else {
            echo "失败";
        }
        return 1;
    }

}
