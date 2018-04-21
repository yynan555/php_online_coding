<?php
namespace App\Core;
/**
 * 文件操作类 
 * 用于完成文件及目录相关底层操作
 * 文件使用绝对地址
 */
use App\Core\CommonFun;

class File
{
    /**
     * 创建文件
     * @param string 文件名称
     */
    public static function createFile($file_path)
    {
        $_file_path = CommonFun::dealPath($file_path, 'u2g');
        if(is_file($_file_path)){
            return false;
        }else{
            $dir_path = dirname($file_path);
            self::createDir($dir_path);

            $fp=fopen($_file_path,"w+");
            fclose($fp);
            return $file_path;
        }
    }

    // 复制文件
    public static function copyFile($sourceFile, $destFile)
    {
        $sourceFile = CommonFun::dealPath($sourceFile,'u2g');
        $destFile = CommonFun::dealPath($destFile,'u2g');
        if(copy($sourceFile, $destFile)){
            return true;
        }else{
            return false;
        }
    }
	// 保存文件内容
	public static function setFileContent($file_path, $file_content = '' ,$open_mode = 'w')
	{
        $_file_path = CommonFun::dealPath($file_path,'u2g');
        if( is_file($_file_path) ){
            // 首先我们要确定文件存在并且可写。
            if (is_writable($_file_path)) {
                if (!$handle = fopen($_file_path, $open_mode)) {
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
        $file_path = CommonFun::dealPath($file_path,'u2g');
        if(is_file($file_path)){
            return file_get_contents($file_path);
        }else{
            return null;
        }
    }
    // 获取文件信息
    public static function getFileInfo($file_path)
    {
        $file_path = CommonFun::dealPath($file_path,'u2g');
        $arr = [
            'is_writeable' => is_writeable($file_path),
        ];
        return array_merge(pathinfo($file_path),$arr);
    }
    // 删除文件
    public static function deleteFile($file_path)
    {
        $_file_path = CommonFun::dealPath($file_path,'u2g');
        if(is_file($_file_path)) {
            if(unlink($_file_path)) { 
                return true; 
            } else { 
                return false; 
            } 
        } else { 
            return false;
        } 
    }
    // 上传文件
    // path 相对文件存放路径,  file 待上传文件
    public static function uploadFile($path,$file)
    {
        $path = CommonFun::dealPath($path, 'u2g');
        $file['name'] = CommonFun::dealPath($file['name'], 'u2g');

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
    // 下载文件或文件夹
    public static function downloadFileOrDir($path)
    {
        $zip_file_cache_path = BASE_DIR.'/Cache/ZipFile';

        $_path = CommonFun::dealPath($path, 'u2g');
        if( is_file($_path) ){
            self::downloadFile($path);
        }elseif( is_dir($_path) ){
            // 设置缓存文件名称
            $zip_file_name = $zip_file_cache_path.'/'.basename($path).'.zip';
            // 清理缓存文件
            self::deleteFile($zip_file_name);
            // 压缩目录
            self::compressDir($path, $zip_file_name);
            // 下载
            self::downloadFile($zip_file_name);
        }else{
            CommonFun::respone_json("非文件或目录",'',300);
        }
        
    }
    /**
     * 创建目录
     * @param string 目录名称
     */
    public static function createDir($dir_path)
    {
        $dir_path = CommonFun::dealPath($dir_path,'u2g');
        if(is_dir($dir_path)){
            return false;
        }else{
            if( !@mkdir($dir_path,0777,true) ){
                return false;
            }
            return CommonFun::dealPath($dir_path,'g2u');
        }
    }
    /**
     * 根据文件夹获取文件中内容
     * @param $path 扫描路径目录
     * @return array
     */
    public static function getFileListByDir($path)
    {
        $_path = CommonFun::dealPath($path, 'u2g');
        $dirs = scandir($_path);

        $result = [];
        foreach($dirs as $dir_name){
            if($dir_name !== '.' && $dir_name !== '..') {
                $dir_item = [];
                $dir_item['name'] = CommonFun::dealPath($dir_name, 'g2u');
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
        // 如果是第一次进入,需要处理路径
        static $first_flag;
        if($first_flag === null){
            $sourceDir = CommonFun::dealPath($sourceDir, 'u2g');
            $destDir = CommonFun::dealPath($destDir, 'u2g');
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
        // 如果是第一次进入,需要处理路径
        static $first_flag;
        if($first_flag === null){
            $dir_path = CommonFun::dealPath($dir_path, 'u2g');
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
        // 判断名称是否合法
        if (!preg_match("#^[\.\w\x{4e00}-\x{9fa5}]+$#u",$new_name)){ 
            CommonFun::respone_json("文件名称不符合规则[$new_name], 规则为^[\.\w\x{4e00}-\x{9fa5}]+$",'',101);
        }
        // 将名称改为全路径
        $old_path = CommonFun::dealPath($old_path, 'u2g');
        $base_path = pathinfo($old_path, PATHINFO_DIRNAME);
        $new_name = $base_path.'/'.CommonFun::dealPath($new_name, 'u2g');

        if( rename( $old_path, $new_name ) ){
            return CommonFun::dealPath($new_name,'g2u');
        }else{
            return false;
        }
    }
    // 移动文件
    public static function moveFileOrDir($from_path, $to_path)
    {
        $from_path = CommonFun::dealPath($from_path,'u2g');
        $_to_path = CommonFun::dealPath($to_path,'u2g');

        if( rename( $from_path, $_to_path ) ){
            return $to_path;
        }else{
            return false;
        }    
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
        $_file_path = CommonFun::dealPath($file_path,'u2g');
        if(!is_file($_file_path)){
            CommonFun::respone_json('文件不存在','',100);
        }

        // 清除filemtime函数缓存
        clearstatcache();

        return md5( $file_path.filemtime($_file_path) );
    }

    // 下载文件
    private static function downloadFile($file, $filename='')
    {
        if(empty($filename)){
            $filename = basename($file);//返回路径中的文件名部分。
        }
        $_file = CommonFun::dealPath($file,'u2g');
        /*
         * header() 函数向客户端发送原始的 HTTP 报头。
         * 必须在任何实际的输出被发送之前调用 header() 函数
         * 
         */
        header("Content-type: application/octet-stream");

        //处理中文文件名
        $ua = $_SERVER["HTTP_USER_AGENT"];
        $encoded_filename = rawurlencode($filename);
        if (preg_match("/MSIE/", $ua)) {
            header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
        } else if (preg_match("/Firefox/", $ua)) {
            header("Content-Disposition: attachment; filename*=\"utf8''" . $filename . '"');
        } else {
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        }

        header("Content-Length: ". filesize($_file));

        /*
         * readfile() 函数输出一个文件。
         * 该函数读入一个文件并写入到输出缓冲。
         * 若成功，则返回从文件中读入的字节数。若失败，则返回 false。
         */
        ob_clean();
        flush();
        readfile($_file);
    }

    // 获取某文件夹下的所有文件
    public static function getAllFileByDir($base_path, &$file_list ,$abs_path='')
    {
        $now_path = $base_path.$abs_path;
        if( is_dir($now_path) ){
            $childs = scandir($now_path);
            foreach($childs as $child){
                if($child !== '.' && $child !== '..') {
                    self::getAllFileByDir($base_path, $file_list,$abs_path.'/'.$child);
                }
            }
        }else if( is_file($now_path) ){
            $file_list[] = $abs_path;
        }
    }
    // 压缩文件
    private static function compressDir($old_path, $new_path){
        self::createFile($new_path);

        $_old_path = CommonFun::dealPath($old_path, 'g2u');

        $all_files = array();
        self::getAllFileByDir($_old_path,$all_files);
        // 判断是否找到文件
        if( empty($all_files) ) CommonFun::respone_json('未找到文件!','',600);

        $folder_name = basename($old_path);

        $zip=new \ZipArchive();
        if($zip->open($new_path, \ZipArchive::OVERWRITE)=== TRUE){
            foreach ($all_files as $file) {
                $zip->addFile($_old_path.$file,$folder_name.$file);//调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
            }
            $zip->close(); //关闭处理的zip文件
        }else{
            CommonFun::respone_json('压缩文件失败!','',601);
        }

    }
}