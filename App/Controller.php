<?php
namespace App;

use \Lib\CommonFun;
use \Core\UserAuth;

class Controller
{
    private $file_obj;

    public function __construct()
    {
        $this->file_obj = new \Lib\File();
    }
    // 获取文件
    public function get_file($file_path){
        $this->file_obj->set_path($file_path);
        $file_info = $this->file_obj->get_file_info();
        if( isset( $file_info['extension']) && stripos(\lib\Config::get('app.unable_suffix'), $file_info['extension']) !== false ){
            echo '该文件不可编辑';
            exit;
        }else if( isset( $file_info['extension']) && stripos(\lib\Config::get('app.img_suffix'), $file_info['extension']) !== false ){
            //输出图片
            header('Content-type: image/'.$file_info['extension']);
            echo file_get_contents($this->file_obj->deal_path($file_path));
            exit;
        }
        $file_key = $this->file_obj->get_file_key();
        $file_content = $this->file_obj->get_file_content();

        CommonFun::view('file_content',compact('file_path','file_content','file_key','file_info'));
    }
    // 保存文件
    public function save_file($file_path,$file_key,$file_content=''){
        $this->file_obj->set_path($file_path);

        if( $new_file_key = $this->file_obj->file_save($file_key, $file_content) ){
            $file_key = $new_file_key;
            CommonFun::respone_json('success',compact('file_key'));
        }
    }
    // 获取目录列表
    public function dir_list($dir_name = '')
    {
        $this->file_obj->set_path($dir_name);
        CommonFun::arr2Json($this->file_obj->get_dir());
    }

    // 文件目录相关操作

    // 删除一个节点
    public function delete_node($path, $type)
    {
        $this->file_obj->set_path($path);
        $path = $this->file_obj->deal_path($path);
        if($type == 'file'){
            if($this->file_obj->delete_file()){
                CommonFun::respone_json("删除文件[$path]成功");
            }else{
                CommonFun::respone_json("删除文件[$path]失败",'',300);
            }
        }else if($type == 'folder'){
            if($this->file_obj->delete_dir( $path )){
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
        $this->file_obj->set_path($path);

        if($type == 'folder'){
            if( $new_path = $this->file_obj->create_dir($name) ){
                CommonFun::respone_json("创建目录[$name]成功",['new_path'=>$new_path]);
            }else{
                CommonFun::respone_json("创建目录[$name]失败",'',200);
            }
        }else if($type == 'file'){
            if( $new_path = $this->file_obj->create_file($name) ){
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
        $this->file_obj->set_path($path);
        if($new_path = $this->file_obj->rename($name)){
            CommonFun::respone_json("重命名[$name]成功",['new_path'=>$new_path]);
        }else{
            CommonFun::respone_json("重命名[$name]失败",'',200);
        }
    }
    public function move_node($old_path, $move_path)
    {
        $this->file_obj->set_path($old_path);
        if($new_path = $this->file_obj->move_file($move_path)){
            CommonFun::respone_json("移动到[$move_path]成功",['new_path'=>$move_path]);
        }else{
            CommonFun::respone_json("移动到[$move_path]失败",'',200);
        }
    }
    public function copy_node($from_path , $to_path , $type)
    {
        $this->file_obj->set_path($from_path);
        if($type == 'folder'){
            if( $new_path = $this->file_obj->copy_dir($to_path) ){
                CommonFun::respone_json("复制目录到[$to_path]成功");
            }else{
                CommonFun::respone_json("复制目录到[$to_path]失败",'',200);
            }
        }else if($type == 'file'){
            if( $new_path = $this->file_obj->copy_file($to_path) ){
                CommonFun::respone_json("复制文件到[$to_path]成功");
            }else{
                CommonFun::respone_json("复制文件到[$to_path]失败");
            }
        }else{
            CommonFun::respone_json("类型 [$type] 无法复制",'',200);
        }
    }

    // 登录及退出登录
    public function login($password='')
    {
        UserAuth::login($password);
    }
    public function logout()
    {
        UserAuth::logout();
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
            CommonFun::respone_json("服务器未接收到文件",'',600);
        }
        $this->file_obj->set_path($path);
        $fail_file = [];
        foreach($_FILES as $key => $file){
            if( strpos($key,'/') !== false ){
                $new_path = $path.'/'.pathinfo($key,PATHINFO_DIRNAME);
            }else{
                $new_path = $path;
            }
            if(!$this->file_obj->upload_file($new_path, $file)){
                array_push($fail_file,$key);
            }
        }
        CommonFun::respone_json("上传文件已完成",['error_list'=>$fail_file]);

    }
}