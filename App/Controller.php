<?php
namespace App;

use \Core\CommonFun;
use \Core\UserAuth;
use \Core\File;
use \Core\Jstree;
use \Core\Config;

class Controller
{
    // 访问首页
    public function index()
    {
        CommonFun::view('index');
    }
    // 获取文件
    public function get_file($file_path)
    {
        $file_info = File::getFileInfo($file_path);
        if( isset( $file_info['extension']) && stripos(\Core\Config::get('app.unable_suffix'), $file_info['extension']) !== false ){
            echo '该文件不可编辑';
            exit;
        }

        $file_content = File::getFileContent($file_path);

        if( isset( $file_info['extension']) && stripos(\Core\Config::get('app.img_suffix'), $file_info['extension']) !== false ){
            //输出图片
            header('Content-type: image/'.$file_info['extension']);
            echo $file_content;
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
    public function dir_list($dir_name = '')
    {
        if(empty($dir_name)){
            $dir_name = UserAuth::getLimitDir();
        }
        if( empty($dir_name) ){
            CommonFun::arr2Json(Jstree::format_item('该用户没有可访问文件',null,false,'folder'));
        }
        CommonFun::arr2Json(Jstree::getDir($dir_name));
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
}