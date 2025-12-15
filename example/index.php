<?php
use usualtool\Ftp\Ftp;
$ftp = new Ftp();
$ch = urldecode($_GET["ch"]) ?? ".";
if($_GET["do"] == "del"):
    if($ftp->Del($_GET["chs"])):
        echo "删除成功";
    else:
        echo "删除失败";
    endif;
endif;
echo "FTP file list demo<br/>";
$data = $ftp->List($ch);
if(!is_array($data)):
    echo $ch;
    die("无法读取目录内容");
endif;
asort($data);
echo "<table style='width:50%;border:1px solid #ddd;'>";
foreach($data as $val):
    if($val === '.' || $val === '..' || $val === '/.' || $val === '/..'):
        continue;
    endif;
    $name = basename($val);
    if($ch === '.'):
        $full_path = $name;
    else:
        $full_path = rtrim($ch, '/') . '/' . $name;
    endif;
    $size = $ftp->Size($full_path);
    echo "<tr>";
    if($size == -1):
        echo "<td><a href='?p=test&ch=" . urlencode($full_path) . "'>" . htmlspecialchars($name) . "</a></td>";
        echo "<td>---</td>";
        echo "<td></td>";
    else:
        echo "<td>" . htmlspecialchars($name) . "</td>";
        echo "<td>" . $size . " B</td>";
        echo "<td><a href='?do=del&chs=" . urlencode($full_path) . "'>删除</a></td>";
    endif;
    echo "</tr>";
endforeach;
echo "</table>";
