<?php
namespace Lib;

use \Lib\CommonFun;
use \lib\Config;
/**
 * 文件操作类 
 * 用于完成文件及目录相关底层操作
 */
class File
{
    private $edit_limit_dir; // 编辑文件限制目录

    private $path = '';  // 当前待访问的文件或文件夹
    private $is_win = false;

	public function __construct($path = '')
    {
        $this->edit_limit_dir = Config::get('app.edit_limit_dir');
        if( !empty($path) ){
            $this->set_path($path);
        }
        if(strtoupper(substr(PHP_OS,0,3))==='WIN'){
            $this->is_win = true;
        }
    }

    //设置file_path
    public function set_path($path)
    {
        $this->path = $path;
        if( $this->is_win ) $this->path = $this->deal_path($this->path);
        $this->_check_domain($this->path);
    }

	// 保存文件, 如果文件不存在,则创建
	public function file_save($file_key,$file_content = '')
	{
        if( is_file($this->path) && $this->_check_file_key($file_key) ){
            // 首先我们要确定文件存在并且可写。
            if (is_writable($this->path)) {
                if (!$handle = fopen($this->path, 'w')) {
                    CommonFun::respone_json("不能打开文件 $this->path",'',100);
                }

                // 将$somecontent写入到我们打开的文件中。
                if (fwrite($handle, $file_content) === FALSE) {
                    CommonFun::respone_json("不能写入到文件 $this->path",'',101);
                }
                $new_file_key = md5($this->path.time());
                fclose($handle);
                return $new_file_key;
            } else {
                CommonFun::respone_json("文件 $this->path 不可写",'',102);
            }
        }else{
            CommonFun::respone_json("文件 $this->path 不存在, 请先创建文件,然后写入内容",'',103);
        }
	}
	// 获取文件内容
	public function get_file_content()
    {
        if(is_file($this->path)){
            return file_get_contents($this->path);
        }else{
            return null;
        }
    }
    // 获取文件信息
    public function get_file_info()
    {
        $arr = [
            'is_writeable' => is_writeable($this->path),
        ];
        return array_merge(pathinfo($this->path),$arr);
    }
    /**
     * 获取目录列表
     * @return array 目录列表
     */
    public function get_dir()
    {
        if(empty($this->path)){
            $this->path = $this->deal_path($this->edit_limit_dir,false) ;
        }else{
            $this->path = $this->deal_path($this->path,false);
        }

        if( !is_dir( $this->deal_path($this->path) ) ){
            return $this->_format_item($this->path,$this->path,'','folder',false,false);
        }
        $dir_arr = [];
        $file_arr = [];
        $childs = $this->_scandir($this->path);

        foreach($childs as $child){
            if($child !== '.' && $child !== '..') {
                // 处理中文文件夹
                $_tmp_file_path = $this->deal_path($this->path.'/'.$child);
                if( is_dir( $_tmp_file_path) ){
                    $dir_arr[] = $this->_format_item($child,$this->path.'/'.$child,true,'folder');
                }else{
                    $file_arr[] = $this->_format_item($child,$this->path.'/'.$child,false,'file');
                }
            }
        }
        $result = array_merge($dir_arr, $file_arr);

        if( $this->path == $this->deal_path($this->edit_limit_dir) ){
            $result = $this->_format_item($this->path,$this->path,$result,NULL,true,true);
        }

        return $result;
    }
    /**
     * 创建目录
     * @param string 目录名称
     */
    public function create_dir($name)
    {
        $_dir_path = $this->path.'/'.$name;
        if(is_dir($_dir_path)){
            return false;
        }else{
            mkdir($_dir_path);
            return $this->deal_path($_dir_path,false);
        }
    }
    /**
     * 创建文件
     * @param string 文件名称
     */
    public function create_file($name)
    {
        $_file_path = $this->path.'/'.$name;
        if(is_file($_file_path)){
            return false;
        }else{
            $fp=fopen($_file_path,"w+");
            fclose($fp);
            return $this->deal_path($_file_path,false);
        }
    }
    // 删除文件
    public function delete_file()
    {
        if(is_file($this->path)) {
            if(unlink($this->path)) { 
                return true; 
            } else { 
                return false; 
            } 
        } else { 
            return false; 
        } 
    }
    // 删除目录
    function delete_dir($dir)
    {
        $this->_check_domain($dir);
        $dh = opendir($dir);
        while( $file = readdir($dh) ){
            if($file != '.' && $file != '..'){
                $fullpath = $dir.'/'.$file;
                if( !is_dir($fullpath) ){
                    unlink($fullpath);
                } else {
                    $this->delete_dir($fullpath);
                }
            }
        }
        closedir($dh);
        //删除完当前目录中的内容，再删除当前目录
        if( rmdir($dir) ){
            return true;
        }
        return false;
    }

    // 文件或目录重命名
    public function rename($new_name)
    {
        // 判断名称是否合法
        if (!preg_match("#^[\.\w\x{4e00}-\x{9fa5}]+$#u",$new_name)){ 
            CommonFun::respone_json("文件名称不符合规则[$new_name]",'',101);
        }
        // 将名称改为全路径
        $base_path = pathinfo($this->path, PATHINFO_DIRNAME);
        $new_name = $base_path.'/'.$this->deal_path($new_name);
        // 检查文件修改域是否正常
        $this->_check_domain($new_name);

        if( rename( $this->path, $new_name ) ){
            return $this->deal_path($new_name,false);
        }else{
            return false;
        }
    }
    // 移动文件
    public function move_file($new_path)
    {
        $this->_check_domain($new_path);

        if( rename( $this->path, $new_path ) ){
            return $this->deal_path($new_path,false);
        }else{
            return false;
        }    
    }

    // 复制文件
    public function copy_file($to_path)
    {
        $this->_check_domain($to_path);
        $to_path = $this->deal_path($to_path);
        if(copy($this->path, $to_path)){
            return true;
        }else{
            return false;
        }
    }
    // 复制文件夹
    public function copy_dir($to_path)
    {
        $this->_check_domain($to_path);
        $to_path = $this->deal_path($to_path);
        if($this->_copydir($this->path, $to_path)){
            return true;
        }else{
            return false;
        }
    }
    // 复制文件夹
    function _copydir($sourceDir, $destDir)
    {
        if( !is_dir($sourceDir) ){
            return false;
        }
         
        if( !is_dir($destDir) ){
            //如果没有目标地址，则尝试创建，失败则返回false;
            if( !mkdir($destDir) ){
                return false;
            }
        }
         
        $dir = opendir($sourceDir);
        if(!$dir){
            return false;
        }
         
        while(false !== ( $file=readdir($dir) )){
            if($file != '.' && $file != '..'){
                if( is_dir($sourceDir.'/'.$file) ){
                    if( !copydir($sourceDir.'/'.$file, $destDir.'/'.$file) ){
                        return false;
                    }
                }else{
                    //如果不是目录而是一个文件
                    if( !copy($sourceDir.'/'.$file, $destDir.'/'.$file) ){
                        return false;
                    }
                }
            }
        }
        closedir($dir);
        return true;
    }
    // 上传文件
    // path 相对文件存放路径,  file 待上传文件
    public function upload_file($path,$file)
    {
        $path = $this->deal_path($path);
        if( !is_dir($path) ){
            //如果没有目标地址，则尝试创建，失败则返回false;
            if( !mkdir($path) ){
                return false;
            }
        }
        $file['name'] = $this->deal_path($file['name']);
        if(move_uploaded_file($file["tmp_name"],"{$path}/" . $file["name"])){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 如果是win系统, 处理路径
     * @param $path
     * @param bool $G2U 国标码转为UTF-8
     * @return bool|mixed|string
     */
    public function deal_path($path, $utf2gbk = true)
    {
        if(!$this->is_win) return $path;
        $path = str_replace("\\",'/',$path);
        if($utf2gbk){
            $path = iconv('UTF-8','GBK',$path);
        }else{
            $path = iconv('GBK','UTF-8',$path);
        }

        return $path;
    }

	//验证当前文件是否可被写入
	// key 为文件在读取时在前端的标识
	private function _check_file_key($file_key)
	{
	    if($file_key == $this->get_file_key()){
            return true;
        }else{
	        CommonFun::respone_json('文件已被修改,请重新打开编辑','',100);
        }

	}
	// 得到文件
	public function get_file_key()
    {
        return md5( $this->path.filemtime($this->path) );
    }



	// 检测文件访问域是否正常
	private function _check_domain($path)
	{
        if(empty($path) || strpos($path, $this->deal_path($this->edit_limit_dir)) === 0 &&
            strpos($path, $this->deal_path(BASE_DIR)) !== 0
        ){
            return true;
        }else{
            CommonFun::respone_json('可编辑文件域错误 : ['.$path.']','',199);
        }
	}

    // 格式化节点信息
	private function _format_item($text, $id, $children,  $type, $disabled=false, $opened=false)
    {
        return [
            'text' => $text,
            'children' => $children,
            'id' => $id,
            'type' => $type,
            'icon' => $this->_get_file_img($this->path.'/'.$text,$type), //$icon,
            'state' => [
                'disabled' => $disabled,
                'opened' => $opened
            ]
        ];
    }
    // 设置显示文件
    private function _get_file_img($path,$icon)
    {
        $suffix = pathinfo($path, PATHINFO_EXTENSION);
        if(empty($suffix)){
            return $icon;
        }

        $img = $icon;
        $able_icons = 'php js css ico jpg png jpeg gif bmp text txt md log htaccess htm html xml xsl rb pdf as c iso cf cpp cs sql xls xlsx h crt pem cer ppt pptx doc docx zip gz tar rar fla';

        if( strpos($able_icons, $suffix) !== false ){
            $img = 'file-'.$suffix;
        }

        return $img; // 否则返回一个自己的图片
    }
    /**
     * 扫描路径, 得到结果数组
     * @param $path 扫描路径目录
     * @return array
     */
    private function _scandir($path)
    {
        $dirs = scandir($this->deal_path($path));
        if($this->is_win){
            foreach($dirs as &$dir_item){
                $dir_item = $this->deal_path($dir_item,false);
            }
        }
        return $dirs;
    }
}