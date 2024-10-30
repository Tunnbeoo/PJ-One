<?php
//*********************************************************************************************************
//***************************************** Kiểm Tra Phiên Bản PHP *********************************************
//echo 'Phiên bản PHP hiện tại: ' . phpversion();
if (phpversion() < "4.1.0") {
    $_GET = $HTTP_GET_VARS;
    $_POST = $HTTP_POST_VARS;
    $_SERVER = $HTTP_SERVER_VARS;
}
//*********************************************************************************************************
//************************************** Lấy cấu hình email *************************************************
$emailConfigRecord = getRecord("tbl_config", "code='adminEmail'");
$adminEmail = $emailConfigRecord['detail'];
//*********************************************************************************************************
//*********************************** Lấy cấu hình đơn vị tiền tệ ********************************************
$currencyUnitConfigRecord = getRecord("tbl_config", "code='currencyUnit'");
$currencyUnit = $currencyUnitConfigRecord['detail'];
//*********************************************************************************************************
//************************************** Hàm mã hóa khóa công khai *********************************************
function mo($g, $l) {
    return $g - ($l * floor($g / $l));
}

function powmod($base, $exp, $modulus) {
    $accum = 1;
    $i = 0;
    $basepow2 = $base;
    while (($exp >> $i) > 0) {
        if ((($exp >> $i) & 1) == 1) {
            $accum = mo(($accum * $basepow2), $modulus);
        }
        $basepow2 = mo(($basepow2 * $basepow2), $modulus);
        $i++;
    }
    return $accum;
}

function PKI_Encrypt($m, $e, $n) {
    $asci = array();
    for ($i = 0; $i < strlen($m); $i += 3) {
        $tmpasci = "1";
        for ($h = 0; $h < 3; $h++) {
            if ($i + $h < strlen($m)) {
                $tmpstr = ord(substr($m, $i + $h, 1)) - 30;
                if (strlen($tmpstr) < 2) {
                    $tmpstr = "0" . $tmpstr;
                }
            } else {
                break;
            }
            $tmpasci .= $tmpstr;
        }
        array_push($asci, $tmpasci . "1");
    }
    $coded = '';
    for ($k = 0; $k < count($asci); $k++) {
        $resultmod = powmod($asci[$k], $e, $n);
        $coded .= $resultmod . " ";
    }
    return trim($coded);
}

function PKI_Decrypt($c, $d, $n) {
    $decryptarray = explode(" ", $c);
    foreach ($decryptarray as $key => $value) {
        if ($value == "") {
            unset($decryptarray[$key]);
        }
    }
    $deencrypt = '';
    foreach ($decryptarray as $value) {
        $resultmod = powmod($value, $d, $n);
        $deencrypt .= substr($resultmod, 1, strlen($resultmod) - 2);
    }
    $resultd = '';
    for ($u = 0; $u < strlen($deencrypt); $u += 2) {
        $resultd .= chr(substr($deencrypt, $u, 2) + 30);
    }
    return $resultd;
}

//************************************************************************************************************
function killInjection($str) {
    $bad = array("\\", "=", ":");
    $good = str_replace($bad, "", $str);
    return $good;
}

//************************************************************************************************************
//************************************************* PHÂN TRANG ***************************************************
function countPages($total, $n) {
    if ($total % $n == 0) return (int)($total / $n);
    return (int)($total / $n) + 1;
}

function createPage($total, $link, $nitem, $itemcurrent, $step = 10) {
    if ($total < 1) {
        return false;
    }
    global $conn;
    $ret = "";
    $pages = countPages($total, $nitem);
    if ($itemcurrent > 0) {
        $ret .= '<a title="Đầu tiên" href="' . $link . '0" class="lslink">[&lt;&lt;]</a> ';
    }
    if ($itemcurrent > 1) {
		$ret .= '<a title="Về trước" href="' . $link . ($itemcurrent - 1) . '" class="lslink">[&lt;]</a> ';
    }

    $from = ($itemcurrent - $step > 0 ? $itemcurrent - $step : 0);
    $to = ($itemcurrent + $step < $pages ? $itemcurrent + $step : $pages);
    for ($i = $from; $i < $to; $i++) {
        if ($i != $itemcurrent) {
            $ret .= '<a href="' . $link . $i . '" class="lslink">' . ($i + 1) . '</a> ';
        } else {
            $ret .= '<b>' . ($i + 1) . '</b> ';
        }
    }

    if (($itemcurrent < $pages - 2) && ($pages > 1)) {
        $ret .= '<a title="Tiếp theo" href="' . $link . ($itemcurrent + 1) . '">[&gt;]</a> ';
    }
    if ($itemcurrent < $pages - 1) {
        $ret .= '<a title="Cuối cùng" href="' . $link . ($pages - 1) . '" class="lslink">[&gt;&gt;]</a>';
    }

    return $ret;
}

//************************************************************************************************************
//********************************************** SẮP XẾP ********************************************************
function getLinkSort($order) {
    $direction = ($_REQUEST['direction'] == '' || $_REQUEST['direction'] != '0') ? "0" : "1";
    return "./?act=" . $_REQUEST['act'] . "&cat=" . $_REQUEST['cat'] . "&page=" . $_REQUEST['page'] . "&sortby=" . $order . "&direction=" . $direction;
}

//************************************************************************************************************
//************************************** file : upload *******************************************************
function getFileExtension($filename) {  
    return strrchr($filename, ".");
}

function checkUpload($f, $ext = "", $maxsize = 0, $req = 0) {
    $fname = strtolower(basename($f['name']));
    $ftemp = $f["tmp_name"];
    $fsize = $f["size"];
    $fext = getFileExtension($fname);
    if ($fsize == 0) {
        if ($req != 0) return "Bạn chưa chọn file!";
        return "";
    } else {
        if ($ext != "" && strpos($ext, $fext) === false) 
            return "Tập tin không đúng định dạng: $fname";
        if ($maxsize > 0 && $fsize > $maxsize) 
            return "Kích thước hình phải nhỏ hơn " . $maxsize . " byte";
    }
    return "";
}

function makeUpload($f, $newfile) {
    return move_uploaded_file($f["tmp_name"], $newfile) ? $newfile : false;
}

//************************************************************************************************************
function getRecord($table, $where = '1=1') {
    global $conn;
    if ($table == '') return false;
    $result = mysqli_query($conn, "SELECT * FROM $table WHERE $where LIMIT 1");
    return mysqli_fetch_assoc($result);
}

function countRecord($table, $where = "") {
    global $conn;
    if ($table == "") return false;
    if ($where == "") $where = "1=1";
    $result = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM $table WHERE $where");
    $row = mysqli_fetch_assoc($result);
    return $row['cnt'];
}

function dateFormat($dateField, $lang = 'vn') {
    if ($dateField == '') return false;
    $arrVN = array("Chủ nhật", "Thứ Hai", "Thứ Ba", "Thứ Tư", "Thứ Năm", "Thứ Sáu", "Thứ Bảy");
    $arrEN = array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
    $date = strtotime($dateField);
    
    $arr = $lang == 'vn' ? $arrVN : $arrEN;
    
    return $arr[date('w', $date)] . ', ' . date('d/m/Y, H:i', $date);
}

function getArrayCategory($table, $catid = "", $split = "=") {
    global $conn;
    $hide = "status=0";
    if (isset($_SESSION['log'])) $hide = "1=1";
	$ret = array();
    if ($catid == "") $catid = 2;
    $result = mysqli_query($conn, "SELECT * FROM $table WHERE $hide AND parent=$catid");
    while ($row = mysqli_fetch_assoc($result)) {
        $ret[] = array($row['id'], ($catid == 1 ? "" : $split) . $row['name']);
        $getsub = getArrayCategory($table, $row['id'], $split . $split);
        foreach ($getsub as $sub)
            $ret[] = array($sub[0], $sub[1]);
    }
    return $ret;
}

function getArrayCategoryChild($table, $catid = "", $split = "=") {
    global $conn;
    $hide = "status=0";
    if (isset($_SESSION['log'])) $hide = "1=1";
    $ret = array();
    if ($catid == "") $catid = 77;
    $result = mysqli_query($conn, "SELECT * FROM $table WHERE $hide AND parent=$catid");
    while ($row = mysqli_fetch_assoc($result)) {
        $ret[] = array($row['id'], ($catid == 2 ? "" : $split) . $row['name']);
        $getsub = getArrayCategory($table, $row['id'], $split . $split);
        foreach ($getsub as $sub)
            $ret[] = array($sub[0], $sub[1]);
    }
    return $ret;
}

function getArrayNews($table, $catid = "", $split = "=") {
    global $conn;
    $hide = "status=0";
    if (isset($_SESSION['log'])) $hide = "1=1";
    $ret = array();
    if ($catid == "") $catid = 2;
    $result = mysqli_query($conn, "SELECT * FROM $table WHERE $hide AND parent=$catid");
    while ($row = mysqli_fetch_assoc($result)) {
        $ret[] = array($row['id'], ($catid == 1 ? "" : $split) . $row['name']);
        $getsub = getArrayCategory($table, $row['id'], $split . $split);
        foreach ($getsub as $sub)
            $ret[] = array($sub[0], $sub[1]);
    }
    return $ret;
}

function getArrayCombo($table, $valueField, $textField, $where = "") {
    global $conn;
    $ret = array();
    $hide = "status=0";
    $where = $where != "" ? $where : "1=1";
    $result = mysqli_query($conn, "SELECT $valueField, $textField FROM $table WHERE $hide AND $where");
    while ($row = mysqli_fetch_assoc($result)) {
        $ret[] = array($row[$valueField], $row[$textField]);
    }
    return $ret;
}

function getArray($query) {
    global $conn;
    $result = mysqli_query($conn, $query);
    $ret = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $ret[] = $row;
    }
    return $ret;
}

function isHaveChild($table, $id) {
    global $conn;
    $result = mysqli_query($conn, "SELECT * FROM $table WHERE parent=$id");
    return mysqli_num_rows($result) > 0;
}

//************************************************************************************************************
//****************************************** combo out HTML **************************************************
function comboLanguage($name, $langSelected, $class) {
    global $arrLanguage;
    $name = $name != '' ? $name : 'cmbLang';
    $out = '<select size="1" name="' . $name . '" class="' . $class . '">';
    foreach ($arrLanguage as $lang) {
        $selected = $lang[0] == $langSelected ? 'selected' : '';
        $out .= '<option value="' . $lang[0] . '" ' . $selected . '>' . $lang[1] . '</option>';
    }
    $out .= '</select>';
    return $out;
}

// $name            : name of combobox
// $arrSource  : function return array ; example : getListCategory(), getListNewsCategory()
// $index           : paramater selected
// $all             : $all==1 => show [Tất cả]
function comboCategory($name, $arrSource, $class, $index, $all) {
    $name = $name != '' ? $name : 'cmbParent';
    if (!$arrSource)
	    $ret = array();
    if ($catid == "") $catid = 2;
    $result = mysqli_query($conn, "SELECT * FROM $table WHERE $hide AND parent=$catid");
    while ($row = mysqli_fetch_assoc($result)) {
        $ret[] = array($row['id'], ($catid == 1 ? "" : $split) . $row['name']);
        $getsub = getArrayCategory($table, $row['id'], $split . $split);
        foreach ($getsub as $sub)
            $ret[] = array($sub[0], $sub[1]);
    }
    return $ret;
}

// Hàm getArrayCategoryChild đã được sửa và chỉ định nghĩa một lần
function getArrayCategoryChild($table, $catid = "", $split = "=") {
    global $conn;
    $hide = "status=0";
    if (isset($_SESSION['log'])) $hide = "1=1";
    $ret = array();
    if ($catid == "") $catid = 77;
    $result = mysqli_query($conn, "SELECT * FROM $table WHERE $hide AND parent=$catid");
    while ($row = mysqli_fetch_assoc($result)) {
        $ret[] = array($row['id'], ($catid == 2 ? "" : $split) . $row['name']);
        $getsub = getArrayCategory($table, $row['id'], $split . $split);
        foreach ($getsub as $sub)
            $ret[] = array($sub[0], $sub[1]);
    }
    return $ret;
}

// Các hàm khác không bị ảnh hưởng

function getArrayNews($table, $catid = "", $split = "=") {
    global $conn;
    $hide = "status=0";
    if (isset($_SESSION['log'])) $hide = "1=1";
    $ret = array();
    if ($catid == "") $catid = 2;
    $result = mysqli_query($conn, "SELECT * FROM $table WHERE $hide AND parent=$catid");
    while ($row = mysqli_fetch_assoc($result)) {
        $ret[] = array($row['id'], ($catid == 1 ? "" : $split) . $row['name']);
        $getsub = getArrayCategory($table, $row['id'], $split . $split);
        foreach ($getsub as $sub)
            $ret[] = array($sub[0], $sub[1]);
    }
    return $ret;
}

function getArrayCombo($table, $valueField, $textField, $where = "") {
    global $conn;
    $ret = array();
    $hide = "status=0";
    $where = $where != "" ? $where : "1=1";
    $result = mysqli_query($conn, "SELECT $valueField, $textField FROM $table WHERE $hide AND $where");
    while ($row = mysqli_fetch_assoc($result)) {
        $ret[] = array($row[$valueField], $row[$textField]);
    }
    return $ret;
}

function getArray($query) {
    global $conn;
    $result = mysqli_query($conn, $query);
    $ret = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $ret[] = $row;
    }
    return $ret;
}

function isHaveChild($table, $id) {
    global $conn;
    $result = mysqli_query($conn, "SELECT * FROM $table WHERE parent=$id");
    return mysqli_num_rows($result) > 0;
}

//************************************************************************************************************
//****************************************** combo out HTML **************************************************
function comboLanguage($name, $langSelected, $class) {
    global $arrLanguage;
    $name = $name != '' ? $name : 'cmbLang';
    $out = '<select size="1" name="' . $name . '" class="' . $class . '">';
    foreach ($arrLanguage as $lang) {
        $selected = $lang[0] == $langSelected ? 'selected' : '';
        $out .= '<option value="' . $lang[0] . '" ' . $selected . '>' . $lang[1] . '</option>';
    }
    $out .= '</select>';
    return $out;
}

// $name            : name of combobox
// $arrSource  : function return array ; example : getListCategory(), getListNewsCategory()
// $index           : paramater selected
// $all             : $all==1 => show [Tất cả]
function comboCategory($name, $arrSource, $class, $index, $all) {
    $name = $name != '' ? $name : 'cmbParent';
    if (!$arrSource)
	return '';
    
    $out = '<select size="1" name="' . $name . '" class="' . $class . '">';
    
    if ($all == 1) {
        $out .= '<option value="0">[Tất cả]</option>';
    }
    
    foreach ($arrSource as $item) {
        $selected = $item[0] == $index ? 'selected' : '';
        $out .= '<option value="' . $item[0] . '" ' . $selected . '>' . $item[1] . '</option>';
    }
    
    $out .= '</select>';
    return $out;
}

//************************************************************************************************************
//****************************************** Ghi log vào file *************************************************
function logToFile($message) {
    $logFile = 'log.txt'; // Đường dẫn đến file log
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

//************************************************************************************************************
//****************************************** Kiểm tra đăng nhập ***********************************************
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

//************************************************************************************************************
//****************************************** Hàm thoát đăng nhập **********************************************
function logout() {
    session_start();
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}

//************************************************************************************************************
//****************************************** Hàm gửi email **************************************************
function sendEmail($to, $subject, $message, $headers = '') {
    // Sử dụng hàm mail để gửi email
    return mail($to, $subject, $message, $headers);
}

//************************************************************************************************************
//****************************************** Hàm kiểm tra email hợp lệ ***************************************
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

//************************************************************************************************************
//****************************************** Hàm tạo mã ngẫu nhiên ********************************************
function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length / 2));
}

//************************************************************************************************************
//****************************************** Hàm mã hóa mật khẩu **********************************************
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

//************************************************************************************************************
//****************************************** Hàm kiểm tra mật khẩu *******************************************
function verifyPassword($password, $hashedPassword) {
    return password_verify($password, $hashedPassword);
}

//************************************************************************************************************
//****************************************** Hàm chuyển đổi định dạng ngày ************************************
function formatDate($date, $format = 'd/m/Y') {
    return date($format, strtotime($date));
}

//************************************************************************************************************
//****************************************** Hàm tạo chuỗi truy vấn SQL ***************************************
function buildQuery($table, $fields, $where = '', $orderBy = '', $limit = '') {
    $query = "SELECT " . implode(',', $fields) . " FROM " . $table;
    if ($where != '') {
        $query .= " WHERE " . $where;
    }
    if ($orderBy != '') {
        $query .= " ORDER BY " . $orderBy;
    }
    if ($limit != '') {
        $query .= " LIMIT " . $limit;
    }
    return $query;
}

//************************************************************************************************************
//****************************************** Hàm thực thi truy vấn *********************************************
function executeQuery($query) {
    global $conn;
    return mysqli_query($conn, $query);
}

//************************************************************************************************************
//****************************************** Hàm lấy dữ liệu từ cơ sở dữ liệu *********************************
function fetchData($query) {
    global $conn;
    $result = mysqli_query($conn, $query);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

//************************************************************************************************************
//****************************************** Hàm kiểm tra tồn tại bản ghi *************************************
function recordExists($table, $where) {
    global $conn;
    $query = "SELECT COUNT(*) as count FROM $table WHERE $where";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['count'] > 0;
}

//************************************************************************************************************
//****************************************** Hàm xóa bản ghi ***********************************************
function deleteRecord($table, $where) {
    global $conn;
    $query = "DELETE FROM $table WHERE $where";
    return mysqli_query($conn, $query);
}

//************************************************************************************************************
//****************************************** Hàm cập nhật bản ghi *********************************************
function updateRecord($table, $data, $where) {
    global $conn;
    
    // Kiểm tra xem mảng dữ liệu có rỗng không
    if (empty($data) || empty($where)) {
        return false; // Trả về false nếu không có dữ liệu hoặc điều kiện WHERE
    }

    $set = [];
    foreach ($data as $key => $value) {
        // Sử dụng mysqli_real_escape_string để bảo vệ khỏi SQL Injection
        $set[] = "$key='" . mysqli_real_escape_string($conn, $value) . "'";
    }

    // Tạo câu truy vấn SQL
    $query = "UPDATE $table SET " . implode(', ', $set) . " WHERE $where";

    // Thực hiện truy vấn và kiểm tra kết quả
    if (mysqli_query($conn, $query)) {
        return true; // Trả về true nếu cập nhật thành công
    } else {
        // Xử lý lỗi nếu có
        handleError(mysqli_error($conn));
        return false; // Trả về false nếu có lỗi
    }
}

//************************************************************************************************************
//****************************************** Hàm thêm bản ghi ***********************************************
function insertRecord($table, $data) {
    global $conn;
    $fields = implode(',', array_keys($data));
    $values = implode(',', array_map(function($value) use ($conn) {
        return "'" . mysqli_real_escape_string($conn, $value) . "'";
    }, array_values($data)));
    
    $query = "INSERT INTO $table ($fields) VALUES ($values)";
    return mysqli_query($conn, $query);
}

//************************************************************************************************************
//****************************************** Hàm lấy tất cả bản ghi ******************************************
function getAllRecords($table, $where = '1=1', $orderBy = '', $limit = '') {
    $query = "SELECT * FROM $table WHERE $where";
    if ($orderBy != '') {
        $query .= " ORDER BY $orderBy";
    }
    if ($limit != '') {
        $query .= " LIMIT $limit";
    }
    return fetchData($query);
}

//************************************************************************************************************
//****************************************** Hàm lấy bản ghi theo ID *****************************************
function getRecordById($table, $id) {
    return getRecord($table, "id = $id");
}

//************************************************************************************************************
//****************************************** Hàm lấy danh sách các bảng trong cơ sở dữ liệu *****************
function getTables() {
    global $conn;
    $result = mysqli_query($conn, "SHOW TABLES");
    $tables = [];
    while ($row = mysqli_fetch_array($result)) {
        $tables[] = $row[0];
    }
    return $tables;
}

//************************************************************************************************************
//****************************************** Hàm thực hiện truy vấn và trả về số dòng bị ảnh hưởng ***********
function executeAndGetAffectedRows($query) {
    global $conn;
    mysqli_query($conn, $query);
    return mysqli_affected_rows($conn);
}

//************************************************************************************************************
//****************************************** Hàm đóng kết nối cơ sở dữ liệu ************************************
function closeConnection() {
    global $conn;
    mysqli_close($conn);
}

//************************************************************************************************************
//****************************************** Hàm khởi tạo kết nối cơ sở dữ liệu *******************************
function createConnection($host, $user, $password, $database) {
    global $conn;
    $conn = mysqli_connect($host, $user, $password, $database);
    if (!$conn) {
        die("Kết nối thất bại: " . mysqli_connect_error());
    }
}

//************************************************************************************************************
//****************************************** Hàm lấy thông tin cấu hình ***************************************
function getConfig($key) {
    // Giả sử bạn có một mảng cấu hình
    $config = [
        'db_host' => 'localhost',
        'db_user' => 'root',
        'db_pass' => '',
        'db_name' => 'your_database',
    ];
    return isset($config[$key]) ? $config[$key] : null;
}

//************************************************************************************************************
//****************************************** Hàm khởi tạo ứng dụng ********************************************
function initApp() {
    $host = getConfig('db_host');
    $user = getConfig('db_user');
    $pass = getConfig('db_pass');
    $db = getConfig('db_name');
    createConnection($host, $user, $pass, $db);
}

//************************************************************************************************************
//****************************************** Hàm khởi động ứng dụng ********************************************
initApp();

//************************************************************************************************************
//****************************************** Hàm xử lý lỗi ***********************************************
function handleError($error) {
    // Ghi lỗi vào file log
    logToFile($error);
    // Hiển thị thông báo lỗi cho người dùng (có thể điều chỉnh theo yêu cầu)
    echo "Đã xảy ra lỗi: " . htmlspecialchars($error);
}

//************************************************************************************************************
//****************************************** Hàm gửi thông báo đến người dùng *******************************
function sendNotification($message) {
    // Bạn có thể sử dụng thư viện gửi email hoặc thông báo khác tại đây
    echo "<script>alert('" . addslashes($message) . "');</script>";
}

//************************************************************************************************************
//****************************************** Hàm kiểm tra quyền truy cập *************************************
function checkPermission($requiredRole) {
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] < $requiredRole) {
        header('Location: unauthorized.php');
        exit();
    }
}

//************************************************************************************************************
//****************************************** Hàm tạo menu động *********************************************
function createMenu($items) {
    $menu = '<ul>';
    foreach ($items as $item) {
        $menu .= '<li><a href="' . $item['link'] . '">' . $item['name'] . '</a></li>';
    }
    $menu .= '</ul>';
    return $menu;
}

//************************************************************************************************************
//****************************************** Hàm tạo breadcrumb *********************************************
function createBreadcrumb($items) {
    $breadcrumb = '<nav aria-label="breadcrumb"><ol class="breadcrumb">';
    foreach ($items as $item) {
        $breadcrumb .= '<li class="breadcrumb-item"><a href="' . $item['link'] . '">' . $item['name'] . '</a></li>';
    }
    $breadcrumb .= '</ol></nav>';
    return $breadcrumb;
}

//************************************************************************************************************
//****************************************** Hàm tạo thẻ HTML ***********************************************
function createHtmlTag($tag, $content, $attributes = []) {
    $attrString = '';
    foreach ($attributes as $key => $value) {
        $attrString .= "$key=\"" . htmlspecialchars($value) . "\" ";
    }
    return "<$tag $attrString>$content</$tag>";
}

//************************************************************************************************************
//****************************************** Hàm tạo bảng HTML **********************************************
function createHtmlTable($data) {
    if (empty($data)) return '<p>Không có dữ liệu để hiển thị.</p>';

    $table = '<table class="table">';
    $table .= '<thead><tr>';
    foreach (array_keys($data[0]) as $header) {
        $table .= '<th>' . htmlspecialchars($header) . '</th>';
    }
    $table .= '</tr></thead><tbody>';

    foreach ($data as $row) {
        $table .= '<tr>';
        foreach ($row as $cell) {
            $table .= '<td>' . htmlspecialchars($cell) . '</td>';
        }
        $table .= '</tr>';
    }
    
    $table .= '</tbody></table>';
    return $table;
}

//************************************************************************************************************
//****************************************** Hàm tạo trang phân trang ****************************************
function createPagination($currentPage, $totalPages, $baseUrl) {
    $pagination = '<nav aria-label="Page navigation"><ul class="pagination">';
    
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = ($i == $currentPage) ? 'active' : '';
        $pagination .= '<li class="' . $active . '"><a href="' . $baseUrl . '?page=' . $i . '">' . $i . '</a></li>';
    }
    
    $pagination .= '</ul></nav>';
    return $pagination;
}

//************************************************************************************************************
//****************************************** Hàm xử lý upload file *******************************************
function uploadFile($file, $targetDir) {
    $targetFile = $targetDir . basename($file["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Kiểm tra xem file có phải là hình ảnh không
    if (isset($_POST["submit"])) {
        $check = getimagesize($file["tmp_name"]);
        if ($check !== false) {
            echo "File là hình ảnh - " . $check["mime"] . ".";
            $uploadOk = 1;
        } else {
            echo "File không phải là hình ảnh.";
            $uploadOk = 0;
        }
    }

    // Kiểm tra xem file đã tồn tại chưa
    if (file_exists($targetFile)) {
        echo "Xin lỗi, file đã tồn tại.";
        $uploadOk = 0;
    }

    // Kiểm tra kích thước file
    if ($file["size"] > 500000) { // Giới hạn kích thước file là 500KB
        echo "Xin lỗi, file của bạn quá lớn.";
        $uploadOk = 0;
    }

    // Chỉ cho phép một số định dạng file nhất định
    if (!in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        echo "Xin lỗi, chỉ cho phép file JPG, JPEG, PNG và GIF.";
        $uploadOk = 0;
    }

    // Kiểm tra xem $uploadOk có bằng 0 không
    if ($uploadOk == 0) {
        echo "Xin lỗi, file của bạn không được tải lên.";
    } else {
        if (move_uploaded_file($file["tmp_name"], $targetFile)) {
            echo "File " . htmlspecialchars(basename($file["name"])) . " đã được tải lên.";
        } else {
            echo "Xin lỗi, đã xảy ra lỗi khi tải file của bạn lên.";
        }
    }
}