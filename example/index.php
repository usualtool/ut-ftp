<?php
require_once dirname(__FILE__).'/'.'autoload.php';
use library\UsualToolInc\UTInc;
use usualtool\Ftp\Ftp;
$ftp=new Ftp();
if(!empty($_GET["ch"])):
    $ch=$_GET["ch"];
else:
    $ch="/.";
endif;
if($_GET["do"]=="del"):
    if($ftp->Del($_GET["chs"])):
        echo"删除成功";
    else:
        echo"删除失败";
    endif;
endif;
echo"FTP file list demo<br/>";
$data=$ftp->List($ch);
asort($data);
echo"<table style='width:50%;border:1px solid #ddd;'>";
foreach($data as $key=>$val):
    if($val!="/."):
        $val=str_replace($ch,"",$val);
        $size=$ftp->Size($val);
        echo"<tr>";
        if($size=="-1"):
            echo"<td><a href='?ch=".$val."'>".$val."</a></td>";
            echo"<td>---</td>";
            echo"<td></td>";
        else:
            echo"<td>".$val."</td>";
            echo"<td>".$ftp->Size($val)." B</td>";
            echo"<td><a href='?do=del&chs=".$ch.$val."'>删除</a></td>";
        endif;
        echo"</tr>";
    endif;
endforeach;
echo"</table>";
