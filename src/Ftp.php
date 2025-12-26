<?php
namespace usualtool\Ftp;
class Ftp{
    public function __construct($server='',$port='',$username='',$password='',$pasv=''){
        if(empty($server)):
            include __DIR__ . '/Config.php';
            $this->server=$config["server"];
            $this->port=$config["port"];
            $this->username=$config["username"];
            $this->password=$config["password"];
            $this->pasv=(bool)$config["pasv"];
        else:
            $this->server=$server;
            $this->port=$port;
            $this->username=$username;
            $this->password=$password;
            $this->pasv=(bool)$pasv;
        endif;
        $this->ftp=ftp_connect($this->server,$this->port,10) or die("FTP CONNECT ERROR\r\n");
        ftp_login($this->ftp,$this->username,$this->password) or die("FTP LOGIN ERROR\r\n");
        if(!ftp_pasv($this->ftp,$this->pasv)):
            die("无法启用被动模式\r\n");
        endif;
    }
    /**
    * FTP当前位置
    */
    public function Cur(){
        return ftp_pwd($this->ftp);
    }
    /**
    * 返回FTP父目录
    */
    public function Par(){
        return ftp_cdup($this->ftp);
    }
    /**
    * FTP文件列表
    */
    public function List($path='/'){
        $data=ftp_nlist($this->ftp,$path);
        return $data;
    }
    /**
    * FTP详细列表
    */
    public function RawList($path='/'){
        $data=ftp_rawlist($this->ftp,$path);
        return $data;
    }
    /**
    * 当前（本地）上传到FTP
    */
	public function Upload($local, $server){
		$dir = dirname($server);
		if(!$this->MakeDir($dir)):
			return false;
		endif;
		$local = realpath($local);
		if(!$local || !file_exists($local)):
			return false;
		endif;
		if(!ftp_pasv($this->ftp, $this->pasv)):
			return false;
		endif;
		$result = ftp_put($this->ftp, $server, $local, FTP_BINARY);
		if($result):
			return true;
		else:
			return false;
		endif;
	}
    /**
    * FTP下载到本地
    */
    public function Download($local,$server){
        $result = ftp_get($this->ftp,$local,$server,FTP_BINARY);
        return $result;
    }
    /**
    * FTP重命名或移动
    */
    public function Rename($old,$new){ 
        $this->MakeDir($new); 
        $res = @ftp_rename($this->ftp,$old,$new);
        if(!$res):
            return false;
        else:
            return true;
        endif;
    }
    /**
    * FTP删除文件
    */
    public function Del($file) { 
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
	public function MakeDir($path){
		if(empty($path) || $path === '/' || $path === '.'):
			return true;
		endif;
		$dirs = array_filter(explode('/', trim($path, '/')));
		if(empty($dirs)):
			return true;
		endif;
		$originalPwd = ftp_pwd($this->ftp);
		foreach($dirs as $dir):
			if(!@ftp_chdir($this->ftp, $dir)):
				if(!@ftp_mkdir($this->ftp, $dir)):
					@ftp_chdir($this->ftp, $originalPwd);
					return false;
				endif;
				if (!@ftp_chdir($this->ftp, $dir)):
					@ftp_chdir($this->ftp, $originalPwd);
					return false;
				endif;
			endif;
		endforeach;
		@ftp_chdir($this->ftp, $originalPwd);
		return true;
	}
    /**
    * FTP文件大小
    */
    public function Size($file){ 
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
    /**
     * 析构函数：自动关闭连接
     */
    public function __destruct(){
        $this->Close();
    }
}
