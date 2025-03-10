<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;

class Layout extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function test_push()
    {
        $data = DB::name('gift_logs')->where('is_play', 2)->limit(1)->field(['id', 'uid', 'giftId', 'giftName', 'user_id', 'fromUid', 'giftNum'])->find();
        if (!$data) {
            $this->ApiReturn(0, '暂无可播放播报');
        }

        $info['uid']       = $data['uid'];
        $info['user_name'] = DB::name('users')->where('id', $data['user_id'])->value('nickname');
        $info['from_name'] = DB::name('users')->where('id', $data['fromUid'])->value('nickname');
        $info['num']       = $data['giftNum'];
        $info['gift_name'] = $data['giftName'];
        $img               = DB::name('gifts')->where('id', $data['giftId'])->value('img');
        $info['img']       = $this->auth->setFilePath($img);
        $arr['type']       = 'gift';
        $arr['data']       = $info;
        $arr_json          = json_encode($arr, JSON_UNESCAPED_UNICODE);
        // $res1=$this->android_push_test('','',$arr);
        $res2        = $this->ios_push_test('', $arr);
        $res['info'] = $arr;
        $res['push'] = $res2;
        $this->ApiReturn(1, '', $res);
    }

    //安卓推送
    public function android_push_test($title = '', $note = '', $info)
    {
        // $device_token='AvMrTf44S3992wKqlN6SIBXrBi4RUyo9eG2dNmBz2EE2';
        include_once VENDOR_PATH . "umeng/Umeng.php";
        $appkey       = $this->getConfig('android_um_appkey');
        $masterSecret = $this->getConfig('android_um_masterSecret');
        // $appkey='5d77386a0cafb2c8b4000393';
        // $masterSecret='qidd4uj3ljxxjjcpv0zsusjcegg2l4ch';
        $umeng = new \Umeng($appkey, $masterSecret);
        $title = $title ?: '青春派语音';
        $note  = $note ?: '礼物播报';
        //单播
        // $res=$umeng->sendAndroidUnicast($title,$note,$info,$device_token);
        // 广播
        $res = $umeng->sendAndroidBroadcast($title, $note, $info);
        $arr = json_decode($res, true);
        return $arr;
        if ($arr['ret'] == 'SUCCESS') {
            return true;
        } else {
            return false;
        }
    }

    //ios推送
    public function ios_push_test($title = null, $info)
    {
        $device_token = '8b3b642c761da12c3d4c67f022dae09555d6b12a9853b42f76f15779882c92c0';
        include_once VENDOR_PATH . "umeng/Umeng.php";
        //测试
        $appkey       = '5d773aab570df3489b00056a';
        $masterSecret = 'ieu2tjabndsdo8xxd3z68dbp7s2fh38i';
        //正式`
        // $appkey='5d36cc453fc19512f300005e';
        // $masterSecret='jazoa7epdzzyopdwbcrdqgomgy5b1uca';
        $umeng = new \Umeng($appkey, $masterSecret);
        $title = $title ?: '青春派语音';
        // 单播
        // $res=$umeng->sendIOSUnicast($title,$info,$device_token,'false');
        // 广播
        $res = $umeng->sendIOSBroadcast($title, $info, 'false');
        $arr = json_decode($res, true);
        return $arr;
        if ($arr['ret'] == 'SUCCESS') {
            return true;
        } else {
            return false;
        }
    }

/*********************************************************************************************************/

    //推送礼物
    public function push_gifts()
    {
        $data = DB::name('gift_logs')->where('is_play', 2)->limit(1)->order("id asc")->field(['id', 'uid', 'giftId', 'giftName', 'user_id', 'fromUid', 'giftNum'])->find();
        if (!$data) {
            $this->ApiReturn(0, '暂无可播放播报');
        }

        $info['uid']       = $data['uid'];
        $info['user_name'] = DB::name('users')->where('id', $data['user_id'])->value('nickname');
        $info['from_name'] = DB::name('users')->where('id', $data['fromUid'])->value('nickname');
        $info['num']       = $data['giftNum'];
        $info['gift_name'] = $data['giftName'];
        $img               = DB::name('gifts')->where('id', $data['giftId'])->value('img');
        $info['img']       = $this->auth->setFilePath($img);
        $arr['type']       = 'gift';
        $arr['data']       = $info;
        $arr_json          = json_encode($arr, JSON_UNESCAPED_UNICODE);
        $res1              = $this->android_push('', '', $arr);
        $res2              = $this->ios_push('', $arr);
        if ($res1 || $res2) {
            DB::name('gift_logs')->where('id', $data['id'])->update(['is_play' => 1]);
            $this->ApiReturn(1, '推送成功' . $data['id']);
        } else {
            $this->ApiReturn(0, '推送失败');
        }
    }
    //安卓推送
    public function android_push($title = '', $note = '', $info)
    {
        include_once VENDOR_PATH . "umeng/Umeng.php";
        $appkey       = $this->getConfig('android_um_appkey');
        $masterSecret = $this->getConfig('android_um_masterSecret');
        $umeng        = new \Umeng($appkey, $masterSecret);
        $title        = $title ?: '青春派语音';
        $note         = $note ?: '礼物播报';
        //单播
        // $res=$umeng->sendAndroidUnicast($title,$note,$info,$device_token);
        // 广播
        $res = $umeng->sendAndroidBroadcast($title, $note, $info);
        $arr = json_decode($res, true);
        if ($arr['ret'] == 'SUCCESS') {
            return true;
        } else {
            return false;
        }
    }
    //ios推送
    public function ios_push($title = null, $info)
    {
        include_once VENDOR_PATH . "umeng/Umeng.php";
        $appkey       = $this->getConfig('ios_um_appkey');
        $masterSecret = $this->getConfig('ios_um_masterSecret');
        $umeng        = new \Umeng($appkey, $masterSecret);
        $title        = $title ?: '青春派语音';
        // 单播
        // $res=$umeng->sendIOSUnicast($title,$info,$device_token,'true');
        // 广播
        $res = $umeng->sendIOSBroadcast($title, $info, 'true');
        $arr = json_decode($res, true);
        if ($arr['ret'] == 'SUCCESS') {
            return true;
        } else {
            return false;
        }
    }

    //推送开奖
    public function push_award()
    {
        /*
        $data = DB::name('award_log')->where('is_play',0)->limit(1)->order("id asc")->field(['id','user_id','num','type','wares_id','box_type'])->find();
        if(!$data)  $this->ApiReturn(0,'暂无可播放播报');
        $wares_id = $data['wares_id'];
        if ($data['type'] == 2){
        $name = Db::name('gifts')->where(array('id'=>$wares_id))->value('name');
        }else{
        $name = Db::name('wares')->where(array('id'=>$wares_id))->value('name');
        }
        $award_box_info     = Db::name('award_box')->where(array('wares_id'=>$wares_id,'type'=>$data['type'],'box_type'=>$data['box_type']))->value('img');
        $img                = $this->auth->setFilePath($award_box_info);
        // $info['user_name']  = DB::name('users')->where('id',$data['user_id'])->value('nickname');
        $info['user_name']  = getUserField($data['user_id'],'nickname');
        $info['num']        = $data['num'];
        $info['gift_name']  = $name;
        $info['img']        = $img;
        $info['boxclass']   = $data['box_type'] == 1 ? "普通宝箱" : "守护宝箱";
        $info['from_name']  = '';
        $arr['type']        = 'award';
        $arr['data']        = $info;

        $arr_json=json_encode($arr,JSON_UNESCAPED_UNICODE);
        $res1 = $this->android_push('','奖品播报',$arr);
        $res2 = $this->ios_push('',$arr);
        // $res1=$this->android_push_test('','',$arr);
        // $res2 = $this->ios_push_test('',$arr);
        $this->ApiReturn(1,'',$res1);
        if($res1 || $res2 ){
        DB::name('award_log')->where('id',$data['id'])->update(['is_play'=>1]);
        $this->ApiReturn(1,'推送成功'.$data['id']);
        }else{
        $this->ApiReturn(0,'推送失败');
        }*/
        $field = "GROUP_CONCAT(id) as ids,GROUP_CONCAT(wares_id) as wares_ids,GROUP_CONCAT(type) as types,GROUP_CONCAT(num) as nums,addtime,user_id,sum(num) as num,box_type";
        $data  = DB::name('award_log')->where('is_play', 0)->limit(1)->order("addtime desc")->field($field)->group('addtime,user_id')->find();

        if (!$data) {
            $this->ApiReturn(0, '暂无可播放播报');
        }

        $info['user_name'] = getUserField($data['user_id'], 'nickname');

        $ids       = $data['ids'];
        $type      = $data['types'];
        $wares_ids = $data['wares_ids'];
        $nums      = $data['nums'];

        $typeArr      = explode(',', $type);
        $wares_idsArr = explode(',', $wares_ids);
        $numsArr      = explode(',', $nums);

        $str = '';
        foreach ($typeArr as $key => $value) {
            if ($value == 2) {
                $name = Db::name('gifts')->where(array('id' => $wares_idsArr[$key]))->value('name');
            } else {
                $name = Db::name('wares')->where(array('id' => $wares_idsArr[$key]))->value('name');
            }

            $str .= $name . '×' . $numsArr[$key] . ' ';
        }
        $info['gift_name'] = $str;
        $info['boxclass']  = $data['box_type'] == 1 ? "普通宝箱" : "守护宝箱";
        $arr['type']       = 'award';
        $arr['data']       = $info;
        $info['from_name'] = '';
        $info['num']       = 1;

        $arr_json = json_encode($arr, JSON_UNESCAPED_UNICODE);
        $res1     = $this->android_push('', '奖品播报', $arr);
        $res2     = $this->ios_push('', $arr);
        //print_r($arr);die;
        if ($res1 || $res2) {
            $wap['id'] = array('in', $ids);
            DB::name('award_log')->where($wap)->update(['is_play' => 1]);
            $this->ApiReturn(1, '推送成功' . $ids);
        } else {
            $this->ApiReturn(0, '推送失败' . $ids);
        }
    }

}
