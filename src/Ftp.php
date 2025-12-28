<?php
namespace usualtool\Ftp;
class Ftp{
    private $ftp;
    private $server;
    private $port;
    private $username;
    private $password;
    private $pasv;
    public function __construct($server = '', $port = 21, $username = '', $password = '', $pasv = false){
        if(empty($server)){
            if (!file_exists('Config.php')) {
                throw new \Exception("未见配置文件Config.php");
            }
            include 'Config.php';
            if(!isset($config) || !is_array($config)){
                throw new \Exception("请确认配置文件是一个有效的数组");
            }
            $this->server = $config["server"] ?? '';
            $this->port = $config["port"] ?? 21;
            $this->username = $config["username"] ?? '';
            $this->password = $config["password"] ?? '';
            $this->pasv = $config["pasv"] ?? false;
        } else {
            $this->server = $server;
            $this->port = $port;
            $this->username = $username;
            $this->password = $password;
            $this->pasv = $pasv;
        }
        $this->ftp = ftp_connect($this->server, $this->port);
        if (!$this->ftp) {
            throw new \Exception("FTP连接失败 {$this->server}:{$this->port}");
        }
        if (!ftp_login($this->ftp, $this->username, $this->password)) {
            throw new \Exception("FTP登录失败: {$this->username}@{$this->server}");
        }
        if ($this->pasv) {
            ftp_pasv($this->ftp, true);
        }
    }
    /**
     * 获取当前远程目录
     */
    public function Cur(){
        return ftp_pwd($this->ftp);
    }
    /**
     * 切换到父目录
     */
    public function Par(){
        return ftp_cdup($this->ftp);
    }
    /**
     * 简单文件列表
     */
    public function List($path = '/'){
        return ftp_nlist($this->ftp, $path);
    }
    /**
     * 详细文件列表（raw format）
     */
    public function RawList($path = '/'){
        return ftp_rawlist($this->ftp, $path);
    }
    /**
     * 上传本地文件到 FTP
     */
    public function Upload($local, $remote){
        if (!file_exists($local)) {
            throw new \Exception("未发现本地文件");
        }
        $this->MakeDir(dirname($remote));
        $result = ftp_put($this->ftp, $remote, $local, FTP_BINARY);
        if (!$result) {
            throw new \Exception("传输文件失败");
        }
        return true;
    }
    /**
     * 从 FTP 下载文件到本地
     */
    public function Download($local, $remote){
        $result = ftp_get($this->ftp, $local, $remote, FTP_BINARY);
        if (!$result) {
            throw new \Exception("文件下载失败");
        }
        return true;
    }
    /**
     * 重命名或移动文件
     */
    public function Rename($old, $new){
        $this->MakeDir(dirname($new));
        if (!@ftp_rename($this->ftp, $old, $new)) {
            throw new \Exception("重命名或移动文件失败");
        }
        return true;
    }
    /**
     * 删除远程文件
     */
    public function Del($file){
        if (!@ftp_delete($this->ftp, $file)) {
            throw new \Exception("删除文件失败");
        }
        return true;
    }
    /**
     * 递归创建远程目录（类似 mkdir -p）
     */
    public function MakeDir($remotePath){
        $remotePath = trim($remotePath);
        if ($remotePath === '' || $remotePath === '/') {
            return true;
        }
        $originalDir = ftp_pwd($this->ftp);
        if ($originalDir === false) {
            throw new \Exception("无法获取目录结构");
        }
        if ($remotePath[0] !== '/') {
            $remotePath = $originalDir . '/' . ltrim($remotePath, '/');
        }
        $remotePath = rtrim(preg_replace('#/+#', '/', $remotePath), '/');
        $parts = array_filter(explode('/', ltrim($remotePath, '/')), 'strlen');
        $current = '/';
        foreach ($parts as $part) {
            $current = ($current === '/') ? "/$part" : "$current/$part";
            if (!@ftp_chdir($this->ftp, $current)) {
                if (!@ftp_mkdir($this->ftp, $current)) {
                    if (!@ftp_chdir($this->ftp, $current)) {
                        throw new \Exception("无法创建目录: '$current'");
                    }
                }
            }
        }
        @ftp_chdir($this->ftp, $originalDir);
        return true;
    }
    /**
     * 获取远程文件大小（字节）
     */
    public function Size($file){
        $size = ftp_size($this->ftp, $file);
        return $size;
    }
    /**
     * 关闭 FTP 连接
     */
    public function Close(){
        if ($this->ftp && is_resource($this->ftp)) {
            ftp_close($this->ftp);
            $this->ftp = null;
        }
    }
    /**
     * 析构函数：自动关闭连接
     */
    public function __destruct(){
        $this->Close();
    }
}
