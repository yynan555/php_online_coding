<?php
namespace App\Controller;

use App\Core\CommonFun;
use App\Core\UserAuth;
use App\Core\File;
use App\Core\Jstree;
use App\Core\Config;
use App\Core\Log;
use App\Core\Message;

class Controller
{
    // 代码执行之前方法
    public function beforeFun($action_name,$args)
    {
        // 日志记录
        if($action_name === 'login') unset($args['password']);
        if( $action_name === 'set_password' ){
            $args = [];
        }
        Log::write($action_name."\t".json_encode($args,JSON_UNESCAPED_UNICODE));

        // 身份验证
        if(!UserAuth::checkAuth()){
            if($action_name !== 'login') CommonFun::view('login');
        }
        
        // 如果变量中存在path 需要判断其是否在合法文件域中
        if(!empty($args)) {
            foreach($args as $key => $act_param){
                if(strpos($key,'path') !== false){
                    if(!UserAuth::checkDomain($act_param)) CommonFun::respone_json("访问路径域错误",'',1000);
                }
            }
        }
    }
    public function login($username='', $password = '')
    {
        if(UserAuth::login($username,$password)){
            $send_usernames = [$username];

            // 获取到超级管理员, 为其发送登录信息
            foreach(Config::get('app.users') as $user){
                if(isset($user['super_user']) && $user['super_user'] === true){
                    $send_usernames[] = $user['name'];
                }
            }
            // 准备内容 发送消息
            $content =  $username.' 与 ['.date('Y-m-d H:i:s',time()).'] 成功登陆。<br/><br/>来自 '.CommonFun::url().'<br/>地址: '.UserAuth::session('address');

            Message::sendMessage($send_usernames, '账号登录提醒', $content);
            CommonFun::go_operation();
        }
    }
    public function logout()
    {
        UserAuth::logout();
        CommonFun::view('login',['error_msg'=>'退出成功']);
    }
    // 设置密码
    public function set_password()
    {
        $old_password = CommonFun::params('old_password','');
        $new_password = CommonFun::params('new_password','');
        $new_password2 = CommonFun::params('new_password2','');

        if(empty($old_password) ||empty($new_password) ||empty($new_password2) ) CommonFun::view('user_set_password');

        if($new_password !== $new_password2)  CommonFun::view('user_set_password',['error_msg'=>'两次输入密码不正确']);
        if(UserAuth::setPassword($old_password, $new_password)){
            //内容填写
            $content = UserAuth::session('username').' 与 ['.date('Y-m-d H:i:s',time()).'] 修改密码。 新密码为:'.$new_password.' 请妥善保管。<br/><br/>来自: '.CommonFun::url().'<br/>地址: '.UserAuth::session('address');

            Message::sendMessage(UserAuth::session('username'),'账号密码修改提醒', $content);

            UserAuth::logout();
            CommonFun::view('login',['error_msg'=>'修改密码成功']);
        }
    }
    // 访问首页
    public function index()
    {
        CommonFun::view('index');
    }
    // 获取文件
    public function get_file($file_path)
    {
        $file_info = File::getFileInfo($file_path);
        if( isset( $file_info['extension']) && stripos(Config::get('app.unable_suffix'), $file_info['extension']) !== false ){
            echo '该文件不可编辑';
            exit;
        }

        $file_content = File::getFileContent($file_path);

        if( isset( $file_info['extension']) && stripos(Config::get('app.img_suffix'), $file_info['extension']) !== false ){
            //输出图片
            $base64_img = CommonFun::base64_encode_image($file_path);

            echo '<img style="max-width: 100%;" src="'.$base64_img.'"/>';
            exit;
        }
        $file_key = File::getFileKey($file_path);

        CommonFun::view('file_content',compact('file_path','file_content','file_key','file_info'));
    }
    // 保存文件
    public function save_file($file_path,$file_key,$file_content='')
    {
        if( File::checkFileKey($file_path,$file_key) && File::setFileContent($file_path, $file_content) ){
            $file_key = File::getFileKey($file_path);
            CommonFun::respone_json('success',compact('file_key'));
        }else{
            CommonFun::respone_json('写入文件 ['.$file_path.'] 失败,未知错误','',200);
        }
    }
    // 获取目录列表
    public function dir_list($dir_path = '')
    {
        if(empty($dir_path)){
            $dir_path = UserAuth::getLimitDir();
        }
        if( empty($dir_path) ){
            CommonFun::arr2Json(Jstree::format_item('该用户没有可访问文件',null,false,'folder'));
        }
        CommonFun::arr2Json(Jstree::getDir($dir_path));
    }

    // 文件目录相关操作

    // 删除一个节点
    public function delete_node($path, $type)
    {
        if($type == 'file'){
            if(File::deleteFile($path)){
                CommonFun::respone_json("删除文件[$path]成功");
            }else{
                CommonFun::respone_json("删除文件[$path]失败",'',300);
            }
        }else if($type == 'folder'){
            if(File::deleteDir( $path )){
                CommonFun::respone_json("删除目录[$path]成功");
            }else{
                CommonFun::respone_json("删除目录[$path]失败",'',301);
            }
        }else{
            CommonFun::respone_json("节点类型未知,无法删除",'',301);
        }
    }
    // 新建一个节点
    public function create_node($type, $path, $name)
    {
        if($type == 'folder'){
            if( $new_path = File::createDir($path.'/'.$name) ){
                CommonFun::respone_json("创建目录[$name]成功",['new_path'=>$new_path]);
            }else{
                CommonFun::respone_json("创建目录[$name]失败",'',200);
            }
        }else if($type == 'file'){
            if( $new_path = File::createFile($path.'/'.$name) ){
                CommonFun::respone_json("创建文件[$name]成功",['new_path'=>$new_path]);
            }else{
                CommonFun::respone_json("创建文件[$name]失败");
            }
        }else{
            CommonFun::respone_json("类型 [$type] 无法进行创建",'',200);
        }
    }
    public function rename_node($path, $name)
    {
        if($new_path = File::renameFileOrDir($path,$name)){
            CommonFun::respone_json("重命名[$name]成功",['new_path'=>$new_path]);
        }else{
            CommonFun::respone_json("重命名[$name]失败",'',200);
        }
    }
    public function move_node($old_path, $move_path)
    {
        if($new_path = File::moveFileOrDir($old_path, $move_path)){
            CommonFun::respone_json("移动到[$move_path]成功",['new_path'=>$move_path]);
        }else{
            CommonFun::respone_json("移动到[$move_path]失败",'',200);
        }
    }
    public function copy_node($from_path , $to_path , $type)
    {
        if($type == 'folder'){
            if( $new_path = File::copyDir($from_path, $to_path) ){
                CommonFun::respone_json("复制目录到[$to_path]成功");
            }else{
                CommonFun::respone_json("复制目录到[$to_path]失败",'',200);
            }
        }else if($type == 'file'){
            if( $new_path = File::copyFile($from_path, $to_path) ){
                CommonFun::respone_json("复制文件到[$to_path]成功");
            }else{
                CommonFun::respone_json("复制文件到[$to_path]失败");
            }
        }else{
            CommonFun::respone_json("类型 [$type] 无法复制",'',200);
        }
    }

    // 上传文件展示页面
    public function show_upload_file($dir_path)
    {
        CommonFun::view('upload_file',compact('dir_path'));
    }
    // 上传文章
    public function upload_file($path)
    {
        if(!count($_FILES)){
            CommonFun::respone_json("服务器未接收到文件或文件过大",'',600);
        }
        $fail_file = [];
        foreach($_FILES as $key => $file){
            if( strpos($key,'/') !== false ){
                $new_path = $path.'/'.pathinfo($key,PATHINFO_DIRNAME);
            }else{
                $new_path = $path;
            }
            if(!File::uploadFile($new_path, $file)){
                array_push($fail_file,$key);
            }
        }
        CommonFun::respone_json("上传文件已完成",['error_list'=>$fail_file]);

    }
    // 下载文件或文件夹
    public function download_file_or_dir($path)
    {
        File::downloadFileOrDir($path);
    }
}