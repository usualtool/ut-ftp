<?php
namespace usualtool\Ftp;
class Ftp{
    function __construct($server='',$port='',$username='',$password='',$pasv=''){
        if(empty($server)):
            include 'Config.php';
            $this->server=$config["server"];
            $this->port=$config["port"];
            $this->username=$config["username"];
            $this->password=$config["password"];
            $this->pasv=$config["pasv"];
        else:
            $this->server=$server;
            $this->port=$port;
            $this->username=$username;
            $this->password=$password;
            $this->pasv=$pasv;
        endif;
        $this->ftp=@ftp_connect($this->server,$this->port) or die("FTP CONNECT ERROR");
        @ftp_login($this->ftp,$this->username,$this->password) or die("FTP LOGIN ERROR");
    }
    /**
    * FTP当前位置
    */
    function Cur(){
        return ftp_pwd($this->ftp);
    }
    /**
    * 返回FTP父目录
    */
    function Par(){
        return ftp_cdup($this->ftp);
    }
    /**
    * FTP文件列表
    */
    function List($path='/'){
        $data=ftp_nlist($this->ftp,$path);
        return $data;
    }
    /**
    * FTP详细列表
    */
    function RawList($path='/'){
        $data=ftp_rawlist($this->ftp,$path);
        return $data;
    }
    /**
    * 当前（本地）上传到FTP
    */
    function Upload($local,$server){
        $this->MakeDir(dirname($server));
        if (!file_exists($local)){
             return false;
        }
        $result = ftp_put($this->ftp,$server,$local,FTP_BINARY);//FTP_ASCII
        $this->Close();
        return (!$result) ? false : true;
    }
    /**
    * FTP下载到本地
    */
    function Download($local,$server){
        $result = ftp_get($this->ftp,$local,$server,FTP_BINARY);
        $this->Close();
        return $result;
    }
    /**
    * FTP重命名或移动
    */
    function Rename($old,$new){ 
        $this->MakeDir($new); 
        $res = @ftp_rename($this->ftp,$old,$new);
        $this->Close();
        if(!$res):
            return false;
        else:
            return true;
        endif;
    }
    /**
    * FTP删除文件
    */
    function Del($file) { 
        $res = @ftp_delete($this->ftp,$file); 
        if(!$res):
            return false;
        else:
            return true;
        endif;
    }
    /**
    * FTP创建目录
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
    * FTP文件大小
    */
    function Size($file) { 
        $res = @ftp_size($this->ftp,$file); 
        if(!$res):
            return false;
        else:
            return $res;
        endif;
    }
    /**
    * 关闭连接
    */
    public function Close(){
        ftp_quit($this->ftp); 
    }
}
