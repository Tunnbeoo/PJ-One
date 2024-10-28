<?php
if (!session_id()) session_start();
error_reporting(0);

require("config.php");
require("common_start.php");
require("lib/func.lib.php");

// Kết nối cơ sở dữ liệu với MySQLi
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
$conn->set_charset("utf8");
?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Loc Phat Computer</title>
    <script language="javascript" src="lib/varAlert.<?php echo $_lang; ?>.unicode.js"></script>
    <script language="javascript" src="lib/javascript.lib.js"></script>
    <script language="javascript">
        function btnSearch_onclick() {
            if (test_empty(document.frmSearch.keyword.value)) {
                alert(mustInput_Search);
                document.frmSearch.keyword.focus();
                return false;
            }
            document.frmSearch.submit();
            return true;
        }
    </script>
    <script>
        function $(url, id, eval_str) {
            if (document.getElementById) {
                var x = (window.ActiveXObject) ? new ActiveXObject("Microsoft.XMLHTTP") : new XMLHttpRequest();
            }
            if (x) {
                x.onreadystatechange = function () {
                    el = document.getElementById(id);
                    el.innerHTML = '<img src="images/weather/loading.gif" align="left" />';
                    if (x.readyState == 4 && x.status == 200) {
                        el.innerHTML = '';
                        el.innerHTML = x.responseText;
                        eval(eval_str); 
                    }
                };
                x.open("GET", url, true);
                x.send(null);
            }
        }
        function change(id) {
            $('weather.php?id=' + id, 'noidung');
        }
    </script>
    <link href="css/css.css" rel="stylesheet" type="text/css">
    <script src="Scripts/AC_RunActiveContent.js" type="text/javascript"></script>
    <style type="text/css">
        body {
            background-color: #CCCCCC;
            margin-top: 0px;
        }
    </style>
</head>
<body>
    <table width="1010" border="0" align="center" cellpadding="0" cellspacing="0">
        <tr>
            <td bgcolor="#FFFFFF"><img src="Hinh/space.jpg" width="5" height="5"></td>
        </tr>
        <tr>
            <td bgcolor="#FFFFFF">
                <table width="1000" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
                    <tr>
                        <td><a href="https://plus.google.com/u/0/101740367695515335148/"><img src="banner.jpg"/></a></td>
                    </tr>
                    <tr>
                        <td class="style1">
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td width="11%"><a href="./" class="link1">TRANG CHỦ</a></td>
                                    <td width="12%"><a href="./?frame=intro" class="link1">GIỚI THIỆU</a></td>
                                    <td width="10%"><a href="./?frame=service" class="link1">DỊCH VỤ</a></td>
                                    <td width="15%"><a href="./?frame=news" class="link1">TIN TỨC &amp; SỰ KIỆN</a></td>
                                    <td width="13%"><a href="./?frame=contact" class="link1">LIÊN HỆ</a></td>
                                    <td width="2%"><img src="images/icon_search.gif" width="11" height="11"/></td>
                                    <form action="./" method="get" name="frmSearch">
                                        <input type="hidden" name="act" value="search"/>
                                        <input type="hidden" name="frame" value="search"/>
                                        <td width="16%"><input name="keyword" type="text" class="search" value="Nhập sản phẩm tìm kiếm ..." onFocus="this.value='';"/></td>
                                        <td width="21%"><input name="Submit" type="submit" class="style19" value="Tìm kiếm nhanh! " onClick="return btnSearch_onclick();"/></td>
                                    </form>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="style16">
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center">
                                        <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" width="614" height="198" align="top">
                                            <param name="movie" value="images/BANNER_KM.swf"/>
                                            <param name="quality" value="high"/>
                                            <embed src="images/BANNER_KM.swf" width="614" height="198" align="top" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash"></embed>
                                        </object>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="style20">
                                        <?php
                                        if (empty($_REQUEST['frame'])) {
                                            include('module/home.php');
                                        } else {
                                            ?>
                                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                <tr>
                                                    <td class="style17">
                                                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                            <tr>
                                                                <td width="6"><img src="images/c_bg1.jpg" width="6" height="29"/></td>
                                                                <td class="style11">
                                                                    <?php include('module/processTitle.php') ?>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="style20">
                                                        <table width="100%" border="0">
                                                            <tr>
                                                                <td><?php include('module/processFrame.php') ?></td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>    
                                            <?php
                                        }
                                        ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="style15">
                            <?php
                            $cart = $_SESSION['cart'];
                            $tongcong = 0;
                            if ($cart != '') {
                                foreach ($cart as $product) {
                                    $sql = "SELECT * FROM tbl_product WHERE id = '" . $product[0] . "'";
                                    $result = $conn->query($sql);
                                    if ($result->num_rows > 0) {
                                        $pro = $result->fetch_assoc();
                                    }
                                    $tongcong += $product[1];
                                }
                            }
                            ?>
                            <span class="style10"><?php echo $tongcong ?></span> sản phẩm
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td bgcolor="#FFFFFF"><img src="Hinh/space.jpg" width="5" height="5"></td>
        </tr>
    </table>
</body>
</html>
<?php
$conn->close();
require("common_end.php");
?>
