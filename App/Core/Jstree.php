<?php
namespace App\Core;

use App\Core\File;
/**
 * 文件操作类 
 * 用于完成文件及目录相关底层操作
 */
class Jstree
{
    /**
     * 获取目录列表
     * @param dir_path mixd (array:root dir|string:file list)
     * @return array 目录列表
     */
    public static function getDir($dir_path = '')
    {
        if(is_array($dir_path)){ // 如果没有指定访问的目录,则访问当前用户的所有目录
            $result = [];
            foreach((array)$dir_path as $dir_item){
                if(is_file(CommonFun::dealPath($dir_item,'u2g'))){
                    $result[] = self::format_item($dir_item,$dir_item,false,'file');
                }else{
                    $result[] = self::format_item($dir_item,$dir_item,true,'folder');
                }
            }
            return $result;
        }else{
        	return self::_getFormatDir($dir_path);
        }
    }
    // 获取一个进过格式化的目录
    private static function _getFormatDir($path)
    {
        $childs = File::getFileListByDir($path);

        $jsDir = [];
        foreach($childs as $child){
            if( $child['type'] == 'dir' ){
                $jsDir[] = self::format_item($child['name'],$child['path'].'/'.$child['name'],true,'folder');
            }else{
                $jsDir[] = self::format_item($child['name'],$child['path'].'/'.$child['name'],false,'file');
            }
        }
        return $jsDir;
    }
    // 格式化节点信息
    public static function format_item($text, $id, $children,  $type, $disabled=false, $opened=false)
    {
        return [
            'text' => $text,
            'children' => $children,
            'id' => $id,
            'type' => $type,
            'icon' => self::_get_file_img($text,$type), //$icon,
            'state' => [
                'disabled' => $disabled,
                'opened' => $opened
            ]
        ];
    }
    // 设置显示文件
    private static function _get_file_img($path,$type)
    {
        if($type == 'folder') return $type;
        $suffix = pathinfo($path, PATHINFO_EXTENSION);
        if(empty($suffix)){
            return $type;
        }

        $img = $type;
        $able_icons = 'php js css ico jpg png jpeg gif bmp text txt md log htaccess htm html xml xsl rb pdf as c iso cf cpp cs sql xls xlsx h crt pem cer ppt pptx doc docx zip gz tar rar fla';

        if( strpos($able_icons, $suffix) !== false ){
            $img = 'file-'.$suffix;
        }

        return $img; // 否则返回一个自己的图片
    }
}