<?php
namespace usualtool\Ftp;
class Ftp{
    function __construct(){
        include 'config.php';
        $this->server=$config["sever"];
        $this->port=$config["port"];
        $this->username=$config["username"];
        $this->password=$config["password"];
        $this->pasv=$config["pasv"];
        $this->ftp=@ftp_connect($this->server,$this->port) or die("FTP ERROR");
        @ftp_login($this->ftp,$this->username,$this->password) or die("FTP LOGIN ERROR");
        @ftp_pasv($this->ftp,$this->pasv);
    }
    /**
    * 当前位置
    */
    function Cur(){
        ftp_pwd($this->ftp);
    }
    /**
    * 返回父目录
    */
    function Par(){
        ftp_cdup($this->ftp);
    }
    /**
    * 列表
    */
    function List($path){
        ftp_nlist($this->ftp,$path);
    }
    /**
    * 上传
    */
    function Upload($path,$newpath,$type=true){
        if($type) $this->MakeDir($newpath);
        $this->res = @ftp_put($this->ftp,$newpath,$path,FTP_BINARY);
        if(!$this->res):
            return false;
        else:
            return true;
        endif;
    }
    /**
    * 下载
    */
    function Download($path,$newpath,$type=true){
        if($type) $this->MakeDir($newpath);
        $this->res = @ftp_get($this->ftp,$newpath,$path,FTP_BINARY);
        if(!$this->res):
            return false;
        else:
            return true;
        endif;
    }
    /**
    * 移动
    */
    function MoveFile($path,$newpath,$type=true) { 
        if($type) $this->dir_mkdirs($newpath); 
        $this->res = @ftp_rename($this->ftp,$path,$newpath); 
        if(!$this->res):
            return false;
        else:
            return true;
        endif;
    } 
    /**
    * 复制
    */
    function CopyFile($path,$newpath,$type=true) { 
        $downpath = "c:/tmp.dat"; 
        $this->res = @ftp_get($this->ftp,$downpath,$path,FTP_BINARY);
        if(!$this->res):
            $this->up_file($downpath,$newpath,$type); 
        else:
            return true;
        endif;
    } 
    /**
    * 删除
    */
    function DelFile($path) { 
        $this->res = @ftp_delete($this->ftp,$path); 
        if(!$this->res):
            return false;
        else:
            return true;
        endif;
    }
    /**
    * 创建目录
    */
    function MakeDir($path){
        $path_arr = explode('/',$path);
        $file_name = array_pop($path_arr);
        $path_div = count($path_arr);
        foreach($path_arr as $val){
            if(@ftp_chdir($this->ftp,$val) == FALSE){
                $tmp = @ftp_mkdir($this->ftp,$val);
                if($tmp == FALSE){
                    throw new \Exception("Dir Error");
                    exit;
                }
                @ftp_chdir($this->ftp,$val);
            }
        }
        for($i=1;$i=$path_div;$i++){
            @ftp_cdup($this->ftp);
        }
    }
    /**
    * 关闭连接
    */
    public function Close(){
        ftp_quit($this->ftp); 
    }
}
