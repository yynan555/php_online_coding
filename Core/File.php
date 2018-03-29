<?php
namespace Core;
/**
 * 文件操作类 
 * 用于完成文件及目录相关底层操作
 * 文件使用绝对地址
 */
use \Core\CommonFun;
use \Core\Config;

class File
{
    /**
     * 创建文件
     * @param string 文件名称
     */
    public static function createFile($file_path)
    {
        // 检查文件域是否安全
        if(!self::_checkDomain($file_path)){
            return false;
        }

        $file_path = self::dealPath($file_path, 'u2g');
        if(is_file($file_path)){
            return false;
        }else{
            $fp=fopen($file_path,"w+");
            fclose($fp);
            return self::dealPath($file_path,'g2u');
        }
    }

    // 复制文件
    public static function copyFile($sourceFile, $destFile)
    {
        // 检查文件域是否安全
        if(!self::_checkDomain($sourceFile, $destFile)){
            return false;
        }

        $sourceFile = self::dealPath($sourceFile,'u2g');
        $destFile = self::dealPath($destFile,'u2g');
        if(copy($sourceFile, $destFile)){
            return true;
        }else{
            return false;
        }
    }
	// 保存文件内容
	public static function setFileContent($file_path, $file_content = '')
	{
        // 检查文件域是否安全
        if(!self::_checkDomain($file_path)){
            return false;
        }

        $_file_path = self::dealPath($file_path,'u2g');
        if( is_file($_file_path) ){
            // 首先我们要确定文件存在并且可写。
            if (is_writable($_file_path)) {
                if (!$handle = fopen($_file_path, 'w')) {
                    CommonFun::respone_json("不能打开文件 $file_path",'',100);
                }

                // 将$somecontent写入到我们打开的文件中。
                if (fwrite($handle, $file_content) === FALSE) {
                    CommonFun::respone_json("不能写入到文件 $file_path",'',101);
                }
                fclose($handle);
                return true;
            } else {
                CommonFun::respone_json("没有写文件 $file_path 权限",'',102);
            }
        }else{
            CommonFun::respone_json("文件 $file_path 不存在, 请先创建文件,然后写入内容",'',103);
        }
	}
	// 获取文件内容
	public static function getFileContent($file_path)
    {
        // 检查文件域是否安全
        if(!self::_checkDomain($file_path)){
            return false;
        }

        $file_path = self::dealPath($file_path,'u2g');
        if(is_file($file_path)){
            return file_get_contents($file_path);
        }else{
            return null;
        }
    }
    // 获取文件信息
    public static function getFileInfo($file_path)
    {
        // 检查文件域是否安全
        if(!self::_checkDomain($file_path)){
            return false;
        }

        $file_path = self::dealPath($file_path,'u2g');
        $arr = [
            'is_writeable' => is_writeable($file_path),
        ];
        return array_merge(pathinfo($file_path),$arr);
    }
    // 删除文件
    public static function deleteFile($file_path)
    {
        // 检查文件域是否安全
        if(!self::_checkDomain($file_path)){
            return false;
        }

        $_file_path = self::dealPath($file_path,'u2g');
        if(is_file($_file_path)) {
            if(unlink($_file_path)) { 
                return true; 
            } else { 
                return false; 
            } 
        } else { 
            CommonFun::respone_json("未找到该文件 $file_path ",'',103);
        } 
    }
    // 上传文件
    // path 相对文件存放路径,  file 待上传文件
    public static function uploadFile($path,$file)
    {
        // 检查文件域是否安全
        if(!self::_checkDomain($path)){
            return false;
        }

        $path = self::dealPath($path, 'u2g');
        $file['name'] = self::dealPath($file['name'], 'u2g');

        if( !is_dir($path) ){
            //如果没有目标地址，则尝试创建，失败则返回false;
            if( !@mkdir($path,0777,true) ){
                return false;
            }
        }

        if(move_uploaded_file($file["tmp_name"],"{$path}/" . $file["name"])){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 创建目录
     * @param string 目录名称
     */
    public static function createDir($dir_path)
    {
        // 检查文件域是否安全
        if(!self::_checkDomain($dir_path)){
            return false;
        }
        
        $dir_path = self::dealPath($dir_path,'u2g');
        if(is_dir($dir_path)){
            return false;
        }else{
            if( !mkdir($dir_path) ){
                return false;
            }
            return self::dealPath($dir_path,'g2u');
        }
    }
    /**
     * 根据文件夹获取文件中内容
     * @param $path 扫描路径目录
     * @return array
     */
    public static function getFileListByDir($path)
    {
        $path = self::dealPath($path);

        // 检查文件域是否安全
        if(!self::_checkDomain($path)){
            return false;
        }
        
        $_path = self::dealPath($path, 'u2g');
        $dirs = scandir($_path);

        $result = [];
        foreach($dirs as $dir_name){
            if($dir_name !== '.' && $dir_name !== '..') {
                $dir_item = [];
                $dir_item['name'] = self::dealPath($dir_name, 'g2u');
                $dir_item['path'] = $path;
                if( is_dir( $_path.'/'.$dir_name ) ){
                    $dir_item['type'] = 'dir';
                }else{
                    $dir_item['type'] = 'file';
                }
                $result[] = $dir_item;
            }
        }

        $result = CommonFun::array_orderby($result, 'type', SORT_ASC , 'name', SORT_ASC);
        return $result;
    }

    // 复制文件夹
    public static function copyDir($sourceDir, $destDir)
    {
        // 检查文件域是否安全
        if(!self::_checkDomain($sourceDir, $destDir)){
            return false;
        }
        
        // 如果是第一次进入,需要处理路径
        static $first_flag;
        if($first_flag === null){
            $sourceDir = self::dealPath($sourceDir, 'u2g');
            $destDir = self::dealPath($destDir, 'u2g');
            $first_flag = true;
        }

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
                    if( !self::copyDir($sourceDir.'/'.$file, $destDir.'/'.$file) ){
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

        if($first_flag !== null){
            unset($first_flag);
        }
        return true;
    }
    // 递归删除目录
    public static function deleteDir($dir_path)
    {
        // 检查文件域是否安全
        if(!self::_checkDomain($dir_path)){
            return false;
        }

        // 如果是第一次进入,需要处理路径
        static $first_flag;
        if($first_flag === null){
            $dir_path = self::dealPath($dir_path, 'u2g');
            $first_flag = true;
        }

        $dh = opendir($dir_path);
        while( $file = readdir($dh) ){
            if($file != '.' && $file != '..'){
                $fullpath = $dir_path.'/'.$file;
                if( !is_dir($fullpath) ){
                    unlink($fullpath);
                } else {
                    self::deleteDir($fullpath);
                }
            }
        }
        closedir($dh);

        if($first_flag !== null){
            unset($first_flag);
        }
        //删除完当前目录中的内容，再删除当前目录
        if( rmdir($dir_path) ){
            return true;
        }
        return false;
    }

    // 文件或目录重命名
    public static function renameFileOrDir($old_path, $new_name)
    {
        // 检查文件域是否安全
        if(!self::_checkDomain($old_path)){
            return false;
        }

        // 判断名称是否合法
        if (!preg_match("#^[\.\w\x{4e00}-\x{9fa5}]+$#u",$new_name)){ 
            CommonFun::respone_json("文件名称不符合规则[$new_name]",'',101);
        }
        // 将名称改为全路径
        $old_path = self::dealPath($old_path, 'u2g');
        $base_path = pathinfo($old_path, PATHINFO_DIRNAME);
        $new_name = $base_path.'/'.self::dealPath($new_name, 'u2g');

        if( rename( $old_path, $new_name ) ){
            return self::dealPath($new_name,'g2u');
        }else{
            return false;
        }
    }
    // 移动文件
    public static function moveFileOrDir($from_path, $to_path)
    {
        // 检查文件域是否安全
        if(!self::_checkDomain($from_path, $to_path)){
            return false;
        }

        $from_path = self::dealPath($from_path,'u2g');
        $_to_path = self::dealPath($to_path,'u2g');

        if( rename( $from_path, $_to_path ) ){
            return $to_path;
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
    public static function dealPath($path, $type = '')
    {
        static $is_win;
        if($is_win === null){
            $is_win = (strtoupper(substr(PHP_OS,0,3))==='WIN')?true:false;
        }
        if(!$is_win){
            return $path;
        }

        $path = str_replace("\\",'/',$path);

        if($type == 'u2g'){
            $path = self::iconv_to($path,'UTF-8','GBK');
        }else if($type == 'g2u'){
            $path = self::iconv_to($path,'GBK','UTF-8');
        }
        return $path;
    }
    public static function iconv_to($str,$from,$to){
        if (!function_exists('iconv')){
            return $str;
        }

        if(function_exists('mb_convert_encoding')){
            $result = @mb_convert_encoding($str,$to,$from);
        }else{
            $result = @iconv($from, $to, $str);
        }
        if(strlen($result)==0){ 
            return $str;
        }
        return $result;
    }

    //验证当前文件是否可被写入
    // key 为文件在读取时在前端的标识
    public static function checkFileKey($file_path, $file_key)
    {
        if($file_key == self::getFileKey($file_path)){
            return true;
        }else{
            CommonFun::respone_json('文件已被修改,请重新打开编辑','',100);
        }
    }
    // 得到文件
    public static function getFileKey($file_path)
    {
        $_file_path = self::dealPath($file_path,'u2g');
        if(!is_file($_file_path)){
            CommonFun::respone_json('文件不存在','',100);
        }

        // 清除filemtime函数缓存
        clearstatcache();

        return md5( $file_path.filemtime($_file_path) );
    }

    // 检测文件路径是否在允许访问域中
    private static function _checkDomain()
    {
        // return true;
        $allow_root_paths = UserAuth::getLimitDir();
        if(empty($allow_root_paths)) CommonFun::respone_json('该用户暂无可访问路径','',199);

        $args = func_get_args();

        foreach($args as $path){
            if(empty($path)) CommonFun::respone_json('路径错误','',199);

            foreach($allow_root_paths as $allow_path){
                if(!empty($allow_path) && strpos($path, self::dealPath($allow_path)) === 0 &&
                    strpos($path, self::dealPath(BASE_DIR)) !== 0
                ){
                    continue 2;
                }
            }
            return false;
        }
        return true;

    }
}