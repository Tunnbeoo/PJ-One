<?php
require("common_start.php");
require("lib/func.lib.php");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Loc Phat Computer</title>
    <script src="lib/varAlert.<?php echo htmlspecialchars($_lang); ?>.unicode.js"></script>
    <script src="lib/javascript.lib.js"></script>
    <link href="css/css.css" rel="stylesheet" type="text/css">
    <script src="Scripts/AC_RunActiveContent.js" type="text/javascript"></script>

    <script>
        // Search form validation
        function btnSearch_onclick() {
            if (typeof test_empty === 'function' && test_empty(document.frmSearch.keyword.value)) {
                alert(typeof mustInput_Search !== 'undefined' ? mustInput_Search : 'Please enter a keyword.');
                document.frmSearch.keyword.focus();
                return false;
            }
            document.frmSearch.submit();
            return true;
        }

        // Fetch content dynamically
        function fetchData(url, id, eval_str) {
            let x = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
            if (x) {
                x.onreadystatechange = function () {
                    const el = document.getElementById(id);
                    el.innerHTML = '<img src="images/weather/loading.gif" align="left" />';
                    if (x.readyState == 4 && x.status == 200) {
                        el.innerHTML = x.responseText;
                        if (eval_str) eval(eval_str); 
                    }
                };
                x.open("GET", url, true);
                x.send();
            }
        }

        function change(id) {
            fetchData('weather.php?id=' + id, 'noidung');
        }
    </script>

    <style>
        body {
            background-color: #CCCCCC;
            margin-top: 0;
        }
    </style>
</head>
<body>
    <table width="1010" border="0" align="center" cellpadding="0" cellspacing="0">
        <tr><td bgcolor="#FFFFFF"><img src="Hinh/space.jpg" width="5" height="5"></td></tr>
        <tr>
            <td bgcolor="#FFFFFF">
                <table width="1000" align="center" bgcolor="#FFFFFF">
                    <tr>
                        <td><a href="https://plus.google.com/u/0/101740367695515335148/"><img src="banner.jpg" alt="Banner"/></a></td>
                    </tr>
                    <tr>
                        <td class="style1">
                            <table width="100%">
                                <tr>
                                    <td><a href="./" class="link1">TRANG CHỦ</a></td>
                                    <td><a href="./?frame=intro" class="link1">GIỚI THIỆU</a></td>
                                    <td><a href="./?frame=service" class="link1">DỊCH VỤ</a></td>
                                    <td><a href="./?frame=news" class="link1">TIN TỨC &amp; SỰ KIỆN</a></td>
                                    <td><a href="./?frame=contact" class="link1">LIÊN HỆ</a></td>
                                    <td><img src="images/icon_search.gif" width="11" height="11"/></td>
                                    <form action="./" method="get" name="frmSearch">
                                        <input type="hidden" name="act" value="search"/>
                                        <input type="hidden" name="frame" value="search"/>
                                        <td><input name="keyword" type="text" class="search" value="Nhập sản phẩm tìm kiếm ..." onFocus="this.value='';"/></td>
                                        <td><input name="Submit" type="submit" class="style19" value="Tìm kiếm nhanh!" onClick="return btnSearch_onclick();"/></td>
                                    </form>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="style16">
                            <?php
                            $frame = $_REQUEST['frame'] ?? '';
                            if ($frame === '') {
                                include('module/home.php');
                            } else {
                                echo '<table width="100%"><tr><td class="style17">';
                                echo '<table width="100%"><tr><td width="6"><img src="images/c_bg1.jpg" width="6" height="29"/></td><td class="style11">';
                                include('module/processTitle.php');
                                echo '</td></tr></table></td></tr><tr><td class="style20">';
                                echo '<table width="100%"><tr><td>';
                                include('module/processFrame.php');
                                echo '</td></tr></table></td></tr></table>';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="style15">
                            <?php
                            $cart = $_SESSION['cart'] ?? [];
                            $tongcong = 0;
                            foreach ($cart as $product) {
                                $stmt = $conn->prepare("SELECT * FROM tbl_product WHERE id = ?");
                                $stmt->bind_param("i", $product[0]);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                if ($result->num_rows > 0) {
                                    $pro = $result->fetch_assoc();
                                }
                                $tongcong += $product[1];
                                $stmt->close();
                            }
                            ?>
                            <span class="style10"><?php echo $tongcong ?></span> sản phẩm
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr><td bgcolor="#FFFFFF"><img src="Hinh/space.jpg" width="5" height="5"></td></tr>
    </table>
</body>
</html>
<?php
$conn->close();
require("common_end.php");
?>
