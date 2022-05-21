<?php
// Embed PHP code from another file
require_once 'jQuery/Action.php';
require_once 'jQuery/Element.php';
require_once 'jQuery.php';
class Click
{
    var $title = "<title>Momenta AB - Regencia address management system</title>";
    var $Ltitle = "LOGIN TO RAMS";
    var $headerTitle = "RAMS <font size='5'>Regencia Address Management System</font>";
    var $secondarytitle = "Regencia address management system";

    // Set of protected variables that cannot be accessed externally.
    protected $db = "Regencia";
    protected $host = "XX";
    protected $user = "root";
    protected $pass = "XXX";

    protected $new_address_type = 1;
    protected $update_address_type = 2;
    protected $fixed_bug_address_type = 3;
    protected $remove_address_type = 4;

    protected $debug = 0;

    var $country_code = "se";

    var $nmbrs = array();

    // Public function that connects to DB
    public function connect()
    {
        if ($this->debug) {
            print "Connect\n";
        }
        //Procedural MYSQL Connection
        $connect = mysql_connect($this->host, $this->user, $this->pass) or die(mysql_error());
        mysql_select_db($this->db, $connect) or die(mysql_error());
    }
    public function mrm_space($str)
    {
        $res = "";
        $str .= ' ';
        $str = str_replace(array(chr(9), chr(10), chr(13)), array(' ', ' ', ' '), $str);
        if ($str != "") {
            $length = strlen($str) - 1;
            for ($i = 0; $i < $length; $i++) {
                if (!($str[$i] == ' ' && $str[$i + 1] == ' '))
                    $res .= $str[$i];
            }
        }
        return $res;
    }
    public function rams_debug($user_id, $file, $func, $str = "", $address_id)
    {
        $connect = $this->connect();

        if ($str == "") {
            $str = $func;
            $func = "";
        }

        // Use concatenation to add string together and run the query string
        $sql = "INSERT INTO Regencia.RAMSDebugLog (user, date, file, func, text, address_id) VALUES ('$user_id', NOW(), '";
        $sql .= mysql_real_escape_string($file) . "', '";
        $sql .= mysql_real_escape_string($func) . "', '";
        $sql .= mysql_real_escape_string($this->mrm_space($str)) . "','" . $address_id . "')";
        mysql_query($sql);
    }

    public function getit($m)
    {
        $this->connect();
        if (strlen($m) != 2) {
            $m = "0" . $m;
        }
        $sql = "SELECT COUNT(DISTINCT(address_id)) AS newAdrs,IF(COUNT(DISTINCT(address_id)),'TRUE','FALSE') AS val FROM Regencia.Address_change_log WHERE SUBSTR(log_time,1,7)='" . date('Y') . "-" . $m . "' AND type_id='1';";
        $res = mysql_query($sql);
        $row = mysql_fetch_array($res);
        $sql2 = "SELECT COUNT(DISTINCT(address_id)) AS UpAdrs,IF(COUNT(DISTINCT(address_id)),'TRUE','FALSE') AS val FROM Regencia.Address_change_log WHERE SUBSTR(log_time,1,7)='" . date('Y') . "-" . $m . "' AND type_id='2';";
        $res2 = mysql_query($sql2);
        $row2 = mysql_fetch_array($res2);
        if ($row['val'] == 'TRUE') {
            $new = $row['newAdrs'];
        } else {
            $new = "NO ADDRESSES";
        }
        if ($row2['val'] == 'TRUE') {
            $upd = $row2['UpAdrs'];
        } else {
            $upd = "NO ADDRESSES";
        }
        $r = "<table border=2>";
        $r .= "<tr><td>New Addresses</td><td>Updated Addresses</td></tr>";
        $r .= "<tr><td>" . $new . "</td><td>" . $upd . "</td></tr>";
        $r .= "</table>";
        return $r;
    }

    public function pagina()
    {
        $y = date('Y');
        $m = date('m');
        $r = "";
        for ($i = 1; $i < 13; $i++) {
            $r .= "<a href='totaddress.php?mo=" . $i . "'>" . date("M", mktime(0, 0, 0, $i, 1, $y)) . "</a>&nbsp;&nbsp;";
        }
        return $r;
    }

    public function getAdminArea()
    {
        $ad = "<form action='' method='post'>";
        $ad .= "<select size='15' name='user_id' multiple='multiple'>" . $this->LogoutUserMsg() . "</select><br />";
        $ad .= "<input type='submit' value='Inactivate' /></form><br />";
        $ad .= "<form>";
        $ad .= "<select size='15' name='inactive_user_id' multiple='multiple'>" . $this->inactiveLogoutUserMsg() . "</select><br />";
        $ad .= "<input type='submit' value='Activate' onclick='alert('hejsan');' /></form>";
        $ad .= "<a href='#' onclick='javascript:$.php(url,{'act':'mask'});return false;'>Activate</a>";
        return $ad;
    }

    public function sendUserMsg($user_name = "", $message = "")
    {
        $this->connect();
        $check = "SELECT * FROM Regencia.Address_users WHERE name LIKE '%" . $user_name . "%'";
        $row = mysql_fetch_array(mysql_query($check));
        $result = mysql_query($check);
        if (mysql_num_rows($result) == 0) {
            $r .= "<script language='javascript' type='text/javascript'>alert('There is no user with that username');</script>";
        } else {
            $sql = "INSERT INTO Regencia.Address_users_msg (user_id, message, `read`) VALUES ('" . $row['user_id'] . "', '" . $message . "', '0');";
            mysql_query($sql);
        }
        return $r;
    }

    public function sendUserMsgs($user_id = null, $message = null)
    {
        if ($this->debug) {
            print "sendUserMsg\n";
        }
        if (!$date) {
            $date = date('Y-m-d');
        }
        $this->connect();
        $sql = "SELECT * FROM Regencia.Address_users WHERE user_id = '" . $user_id . "'";
        $result = mysql_query($sql);
        if (!$result) {
            return "Error! " . mysql_error() . " in sendUserMsg\n";
        }
        $r .= "<input style='background:#cdcdcd;' type='text' id='send_user_msg_id' value='Recivers ID number here...' onfocus='doClear(this);' onblur='doClear(this);' /><br />";
        $r .= "<textarea cols='35' rows='5 id='send_user_msg' style='background:#cdcdcd;' onfocus='doClear(this);' onblur='doClear(this);'>Type your message here...</textarea><br />";
        $r .= "<input type='button' value='Send' name='Send_message' onclick=\"javascript:$.php(url,{'act':'sendUserMsg','user_id':document.getElementById('send_user_msg_id').value,'send_user_msg':document.getElementById('send_user_msg').value});return false;\" />";
        if (mysql_num_rows($result) == 0) {
            $r .= "<p><strong>No user with that id</strong></p>";
        } else {
            $sql = "INSERT INTO Regencia.Address_users_msg (user_id, message, `read`) VALUES ('" . $user_id . "', '" . $message . "', '0');";
            $r .= mysql_query($sql);
        }
        //print "<p><strong>Total: <span style='color: red;'>$total</span></strong></p>";
        return utf8_decode($r);
    }

    public function IsLoggedOut($user_id)
    {
        $this->connect();
        return mysql_query("DELETE FROM Regencia.Address_users_online WHERE user_id = '" . $user_id . "';");
    }
    public function IsLoggedIn($user_id)
    {
        $this->connect();
        return mysql_query("INSERT INTO Regencia.Address_users_online (user_id) VALUES ('" . $user_id . "')");
    }
    public function CountOnlineUsers()
    {
        $this->connect();
        $result = mysql_query("SELECT DISTINCT(user_id) FROM Regencia.Address_users_online;");
        return mysql_num_rows($result);
    }
    private function injectioncontroll($var)
    {
        if ($this->debug) {
            print "Injectioncontroll\n";
        }
        return mysql_real_escape_string($var);
    }
    public function checklogin($arr)
    {
        if ($this->debug) {
            print "Checklogin\n";
        }
        $this->connect();

        return mysql_query("SELECT * FROM Regencia.Address_users WHERE name = " . "'" . $this->injectioncontroll($arr['username']) . "' AND password = " . "'" . $this->injectioncontroll($arr['password']) . "';");
    }
    public function MaintainanceLogoutMsg($user_id)
    {
        $this->connect();
        $sql2 = "SELECT * FROM Regencia.Address_users WHERE user_id = '" . $user_id . "'";
        $result2 = mysql_query($sql2);
        while ($row2 = mysql_fetch_array($result2)) {
            $r = "";
            if ($row2['active'] == '0') {
                $r  .= "<script language='javascript' type='text/javascript'>";
                $r .= " alert('Your account has been inactivated, contact your supervisor for further information');window.location = 'index.php?logout';";
                $r .= "</script>";
            }
            return $r;
        }
    }

    public function getusername($user_id)
    {
        $this->connect();
        $sql = "SELECT * FROM Regencia.Address_users A WHERE user_id = '" . $user_id . "'";
        $result = mysql_query($sql);
        $user = mysql_fetch_array($result);
        return $user['name'] . "'s";
    }

    public function getAdminMsg($user_id)
    {
        $this->connect();
        $sql = "SELECT * FROM Regencia.Address_users_msg WHERE user_id = '" . $user_id . "' AND `read` = '0';";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_array($result)) {
            $sql1 = "UPDATE Regencia.Address_users_msg SET `read`='1' WHERE user_id='" . $user_id . "' AND id = '" . $row['id'] . "';";
            $sql2 = "UPDATE Regencia.Address_users_msg SET `read`='2' WHERE user_id='" . $user_id . "' AND id = '" . $row['id'] . "';";
            //$update="UPDATE Regencia.Address_users_msg SET `read`='1' WHERE user_id='".$user_id."' AND id = '".$row['id']."';";
            print "
				<script language='javascript' type='text/javascript'>
					var r=confirm('" . $row['message'] . "');
						if (r==true)
						  {
						  " . mysql_query($sql1) . $this->rams_debug($user_id, "RAMS", "in ReadAdminMessage", $sql) . "
						  }
						else
						  {
						  " . mysql_query($sql2) . $this->rams_debug($user_id, "RAMS", "in ReadAdminMessage", $sql) . "
						  }
				</script>";
        }
        mysql_close();
    }

    public function LogoutUserMsg()
    {
        $this->connect();
        $sql = "SELECT * FROM Regencia.Address_users WHERE active = '1' ORDER BY name ASC;";
        $result = mysql_query($sql);
        if (mysql_num_rows($result) == 0) {
            $r = "<option name='user' onclick=\"defval();\" value='useridinhere'>There is no active users</option>";
            return $r;
        } else {
            $r = "<option name='user' onclick=\"defval();\" value='useridinhere' selected='selected'>Select user to inactivate</option>";
            while ($row = mysql_fetch_array($result)) {

                $r .= "<option name='user' onclick=\"select_user_send(this.innerHTML);\" id='inactivated_user' value='" . $row['user_id'] . "'>" . $row['name'] . "</option>";
            }
            return $r;
        }
    }

    public function getKommun($country)
    {
        $this->connect();
        switch ($country) {
            case 'SE':
                $land = "SWEDEN";
                break;
            case 'NO':
                $land = "NORWAY";
                break;
            case 'FI':
                $land = "FINLAND";
                break;
        }
        $sql = "SELECT DISTINCT(district_name) FROM Regencia.District D WHERE country ='" . $land . "' ORDER BY district_name ASC;";
        $result = mysql_query($sql);
        $r = "<select id='new_kommun_holder' name='ny_kommun'>";
        while ($row = mysql_fetch_array($result)) {
            $r .= "<option id='new_kommun' name='new_kommun_name' value='" . utf8_decode($row['district_name']) . "'>" . utf8_decode($row['district_name']) . "</option>";
        }
        $r .= "</select>";


        return $r;
    }
    public function inactiveLogoutUserMsg()
    {
        $this->connect();
        $sql = "SELECT * FROM Regencia.Address_users WHERE active = '0' ORDER BY name ASC;";
        $result = mysql_query($sql);
        if (mysql_num_rows($result) == 0) {

            $r = "<option name='user' onclick=\"defval();\" value='useridinhere'>There is no inactive users</option>";
            return $r;
        } else {
            $r = "<option name='user' onclick=\"defval();\" value='useridinhere' selected='selected'>Select user to activate</option>";
            while ($row = mysql_fetch_array($result)) {

                $r .= "<option name='user' onclick=\"select_user_send(this.innerHTML);\" value='" . $row['user_id'] . "'>" . $row['name'] . "</option>";
            }
            return $r;
        }
    }

    public function LogUserOut($user_id)
    {
        //$sql = "UPDATE Regencia.Address_users_msg SET  active = '1' WHERE user_id = '".$user_id."';";
        $this->connect();
        $sql = "SELECT * FROM Regencia.Address_users WHERE user_id = '" . $user_id . "'";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_array($result)) {
            if ($row['active'] == '0') {
                $sql1 = "UPDATE Regencia.Address_users SET  active = '1' WHERE user_id = '" . $user_id . "';";
                mysql_query($sql1);
                //$this->rams_debug($user_id, "RAMS", "in LogUserOut",$sql1);
                //return $row['name']." has been activated.";
            } else if ($row['active'] == '1') {
                $sql2 = "UPDATE Regencia.Address_users SET  active = '0' WHERE user_id = '" . $user_id . "';";
                mysql_query($sql2);
                //return $row['name']." have been inactivated.";
                //$this->rams_debug($user_id, "RAMS", "in LogUserOut",$sql2);
            }
        }
    }

    function query($query, $mess)
    {
        if ($this->debug) {
            print "Query\n";
        }
        $this->connect();
        $result = mysql_query($query);
        if (!$result) {
            return "FEL! " . mysql_error() . "";
        } else {
            return "Posten " . $mess . "!";
        }
    }

    public function getPostal($postalcode, $x = 0)
    {
        if ($this->debug) {
            print "getPostal\n";
        }
        $this->connect();
        $postalcode = $this->fix($postalcode);
        $result = mysql_query("SELECT P.city,P.postal_code FROM Postal_codes P WHERE P.postal_code = '" . $postalcode . "' ");
        if ($result) {
            if (mysql_num_rows($result) == 0) {
                return "Missing Postal Code";
            } else {
                return utf8_encode(mysql_result($result, 0, $x));
            }
        } else {
            return "function getPostal failed!" . mysql_error() . "";
        }
    }

    public function getUsers()
    {
        if ($this->debug) {
            print "getUsers\n";
        }
        $this->connect();
        $result = mysql_query("SELECT u.user_id, u.email, u.name, COUNT(u.user_id) AS addresses FROM Address_users u LEFT JOIN Address_change_log a ON u.user_id = a.user_id WHERE u.level <> 9 GROUP BY u.user_id ORDER BY addresses DESC;");
        if (mysql_num_rows($result) == 0) {
            return "";
        } else {
            while ($row = mysql_fetch_array($result)) {
                $r .= "<p><a href='#' onclick=\"javascript:$.php(url,{'act':'modify_user','user_id':'" . $row["user_id"] . "' });return false;\">" . $row["name"] . "</a> <i style='font-size:10px;'>" . $row["email"] . "</i><br />Total addresses: " . ($row["addresses"]) . "</p>";
            }
            return utf8_decode($r);
        }
    }

    public function totalNewAdrs($date)
    {
        $this->connect();
        $sql = "SELECT COUNT(address_id) FROM Regencia.Address_change_log A where SUBSTR(log_time,1,10) = '$date' AND type_id='1';";
        $res = mysql_query($sql);

        return mysql_result($res, 0);
    }

    public function totalUpdrs($date)
    {
        $this->connect();
        $sql = "SELECT COUNT(address_id) FROM Regencia.Address_change_log A where SUBSTR(log_time,1,10) = '$date' AND type_id='2';";
        $res = mysql_query($sql);

        return mysql_result($res, 0);
    }

    public function getStatsupddrs($uid, $date)
    {
        $this->connect();
        $sql = "SELECT COUNT(DISTINCT(A.address_id)) FROM Regencia.Address_change_log A WHERE user_id = '$uid' AND SUBSTR(log_time,1,10) = '$date' AND type_id='2';";
        $res = mysql_query($sql);

        return mysql_result($res, 0);
    }

    public function getStatsBlankaddrs($uid, $date)
    {
        $this->connect();
        $sql = "SELECT COUNT(DISTINCT(A.address_id)) FROM Regencia.Address_change_log A
		INNER JOIN Regencia.Categorized B ON B.address_id = A.address_id
		WHERE A.user_id = $uid AND DATE(A.log_time) = DATE('$date') AND B.sub_category_id = 5 ORDER BY A.log_time DESC;";
        $res = mysql_query($sql);

        return mysql_result($res, 0);
    }

    public function getStatsBlankaddrsBetween($uid, $date_from, $date_to)
    {
        $this->connect();
        $sql = "SELECT COUNT(DISTINCT(A.address_id)) FROM Regencia.Address_change_log A
		INNER JOIN Regencia.Categorized B ON B.address_id = A.address_id
		WHERE A.user_id = $uid AND DATE(A.log_time) BETWEEN '$date_from' AND '$date_to' AND B.sub_category_id = 5 ORDER BY A.log_time DESC;";
        $res = mysql_query($sql);

        return mysql_result($res, 0);
    }

    public function getStatsBlankaddrsBetween_test($uid, $date = '', $opt = 0)
    {
        $this->connect();
        $date_from = date('Y-m-') . '01';
        $date_to = date('Y-m-') . date('t');
        $date2 = date('Y-m-d');

        if (strlen($date) != 0) {
            $date_from = substr($date, 0, -2) . "01";
            $date_to = substr($date, 0, -2) . date('t');
            $date2 = $date;
        }
        $sql = "SELECT DISTINCT(A.address_id), DATE(A.log_time) AS datum FROM Regencia.Address_change_log A
		INNER JOIN Regencia.Categorized B ON B.address_id = A.address_id
		WHERE A.user_id = $uid AND DATE(A.log_time) BETWEEN '$date_from' AND '$date_to' AND B.sub_category_id = 5 ORDER BY A.log_time DESC;";
        $q = mysql_query($sql);
        $count = 0;
        while ($row = mysql_fetch_array($q)) {
            if ($row['datum'] === $date) {
                $count++;
            }
        }
        if ($opt == 2) {
            return mysql_num_rows($q);
        } else {
            return $count;
        }
    }

    public function getStatsControlledaddrsBetween_test($uid, $date = '', $opt = 0)
    {
        $this->connect();
        $date_from = date('Y-m-') . '01';
        $date_to = date('Y-m-') . date('t');
        $date2 = date('Y-m-d');

        if (strlen($date) != 0) {
            $date_from = substr($date, 0, -2) . "01";
            $date_to = substr($date, 0, -2) . date('t');
            $date2 = $date;
        }

        $sql = "SELECT DISTINCT(A.address_id), DATE(A.log_time) AS datum FROM Regencia.Address_change_log A
		INNER JOIN Regencia.Categorized B ON B.address_id = A.address_id
		WHERE A.user_id = $uid AND DATE(A.log_time) BETWEEN '$date_from' AND '$date_to' AND B.sub_category_id = 6 ORDER BY A.log_time DESC;";
        $q = mysql_query($sql);
        $count = 0;
        while ($row = mysql_fetch_array($q)) {
            if ($row['datum'] === $date) {
                $count++;
            }
        }
        if ($opt == 2) {
            return mysql_num_rows($q);
        } else {
            return $count;
        }
    }

    public function getStatsControlledaddrs($uid, $date)
    {
        $this->connect();
        $sql = "SELECT COUNT(DISTINCT(A.address_id)) FROM Regencia.Address_change_log A
		INNER JOIN Regencia.Categorized B ON B.address_id = A.address_id
		WHERE A.user_id = $uid AND DATE(A.log_time) = DATE('$date') AND B.sub_category_id = 6 ORDER BY A.log_time DESC;";
        $res = mysql_query($sql);

        return mysql_result($res, 0);
    }

    public function getStatsControlledaddrsBetween($uid, $date_from, $date_to)
    {
        $this->connect();
        $sql = "SELECT COUNT(DISTINCT(A.address_id)) FROM Regencia.Address_change_log A
		INNER JOIN Regencia.Categorized B ON B.address_id = A.address_id
		WHERE A.user_id = $uid AND DATE(A.log_time) BETWEEN '$date_from' AND '$date_to' AND B.sub_category_id = 6 ORDER BY A.log_time DESC;";
        $res = mysql_query($sql);

        return mysql_result($res, 0);
    }

    public function totalBlankadrs($date)
    {
        $this->connect();
        $sql = "SELECT COUNT(DISTINCT(A.address_id)) FROM Regencia.Address_change_log A
			INNER JOIN Regencia.Categorized B ON B.address_id = A.address_id
			WHERE DATE(A.log_time) = DATE('$date') AND B.sub_category_id = 5 ORDER BY A.log_time DESC;";
        $res = mysql_query($sql);

        return mysql_result($res, 0);
    }

    public function getNewAdrStats($uid, $date)
    {
        $this->connect();
        $sql = "SELECT COUNT(DISTINCT(address_id)) FROM Regencia.Address_change_log A WHERE user_id = '$uid' AND SUBSTR(log_time,1,10) = '$date' AND type_id='1';";
        $res = mysql_query($sql);

        return mysql_result($res, 0);
    }

    public function getCeasedStats($uid, $date)
    {
        $this->connect();
        $sql = "SELECT COUNT(DISTINCT(address_id)) FROM Regencia.Address_change_log A WHERE user_id = '$uid' AND SUBSTR(log_time,1,10) = '$date' AND type_id='4' AND `function` = 'isCeased';";
        $res = mysql_query($sql);

        return mysql_result($res, 0);
    }
    public function getNotCeasedStats($uid, $date)
    {
        $this->connect();
        $sql = "SELECT COUNT(DISTINCT(address_id)) FROM Regencia.Address_change_log A WHERE user_id = '$uid' AND SUBSTR(log_time,1,10) = '$date' AND type_id='3' AND `function` = 'notCeased';";
        $res = mysql_query($sql);

        return mysql_result($res, 0);
    }
    public function getStats($date = null)
    {
        if ($this->debug) {
            print "getStats\n";
        }
        if (!$date) {
            $date = date('Y-m-d');
        }
        $this->connect();
        $sql = "SELECT DISTINCT(user_id) FROM Regencia.Address_change_log A WHERE  SUBSTR(log_time,1,10) = '$date';";
        $res = mysql_query($sql);
        $r = "<table>";
        $r .= "<tr><td>Select date:</td><td><input type='text' name='date' id='date' value='$date' /><input type='button' value='Change' onclick=\"javascript:$.php(url,{'act':'stats','date':document.getElementById('date').value });return false;\" /></td></tr>";
        $r .= "<tr><td>From:</td><td><input type='text' id='date_from' /> To: <input type='text' id='date_to' /> user id: <input type='text' id='date_user_id' /><input type='button' value='Change' onclick=\"javascript:$.php(url,{'act':'dateBetween','date_from':document.getElementById('date_from').value,'date_to':document.getElementById('date_to').value,'date_user_id':document.getElementById('date_user_id').value});return false;\" /></tr>";
        $r .= "</table>";
        //  $r.= "<p>Total users online: ".$this->CountOnlineUsers()."</p>";
        $r .= "<div id='statsFrame'>";
        $r .= "<table border=1 style='border-width:1px;border-spacing:0px;border-collapse:collapse;'>";
        $r .= "<tr><th><b>New</b></th><th><b>Updated</b></th><th><b>Blank</b></th><th><b>Controlled</b></th><th><b>Ceased</b></th><th><b>Not Ceased</b></th><th><b>User</b></th><th><b>Salesman</b></th><th><b>ID</b></th></tr>";
        $totControlled = 0;
        while ($row = mysql_fetch_array($res)) {
            $totControlled += $this->getStatsControlledaddrs($row['user_id'], $date);
            $r .= "<tr><td>" . $this->getNewAdrStats($row['user_id'], $date)
                . "</td><td>" . $this->getStatsupddrs($row['user_id'], $date)
                . "</td><td>" . $this->getStatsBlankaddrs($row['user_id'], $date)
                . "</td><td>" . $this->getStatsControlledaddrs($row['user_id'], $date)
                . "</td><td>" . $this->getCeasedStats($row['user_id'], $date) . "</td>"
                . "</td><td>" . $this->getNotCeasedStats($row['user_id'], $date) . "</td>"
                . "</td><td onclick=\"window.open('worked.php?userid=" . $row['user_id'] . "&date=" . $date . "','Conducted Work','width=760,height=640,resizable=yes,scrollbars=yes');\">" . $this->getusernames($row['user_id'])
                . "<td>{$this->getUserSalesman($row['user_id'])}</td>"
                . "</td><td>" . $row['user_id'] . "</tr>";
        }
        $r .= "</table><p><strong>Total New Addresses: <span style='color: red;'>" . $this->totalNewAdrs($date) . "</span></strong></p>";
        $r .= "<p><strong>Total Updated Addresses: <span style='color: red;'>" . $this->totalUpdrs($date) . "</span></strong></p>";
        $r .= "<p><strong>Total Blank Addresses: <span style='color:red;'>" . $this->totalBlankadrs($date) . "</span></strong></p>";
        $r .= "<p><strong>Total Controlled Addresses: <span style='color:red;'>" . $totControlled . "</span></strong></p>";
        $total =    $this->totalNewAdrs($date) + $this->totalUpdrs($date);
        $r .= "<p><strong>Overall Total Addresses: <span style='color: red;'>" . $total . "</span></strong></p>";
        $r .= "</div>";
        return $r;
    }
    public function getusernames($user_id)
    {
        $this->connect();
        $sql = "SELECT * FROM Regencia.Address_users A WHERE user_id = '" . $user_id . "'";
        $result = mysql_query($sql);
        $user = mysql_fetch_array($result);
        return $user['name'];
    }

    public function getUserSalesman($uid)
    {
        $this->connect();
        $sql = "SELECT Namn FROM Accenta.Personal WHERE Anställningsnr = (SELECT salesman_id FROM Regencia.Address_users WHERE user_id = $uid);";
        $q = mysql_query($sql);
        $ret = "N/A";
        while ($row = mysql_fetch_array($q)) {
            if ($row['Namn'] == '') {
                $ret = "N/A";
            } else {
                $ret = $row['Namn'];
            }
        }

        return $ret;
    }

    public function statsBetween($date_from, $date_to, $user_id)
    {
        $this->connect();
        $sql = "SELECT COUNT(address_id) FROM Regencia.Address_change_log WHERE SUBSTRING(log_time,1,10) between '$date_from' and '$date_to' AND user_id = '$user_id' AND type_id='1';";
        $res = mysql_query($sql);
        $row = mysql_fetch_array($res);
        $sql2 = "SELECT COUNT(address_id) FROM Regencia.Address_change_log WHERE SUBSTRING(log_time,1,10) between '$date_from' and '$date_to' AND user_id = '$user_id' AND type_id='2';";
        $res2 = mysql_query($sql2);
        $row2 = mysql_fetch_array($res2);
        $tot = $row[0] + $row2[0] + $this->getStatsBlankaddrsBetween_test($user_id, $date_from, 2) + $this->getStatsControlledaddrsBetween_test($user_id, $date_from, 2);
        $r = "<table border=1 style='border-width:1px;border-spacing:0px;border-collapse:collapse;'>";
        $r .= "<tr><td><b>User</b></td><td><b>New</b></td><td><b>Updated</b></td> <td><b>Blank</b></td> <td><b>Controlled</b></td> <td><b>Total</b></td></tr>";
        $r .= "<tr>";
        $r .= "<td>" . $this->getusernames($user_id) . "</td>";
        $r .= "<td>" . $row[0] . "</td>";
        $r .= "<td>" . $row2[0] . "</td>";
        $r .= "<td>" . $this->getStatsBlankaddrsBetween_test($user_id, $date_from, 2) . "</td>";
        $r .= "<td>" . $this->getStatsControlledaddrsBetween_test($user_id, $date_from, 2) . "</td>";
        $r .= "<td>$tot</td>";
        $r .= "</tr>";
        $r .= "</table>";

        return $r;
    }

    public function getinvStats($date = null, $user_id)
    {
        if ($this->debug) {
            print "getinvStats\n";
        }
        if (!$date) {
            $date = date('Y-m-d');
        }
        $this->connect();
        $sql = "SELECT DISTINCT(user_id) FROM Regencia.Address_change_log A WHERE user_id = '$user_id' AND SUBSTR(log_time,1,10) = '$date';";
        $res = mysql_query($sql);
        $r = "<table>";
        $r .= "<tr><td>Select date:</td><td><input type='text' name='date' id='date' value='$date' /><input type='button' value='Change' onclick=\"javascript:$.php(url,{'act':'invstats','date':document.getElementById('date').value });return false;\" /></td></tr>";
        //$r.="<tr><td>From:</td><td><input type='text' id='date_from' /> To: <input type='text' id='date_to' /> user id: <input type='text' id='date_user_id' /><input type='button' value='Change' onclick=\"javascript:$.php(url,{'act':'dateBetween','date_from':document.getElementById('date_from').value,'date_to':document.getElementById('date_to').value,'date_user_id':document.getElementById('date_user_id').value});return false;\" /></tr>";
        $r .= "</table>";
        //$r.= "<p>Total users online: ".$this->CountOnlineUsers()."</p>";
        $r .= "<br />";
        $r .= "<div id='statsFrame'>";
        $r .= "<table border=1 style='border-width:1px;border-spacing:0px;border-collapse:collapse;'>";
        $r .= "<tr><td><b>New</b></td><td><b>Updated</b></td>
	    <td><b>Blank</b></td>
	    <td><b>Controlled</b></td>
	    <td><b>Ceased</b></td><td><b>Not Ceased</b></td><td><b>User</b></td><td><b>ID</b></td></tr>";
        while ($row = mysql_fetch_array($res)) {
            $r .= "<tr><td>" . $this->getNewAdrStats($row['user_id'], $date) . "</td>
	    		<td>" . $this->getStatsupddrs($row['user_id'], $date) . "</td>
	    		<td>" . $this->getStatsBlankaddrsBetween_test($row['user_id'], $date) . "</td>
				<td>" . $this->getStatsControlledaddrsBetween_test($row['user_id'], $date) . "</td>
	    		<td>" . $this->getCeasedStats($row['user_id'], $date) . "</td>" . "</td>
	    		<td>" . $this->getNotCeasedStats($row['user_id'], $date) . "</td>" . "</td>
	    		<td>" . $this->getusernames($row['user_id']) . "</td>
	    		<td>" . $row['user_id'] . "</tr>";
        }
        $r .= "</table>";
        $r .= "</div>";
        return $r;
    }

    public function companyForm($string)
    {
        switch ($string) {
            case 'ab':
                return  'AB';
                break;
            case 'Ab':
                return  'AB';
                break;
            case 'aB':
                return  'AB';
                break;
            case 'Ab':
                return  'AB';
                break;
            case 'hb':
                return  'HB';
                break;
            case 'Hb':
                return  'HB';
                break;
            case 'hB':
                return  'HB';
                break;
            case 'kb':
                return  'KB';
                break;
            case 'Kb':
                return  'KB';
                break;
            case 'kB':
                return  'KB';
                break;
        }
    }

    public function comName($str)
    {
        $kat = preg_split("/ /", $str);

        foreach ($kat as $key => $val) {
            $i = 0;
            $firstLetter = strtoupper(substr($val, 0, 1));
            $rest = strtolower(substr($val, 1));
            $semifinal = $firstLetter . $rest;
            $pattern = array('hb', 'Hb', 'hB', 'ab', 'Ab', 'aB', 'kb', 'Kb', 'kB');
            $replacements = $this->companyForm($semifinal);
            $final = str_replace($pattern, "", $str);
            $final .= str_replace($pattern, $replacements, $semifinal) . " ";
        }
        return $final;
    }

    public function insAddress(
        $user_id,
        $company,
        $address,
        $postalcode,
        $phone,
        $phone2,
        $email,
        $website,
        $omsattning,
        $antalAnstallda,
        $verksamhet,
        $orgnr,
        $registreringsdatum,
        $omsattningsar,
        $kalla,
        $completevalue,
        $projid = 0,
        $phone3,
        $unReOrNot
    ) {
        if ($this->debug) {
            print "insAddress\n";
        }
        if ($this->checkAllowedPostals($postalcode, $user_id) == 0) {
            return "<script>alert('Address not saved Cause: The address is not in your allowed districts');$.php(url,{'act':'add_address'});</script>";
        }
        $postalcode = $this->fix($postalcode);
        $this->connect();

        $result = mysql_query("SELECT C.county_id FROM County C WHERE C.postal_code = '" . $postalcode . "' ");
        if ($result) {
            if (mysql_num_rows($result) == 0) {
                return "Missing County ID for Postal Code " . $postalcode . "in insAddress";
            } else {
                $county_id = utf8_decode(mysql_result($result, 0, $x));
            }
        } else {
            return "Query for County ID failed!";
        }

        $result = mysql_query("SELECT D.district_id FROM District D WHERE D.postal_code = '" . $postalcode . "' ");
        if ($result) {
            if (mysql_num_rows($result) == 0) {
                return "Missing County ID for Postal Code $postalcode";
            } else {
                $district_id = utf8_decode(mysql_result($result, 0, $x));
            }
        } else {
            return "Query for District ID failed!";
        }

        //$ckeckit = "SELECT COUNT(address_id) FROM Regencia.Address WHERE name = '".utf8_decode($company)."' AND phone1 = '".$this->fixIT($phone)."' AND box_postal_code ='$postalcode';";
        $ckeckit = "SELECT COUNT(address_id) FROM Regencia.Address WHERE ((name = '" .
            utf8_decode($company) . "' AND phone1 = '" . $this->fixIT($phone) .
            "' AND box_postal_code ='$postalcode') OR " .
            "(org_nr = '$orgnr' AND name = '" . utf8_decode($company) . "') OR " .
            "(org_nr = '$orgnr' AND phone1 = '" . $this->fixIT($phone) . "')) " .
            "AND deleted = '0';";
        $res = mysql_query($ckeckit);
        $returnset = mysql_result($res, 0, $rs);
        if ($returnset > 0) {
            return "<script>Prevented address duplication\n</script>";
        }

        $query = "INSERT INTO Address
		                     (name, box_address1, box_postal_code, phone1, phone2, 
		                      email, website, county_id, district_id, org_nr,complete,mobile_phone,deleted) VALUES 
		                      ('" . utf8_decode($company) . "', '" . utf8_decode($address) . "', 
		                      '" . $postalcode . "','" . $this->fixIT($phone) . "', 
		                      '" . $this->fixIT($phone2) . "', '" . utf8_decode(str_replace('mailto:', '', $email)) . "',
		                     '" . utf8_decode($website) . "', '" . utf8_decode($county_id) . "', 
		                      '" . utf8_decode($district_id) . "', '" . utf8_decode($orgnr) . "', '" . $completevalue . "', '" . $phone3 . "','{$unReOrNot}');";
        $result = mysql_query($query);
        $address_id = mysql_insert_id();
        $this->rams_debug($user_id, "RAMS", "in insAddress", $query, $address_id);



        if (!$result) {
            return "Error! " . mysql_error() . " in insAddress INSERT INTO Address\n";
        }

        if (!$address_id) {
            return "Error! " . mysql_error() . " in insAddress mysql_insert_id\n";
        }
        $query2 = "INSERT INTO Company_facts (turnover, number_of_employees, description, address_id, year_of_registration, turnover_year, org_nr) VALUES 
                              ('" . utf8_decode($omsattning) . "', '" . utf8_decode($antalAnstallda) . "', 
                              '" . utf8_decode($verksamhet) . "', '" . $address_id . "', 
                              '" . utf8_decode($registreringsdatum) . "', 
                              '" . utf8_decode($omsattningsar) . "', '" . utf8_decode($orgnr) . "');";

        $result = mysql_query($query2);
        $this->rams_debug($user_id, "RAMS", "in insAddress", $query2, $address_id);
        if (!$result) {
            return "Error! " . mysql_error() . " in insAddress INSERT INTO Company_facts<br />" . $query2;
        }
        $query3 = "INSERT INTO Regencia.Categorized (address_id) VALUES ('" . $address_id . "');";
        $result = mysql_query($query3);
        $this->rams_debug($user_id, "RAMS", "in insAddress", $query3, $address_id);
        if (!$result) {
            return "Error! " . mysql_error() . " in insAddress INSERT INTO Categorized\n";
        }

        $last_num = substr($query, -1);
        $query = rtrim($query, $last_num) . " turnover = '" . utf8_decode($omsattning) . "'" . $last_num;
        $query4 = "INSERT INTO Regencia.Address_change_log " .
            "( source, log_time, user_id, address_id, type_id, function, query ) " .
            "VALUES " .
            "('" . utf8_decode($kalla) . "', '" . date('Y-m-d H:i:s') . "'," .
            "'" . $user_id . "', '" . $address_id . "'," .
            "'" . $this->new_address_type . "'," .
            "'" . __FILE__ . ":" . __METHOD__ . "'," .
            "'" . preg_replace("/\'/", "\\\\'", $query) . "');";
        $result = mysql_query($query4);
        $this->rams_debug($user_id, "RAMS", "in insAddress", $query4, $address_id);
        if (!$result) {
            return "Error! " . mysql_error() . " in insAddress INSERT INTO Address_change_log\n";
        } else {
            $_SESSION['kalla'] = $kalla;
            if ($projid != 0) {
                mysql_query("UPDATE Accenta.AdressListor SET RegenciaAdressId='$address_id' WHERE ProjektAdresserID='$projid'");
                return "<h1>Address succesfully added</h1><a href=''>Add another!</a><script>self.close();</script>";
            } else {
                return "<h1>Address succesfully added</h1><a href=''>Add another!</a>";
            }
        }
    }

    public function checkPostalCode($postalcode, $countrycode)
    {
        $this->connect();
        if ((strlen($countrycode) == 0) | ($countrycode == "COUNTRY")) {
            $r = "<td style='border:1px solid #000000;width:100%;'>SELECT A COUNTRY</td>";
        } else {
            $url = "http://www.posten.se/soktjanst/postnummersok/resultat.jspv?pnr=" . $postalcode;
            $contents = file_get_contents($url);
            $ggg = preg_match_all("/lastcol\">(.*?)<\//s", $contents, $as);
            //$patt=array("/'/s","/ort/s","/{/s","/;/s","/}/s","/return/s","/=/s","/\(/s","/\)/s");
            //$g = preg_replace($patt, "$1", $as[1][0]);
            $g = $as[1][1];
            $sql = "SELECT P.postal_code FROM Regencia.Postal_codes P WHERE postal_code = '" . $countrycode . $postalcode . "'";
            $result = mysql_query($sql);

            if (mysql_num_rows($result) == 0) {
                $r .= "<td style='border:1px solid #000000;width:50%;' id='postalort'>" . utf8_decode($g) . "</td><td style='border:1px solid #000000;'>NO</td><a href='#' onclick=\"javascript:$.php(url,{'act':'add_postalcode','postalort':document.getElementById('postalort').innerHTML,'postalcode':'" . $postalcode . "','countrycode':'" . $countrycode . "'});return false;\">[ADD]</a>";
            } else {
                $r .= "<td style='border:1px solid #000000;width:50%;'>" . utf8_encode($g) . "</td><td style='border:1px solid #000000;'>YES</td>";
            }

            //return $r;
        }
        return $r;
    }

    public function addPostalCode($code, $ort, $countrycode)
    {
        $r = "<b>Add new postal code</b><br />";
        $r .= "<input type='text' id='new_postal_ort' value='" . trim($ort) . "' /><br />";
        $r .= "<input type='text' id='new_postal_code' value='" . $code . "' /><br />";
        $r .= "<input type='button' value='Add postal code' onclick=\"javascript:$.php(url,{'act':'add_postalcode_new','postalort':document.getElementById('new_postal_ort').value,'postalcode':document.getElementById('new_postal_code').value,'countrycode':'" . $countrycode . "'});return false;\" />";
        return $r;
    }

    public function addPostalCodeNew($code, $ort, $countrycode)
    {
        $this->connect();
        switch ($countrycode) {
            case 'SE':
                $country = "SWEDEN";
                break;
            case 'FI':
                $country = "FINLAND";
                break;
            case 'NO':
                $country = "NORWAY";
                break;
            case 'GB':
                $country = "GREAT BRITAIN";
                break;
            case 'FR':
                $country = "FRANCE";
                break;
            default:
                break;
        }
        $sql = "INSERT INTO Regencia.Postal_codes (postal_code, city, country)VALUES ('" . $countrycode . $code . "', '" . strtoupper($ort) . "', '" . $country . "'); ";
        $query = mysql_query($sql);
        if (!$query) {
            return "<b>Add new postal code</b><br />Error! " . mysql_error() . " in AddPostalCodeNew \n";
        } else {
            return mysql_query($sql) . "<b>Add new postal code</b><br />Successfully added new postal code<br /><input type='button' onclick=\"go_back();\" value='Back' />";
        }
    }

    public function getAddress($address_id)
    {
        if ($this->debug) {
            print "getAddress\n";
        }
        $this->connect();
        $query = mysql_query("SELECT *, substring(a.box_postal_code,1,2) AS CountryCode, substring(a.box_postal_code,3) AS box_postal_code FROM Regencia.Address a INNER JOIN Regencia.Company_facts k ON (a.address_id = k.address_id) INNER JOIN Regencia.Address_change_log g ON (a.address_id = g.address_id) WHERE a.address_id = '" . $address_id . "' ORDER BY g.log_time DESC;");
        if (!$query) {
            return "Error! " . mysql_error() . " in getAddress \n";
        } else {
            $result = mysql_fetch_array($query);
            return $result;
        }
    }

    public function ajax_mail($email_to, $email_subject, $email_message)
    {
        /**
         * Denna funktionen kräver att följande pear moduler är installerade:
         * 
         * debian paket:
         * 		php-net-smtp
         * 		php-mail
         * 		php-mail-mime
         * 
         */

        include("Mail.php");
        include("Mail/mime.php");
        $email_from = "CCC2 BETA V1 <no-reply>";

        $text = utf8_decode(urldecode($email_message));
        $crlf = "\r\n";
        $hdrs = array(
            "To"             => $email_to,
            "From"     => $email_from,
            "Reply-To" => $email_from,
            "Return-path" => $email_from,
            "X-Mailer" => "PHP/" . phpversion(),
            "Subject"  => utf8_decode(urldecode($email_subject))
        );

        $smtp_hdrs["host"] = "192.168.96.6";
        $smtp_hdrs["localhost"] = gethostbyaddr(gethostbyname($_SERVER['SERVER_ADDR']));



        $params = array(
            "text_encoding" => "quoted-printable"
        );

        $mime = new Mail_mime($crlf);
        $mime->setTXTBody($text);

        $body = $mime->get($params);
        $hdrs = $mime->headers($hdrs);

        $mail = &Mail::factory("smtp", $smtp_hdrs);
        //	$mail =& Mail::factory("mail");
        $res = $mail->send($email_to, $hdrs, $body);
    }

    public function chkAddress_id($address_to_chk, $user_id)
    {
        //echo "MAIL";
        //$this->ajax_mail('robin.persson@momenta.se', 'Error: UPDATE', 'The user with user_id: '.$user_id.' has updated an address without address_id');
        if (($address_to_chk == 0) || ($address_to_chk == '') || ($address_to_chk == null)) {
            $this->ajax_mail('robin.persson@momenta.se', 'Error: UPDATE', 'The user with user_id: ' . $user_id . ' has updated an address without address_id');
        }
    }

    public function getDistID($postalcode)
    {
        $this->connect();
        $result = mysql_query("SELECT C.county_id FROM Regencia.County C WHERE C.postal_code = '" . $postalcode . "';");
        return mysql_result($result, 0, $x) . "00";
    }

    public function getCtrlBlankMarkAddressType($cat)
    {
        switch ($cat) {
            case '5':
                return 6;
                break;
            case '6':
                return 7;
                break;
        }
    }
    public function ctrlBlankMark($cat, $address_id, $user_id, $kalla)
    {
        $this->connect();
        $sql = "UPDATE Regencia.Categorized SET sub_category_id = $cat WHERE address_id = $address_id;";
        $query = mysql_query($sql);
        if ($query) {
            $query2 = "INSERT INTO Regencia.Address_change_log " .
                "( source, log_time, user_id, address_id, type_id, function, query ) " .
                "VALUES " .
                "('" . utf8_decode($kalla) . "', '" . date('Y-m-d H:i:s') . "'," .
                "'" . $user_id . "', '" . $address_id . "'," .
                "'" . $this->getCtrlBlankMarkAddressType($cat) . "'," .
                "'" . __FILE__ . ":" . __METHOD__ . "'," .
                "'" . preg_replace("/\'/", "\\\\'", $sql) . "');";
            $result = mysql_query($query2);
            $this->rams_debug($user_id, "RAMS", "in upAddress", $query2, $address_id);

            return "<script language='javascript' type='text/javascript'>alert('Address have been successfully updated');delete_cookie('what');window.location='click.php';</script>";
        }
    }

    public function updAddress(
        $user_id,
        $company,
        $address,
        $postalcode,
        $phone,
        $phone2,
        $email,
        $website,
        $omsattning,
        $antalAnstallda,
        $verksamhet,
        $orgnr = 0,
        $registreringsdatum,
        $omsattningsar,
        $kalla,
        $address_id,
        $nykommun,
        $completevalue,
        $phone3,
        $unReOrNot,
        $cat
    ) {
        //if($orgnr==0){
        //return "no orgnr";
        //}
        //else{
        //$query6667 = mysql_query("UPDATE Regencia.Categorized SET sub_category_id = $cat WHERE address_id = $address_id;");
        $this->chkAddress_id($address_id, $user_id);
        if ($this->debug) {
            print "updAddress\n";
        }
        $postalcode = $this->fix($postalcode);
        $this->connect();
        $result = mysql_query("SELECT C.county_id FROM Regencia.County C WHERE C.postal_code = '" . $postalcode . "' ");
        if ($result) {
            if (mysql_num_rows($result) == 0) {
                return "Missing County ID for Postal Code $postalcode";
            } else {
                $county_id = utf8_decode(mysql_result($result, 0, $x));
            }
        } else {
            return "Query for County ID failed!";
        }

        $result = mysql_query("SELECT D.district_id FROM Regencia.District D WHERE D.postal_code = '" . $postalcode . "' ");
        if ($result) {
            if (mysql_num_rows($result) == 0) {
                return "Missing County ID for Postal Code $postalcode";
            } else {
                $district_id = utf8_decode(mysql_result($result, 0, $x));
            }
        } else {
            return "Query for District ID failed!";
        }
        $query = "UPDATE Regencia.Address SET 
							   name = '" . utf8_decode($company) . "', 
							   box_address1 = '" . utf8_decode($address) . "', 
							   box_postal_code = '" . $postalcode . "', 
							   phone1 = '" . $this->fixIT($phone) . "', 
							   phone2 = '" . $this->fixIT($phone2) . "',
							   mobile_phone = '" . $this->fixIT($phone3) . "', 
							   email = '" . utf8_decode(str_replace('mailto:', '', $email)) . "', 
							   website = '" . utf8_decode($website) . "', 
							   org_nr = '" . utf8_decode($orgnr) . "',
							   visit_postal_code = '" . $postalcode . "',
							   county_id = '" . utf8_decode($county_id) . "',
							   district_id = '" . utf8_decode($nykommun) . "',
							   complete = '" . $completevalue . "',
							   deleted = '{$unReOrNot}'
							   WHERE address_id = '" . $address_id . "';";
        $result = mysql_query($query);
        $this->rams_debug($user_id, "RAMS", "in upAddress", $query, $address_id);
        if (!$result) {
            return "Error! " . mysql_error() . " in upAddress \n";
        }
        $query1 = "UPDATE Regencia.Company_facts SET 
		                       turnover = '" . utf8_decode($omsattning) . "', 
                               number_of_employees = '" . utf8_decode($antalAnstallda) . "', 
                               description = '" . utf8_decode($verksamhet) . "', 
                               year_of_registration = '" . $registreringsdatum . "', 
                               turnover_year = '" . utf8_decode($omsattningsar) . "',
                               
                               org_nr = '" . utf8_decode($orgnr) . "' WHERE address_id = '" . $address_id . "';";
        $result = mysql_query($query1);
        $this->rams_debug($user_id, "RAMS", "in upAddress", $query1, $address_id);
        if (!$result) {
            return "Error! " . mysql_error() . " in upAddress \n";
        }


        $last_num = substr($query, -1);
        $query = rtrim($query, $last_num) . " turnover = '" . utf8_decode($omsattning) . "'" . $last_num;
        $query2 = "INSERT INTO Regencia.Address_change_log " .
            "( source, log_time, user_id, address_id, type_id, function, query ) " .
            "VALUES " .
            "('" . utf8_decode($kalla) . "', '" . date('Y-m-d H:i:s') . "'," .
            "'" . $user_id . "', '" . $address_id . "'," .
            "'" . $this->update_address_type . "'," .
            "'" . __FILE__ . ":" . __METHOD__ . "'," .
            "'" . preg_replace("/\'/", "\\\\'", $query) . "');";
        $result = mysql_query($query2);
        $this->rams_debug($user_id, "RAMS", "in upAddress", $query2, $address_id);
        if (!$result) {
            return "Error! " . mysql_error() . " in upAddress \n";
        } else {
            $_SESSION['kalla'] = $kalla;
            return "<script language='javascript' type='text/javascript'>alert('Address have been successfully updated');delete_cookie('what');window.location='click.php';</script>";
        }
        //}
    }

    public function getAddressID($phone, $orgnr)
    {
        if ($this->debug) {
            print "getAddressID\n";
        }
        $phone = $this->fixIT($phone);
        if (!$phone) {
            return 0;
        }
        $this->connect();
        if ($orgnr == "") {
            $result = mysql_query("SELECT * FROM Regencia.Address a WHERE a.deleted BETWEEN '0' AND '2' AND a.phone1 = '$phone' OR a.phone2 = '$phone' OR a.mobile_phone = '$phone';");
        } else {
            $result = mysql_query("SELECT * FROM Regencia.Address a WHERE a.deleted BETWEEN '0' AND '2' AND a.org_nr = '$orgnr' AND a.phone1 = '$phone' OR a.phone2 = '$phone' OR a.mobile_phone = '$phone';");
        }
        if ($row = mysql_fetch_array($result)) {
            return $row[0];
        } else {
            return 0;
        }
    }

    public function getAddressIdFromOrgnr($orgnr)
    { //hämtar address_id utifrån organisations nummret
        $this->connect();
        $sql = "SELECT address_id FROM Regencia.Address
			WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(org_nr,'-',''),':',''),'\t',''),'\n',''),'\r',''),' ',''),',','') = '$orgnr';";
        $q = mysql_query($sql);
        if ($row = mysql_fetch_array($q)) {
            return $row[0];
        } else {
            return 0;
        }
    }

    public function checkOrgnr($orgnr) //kollar om adressen med inmatat organisations nummer finns i databasen
    {
        if ($this->debug) {
            print "checkOrgnr\n";
        }
        if (!$orgnr) {
            return FALSE;
        }
        $this->connect();
        $result = mysql_query("SELECT * FROM Regencia.Address
			WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(org_nr,'-',''),':',''),'\t',''),'\n',''),'\r',''),' ',''),',','') = '$orgnr';");
        $row = mysql_fetch_array($result);
        if (mysql_num_rows($result) == 0) {
            return TRUE;
        } else  if ($row['deleted'] != 0) {
            return FALSE;
        } else {
            return FALSE;
        }
    }



    public function checkPhone($phone, $CC)
    {
        if ($this->debug) {
            print "checkPhone\n";
        }
        $phone = $this->fixIT($phone);
        if (!$phone) {
            return FALSE;
        }
        $this->connect();
        $result = mysql_query("SELECT * FROM Regencia.Address a WHERE SUBSTRING(a.box_postal_code,1,2) = '$CC' AND a.phone1 = '" . $phone . "' OR a.phone2 = '" . $phone . "' OR a.mobile_phone = '$phone';");
        $row = mysql_fetch_array($result);
        if (mysql_num_rows($result) == 0) {
            return TRUE;
        } else  if ($row['deleted'] != 0) {
            return FALSE;
        } else {
            return FALSE;
        }
    }
    public function getkommuner($countrycode)
    {
        $this->connect();
        switch ($countrycode) {
            case 'SE':
                $country = "SWEDEN";
                break;
            case 'NO':
                $country = "NORWAY";
                break;
            case 'FI':
                $country = "FINLAND";
                break;
        }
        $sql = "SELECT district_name,district_id,SUBSTR(MIN(postal_code),3,7) AS `FROM`,SUBSTR(MAX(postal_code),3,7) AS `TO` FROM Regencia.District WHERE country = '" . $country . "' GROUP BY district_name;";
        $res = mysql_query($sql);
        $r .= "<select id='select_the_district' name='select_the_district' style='width:200px;'><option>SELECT DISTRICT</option>";
        while ($row = mysql_fetch_array($res)) {
            if ($_SESSION['level'] <> 9) {
            } else {
                $r .= "<option value='" . $row['district_id'] . "'>" . utf8_encode($row['district_name']) . " (" . $row['FROM'] . "-" . $row['TO'] . ")</option>";
            }
        }
        $r .= "</select>";
        return $r;
    }

    public function uppdatera($userid, $address_id, $projid = "")
    {
        $this->connect();
        $address = $this->getAddress($address_id);
        $country = substr($address['county_id'], 0, 2);
        $this->nmbrs[0] = utf8_decode($address['phone1']);
        $this->nmbrs[1] = utf8_decode($address['phone2']);
        $this->nmbrs[2] = utf8_decode($address['mobile_phone']);
        $r = "";
        if (($address_id == 0) || ($address_id == '')) {
            $r .= "";
        } else {
            $r .= "<div id='loading' style='display:none;'><img src='ajax-loader2.gif' /></div>";
            if ($country == 'SE') {
                $r .= "<script type='text/javascript'>document.getElementById('countrycode_up').selectedIndex = 0;</script>";
            } else if ($country == "FI") {
                $r .= "<script type='text/javascript'>document.getElementById('countrycode_up').selectedIndex = 1;</script>";
            } else if ($country == 'NO') {
                $r .= "<script type='text/javascript'>document.getElementById('countrycode_up').selectedIndex = 2;</script>";
            } else if ($country == 'GB') {
                $r .= "<script type='text/javascript'>document.getElementById('countrycode_up').selectedIndex = 3;</script>";
            }

            if ($address['turnover_year'] == "0") {
                $r .= "<script type='text/javascript'>document.getElementById('omsattningsar_up').selectedIndex = 0;</script>";
            }
            if ($address['turnover_year'] == "2006") {
                $r .= "<script type='text/javascript'>document.getElementById('omsattningsar_up').selectedIndex = 1;</script>";
            } else if ($address['turnover_year'] == "2007") {
                $r .= "<script type='text/javascript'>document.getElementById('omsattningsar_up').selectedIndex = 2;</script>";
            } else if ($address['turnover_year'] == "2008") {
                $r .= "<script type='text/javascript'>document.getElementById('omsattningsar_up').selectedIndex = 3;</script>";
            } else if ($address['turnover_year'] == "2009") {
                $r .= "<script type='text/javascript'>document.getElementById('omsattningsar_up').selectedIndex = 4;</script>";
            } else if ($address['turnover_year'] == "2010") {
                $r .= "<script type='text/javascript'>document.getElementById('omsattningsar_up').selectedIndex = 5;</script>";
            }
            if ($address['turnover_year'] == "2011") {
                $r .= "<script type='text/javascript'>document.getElementById('omsattningsar_up').selectedIndex = 6;</script>";
            }
            if ($address['turnover_year'] == "2012") {
                $r .= "<script type='text/javascript'>document.getElementById('omsattningsar_up').selectedIndex = 7;</script>";
            }
            if ($address['turnover_year'] == "2013") {
                $r .= "<script type='text/javascript'>document.getElementById('omsattningsar_up').selectedIndex = 8;</script>";
            }
            $r .= "<script>selectKalla('" . $address['source'] . "');</script>";
            //	if($address['source']=="28"){
            //		$r.="<script type='text/javascript'>document.getElementById('kalla_up').selectedIndex = 0;</script>";
            //	}
            //	else if($address['source']=="29"){
            //		$r.="<script type='text/javascript'>document.getElementById('kalla_up').selectedIndex = 1;</script>";
            //	}
            if ($address['inactive'] == 0) {
                $inactive = '';
            } else {
                $inactive = 'checked';
            }
            //	if($address['deleted']==1){
            //		$unregister = 'checked';
            //	}
            $r .= "<h3>Update address:</h3>";
            $r .= "<script>javascript:$.php(url,{'act':'postal_code_up','postalcode_up': document.getElementById('postalcode_up').value,'countrycode_up': document.getElementById('countrycode_up').value});</script>";
            $r .= "<form id='form2'><input type='hidden' id='javetinte' name='acta' value='update_address' /><input type='hidden' id='address_id_up' name='address_id_up' value='" . $address_id . "' />";
            $r .= "K&auml;lla: <br />";
            $r .= "<select id='kalla_up' name='kalla_up'>";
            $r .= $this->getAllowedSources(9999999);
            $r .= "</select>";
            $r .= "<input DISABLED onclick=\"inactivateCheck(this);\" type='checkbox' {$inactive} id='inactive_up' value='0' /> ";
            $r .= "<input onclick=\"unregisterCheck(this);\" type='checkbox' {$unregister} id='unregister_up' value='0' /> Unregister<br />";
            $r .= "<span id='nomatch_kalla'></span><img id='img_kalla' src=\"../images/green_tick.png\" width='20px' height='20px' /><br />";
            $r .= "Company: <br  />";
            $r .= "<input type='text' id='company_up' name='company_up' value='" . utf8_encode($address['name']) . "' onclick=\"clean(this.id);\" /><span id='nomatch_company'></span><img id='img_company' src=\"../images/green_tick.png\" width='20px' height='20px' /><br />";
            $r .= "Address: <br />";
            $r .= "<input type='text' id='address_up' name='address_up' value='" . utf8_encode($address['box_address1']) . "' onclick=\"clean(this.id);\" /><span id='nomatch_address'></span><img id='img_address' src=\"../images/green_tick.png\" width='20px' height='20px' /><br />";
            $r .= "Postal: <br />";
            $r .= "<input onblur=\"javascript:$.php(url,{'act':'postal_code_up','postalcode_up': document.getElementById('postalcode_up').value,'countrycode_up': document.getElementById('countrycode_up').value});return false;\" type='text' name='postalcode_up' id='postalcode_up' size='3' value='" . $address['box_postal_code'] . "' onclick=\"clean(this.id);\" /> <span id='postal_up'></span><span id='nomatch_postal'></span>";
            //	if($_SESSION['level']==10){
            $r .= $this->getkommuner($address['CountryCode']) . "<img id='img_postal' src=\"../images/green_tick.png\" width='20px' height='20px' /><br />";
            //	}
            //	else{
            $r .= "<input type='hidden' id='select_the_district' name='select_the_district' value='SELECT_DISTRICT'/><img id='img_postal' src=\"../images/green_tick.png\" style='display:none;' width='20px' height='20px' />";
            $r .= "<br />";
            //}
            $r .= "Country: <br />";
            $r .= "<select id='countrycode_up' name='countrycode_up'>";
            $r .= "<option value='SE' >SWEDEN</option>";
            $r .= "<option value='FI' >FINLAND/SUOMI</option>";
            $r .= "<option value='NO' >NORWAY</option>";
            $r .= "<option value='GB' >UNITED KINGDOM</option>";
            $r .= "</select><span id='nomatch_countrycode'></span><img id='img_countrycode' src=\"../images/green_tick.png\" width='20px' height='20px' /><br />";
            $r .= "Phone: <br />";
            $r .= "<input type='text' id='phone_up' name='phone_up' value='" . utf8_decode($address['phone1']) . "' /><img id='img_phone' src=\"../images/green_tick.png\" width='20px' height='20px' /><br />";
            $r .= "Phone2: <br />";
            $r .= "<input type='text' id='phone2_up' name='phone2_up' value='" . utf8_decode($address['phone2']) . "' onclick=\"clean(this.id);\" /><span id='nomatch_phone2'></span><img id='img_phone2' src=\"../images/green_tick.png\" width='20px' height='20px' /><br />	";
            $r .= "Mobile phone: <br />";
            $r .= "<input type='text' id='phone3_up' name='phone3_up' value='" . utf8_decode($address['mobile_phone']) . "' onclick=\"clean(this.id);\" /><span id='nomatch_phone3'></span><img id='img_phone3' src=\"../images/green_tick.png\" width='20px' height='20px' /><br />	";
            $r .= "E-mail: <br />";
            $r .= "<input type='text' id='email_up' name='email_up' value='" . utf8_decode($address['email']) . "' onclick=\"clean(this.id);\" /><span id='nomatch_email'></span><img id='img_email' src=\"../images/green_tick.png\" width='20px' height='20px' /><br /><br />";
            $r .= "Website: <br />";
            $r .= "<input type='text' id='website_up' name='website_up' value='" . utf8_decode($address['website']) . "' onclick=\"clean(this.id);\" /><span id='nomatch_website'></span><img id='img_website' src=\"../images/green_tick.png\" width='20px' height='20px' /><br />";
            $r .= "Oms&auml;ttning (TKR): <br />";
            $r .= "<input type='text' id='omsattning_up' name='omsattning_up' value='" . utf8_decode($address['turnover']) . "' onclick=\"clean(this.id);\" />";
            $r .= "<select id='omsattningsar_up' name='omsattningsar_up'>";
            $r .= "<option value='0'>Missing</option>";
            $r .= "<option value='2006'>2006</option>";
            $r .= "<option value='2007'>2007</option>";
            $r .= "<option value='2008'>2008</option>";
            $r .= "<option value='2009'>2009</option>";
            $r .= "<option value='2010'>2010</option>";
            $r .= "<option value='2011'>2011</option>";
            $r .= "<option value='2012'>2012</option>";
            $r .= "<option value='2013'>2013</option>";
            $r .= "</select><span id='nomatch_omsattning'></span><img id='img_omsattning' src=\"../images/green_tick.png\" width='20px' height='20px' /><br />";
            $r .= "Antal anst&auml;llda: <br />";
            $r .= "<input type='text' id='antalAnstallda_up' name='antalAnstallda_up' value='" . utf8_decode($address['number_of_employees']) . "' onclick=\"clean(this.id);\" /><span id='nomatch_anstallda'></span><img id='img_anstallda' src=\"../images/green_tick.png\" width='20px' height='20px' /><br />";
            $r .= "Verksamhet: <br />";
            $r .= "<input  type='text' id='verksamhet_up' name='verksamhet_up' value='" . utf8_encode($address['description']) . "' onclick=\"clean(this.id);\" /><span id='nomatch_verksamhet'></span><img id='img_verksamhet' src=\"../images/green_tick.png\" width='20px' height='20px' /><br />";
            $r .= "Orgnr: <br />";
            $r .= "<input type='text' id='org_nr_up' name='orgnr_up' value='" . utf8_decode($address['org_nr']) . "' onclick=\"clean(this.id);\" onclick=\"clean(this.id);\" /><span id='nomatch_orgnr'></span><img id='img_orgnr' src=\"../images/green_tick.png\" width='20px' height='20px' /><br />";
            $r .= "Registrerings&aring;r: <br />";
            $r .= "<input type='text' id='registreringsdatum_up' name='registrerinsdatum_up' value='" . utf8_decode($address['year_of_registration']) . "' onclick=\"clean(this.id);\" /><span id='nomatch_registreringsar'></span><img id='img_registreringsar' src=\"../images/green_tick.png\" width='20px' height='20px' /><br />";
            $r .= "<div id='LeButtons'>";
            $r .= "<input type='button' id='submit' name='submit' value='Update' onclick=\"blank_check(document.getElementById('address_id_up').value,5);\" />";
            $r .= "<input type='button' id='submit_controlled' name='submit' value='Controlled' onclick=\"blank_check(document.getElementById('address_id_up').value,6);\" />";
            //if($_SESSION['level']==6 || $_SESSION['level']==9){
            if ($projid != "") {
                $r .= "<input type='button' name='expired' value='Ceased' onClick=\"javascript:$.php(url,{'act':'isCeased','RegID':$address_id,'where':'clicknewaddress','exist':'1','ProjektAdresserID':'$projid','close':'1'});return;bla();\" />";
            }
            //}
            //if($_SESSION['level']==6 || $_SESSION['level']==9){
            if ($projid != "") {
                $r .= "<input type='button' name='expired' value='Not Ceased' onClick=\"javascript:$.php(url,{'act':'notCeased','RegID':$address_id,'where':'clicknewaddress','ProjektAdresserID':'$projid','close':'1'});return;bla();\" />";
            }
            $r .= "<div>";
            //}
            $r .= "</form></div>";
        }
        return $r;
    }
    private function fixIT($str)
    {
        if ($this->debug) {
            print "fixIT\n";
        }
        $search = array(' ', '-');
        $replace = array('', '');
        #$str = str_replace($search, $replace, $str);
        $str = preg_replace('/\D/', '', $str);
        return $str;
    }
    private function fix($str)
    {
        if ($this->debug) {
            print "fixIT\n";
        }
        $search = array(' ', '-');
        $replace = array('', '');
        #$str = str_replace($search, $replace, $str);
        $str = preg_replace('/ /', '', $str);
        return $str;
    }

    public function changePassword($userid, $newpass)
    {
        $this->connect();
        if (empty($newpass)) {
            $r = "<script type='text/javascript' language='javascript'>chpwd();alert('You must enter a password');</script>";
        } else {
            $sql = "UPDATE Regencia.Address_users SET password = '" . $newpass . "' WHERE user_id = '" . $userid . "'";
            mysql_query($sql);
            $r .= "<script type='text/javascript' language='JavaScript'>chpwd();alert('Your password has been changed, You will be logged out in order for the changes to take action');window.location='index.php?logout';</script>";
            $this->rams_debug($user_id, "RAMS", "in ChangePassword", $sql);
        }
        return $r;
    }
    public function checkLevelOnlogin($level)
    {
        $r = "";
        if ($level == 0) {
            //$r.="javascript:$.php(url,{'act':'add_address'});return false;";
        }
        if ($level == 1) {
            $r .= "javascript:$.php(url,{'act':'stats'});return false;";
        }
        if ($level == 2) {
            $r .= "javascript:$.php(url,{'act':'DoubletsRemover'});return false;";
        }
        if ($level == 3) {
            //$r.="javascript:$.php(url,{'act':'add_address'});return false;";
        }
        if ($level == 5) {
            $r .= "window.location.replace('http://rams.momenta.se/message.php');";
        }
        if ($level == 6) {
            $r .= "javascript:$.php(url,{'act':'inactivatedAddresses'});return false;";
        }
        if ($level == 8) {
            $r .= "window.location.replace('http://rams.momenta.se/logout.php');";
        }
        if ($level == 9) {
            //$r.="window.location.replace('http://rams.momenta.se/message.php');";
        }


        return $r;
    }
    public function checkLevelForLinks($level, $user)
    {
        $r = "";
        if ($level == 0) {
            //RAMS standard user
            $r .= "<li><a href='#' class='tab' id='alla'>&nbsp;</a></li>";
            $r .= "<li><a href='#' class='tab' id='sundsvall' onmouseover=\"document.getElementById('alla').style.backgroundImage='url(images/vansterknapp_GRA.png)';\" onmouseout=\"document.getElementById('alla').style.backgroundImage='url(images/vansterknapp.png)';\" onclick=\"javascript:$.php(url,{'act':'invstats'});return false;\">" . $user . " stats</a></li>";
            $r .= "<li><a href='click.php' class='tab' id='bergeforsen'>Add address</a></li>";
            $r .= "<li><a href='index.php?logout' class='tab' id='&Ouml;stersund' onmouseover=\"this.style.backgroundImage='url(images/hogerknapp_GRA.png)';\" onmouseout=\"this.style.backgroundImage='url(images/hogerknapp.png)';\" style='background-image:url(images/hogerknapp.png);border:0px;background-repeat:no-repeat;'>&nbsp;&nbsp;Logout&nbsp;&nbsp;</a></li>";
        } else if ($level == 1) {
            //RAMS statistics viewer
            $r .= "<li><a href='#' class='tab' id='alla'>&nbsp;</a></li>";
            $r .= "<li><a href='#' class='tab' id='bangkok' onclick=\"chpwd();\" onmouseover=\"document.getElementById('alla').style.backgroundImage='url(images/vansterknapp_GRA.png)';\" onmouseout=\"document.getElementById('alla').style.backgroundImage='url(images/vansterknapp.png)';\">Change Password</a></li>";
            $r .= "<li><a href='user_stats.php' class='tab' id='tibro'>Total User Stats</a></li>";
            $r .= "<li><a href='#' class='tab' id='tradenom' onclick=\"javascript:$.php(url,{'act':'users'});return false;\">Users</a></li>";
            $r .= "<li><a href='index.php?logout' class='tab' id='&Ouml;stersund' onmouseover=\"this.style.backgroundImage='url(images/hogerknapp_GRA.png)';\" onmouseout=\"this.style.backgroundImage='url(images/hogerknapp.png)';\" style='background-image:url(images/hogerknapp.png);border:0px;background-repeat:no-repeat;'>&nbsp;&nbsp;Logout&nbsp;&nbsp;</a></li>";
        } else if ($level == 2) {
            //RAMS Doublets Remover

            $r .= "<li><a href='#' class='tab' id='alla'>&nbsp;</a></li>";
            $r .= "<li><a href='#' class='tab' id='bangkok' onclick=\"chpwd();\" onmouseover=\"document.getElementById('alla').style.backgroundImage='url(images/vansterknapp_GRA.png)';\" onmouseout=\"document.getElementById('alla').style.backgroundImage='url(images/vansterknapp.png)';\">Change Password</a></li>";
            $r .= "<li><a href='#' class='tab' id='tradenom' onclick=\"javascript:$.php(url,{'act':'DoubletsRemover'});return false;\">Remove Doublets</a></li>";
            $r .= "<li><a href='index.php?logout' class='tab' id='&Ouml;stersund' onmouseover=\"this.style.backgroundImage='url(images/hogerknapp_GRA.png)';\" onmouseout=\"this.style.backgroundImage='url(images/hogerknapp.png)';\" style='background-image:url(images/hogerknapp.png);border:0px;background-repeat:no-repeat;'>&nbsp;&nbsp;Logout&nbsp;&nbsp;</a></li>";
        } else if ($level == 3) {
            //RAMS Doublets Remover

            $r .= "<li><a href='#' class='tab' id='alla'>&nbsp;</a></li>";
            $r .= "<li><a href='#' class='tab' id='sundsvall' onmouseover=\"document.getElementById('alla').style.backgroundImage='url(images/vansterknapp_GRA.png)';\" onmouseout=\"document.getElementById('alla').style.backgroundImage='url(images/vansterknapp.png)';\" onclick=\"javascript:$.php(url,{'act':'invstats'});return false;\">" . $user . " stats</a></li>";
            $r .= "<li><a href='#' class='tab' id='bangkok' onclick=\"chpwd();\" onmouseover=\"document.getElementById('alla').style.backgroundImage='url(images/vansterknapp_GRA.png)';\" onmouseout=\"document.getElementById('alla').style.backgroundImage='url(images/vansterknapp.png)';\">Change Password</a></li>";
            $r .= "<li><a href='click.php' class='tab' id='bergeforsen'>Add address</a></li>";
            $r .= "<li><a href='#' class='tab' onclick=\"javascript:$.php(url,{'act':'bugReport'});return false;\">Bug reported addresses</a></li>";
            $r .= "<li><a href='#' class='tab' id='tradenom' onclick=\"javascript:$.php(url,{'act':'DoubletsRemover'});return false;\">Remove Doublets</a></li>";
            $r .= "<li><a href='index.php?logout' class='tab' id='&Ouml;stersund' onmouseover=\"this.style.backgroundImage='url(images/hogerknapp_GRA.png)';\" onmouseout=\"this.style.backgroundImage='url(images/hogerknapp.png)';\" style='background-image:url(images/hogerknapp.png);border:0px;background-repeat:no-repeat;'>&nbsp;&nbsp;Logout&nbsp;&nbsp;</a></li>";
            //$r.="<br /><br /><a href='#' id='button'>Doublet How-To click here</a>";
        } else if ($level == 5) {
            //RAMS subadmin
            $r .= "<li><a href='#' class='tab' id='alla'>&nbsp;</a></li>";
            $r .= "<li><a href='#' class='tab' id='bangkok' onclick=\"chpwd();\" onmouseover=\"document.getElementById('alla').style.backgroundImage='url(images/vansterknapp_GRA.png)';\" onmouseout=\"document.getElementById('alla').style.backgroundImage='url(images/vansterknapp.png)';\">Change Password</a></li>";
            $r .= "<li><a href='message.php' class='tab' id='vaasa'>Admin</a></li>";
            $r .= "<li><a href='index.php?logout' class='tab' id='&Ouml;stersund' onmouseover=\"this.style.backgroundImage='url(images/hogerknapp_GRA.png)';\" onmouseout=\"this.style.backgroundImage='url(images/hogerknapp.png)';\" style='background-image:url(images/hogerknapp.png);border:0px;background-repeat:no-repeat;'>&nbsp;&nbsp;Logout&nbsp;&nbsp;</a></li>";
        } else if ($level == 6) {
            //RAMS inactive addresses user
            $r .= "<li><a href='#' class='tab' id='alla'>&nbsp;</a></li>";
            $r .= "<li><a href='#' class='tab' id='bangkok' onclick=\"chpwd();\" onmouseover=\"document.getElementById('alla').style.backgroundImage='url(images/vansterknapp_GRA.png)';\" onmouseout=\"document.getElementById('alla').style.backgroundImage='url(images/vansterknapp.png)';\">Change Password</a></li>";
            $r .= "<li><a href='click.php' class='tab' id='bergeforsen'>Add address</a></li>";
            $r .= "<li><a href='#' class='tab' onclick=\"javascript:$.php(url,{'act':'bugReport'});return false;\">Bug reported addresses</a></li>";
            $r .= "<li><a href='#' class='tab' id='tradenom' onclick=\"javascript:$.php(url,{'act':'DoubletsRemover'});return false;\">Remove Doublets</a></li>";
            $r .= "<li><a href='#' class='tab' onclick=\"javascript:$.php(url,{'act':'inactivatedAddresses'});return false;\">Inaktivated addresses</a></li>";
            $r .= "<li><a href='index.php?logout' class='tab' id='&Ouml;stersund' onmouseover=\"this.style.backgroundImage='url(images/hogerknapp_GRA.png)';\" onmouseout=\"this.style.backgroundImage='url(images/hogerknapp.png)';\" style='background-image:url(images/hogerknapp.png);border:0px;background-repeat:no-repeat;'>&nbsp;&nbsp;Logout&nbsp;&nbsp;</a></li>";
        } else if ($level == 9) {
            //RAMS Superadmin
            $r .= "<li><a href='#' class='tab' id='alla'>&nbsp;</a></li>";
            $r .= "<li><a href='#' class='tab' id='sundsvall' onmouseover=\"document.getElementById('alla').style.backgroundImage='url(images/vansterknapp_GRA.png)';\" onmouseout=\"document.getElementById('alla').style.backgroundImage='url(images/vansterknapp.png)';\" onclick=\"javascript:$.php(url,{'act':'invstats'});return false;\">" . $user . " stats</a></li>";
            $r .= "<li><a href='click.php' class='tab' id='bergeforsen'>Add address</a></li>";
            $r .= "<li><a href='#' class='tab' id='tradenom' onclick=\"javascript:$.php(url,{'act':'DoubletsRemover'});return false;\">Remove Doublets</a></li>";
            $r .= "<li><a href='#' class='tab' onclick=\"javascript:$.php(url,{'act':'bugReport'});return false;\">Bug reported addresses</a></li>";
            $r .= "<li><a href='#' class='tab' onclick=\"javascript:$.php(url,{'act':'inactivatedAddresses'});return false;\">Inaktivated addresses</a></li>";
            $r .= "<li><a href='#' class='tab' id='bangkok' onclick=\"chpwd();\">Change Password</a></li>";
            $r .= "<li><a href='user_stats.php' class='tab' id='tibro'>Total User Stats</a></li>";
            $r .= "<li><a href='#' class='tab' id='tradenom' onclick=\"javascript:$.php(url,{'act':'users'});return false;\">Users</a></li>";
            $r .= "<li><a href='message.php' class='tab' id='vaasa'>Admin</a></li>";
            $r .= "<li><a href='index.php?logout' class='tab' id='&Ouml;stersund' onmouseover=\"this.style.backgroundImage='url(images/hogerknapp_GRA.png)';\" onmouseout=\"this.style.backgroundImage='url(images/hogerknapp.png)';\" style='background-image:url(images/hogerknapp.png);border:0px;background-repeat:no-repeat;'>&nbsp;&nbsp;Logout&nbsp;&nbsp;</a></li>";
        }
        return $r;
    }

    public function addAddress($userid)
    {
        $r = "";
        $r .= "<div id='left_content'>";
        $r .= "<h3>Add a new address:</h3>";
        $r .= "<form id='form' name='addAddressFormName' onsubmit=\"formAjax();return false; method='get'\">";
        $r .= "<input type='hidden' id='javet' name='act' value='post_address' />	";
        $r .= "K&auml;lla: <br />";
        $r .= "<select id='kalla' name='kalla'>";
        $r .= $this->getAllowedSources($userid);
        $r .= "</select>";
        $r .= "<input DISABLED onclick=\"inactivateCheck(this);\" type='checkbox' id='inactive' value='0' /> ";
        $r .= "<input onclick=\"unregisterCheck(this);\" type='checkbox' id='unregister' value='0' /> Unregister<br />";
        $r .= "Company: <br  />";
        $r .= "<input type='text' id='company' name='company' /><br />";
        $r .= "Address: <br />";
        $r .= "<input type='text' id='address' name='address' /><br />";
        $r .= "Postal: <br />";
        $r .= "<input onblur=\"javascript:$.php(url,{'act':'postal_code','postalcode': document.getElementById('postalcode').value,'countrycode': document.getElementById('countrycode').value});return false;\" type='text' name='postalcode' id='postalcode' size='3' /> <span id='postal'></span><br />";
        $r .= "Country: <br />";
        $r .= "<select id='countrycode' name='countrycode'>";
        $r .= $this->getAllowedCountryCodes($userid);
        $r .= "</select><br />";
        $r .= "Phone: <br />";
        $r .= "<input type='text' id='phone' name='phone' /><br />";
        $r .= "Phone2: <br />";
        $r .= "<input id='phone2' type='text' name='phone2' /><br />";
        $r .= "Mobile phone: <br />";
        $r .= "<input id='phone3' type='text' name='phone3' /><br />";
        $r .= "E-mail: <br />";
        $r .= "<input id='email' type='text' name='email' value='' /><br /><br />";
        $r .= "Website: <br />";
        $r .= "<input id='website' type='text' name='website' value='' /><br />";
        $r .= "Oms&auml;ttning (TKR): <br />";
        $r .= "<input id='omsattning' type='text' name='omsattning' value='' />";
        $r .= "<select id='omsattningsar' name='omsattningsar'>";
        $r .= "<option value='0'>Missing</option>";
        $r .= "<option value='2006'>2006</option>";
        $r .= "<option value='2007'>2007</option>";
        $r .= "<option value='2008'>2008</option>";
        $r .= "<option value='2009'>2009</option>";
        $r .= "<option value='2010'>2010</option>";
        $r .= "<option value='2011'>2011</option>";
        $r .= "<option value='2012'>2012</option>";
        $r .= "<option value='20013'>2013</option>";
        $r .= "</select><br />";
        $r .= "Antal anst&auml;llda: <br />";
        $r .= "<input id='antalanstallda' type='text' name='antalAnstallda' value='' /><br />";
        $r .= "Verksamhet: <br />";
        $r .= "<input id='verksamhet' type='text' name='verksamhet' /><br />";
        $r .= "Orgnr: <br />";
        $r .= "<input id='orgnr' type='text' name='orgnr' id='orgnr' value='' /><input type='button' id='searchOrgnr' value='Search' onclick=\"javascript:$.php(url,{'act':'searchOrgnr','orgnr': document.getElementById('orgnr').value});return false;\" /><br />";
        $r .= "Registrerings&aring;r: <br />";
        $r .= "<input id='registreringsdatum' type='text' onblur=\"javascript:$.php(url,{'act':'check_phone','phone': document.getElementById('phone').value,'countrycode':document.getElementById('countrycode').value,'company':document.getElementById('company').value,'address':document.getElementById('address').value,'kalla':document.getElementById('kalla').value,'postal':document.getElementById('postalcode').value,'countrycode':document.getElementById('countrycode').value,'phone2':document.getElementById('phone2').value,'email':document.getElementById('email').value,'website':document.getElementById('website').value,'omsattning':document.getElementById('omsattning').value,'antalanstallda':document.getElementById('antalanstallda').value,'verksamhet':document.getElementById('verksamhet').value,'orgnr':document.getElementById('orgnr').value,'registreringsdatum':document.getElementById('registreringsdatum').value,'omsattningsar':document.getElementById('omsattningsar').value,'phone3':document.getElementById('phone3').value,'unregister':document.getElementById('unregister').value});return false;\" name='registreringsdatum' value='' /><br />";
        $r .= "<input type='submit' id='submit' name='submit' disabled='disabled' value='Save' />";
        $r .= "</form>";
        $r .= "</div>";
        return $r;
    }
    public function getAllowedSources($userid)
    {
        $this->connect();
        if ($userid == 9999999) {
            $sql = "SELECT CAST(GROUP_CONCAT(source_id SEPARATOR ',') AS CHAR CHARACTER SET utf8) as source_id FROM Regencia.Sources;";
        } else {
            $sql = "SELECT allowed_sources FROM Address_users WHERE user_id='" . $userid . "';";
        }
        $res = mysql_query($sql);
        $row = mysql_fetch_array($res);
        $k = preg_split("/,/", $row[0]);
        $g = count($k);
        $r = "";
        for ($i = 0; $i < $g; $i++) {
            $sql2 = "SELECT source_name FROM Sources WHERE source_id='" . $k[$i] . "';";
            $res2 = mysql_query($sql2);
            $row2 = mysql_fetch_array($res2);
            $r .= "<option value='" . $k[$i] . "' onclick=\"$.php(url,{'act':'sourceReguire','source':this.value});\">" . $row2[0] . "</option>";
        }
        return $r;
    }
    public function getAllowedCountryCodes($userid)
    {
        $this->connect();
        $sql = "SELECT allowed_countrys FROM Address_users WHERE user_id='" . $userid . "';";
        $res = mysql_query($sql);
        $row = mysql_fetch_array($res);
        $k = preg_split("/,/", $row[0]);
        $g = count($k);
        $r = "";
        for ($i = 0; $i < $g; $i++) {
            $r .= "<option value='" . $k[$i] . "'>" . $k[$i] . "</option>";
        }
        return $r;
    }
    public function adminSectionCheck($level)
    {
        $r = "";
        if ($level == 9 || $level == 5) {
            $r .= "";
        } else {
            $r .= "<script language='javascript' type='text/javascript'>alert('You dont have permissions to this part of the site');window.location = 'index.php?logout';</script>";
        }
        return $r;
    }
    //CEASED ADDRESSES functionS START

    public function ceasedAddresses()
    {
        $this->connect();
        //AND RegenciaAdressId IS NOT NULL AND RegenciaAdressId != 0
        $sql = "SELECT * FROM Accenta.AdressListor WHERE `Färg` = 7 AND Raderad = 0  ORDER BY `Företag`  LIMIT 15;";
        $res = mysql_query($sql);
        $r = "<table style='border-bottom:1px solid #000000; width:100%;'>";
        while ($row = mysql_fetch_array($res)) {
            $pattern = array('/ /', '/-/');
            $a = preg_replace($pattern, '', $row['TelefonFormaterad']);
            $phone = preg_split("/[\s,]+/", $a);
            $r .= "<tr><td>" . utf8_encode($row['Företag']) . "</td>";
            if ($row['RegenciaAdressId'] != 0 && $row['RegenciaAdressId'] != NULL) {
                $r .= "<td style='float:right;'><input type='button' value='report bug' onclick=\"javascript:$.php(url,{'act':'reportAsBug','pid':'" . $row['ProjektAdresserID'] . "'});\" /></td>";
                $r .= "<td style='float:right;'>
				 	<input type='button' value='not ceased' onclick=\"javascript:$.php(url,{'act':'notCeased','foretag':'" . utf8_encode($row['Företag']) . "','ProjektAdresserID':'" . $row['ProjektAdresserID'] . "','GatuAdress':'" . utf8_decode($row['GatuAdress']) . "','Postnr':'" . $row['Postnr'] . "','TelefonFormaterad':'" . $phone[0] . "','RegID':'" . $row['RegenciaAdressId'] . "'});return false;\" />
				 </td>";
                $r .= "<td style='float:right;'><input type='button' value='ceased' onclick=\"javascript:$.php(url,{'act':'isCeased','ProjektAdresserID':'" . $row['ProjektAdresserID'] . "','RegID':'" . $row['RegenciaAdressId'] . "','where':'inactivatedaddress'});return false;\" /></td>";
            } else {
                $r .= "<td style='float:right;'>
				 	<input type='button' value='Check it' onclick=\"window.open('click.php?q=" . $row['ProjektAdresserID'] . "&p=" . $row['TelefonFormaterad'] . "','test','width=500','height=500');\" />
				 </td>";
            }
            $r .= "</tr>";
            $r .= "<tr><td>Orgnr: " . utf8_encode($row['Orgnr']) . "</td></tr>";
            $r .= "<tr><td style='border-bottom:1px solid #000000;'>Phone: " . utf8_encode($row['TelefonFormaterad']) . "</td></tr>";
        }
        $r .= "</table>";
        return $r;
    }
    public function reportAsBug($pid, $rid = "")
    {
        $this->connect();
        $sql = "UPDATE Accenta.AdressListor SET Färg='8' WHERE ProjektAdresserID='" . $pid . "';";
        $res = mysql_query($sql);
        if (!$res) {
            return "<script>alert('Something went wrong, contact support');</script>";
        } else {
            return "<script>javascript:$.php(url,{'act':'inactivatedAddresses'});</script>";
        }
    }
    public function notCeasedAdr($foretag, $ProjektAdresserID, $GatuAdress, $Postnr, $TelefonFormaterad, $regID, $close = 0)
    {
        $this->connect();
        $sql = "SELECT deleted FROM Regencia.Address WHERE address_id = '$regID' LIMIT 1;";
        $res = mysql_query($sql);
        $adrID = mysql_fetch_array($res);
        if (!$adrID) {
            mysql_query("INSERT INTO Regencia.Address(name, box_address1, box_postal_code, phone1,deleted) VALUES('" . $foretag . "', '" . $GatuAdress . "', '" . $Postnr . "', '" . $TelefonFormaterad . "', '0' );")
                or die(mysql_error());

            $query2 = "INSERT INTO Regencia.Address_change_log 
                              ( source, log_time, user_id, address_id, type_id, function ) VALUES 
                              ('9999', '" . date('Y-m-d H:i:s') . "', 
                              '" . $_SESSION['user_id'] . "', '" . mysql_id() . "',
                              '" . $this->fixed_bug_address_type . "', 'notCeased' );";
            $result = mysql_query($query2);

            mysql_query("UPDATE Accenta.AdressListor SET Färg='0' WHERE ProjektAdresserID='" . $ProjektAdresserID . "'")
                or die(mysql_error());
            mysql_query("UPDATE Accenta.AdressListor SET Raderad ='0' WHERE ProjektAdresserID ='" . $ProjektAdresserID . "'")
                or die(mysql_error());

            if ($close == 1) {
                return "<script>self.close();</script>";
            } else {
                return "<script>javascript:$.php(url,{'act':'inactivatedAddresses'});</script>";
            }
        } else {
            mysql_query("UPDATE Accenta.AdressListor SET Färg='0' WHERE ProjektAdresserID='" . $ProjektAdresserID . "'")
                or die(mysql_error());
            mysql_query("UPDATE Accenta.AdressListor SET Raderad ='0' WHERE ProjektAdresserID ='" . $ProjektAdresserID . "'")
                or die(mysql_error());
            mysql_query("UPDATE Regencia.Address SET deleted ='0' WHERE address_id ='" . $regID . "'")
                or die(mysql_error());
            mysql_query("UPDATE Accenta.AdressListor SET RegenciaAdressId ='$regID' WHERE ProjektAdresserID ='" . $ProjektAdresserID . "'")
                or die(mysql_error());

            $query2 = "INSERT INTO Regencia.Address_change_log 
                              ( source, log_time, user_id, address_id, type_id, function ) VALUES 
                              ('9999', '" . date('Y-m-d H:i:s') . "', 
                              '" . $_SESSION['user_id'] . "', '" . $regID . "',
                              '" . $this->fixed_bug_address_type . "', 'notCeased' );";
            $result = mysql_query($query2);

            if ($close == 1) {
                return "<script>self.close();</script>";
            } else {
                return "<script>javascript:$.php(url,{'act':'inactivatedAddresses'});</script>";
            }
        }
    }

    public function isCeasedAdr($regID, $where = "", $projid = "", $exist = 1, $close = 0)
    {
        $this->connect();

        mysql_query("UPDATE Regencia.Address SET deleted='1' WHERE address_id ='" . $regID . "'")
            or die(mysql_error());
        if ($where == "inactivatedaddress") {
            mysql_query("UPDATE Accenta.AdressListor SET Raderad ='1' WHERE ProjektAdresserID ='" . $projid . "'")
                or die(mysql_error());

            $query2 = "INSERT INTO Regencia.Address_change_log 
                              ( source, log_time, user_id, address_id, type_id, function ) VALUES 
                              ('9999', '" . date('Y-m-d H:i:s') . "', 
                              '" . $_SESSION['user_id'] . "', '" . $regID . "',
                              '" . $this->remove_address_type . "', 'isCeased' );";
            $result2 = mysql_query($query2);

            if ($close == 1) {
                return "<script>javascript:$.php(url,{'act':'inactivatedAddresses'});</script>";
            } else {
                return "<script>javascript:$.php(url,{'act':'inactivatedAddresses'});</script>";
            }
        }
        if ($where == "clicknewaddress") {
            if ($exist == 1) {
                mysql_query("UPDATE Accenta.AdressListor SET Raderad ='1' WHERE ProjektAdresserID ='" . $projid . "'")
                    or die(mysql_error());
                mysql_query("UPDATE Accenta.AdressListor SET RegenciaAdressId = '$regID' WHERE ProjektAdresserID ='" . $projid . "'")
                    or die(mysql_error());
                $query2 = "INSERT INTO Regencia.Address_change_log 
                              ( source, log_time, user_id, address_id, type_id, function ) VALUES 
                              ('9999', '" . date('Y-m-d H:i:s') . "', 
                              '" . $_SESSION['user_id'] . "', '" . $regID . "',
                              '" . $this->remove_address_type . "', 'isCeased' );";
                $result = mysql_query($query2);
            }
            if ($close == 1) {
                return "<script>alert('You have marked the address as Ceased');self.close();</script>";
            } else {
                return "<script>alert('You have marked the address as Ceased');window.location = 'click.php';</script>";
            }
        }
    }
    //CEASED ADDRESSES functionS STOP


    public function DoubletsRemover($userid)
    {
        $i = 1;
        $this->connect();
        $districts = $this->getAllowedDistricts($userid);
        if ($districts == "") {
            $sql = "SELECT * FROM Regencia.Address WHERE deleted='0' ORDER BY address_id DESC;";
        } else {
            $sql = "SELECT * FROM Regencia.Address WHERE deleted='0' AND $districts";
        }
        $res = mysql_query($sql);
        $r = "<table border=1 cellspacing=0 style='float:left;'>";
        $r .= "<tr><td><b>Specifications</b></td></tr>";
        $r .= "<tr><td>address_id</td></tr>";
        $r .= "<tr><td>name</td></tr>";
        $r .= "<tr><td>marketing_name</td></tr>";
        $r .= "<tr><td>co_address</td></tr>";
        $r .= "<tr><td>box_address1</td></tr>";
        $r .= "<tr><td>box_address2</td></tr>";
        $r .= "<tr><td>box_postal_code</td></tr>";
        $r .= "<tr><td>visit_address1</td></tr>";
        $r .= "<tr><td>visit_address2</td></tr>";
        $r .= "<tr><td>visit_postal_code</td></tr>";
        $r .= "<tr><td>county_id</td></tr>";
        $r .= "<tr><td>district_id</td></tr>";
        $r .= "<tr><td>phone1</td></tr>";
        $r .= "<tr><td>phone2</td></tr>";
        $r .= "<tr><td>email</td></tr>";
        $r .= "<tr><td>website</td></tr>";
        $r .= "<tr><td>org_nr</td></tr>";
        $r .= "<tr><td>deleted</td></tr>";
        $r .= "<tr><td>Options</td></tr>";
        $r .= "</table>";

        while ($row = mysql_fetch_array($res)) {
            $sql1 = "SELECT * FROM Regencia.Address WHERE phone1 = '" . $row['phone1'] . "' AND org_nr='" . $row['org_nr'] . "' AND deleted = '0' LIMIT 100;";
            $res1 = mysql_query($sql1);
            if (($g = mysql_num_rows($res1)) > 1) {
                while ($row1 = mysql_fetch_array($res1)) {
                    $r .= "<table id='AddressTable" . $row1['address_id'] . "' border=1 style='float:left;' cellspacing=0>";
                    $r .= "<tr><td><b>Address " . $i++ . "</b></td></tr>";
                    $r .= "<tr><td>" . utf8_decode($row1['address_id']) . "&nbsp;</td></tr>";
                    $r .= "<tr><td>" . utf8_encode($row1['name']) . "&nbsp;</td></tr>";
                    $r .= "<tr><td>" . utf8_encode($row1['marketing_name']) . "&nbsp;</td></tr>";
                    $r .= "<tr><td>" . utf8_encode($row1['co_address']) . "&nbsp;</td></tr>";
                    $r .= "<tr><td>" . utf8_encode($row1['box_address1']) . "&nbsp;</td></tr>";
                    $r .= "<tr><td>" . utf8_encode($row1['box_address2']) . "&nbsp;</td></tr>";
                    $r .= "<tr><td>" . utf8_encode($row1['box_postal_code']) . "&nbsp;</td></tr>";
                    $r .= "<tr><td>" . utf8_encode($row1['visit_address1']) . "&nbsp;</td></tr>";
                    $r .= "<tr><td>" . utf8_encode($row1['visit_address2']) . "&nbsp;</td></tr>";
                    $r .= "<tr><td>" . utf8_encode($row1['visit_postal_code']) . "&nbsp;</td></tr>";
                    $r .= "<tr><td>" . utf8_encode($row1['county_id']) . "&nbsp;</td></tr>";
                    $r .= "<tr><td>" . utf8_encode($row1['district_id']) . "&nbsp;</td></tr>";
                    $r .= "<tr><td>" . utf8_encode($row1['phone1']) . "&nbsp;</td></tr>";
                    $r .= "<tr><td>" . utf8_encode($row1['phone2']) . "&nbsp;</td></tr>";
                    $r .= "<tr><td>" . utf8_encode($row1['email']) . "&nbsp;</td></tr>";
                    $r .= "<tr><td>" . utf8_encode($row1['website']) . "&nbsp;</td></tr>";
                    $r .= "<tr><td>" . utf8_encode($row1['org_nr']) . "&nbsp;</td></tr>";
                    $r .= "<tr><td id='Deleted" . $i . "'>" . utf8_encode($this->DeleteValueToStr($row1['deleted'])) . "&nbsp;</td></tr>";
                    $r .= "<tr><td height=23>
						<input id='ADRSID' class='buttons' type='button' value='Delete' onclick=\"buttonClickDisable('" . $i . "');document.getElementById('AddressTable" . $row1['address_id'] . "').style.background='#FF0000';javascript:$.php(url,{'act':'DeleteMarked','id':'" . utf8_decode($row1['address_id']) . "'});return false;\" />
						<input type='button' value='Restore' onclick=\"deleteOldCookie('" . $i . "');buttonDisableCheck();document.getElementById('AddressTable" . $row1['address_id'] . "').style.background='';javascript:$.php(url,{'act':'UnDeleteMarked','id':'" . utf8_decode($row1['address_id']) . "'});return false;\" />
						</td>";
                    $r .= "</table>";
                }
                $r .= "<input type='button' onclick=\"delete_cookie('buttonClicks');javascript:$.php(url,{'act':'DoubletsRemover'});return false;\" value='Next Doublet' />";
                return $r;
                mysql_close();
                exit;
            }
        }
    }
    private function DeleteValueToStr($value)
    {
        switch ($value) {
            case '0':
                return "NO";
                break;
            case '1':
                return "YES";
                break;
        }
    }

    public function getAllowedDistricts($userid)
    {
        $this->connect();
        $sql = "SELECT allowed_districts FROM Regencia.Address_users WHERE user_id = '" . $userid . "';";
        $res = mysql_query($sql);
        $districts = mysql_fetch_assoc($res);
        if ($districts['allowed_districts'] == "") {
            return "";
        }
        $k = preg_split("/,/", $districts['allowed_districts']);
        $g = count($k);
        $r = "district_id='" . $k[0] . "'";
        for ($i = 1; $i < $g; $i++) {
            $r .= " OR district_id='" . $k[$i] . "'";
        }
        $r .= "";
        return $r;
    }

    public function deleteAddress($address_id)
    {
        if ($address_id == '') {
            return "The Address_id is not set \n";
        }
        $this->connect();
        $res = mysql_query("SELECT deleted FROM Regencia.Address WHERE address_id = '" . $address_id . "';");
        $deleted = mysql_fetch_array($res);
        if ($deleted[0] == '1') {
            return "<script>alert('This address is allready deleted!');</script>";
        } else {
            $result = mysql_query("UPDATE Regencia.Address SET deleted = '1' WHERE address_id='" . $address_id . "';");
            if (!$result) {
                return "Error! " . mysql_error() . " in deleteAddress \n";
            } else {

                return "<script language='javascript' type='text/javascript'>buttonDisableCheck();</script>";
            }
        }
    }
    public function UndeleteAddress($address_id)
    {
        if ($address_id == '') {
            return "The Address_id is not set \n";
        }
        $this->connect();
        $res = mysql_query("SELECT deleted FROM Regencia.Address WHERE address_id = '" . $address_id . "';");
        $deleted = mysql_fetch_array($res);
        if ($deleted[0] == '0') {
            return "<script>alert('This address is allready restored!');</script>";
        } else {
            $result = mysql_query("UPDATE Regencia.Address SET deleted = '0' WHERE address_id='" . $address_id . "';");
            if (!$result) {

                return "Error! " . mysql_error() . " in deleteAddress \n";
            } else {

                return "";
            }
        }
    }

    public function getSourceReq($source)
    {
        $this->connect();
        $sql = "SELECT * FROM Sources WHERE source_id = '" . $source . "';";
        $res = mysql_query($sql);
        $req = mysql_fetch_array($res);
        $r = "";
        $count = count($req);
        $count = $count / 2;

        for ($i = 0; $i < $count; $i++) {
            $result = mysql_query("SELECT * FROM Sources;");
            $re = mysql_fetch_field($result, $i);
            //$re->name."-".
            $r .= " " . $req[$i];
        }
        return $r;
    }

    public function checkIfNeeded(
        $source,
        $company,
        $address,
        $postalcode,
        $phone,
        $phone2,
        $email,
        $website,
        $omsattning,
        $antalAnstallda,
        $verksamhet,
        $orgnr,
        $registreringsdatum,
        $omsattningsar
    ) {
        $this->connect();
        $sql = "SELECT req_name, req_address, req_postalcode, req_country, req_phone, req_mobile, req_email, req_website, req_turnover, req_turnover_year, req_employees, req_description, req_org_nr, req_year_of_reg FROM Regencia.Sources WHERE source_id = '" . $source . "';";
        $res = mysql_query($sql);
        $notcomplete = 0;
        $complete = 1;
        $r = "";
        $row = mysql_fetch_array($res);
        $count = count($row) / 2;
        for ($i = 0; $i < $count; $i++) {
            $result = mysql_query($sql);
            $re = mysql_fetch_field($result, $i);
            if ($row[$i] == 1) {
                $col = $re->name;
                switch ($col) {
                    case 'req_name':
                        if ($company == "") {
                            return $notcomplete;
                        }
                        break;
                    case 'req_address':
                        if ($address == "") {
                            return $notcomplete;
                        }
                        break;
                    case 'req_postalcode':
                        if ($postalcode == "") {
                            return $notcomplete;
                        }
                        break;
                    case 'req_phone':
                        if ($phone == "") {
                            return $notcomplete;
                        }
                        break;
                    case 'req_mobile':
                        if ($phone2 == "") {
                            return $notcomplete;
                        }
                        break;
                    case 'req_email':
                        if ($email == "") {
                            return $notcomplete;
                        }
                        break;
                    case 'req_website':
                        if ($website == "") {
                            return $notcomplete;
                        }
                        break;
                    case 'req_turnover':
                        if ($omsattning == "") {
                            return $notcomplete;
                        }
                        break;
                    case 'req_employees':
                        if ($antalAnstallda == "") {
                            return $notcomplete;
                        }
                        break;
                    case 'req_description':
                        if ($verksamhet == "") {
                            return $notcomplete;
                        }
                        break;
                    case 'req_org_nr':
                        if ($orgnr == "") {
                            return $notcomplete;
                        }
                        break;
                    case 'req_year_of_reg':
                        if ($registreringsdatum == "") {
                            return $notcomplete;
                        }
                        break;
                    case 'req_turnover_year':
                        if ($omsattningsar == "") {
                            return $notcomplete;
                        }
                        break;
                }
            }
        }
        return $complete;
    }

    public function workWithTask($uid)
    {
        $this->connect();
        $sql = "SELECT level FROM Regencia.Address_users WHERE user_id = '" . $uid . "';";
        $res = mysql_query($sql);
        $r = "<center>";
        while ($level = mysql_fetch_array($res)) {
            $levels = preg_split("/,/", $level[0]);
            $numLevels = count($levels);
            for ($i = 0; $i < $numLevels; $i++) {
                $r .= "<a href='#' id='" . $levels[$i] . "' onclick=\"javascript:$.php(url,{'act':'useTask','task':this.id});return false;\">" . $levels[$i] . "</a><br />";
            }
        }
        $r .= "</center>";
        return $r;
    }
    public function useThisTask($tid)
    {
        $_SESSION['level'] = $tid;
        return "<script type='text/javascript'>window.location.reload();</script>";
    }
    public function checkAllowedPostals($postal, $uid)
    {
        $postal2 = $this->fix($postal);
        $districts = $this->getAllowedDistricts($uid);
        if ($districts == "") {
            return 1;
        } else {
            $sql = "SELECT * FROM Regencia.District WHERE $districts AND postal_code = '$postal2';";
            $result = mysql_query($sql);
            $ray = mysql_num_rows($result);
            if ($ray == 0) {
                return 0;
            } else {
                return 1;
            }
            //return $sql;
        }
    }

    public function getUserGroups()
    {
        $this->connect();
        $sql = "SELECT * FROM Regencia.user_levels u ORDER BY user_level ASC;";
        $res = mysql_query($sql);
        $r = "";
        while ($row = mysql_fetch_array($res)) {
            $r .= $row['user_level'] . " - " . $row['description'] . "<br />";
        }
        return $r;
    }

    public function userLevelMenu($ulvl, $user)
    {
        $this->connect();
        $sql = "SELECT * FROM Regencia.user_levels WHERE user_level='" . $ulvl . "';";
        $res = mysql_query($sql);
        $r = "<ul>";
        $r .= "<li><a href='#' class='tab' id='alla'>&nbsp;</a></li>";
        $r .= "<li><a href='#' class='tab' id='sundsvall' onmouseover=\"document.getElementById('alla').style.backgroundImage='url(images/vansterknapp_GRA.png)';\" onmouseout=\"document.getElementById('alla').style.backgroundImage='url(images/vansterknapp.png)';\" onclick=\"javascript:$.php(url,{'act':'invstats'});return false;\">" . $user . " stats</a></li>";
        $r .= "<li><a href='#' class='tab' id='bangkok' onclick=\"chpwd();\">Change Password</a></li>";
        $row = mysql_fetch_array($res);
        $count = count($row) / 2;
        for ($i = 0; $i < $count; $i++) {
            $result = mysql_query($sql);
            $re = mysql_fetch_field($result, $i);
            if ($row[$i] == 1) {
                $col = $re->name;
                switch ($col) {
                    case 'add_address':
                        $r .= "<li><a href='click.php' class='tab' id='bergeforsen'>Add address</a></li>";
                        break;
                    case 'tot_user_stat':
                        $r .= "<li><a href='user_stats.php' class='tab' id='tibro'>Total User Stats</a></li>";
                        break;
                    case 'rem_douplets':
                        $r .= "<li><a href='#' class='tab' id='tradenom' onclick=\"javascript:$.php(url,{'act':'DoubletsRemover'});return false;\">Remove Doublets</a></li>";
                        break;

                    case 'users':
                        $r .= "<li><a href='#' class='tab' id='tradenom' onclick=\"javascript:$.php(url,{'act':'users'});return false;\">Users</a></li>";
                        break;
                    case 'admin':
                        $r .= "<li><a href='message.php' class='tab' id='vaasa'>Admin</a></li>";
                        break;
                }
            }
        }
        $r .= "<li><a href='index.php?logout' class='tab' id='&Ouml;stersund' onmouseover=\"this.style.backgroundImage='url(images/hogerknapp_GRA.png)';\" onmouseout=\"this.style.backgroundImage='url(images/hogerknapp.png)';\" style='background-image:url(images/hogerknapp.png);border:0px;background-repeat:no-repeat;'>&nbsp;&nbsp;Logout&nbsp;&nbsp;</a></li>";
        $r .= "</ul>";
        return $r;
    }

    private function getSourceFromAddressID($address_id)
    {
        $this->connect();
        $sql = "SELECT source FROM Regencia.Address_change_log A WHERE address_id = '$address_id' AND user_id = '" . $_SESSION['user_id'] . "' ORDER BY log_time DESC LIMIT 1;";
        $res = mysql_query($sql);
        $row = mysql_fetch_array($res);

        return $row[0];
    }

    public function fixedBugAddress($accentaID, $regenciaID, $user_id)
    {
        $this->connect();
        $sql2 = "SELECT * FROM Regencia.Address WHERE address_id = '$regenciaID';";
        $res = mysql_query($sql2);
        $r = "";

        while ($row = mysql_fetch_array($res)) {
            $arr = array();
            $arr[0] = $row['phone1'];
            $arr[1] = $row['phone2'];
            $arr[2] = $row['mobile_phone'];

            $dist = $this->getDistrictFromID($row['district_id'], $row['box_postal_code']);
            $postal = str_replace("SE", "", $row['box_postal_code']);
            $updSQL = "UPDATE Accenta.AdressListor SET Färg = '0' ,
					`Företag` = '" . $row['name'] . "', 
					GatuAdress = '" . $row['box_address1'] . "',
					Postnr = '$postal',
					PostOrt = '$dist',
					TelefonFormaterad = '" . $this->fixaNummer($arr) . "',
					Orgnr = '" . $row['org_nr'] . "',
					Epost = '" . $row['email'] . "',
					Hemsida = '" . $row['website'] . "',
					Senastestatus = '0',
					" . $this->fixedBugSQL($regenciaID) . "
					WHERE ProjektAdresserID = '$accentaID';";
            $result = mysql_query($updSQL);
            if (!$result) {
                return "Error! " . mysql_error() . " in fixedBudAddress\n";
            }
            $query = "INSERT INTO Regencia.Address_change_log 
                              ( source, log_time, user_id, address_id, type_id ) VALUES 
                              ('" . utf8_decode($this->getSourceFromAddressID($regenciaID)) . "', '" . date('Y-m-d H:i:s') . "', 
                              '" . $user_id . "', '" . $regenciaID . "',
                              '" . $this->fixed_bug_address_type . "' );";
            $result = mysql_query($query);
            $this->rams_debug($user_id, "RAMS", "in fixedBugAddress", $query, $address_id);
            if (!$result) {
                return "Error! " . mysql_error() . " in fixedBugAddress  INSERT INTO Address_change_log\n";
            }
        }
        return "You have successfully <b>fixed</b> the address!<script>javascript:$.php(url,{'act':'bugReport'});</script>";
    }

    public function deletBugAddress($aid, $rid)
    {
        $this->connect();
        $sql = "UPDATE Accenta.AdressListor SET Raderad = 1 WHERE ProjektAdresserID = '$aid';";
        $res = mysql_query($sql);
        if (!$res) {
            return "Error! " . mysql_error() . " in fixedBudAddress on line 1592\n";
        }
        $sql2 = "UPDATE Regencia.Address SET deleted = 1 WHERE address_id = '$rid';";
        $res2 = mysql_query($sql2);
        if (!$res2) {
            return "Error! " . mysql_error() . " in fixedBudAddress on line 1597\n";
        }
        $query = "INSERT INTO Regencia.Address_change_log 
                              ( source, log_time, user_id, address_id, type_id ) VALUES 
                              ('" . utf8_decode($this->getSourceFromAddressID($rid)) . "', '" . date('Y-m-d H:i:s') . "', 
                              '" . $_SESSION['user_id'] . "', '" . $rid . "',
                              '" . $this->remove_address_type . "' );";
        $result = mysql_query($query);
        $this->rams_debug($user_id, "RAMS", "in fixedBugAddress", $query, $address_id);
        if (!$result) {
            return "Error! " . mysql_error() . " in deletBugAddres  INSERT INTO Address_change_log\n";
        }
        return "<font>You have successfully <b>ignored</b> the address!</font><script>javascript:$.php(url,{'act':'bugReport'});</script>";
    }
    public function noBugAddress($aid)
    {
        $this->connect();
        $sql = "UPDATE Accenta.AdressListor SET Färg = 'ÅT' WHERE ProjektAdresserID = '$aid';";
        $res = mysql_query($sql);
        if (!$res) {
            return "Error! " . mysql_error() . " in noBugAddress on line 1606\n";
        }
        return "You have successfully <b>marked</b> the address as <b>No bug!</b>";
    }
    public function fixedBugSQL($aid)
    {
        $this->connect();
        $sql = "SELECT A.turnover,A.description,A.number_of_employees,B.source,C.source_name FROM Regencia.Company_facts A
			INNER JOIN Regencia.Address_change_log B ON B.address_id = A.address_id
			INNER JOIN Regencia.Sources C ON C.source_id = B.source
			WHERE A.address_id = '$aid' ORDER BY log_time DESC LIMIT 1;";
        $res = mysql_query($sql);
        $r = "";
        while ($row = mysql_fetch_array($res)) {
            $r .= "`Omsättning` = '" . $row['turnover'] . "',";
            $r .= "`AntalAnställda` = '" . $row['number_of_employees'] . "',";
            $r .= "Bransch = '" . $row['description'] . "',";
            $r .= "`KällaID` = '" . $row['source'] . "',";
            $r .= "`Källa` = '" . $row['source_name'] . "'";
        }
        return $r;
    }

    public function getBugAddresses($uid)
    {
        $this->connect();
        $sql = "SELECT salesman_id FROM Regencia.Address_users WHERE user_id = '$uid';";
        $res = mysql_query($sql);
        $sid = mysql_fetch_array($res);
        $Countsql = "SELECT A.* FROM Accenta.AdressListor A
				INNER JOIN Accenta.ProjektOversiktInternet P ON (A.Projektnr = P.Projektnr)
				WHERE A.Färg = '8' AND A.Anstnr = '" . $sid[0] . "' AND A.RegenciaAdressId !=0 AND
				A.Raderad = 0 AND
				UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(P.ProjektSkapatDatum) <= (60*24*3600)
				AND P.KlarrapporteratDatum IS NULL
				ORDER BY P.ProjektSkapatDatum ASC;";
        $countQuery = mysql_query($Countsql);
        $countRow = mysql_num_rows($countQuery);

        if ($countRow != 0) {
            $sql2 = "SELECT A.* FROM Accenta.AdressListor A
					INNER JOIN Accenta.ProjektOversiktInternet P ON (A.Projektnr = P.Projektnr)
					WHERE A.Färg = '8' AND A.Anstnr = '" . $sid[0] . "' AND A.RegenciaAdressId !=0 AND
					A.Raderad = 0 AND
					UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(P.ProjektSkapatDatum) <= (60*24*3600)
					AND P.KlarrapporteratDatum IS NULL
					ORDER BY P.ProjektSkapatDatum ASC LIMIT 1;";
        } else {
            $csql = "SELECT A.* FROM Accenta.AdressListor A
					INNER JOIN Accenta.ProjektOversiktInternet P ON (A.Projektnr = P.Projektnr)
					WHERE A.Färg = '8' AND A.RegenciaAdressId !=0 AND
					A.Raderad = 0 AND
					UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(P.ProjektSkapatDatum) <= (60*24*3600)
					AND P.KlarrapporteratDatum IS NULL
					ORDER BY P.ProjektSkapatDatum ASC;";
            $countQuery2 = mysql_query($csql);
            $countRow2 = mysql_num_rows($countQuery2);
            if ($countQuery2 < 1) {
                $sql2 = "SELECT A.* FROM Accenta.AdressListor A
					INNER JOIN Accenta.ProjektOversiktInternet P ON (A.Projektnr = P.Projektnr)
					WHERE A.Färg = '8' AND A.RegenciaAdressId !=0 AND
					A.Raderad = 0 LIMIT 1;";
            } else {
                $sql2 = "SELECT A.* FROM Accenta.AdressListor A
					INNER JOIN Accenta.ProjektOversiktInternet P ON (A.Projektnr = P.Projektnr)
					WHERE A.Färg = '8' AND A.RegenciaAdressId !=0 AND
					A.Raderad = 0 AND
					UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(P.ProjektSkapatDatum) <= (60*24*3600)
					AND P.KlarrapporteratDatum IS NULL
					ORDER BY P.ProjektSkapatDatum ASC LIMIT 1;";
            }
        }

        $res2 = mysql_query($sql2);
        $r = "<center><table border=0>";
        if (mysql_num_rows($res2) == 0) {
            return "<center><h1>There are no Bug reported addresser!</h1></center>";
        }
        while ($row = mysql_fetch_array($res2)) {
            $r .= "<tr><td><b>Company: </b></td><td>" . utf8_encode($row['Företag']) . "</td></tr>";
            $r .= "<tr><td><b>Phone: </b></td><td>" . utf8_encode($row['TelefonFormaterad']) . "</td></tr>";
            $r .= "<tr><td><b>Orgnr: </b></td><td>" . utf8_encode($row['Orgnr']) . "</td></tr>";
            $r .= "<tr><td><input type='button' onclick=\"fixDelMSG(" . $row['ProjektAdresserID'] . "," . $row['RegenciaAdressId'] . ",'fix');\" id='fixedBUG' value='Fixed' />";
            $r .= "<input type='button' onclick=\"fixDelMSG(" . $row['ProjektAdresserID'] . "," . $row['RegenciaAdressId'] . ",'delete');\" id='deleteBUG' value='Ignore' />";
            //$r.="<input type='button' onclick=\"fixDelMSG(".$row['ProjektAdresserID'].",0,'no bug');\" id='noBUG' value='No bug' />";
            $r .= "</td></tr>";
        }
        $r .= "</table></center>";
        return $r;
    }

    public function getDistrictFromID($did, $postal)
    {
        $this->connect();
        $sql = "SELECT district_name FROM Regencia.District WHERE district_id = '$did' AND postal_code = '$postal' LIMIT 1;";
        $res = mysql_query($sql);
        $dis = mysql_fetch_array($res);
        return $dis['district_name'];
    }

    public function addAddressFromAccentaToRegencia($projid)
    {
        $this->connect();
        $sql = "SELECT * FROM Accenta.AdressListor WHERE ProjektAdresserID = '$projid';";
        $res = mysql_query($sql);
        $row = mysql_fetch_array($res);

        if (preg_match('/,/', $row['TelefonFormaterad'])) {
            $pnum = preg_split('/,/', $row['TelefonFormaterad']);
            $phone1 = $this->fixIT($pnum[0]);
            $phone2 = $this->fixIT($pnum[1]);
        } else {
            $phone1 = $this->fixIT($row['TelefonFormaterad']);
            $phone2 = '';
        }
        if (strlen($row['KällaID']) == 0) {
            $source = 9999;
        }
        if ($row['Omsättning'] == "Okänt") {
            $turn_over = 0;
        } else {
            $turn_over = $row['Omsättning'];
        }
        if ($row['Branch'] == "Okänt") {
            $branch = "";
        } else {
            $branch = $row['Branch'];
        }
        $r = "";
        $r .= "<script>javascript:$.php(url,{'act':'post_address','company':'" . utf8_encode($row['Företag']) . "','kalla':'" . $source . "','address':'" . utf8_encode($row['GatuAdress']) . "','postalcode':'" . $row['Landskod'] . $row['Postnr'] . "','phone':'" . $phone1 . "','phone2':'" . $phone2 . "','email':'" . $row['Epost'] . "','website':'" . $row['Hemsida'] . "','omsattning':'" . $turn_over . "','antalAnstallda':'" . $row['AntalAnställda'] . "','verksamhet':'" . $branch . "','orgnr':'" . $row['Orgnr'] . "','registreringsdatum':'','omsattningsar':'','projid':'$projid'});</script>";
        return $r;
    }
    //this one takes an array of phone numbers and puts them in one string and seperates them with comma
    public function fixaNummer($arr)
    {
        $r = "";
        $arr = array_filter($arr);
        $totElements = count($arr);
        $i = 0;
        foreach ($arr as $val) {
            $i++;
            if ($val == "" || strlen($val) == 0) {
                $r .= "";
            } else {
                if ($i == $totElements) {
                    $r .= $this->format_number($val);
                } else {
                    $r .= $this->format_number($val) . ", ";
                }
            }
        }
        return $r;
    }

    // This one is stolen from Turbo (thanks to Joakim Bülow!)
    public function format_number($number)
    {

        $number = preg_replace('/\D/', "", $number);
        $tva = array(1 => "08");
        $tre = array(1 => "010", "011", "013", "016", "018", "019", "020", "021", "023", "026", "031", "033", "035", "036", "040", "042", "044", "046", "054", "060", "063", "070", "071", "073", "074", "075", "076", "077", "078", "090", "099");
        $fyra = array(1 => "0100", "0120", "0121", "0122", "0123", "0125", "0140", "0141", "0142", "0143", "0144", "0150", "0151", "0152", "0155", "0156", "0157", "0158", "0159", "0171", "0173", "0174", "0175", "0176", "0220", "0221", "0222", "0223", "0224", "0225", "0226", "0227", "0240", "0241", "0243", "0246", "0247", "0248", "0250", "0251", "0252", "0253", "0258", "0270", "0271", "0278", "0280", "0281", "0290", "0291", "0292", "0293", "0294", "0295", "0297", "0300", "0301", "0302", "0303", "0304", "0320", "0321", "0322", "0325", "0340", "0345", "0346", "0370", "0371", "0372", "0376", "0378", "0379", "0380", "0381", "0382", "0383", "0390", "0392", "0393", "0394", "0400", "0410", "0411", "0413", "0414", "0415", "0416", "0417", "0418", "0430", "0431", "0433", "0435", "0451", "0454", "0455", "0456", "0457", "0459", "0470", "0471", "0472", "0474", "0476", "0477", "0478", "0479", "0480", "0481", "0485", "0486", "0490", "0491", "0492", "0493", "0494", "0495", "0496", "0498", "0499", "0500", "0501", "0502", "0503", "0504", "0505", "0506", "0510", "0511", "0512", "0513", "0514", "0515", "0518", "0519", "0520", "0521", "0522", "0523", "0524", "0525", "0526", "0528", "0530", "0531", "0532", "0533", "0534", "0550", "0551", "0552", "0553", "0554", "0555", "0560", "0563", "0564", "0565", "0570", "0571", "0573", "0580", "0581", "0582", "0583", "0584", "0585", "0586", "0587", "0589", "0590", "0591", "0600", "0611", "0612", "0613", "0620", "0621", "0622", "0623", "0624", "0640", "0642", "0643", "0644", "0645", "0647", "0649", "0650", "0651", "0652", "0653", "0655", "0656", "0657", "0660", "0661", "0662", "0663", "0670", "0671", "0672", "0673", "0674", "0675", "0676", "0680", "0682", "0684", "0687", "0690", "0691", "0692", "0693", "0695", "0696", "0800", "0900", "0910", "0911", "0912", "0913", "0914", "0915", "0916", "0918", "0920", "0921", "0922", "0923", "0924", "0925", "0926", "0927", "0928", "0929", "0930", "0932", "0933", "0934", "0935", "0939", "0940", "0941", "0942", "0943", "0944", "0946", "0950", "0951", "0952", "0953", "0954", "0958", "0960", "0961", "0967", "0969", "0970", "0971", "0973", "0975", "0976", "0977", "0978", "0980", "0981");

        $number = str_replace(" ", "", $number);
        $number = str_replace("-", "", $number);

        if (strlen($number) > 5 && $number[0] == '0') {
            $rikt = 0;
            $nummer = 0;

            if (array_search(substr($number, 0, 4), $fyra) != FALSE) {
                $rikt = substr($number, 0, 4);
                $nummer = substr($number, 4);
            } elseif (array_search(substr($number, 0, 3), $tre) != FALSE) {
                $rikt = substr($number, 0, 3);
                $nummer = substr($number, 3);
            } elseif (array_search(substr($number, 0, 2), $tva) != FALSE) {
                $rikt = substr($number, 0, 2);
                $nummer = substr($number, 2);
            }

            if ($rikt != 0) {
                switch (strlen($nummer)) {
                    case 5:
                        $delar = preg_split('/^(\d{3})(\d{2})$/', $nummer, -1, PREG_SPLIT_DELIM_CAPTURE);
                        $str = "$rikt-$delar[1] $delar[2]";
                        break;
                    case 6:
                        $delar = preg_split('/^(\d{2})(\d{2})(\d{2})$/', $nummer, -1, PREG_SPLIT_DELIM_CAPTURE);
                        $str = "$rikt-$delar[1] $delar[2] $delar[3]";
                        break;
                    case 7:
                        $delar = preg_split('/^(\d{3})(\d{2})(\d{2})$/', $nummer, -1, PREG_SPLIT_DELIM_CAPTURE);
                        $str = "$rikt-$delar[1] $delar[2] $delar[3]";
                        break;
                    case 8:
                        $delar = preg_split('/^(\d{3})(\d{3})(\d{2})$/', $nummer, -1, PREG_SPLIT_DELIM_CAPTURE);
                        $str = "$rikt-$delar[1] $delar[2] $delar[3]";
                        break;
                }
                return $str;
            }
        }
        return $number;
    }

    private function typeIDcheck($tid)
    {
        $this->connect();
        $sql = "SELECT description FROM Regencia.Address_change_type WHERE type_id = '$tid';";
        $q = mysql_query($sql);
        $res = mysql_result($q, 0);
        return $res;
    }

    private function isavailable($input)
    {
        if (strlen($input) == 0 || $input == NULL) {
            return "N/A";
        } else {
            return $input;
        }
    }

    private function getSourceName($sid)
    {
        $this->connect();
        $sql = "SELECT source_name FROM Regencia.Sources WHERE source_id = '$sid';";
        $q = mysql_query($sql);
        $res = mysql_result($q, 0);
        return $res;
    }

    private function formatOrgNR($orgnr)
    {
        /*
	 * This function is pretty self explanatory.
	 * It takes one variable that is a string not more than 10 chars
	 * and it format it to the proper format for an swedish organisation number
	 * 
	 */
        $orgnr = preg_replace("/\D/", "", $orgnr);
        $orgnrL = substr($orgnr, 6);
        $orgnrR = substr($orgnr, 0, 6);
        $orgnr = $orgnrR . "-" . $orgnrL;
        return $orgnr;
    }

    public function addressLive($upornew = 9999, $limit = 50, $user_id = 0)
    {
        /* This function shows the newest changes on the addresses in the Regencia Database
	 * 
	 * if the $upornew variable is set to 1(ONE) the function will grab just the newaddresses
	 * if it is set to 2(TWO) The function will grab just the updated ones
	 * if it is set to 9999 the function will grab both new and updated addresses
	 * 
	 * if the variable $limit has not been set, the default value is 50, 
	 * that means that the function will print out 50 addresses by default if not set to another number.
	 */
        if (is_numeric($upornew) < 1) {
            $upornew = 9999;
        }
        if (is_numeric($limit) < 1) {
            $limit = 50;
        }
        if (is_numeric($user_id) < 1) {
            $user_id = 0;
        }
        $this->connect();
        switch ($upornew) {
            case '1':
                if ($user_id > 0) {
                    $sql = "SELECT * FROM Regencia.Address_change_log A INNER JOIN Regencia.Address B ON A.address_id = B.address_id INNER JOIN Regencia.Company_facts C ON A.address_id = C.address_id WHERE type_id = '1' AND user_id='$user_id' ORDER BY log_time DESC LIMIT $limit;";
                } else {
                    $sql = "SELECT * FROM Regencia.Address_change_log A INNER JOIN Regencia.Address B ON A.address_id = B.address_id INNER JOIN Regencia.Company_facts C ON A.address_id = C.address_id WHERE type_id = '1' ORDER BY log_time DESC LIMIT $limit;";
                }
                break;

            case '2':
                if ($user_id > 0) {
                    $sql = "SELECT * FROM Regencia.Address_change_log A INNER JOIN Regencia.Address B ON A.address_id = B.address_id INNER JOIN Regencia.Company_facts C ON A.address_id = C.address_id WHERE type_id = '2' AND user_id='$user_id' ORDER BY log_time DESC LIMIT $limit;";
                } else {
                    $sql = "SELECT * FROM Regencia.Address_change_log A INNER JOIN Regencia.Address B ON A.address_id = B.address_id INNER JOIN Regencia.Company_facts C ON A.address_id = C.address_id WHERE type_id = '2' ORDER BY log_time DESC LIMIT $limit;";
                }
                break;

            case '9999':
                if ($user_id > 0) {
                    $sql = "SELECT * FROM Regencia.Address_change_log A INNER JOIN Regencia.Address B ON A.address_id = B.address_id INNER JOIN Regencia.Company_facts C ON A.address_id = C.address_id WHERE user_id='$user_id' ORDER BY log_time DESC LIMIT $limit;";
                } else {
                    $sql = "SELECT * FROM Regencia.Address_change_log A INNER JOIN Regencia.Address B ON A.address_id = B.address_id INNER JOIN Regencia.Company_facts C ON A.address_id = C.address_id ORDER BY log_time DESC LIMIT $limit;";
                }
                break;
        }
        $q = mysql_query($sql);
        $r = "<table border=1 style='width:auto;margin:auto;background:url(images/filler.png);'>";
        $r .= "<tr>";
        $r .= "<td><b>Address ID</b></td>";
        $r .= "<td><b>Company</b></td>";
        $r .= "<td><b>Status</b></td>";
        $r .= "<td><b>Log time</b></td>";
        $r .= "<td><b>User/user id</b></td>";
        $r .= "</tr>";
        $i = 0;
        while ($row = mysql_fetch_array($q)) {
            $i++;
            $r .= "<tr onMouseover=\"this.bgColor='#FFFFFF'\"onMouseout=\"this.bgColor=''\">";
            $r .= "<td>" . $row['address_id'] . "</td>";
            $r .= "<td><a href='#' style='display:block;' onclick=\"showAddressInfo('compInfo$i');\">" . $row['name'] . "</a>
					<div id='compInfo$i' style='display:none;'>
						<ul id='2'>
							<li>Marketing name : " . $this->isavailable($row['marketing_name']) . "</li>
							<li>Co adderss : " . $this->isavailable($row['co_address']) . "</li>
							<li>Box address1 : " . $this->isavailable($row['box_address1']) . "</li>
							<li>Box address2 : " . $this->isavailable($row['box_address2']) . "</li>
							<li>Box postal code : " . $this->isavailable(utf8_decode($this->getPostal($row['box_postal_code']))) . " (" . $this->isavailable($row['box_postal_code']) . ")</li>
							<li>Visit address1 : " . $this->isavailable($row['visit_address1']) . "</li>
							<li>Visit address2 : " . $this->isavailable($row['visit_address2']) . "</li>
							<li>Visit postal code : " . $this->isavailable(utf8_decode($this->getPostal($row['visit_postal_code']))) . " (" . $this->isavailable($row['visit_postal_code']) . ")</li>
							<li>County id : " . $this->isavailable($row['county_id']) . "</li>
							<li>District id : " . $this->isavailable($this->getDistrictFromID($row['district_id'], $row['box_postal_code'])) . " (" . $this->isavailable($row['district_id']) . ")</li>
							<li>Region : " . $this->isavailable($row['region']) . "</li>
							<li>Phone1 : " . $this->isavailable($this->format_number($row['phone1'])) . "</li>
							<li>Phone2 : " . $this->isavailable($this->format_number($row['phone2'])) . "</li>
							<li>Mobile phone : " . $this->isavailable($this->format_number($row['mobile_phone'])) . "</li>
							<li>Contact : " . $this->isavailable($row['contact']) . "</li>
							<li>Email : " . $this->isavailable($row['email']) . "</li>
							<li>Website : " . $this->isavailable($row['website']) . "</li>
							<li>Org nr : " . $this->isavailable($this->formatOrgNR($row['org_nr'])) . "</li>
							<li>Turnover : " . $this->isavailable($row['turnover']) . " Tkr</li>
							<li>Turnover year : " . $this->isavailable($row['turnover_year']) . "</li>
							<li>Number of employees : " . $this->isavailable($row['number_of_employees']) . "</li>
							<li>Year of registration : " . $this->isavailable($row['year_of_registration']) . "</li>
							<li>Description : " . $this->isavailable($row['description']) . "</li>
							<li>Source : " . $this->isavailable($this->getSourceName($row['source'])) . "</li>
							<li>" . $this->isavailable($this->typeIDcheck($row['type_id'])) . " from <b>" . $this->isavailable($this->getusernames($row['user_id'])) . " (user id " . $this->isavailable($row['user_id']) . ")</b></li>
						</ul>
					</div>
				</td>";
            $r .= "<td>" . $this->isavailable($this->typeIDcheck($row['type_id'])) . "</td>";
            $r .= "<td>" . $row['log_time'] . "</td>";
            $r .= "<td>" . $this->isavailable($this->getusernames($row['user_id'])) . " (user id " . $this->isavailable($row['user_id']) . ")</td>";
            $r .= "</tr>";
        }
        $r .= "</table>";
        return $r;
    }
    //skum functions
    public function checkIfSkum($rid)
    {
        $this->connect();
        $sql = "SELECT * FROM `Accenta`.`AdressListor` WHERE RegenciaAdressId = $rid ORDER BY `Färg` ASC;";
        $q = mysql_query($sql);
        $skum = false;

        while ($row = mysql_fetch_array($q)) {
            if ($row['Färg'] == 8) {
                $skum = true;
            }
        }
        return $skum;
    }

    public function skumFixButtons()
    {
        $r = "<input type='button' name='fixed' value='Fixed' onClick=\"fixSkumPhoneCheck('" . $this->nmbrs[0] . "','" . $this->nmbrs[1] . "','" . $this->nmbrs[2] . "');\" />";
        $r .= "<input type='button' name='notFixed' value='Not Fixed' onClick=\"notFixedSkum();\" />";
        return $r;
    }

    public function updateAccentaAdresslistor($company, $address, $postalcode, $phone, $phone2, $email, $website, $omsattning, $antalAnstallda, $verksamhet, $orgnr, $kalla, $address_id, $phone3)
    {
        $this->connect();
        $tfn = array();
        $tfn[] = $this->fixIT($phone);
        $tfn[] = $this->fixIT($phone2);
        $tfn[] = $this->fixIT($phone3);

        $sql = "UPDATE `Accenta`.`AdressListor` SET" .
            " `Företag`='" . utf8_decode($this->injectioncontroll($company)) . "'," .
            " `GatuAdress`='" . utf8_decode($this->injectioncontroll($address)) . "'," .
            " `Postnr`='" . substr($this->injectioncontroll($postalcode), 2) . "'," .
            " `PostOrt`='" . utf8_decode($this->injectioncontroll($this->getPostal($postalcode))) . "'," .
            " `TelefonFormaterad`='{$this->injectioncontroll($this->fixanummer($tfn))}'," .
            " `Orgnr`='{$this->injectioncontroll($orgnr)}'," .
            " `Hemsida`='{$this->injectioncontroll($website)}'," .
            " `Källa`='{$this->injectioncontroll($this->getSourceName($kalla))}'," .
            " `Färg`='0'," .
            " `Landskod`='{$this->injectioncontroll(substr($postalcode, 0, 2))}'," .
            " `Omsättning`='{$this->injectioncontroll($omsattning)}'," .
            " `Bransch`='" . utf8_decode($this->injectioncontroll($verksamhet)) . "'," .
            " `Raderad`='0'," .
            " `SenasteStatus`='FX'," .
            " `KällaID`='$kalla'," .
            " `Epost`='$email'," .
            " `AntalAnställda`='{$this->injectioncontroll($antalAnstallda)}'" .
            " WHERE `RegenciaAdressId`={$this->injectioncontroll($address_id)};";

        //Uppdaterar addresserlistorÄNDRINGAR med aktuell information om företaget.
        $sql2 = "UPDATE Accenta.AdressListor A
					INNER JOIN Accenta.`AdressListorÄndringar` B
					ON B.ProjektAdresserID = A.ProjektAdresserID
					SET B.Företag = '" . utf8_decode($this->injectioncontroll($company)) . "',
					    B.GatuAdress = '" . utf8_decode($this->injectioncontroll($address)) . "',
					    B.Postnr = '" . substr($this->injectioncontroll($postalcode), 2) . "',
					    B.PostOrt = '" . utf8_decode($this->injectioncontroll($this->getPostal($postalcode))) . "',
					    B.TelefonFormaterad = '{$this->injectioncontroll($this->fixanummer($tfn))}',
					    B.Orgnr = '{$this->injectioncontroll($orgnr)}',
					    B.Hemsida = '{$this->injectioncontroll($website)}',
					    B.Epost = '$email'
					WHERE A.RegenciaAdressId = {$this->injectioncontroll($address_id)};";
        mysql_query($sql2);
        $result = mysql_query($sql);
        if (!$result) {
            return false;
        } else {
            return true;
        }
    }

    public function updAddressSkum(
        $user_id,
        $company,
        $address,
        $postalcode,
        $phone,
        $phone2,
        $email,
        $website,
        $omsattning,
        $antalAnstallda,
        $verksamhet,
        $orgnr = 0,
        $registreringsdatum,
        $omsattningsar,
        $kalla,
        $address_id,
        $nykommun,
        $completevalue,
        $phone3,
        $unReOrNot
    ) {
        //if($orgnr==0){
        //return "no orgnr";
        //}
        //else{
        $this->chkAddress_id($address_id, $user_id);
        if ($this->debug) {
            print "updAddress\n";
        }
        $postalcode = $this->fix($postalcode);
        $this->connect();
        $result = mysql_query("SELECT C.county_id FROM Regencia.County C WHERE C.postal_code = '" . $postalcode . "' ");
        if ($result) {
            if (mysql_num_rows($result) == 0) {
                return "Missing County ID for Postal Code $postalcode";
            } else {
                $county_id = utf8_decode(mysql_result($result, 0, $x));
            }
        } else {
            return "Query for County ID failed!";
        }

        $result = mysql_query("SELECT D.district_id FROM Regencia.District D WHERE D.postal_code = '" . $postalcode . "' ");
        if ($result) {
            if (mysql_num_rows($result) == 0) {
                return "Missing County ID for Postal Code $postalcode";
            } else {
                $district_id = utf8_decode(mysql_result($result, 0, $x));
            }
        } else {
            return "Query for District ID failed!";
        }
        $query = "UPDATE Regencia.Address SET 
								   name = '" . utf8_decode($company) . "', 
								   box_address1 = '" . utf8_decode($address) . "', 
								   box_postal_code = '" . $postalcode . "', 
								   phone1 = '" . $this->fixIT($phone) . "', 
								   phone2 = '" . $this->fixIT($phone2) . "',
								   mobile_phone = '" . $this->fixIT($phone3) . "', 
								   email = '" . utf8_decode(str_replace('mailto:', '', $email)) . "', 
								   website = '" . utf8_decode($website) . "', 
								   org_nr = '" . utf8_decode($orgnr) . "',
								   visit_postal_code = '" . $postalcode . "',
								   county_id = '" . utf8_decode($county_id) . "',
								   district_id = '" . utf8_decode($nykommun) . "',
								   complete = '" . $completevalue . "',
								   deleted = '{$unReOrNot}'
								   WHERE address_id = '" . $address_id . "';";
        $result = mysql_query($query);
        $this->rams_debug($user_id, "RAMS", "in upAddress", $query, $address_id);
        if (!$result) {
            return "Error! " . mysql_error() . " in upAddress \n";
        }
        $query1 = "UPDATE Regencia.Company_facts SET 
			                       turnover = '" . utf8_decode($omsattning) . "', 
	                               number_of_employees = '" . utf8_decode($antalAnstallda) . "', 
	                               description = '" . utf8_decode($verksamhet) . "', 
	                               year_of_registration = '" . $registreringsdatum . "', 
	                               turnover_year = '" . utf8_decode($omsattningsar) . "',
	                              
	                               org_nr = '" . utf8_decode($orgnr) . "' WHERE address_id = '" . $address_id . "';";
        $result = mysql_query($query1);
        $this->rams_debug($user_id, "RAMS", "in upAddress", $query1, $address_id);
        if (!$result) {
            return "Error! " . mysql_error() . " in upAddress \n";
        }
        $last_num = substr($query, -1);
        $query = rtrim($query, $last_num) . " turnover = '" . utf8_decode($omsattning) . "'" . $last_num;
        $query2 = "INSERT INTO Regencia.Address_change_log " .
            "( source, log_time, user_id, address_id, type_id, function, query ) " .
            "VALUES " .
            "('" . utf8_decode($kalla) . "', '" . date('Y-m-d H:i:s') . "'," .
            "'" . $user_id . "', '" . $address_id . "'," .
            "'" . $this->update_address_type . "'," .
            "'" . __FILE__ . ":" . __METHOD__ . "'," .
            "'" . preg_replace("/\'/", "\\\\'", $query) . "');";
        $result = mysql_query($query2);
        $this->rams_debug($user_id, "RAMS", "in upAddress", $query2, $address_id);
        if (!$result) {
            return "Error! " . mysql_error() . " in upAddress \n";
        } else {
            $_SESSION['kalla'] = $kalla;

            if (!$this->updateAccentaAdresslistor($company, $address, $postalcode, $phone, $phone2, $email, $website, $omsattning, $antalAnstallda, $verksamhet, $orgnr, $kalla, $address_id, $phone3)) {
                return "<script language='javascript' type='text/javascript'>alert('Address have been successfully updated');</script>";
            }
            return "<script language='javascript' type='text/javascript'>alert('Address have been successfully updated');delete_cookie('what');window.location='click.php';</script>";
        }
        //}
    }

    public function skumNotFixedInKenya($address_id, $kalla)
    {
        $this->connect();
        $sql = "UPDATE `Regencia`.`Address` SET `deleted`=2 WHERE `address_id`='$address_id';";
        $q = mysql_query($sql);
        if (!$q) {
            return "Error! " . mysql_error() . " in upAddress \n";
        }

        $query2 = "INSERT INTO Regencia.Address_change_log " .
            "( source, log_time, user_id, address_id, type_id, function, query,deleted ) " .
            "VALUES " .
            "('" . utf8_decode($kalla) . "', '" . date('Y-m-d H:i:s') . "'," .
            "'" . $_SESSION['user_id'] . "', '" . $address_id . "'," .
            "'" . $this->update_address_type . "'," .
            "'" . __FILE__ . ":" . __METHOD__ . "'," .
            "'" . preg_replace("/\'/", "\\\\'", $query) . "'," .
            "2);";
        $result = mysql_query($query2);
        mysql_query("UPDATE Accenta.AdressListor SET Raderad = 1, SenasteStatus = 'NFX' WHERE RegenciaAdressId = $address_id;");
        if (!$result) {
            return "Error! " . mysql_error() . " in upAddress \n";
        }
    }

    //address limit functions

    public function getLimit($uid)
    {
        $this->connect();
        $sql = "SELECT B.limit_value FROM Regencia.Address_users A INNER JOIN Regencia.Address_user_limit B ON B.limit_id = A.have_limit WHERE A.user_id = $uid;";
        $q = mysql_query($sql);
        if (mysql_result($q, 0) == 0) {
            return false;
        } else {
            return mysql_result($q, 0);
        }
    }

    public function address_limit($uid)
    {
        $this->connect();
        $sql = "SELECT DISTINCT(address_id) FROM Regencia.Address_change_log WHERE user_id = $uid AND DATE(log_time) = DATE(NOW()) AND (type_id = 2 OR type_id = 1) LIMIT 10;";
        $q = mysql_query($sql);
        $count = mysql_num_rows($q);
        if (!$this->getLimit($uid)) {
            return array(false, "xoXXox", false);
        }
        if ($count >= $this->getLimit($uid)) {
            return array(false, ($this->getLimit($uid) - $count), $this->getLimit($uid));
        }

        return array(true, ($this->getLimit($uid) - $count), $this->getLimit($uid));
    }

    //fetching deleted code
    function getDeletedCode($adrID)
    {
        $this->connect();
        $sql = "SELECT * FROM Regencia.Address WHERE address_id = $adrID;";
        $query = mysql_query($sql);
        $row = mysql_fetch_array($query);
        return $row['deleted'];
    }

    //fetch old data from database for the blank_check
    public function getOldInput($adrID)
    {
        $this->connect();
        $sql = "SELECT A.source, C.inactive, B.deleted, B.name, B.box_address1, SUBSTRING(B.box_postal_code,3) AS postalcode,
			SUBSTRING(B.box_postal_code,1,2) AS countrycode, B.phone1,B.phone2,B.mobile_phone, B.email, B.website, C.turnover,C.turnover_year,
			C.number_of_employees, C.description, C.org_nr, C.year_of_registration
			FROM Regencia.Address_change_log A
			INNER JOIN Regencia.Address B ON B.address_id = A.address_id
			INNER JOIN Regencia.Company_facts C ON C.address_id = A.address_id
			WHERE A.address_id = $adrID
			ORDER BY A.log_time DESC LIMIT 1;";
        $q = mysql_query($sql);
        $arr = array();
        $row = mysql_fetch_array($q);
        for ($i = 0; $i < (count($row) / 2); $i++) {
            $arr[] = utf8_encode($row[$i]);
        }
        return $arr;
    }

    //get the number of previously uploaded csv files
    public function CountPreviouslyUploadedFiles()
    {

        $path = "./csv/";
        $count = 0;
        if ($handle = opendir($path)) {
            while (false !== ($entry = readdir($handle))) {
                if (substr($entry, 0, 1) != '.') {
                    $count++;
                }
            }
        }
        closedir($handle);
        return $count;
    }

    //get a table of files names and actions for uploaded csv files
    public function PreviouslyUploadedFiles()
    {

        $path = "./csv/";
        $dir = opendir($path);
        $list = array();
        while ($file = readdir($dir)) {
            if (substr($file, 0, 1) != '.') {
                // add the filename, to be sure not to
                // overwrite a array key
                $ctime = filectime($data_path . $file) . ',' . $file;
                $list[$ctime] = $file;
            }
        }
        closedir($dir);
        krsort($list);
        $table = "<table border='0'>";
        foreach ($list as &$filename) {
            $table .= "<tr>";
            $table .= "<td>" . $filename . "</td>";
            $table .= "<td>" .
                "<input type=\"submit\" value=\"Check\" " .
                "onclick=\"checkCSV('" . $filename . "');\"/>" .
                "</td>";
            $table .= "<td>" .
                "<input type=\"submit\" value=\"Import/Update\" " .
                "onclick=\"importCSV('" . $filename . "');\"/>" .
                "</td>";
            //$table .= "<td>" . 
            //	"<select>" .
            //    $this->getAllowedSources($_SESSION['user_id']).
            //	"</select>" .  
            //	"</td>";
            $table .= "<td>" .
                "<input type=\"submit\" value=\"Remove\" " .
                "onclick=\"removeCSV('" . $filename . "');\"/>" .
                "</td>";
            $table .= "</tr>";
        }
        $table .= "</table>";
        return $table;
    }

    // Check number of lines in CSV file
    private function CheckNumberOfLinesInCSVFil($path)
    {

        //Open csv file with addresses
        if (!$fh = fopen($path, "r")) {
            die("Failed to open csv file!\n");
        }

        // Read the file line by line
        $line = 0;

        while (!feof($fh)) {
            if (!$nextline = fgets($fh)) {
                break;
            }
            $line++;
        }
        return $line;
    }

    //function to get the progress percent
    public function GetProgressPercent()
    {
        $this->connect();
        $sql = "SELECT L.text AS Percent " .
            "FROM Regencia.RAMSDebugLog L " .
            "WHERE L.user = '" . $_SESSION['user_id'] . "' AND L.file = 'RAMS' " .
            "AND func = 'ProgressPercent' " .
            "ORDER BY date DESC LIMIT 1";

        $this->rams_debug($_SESSION['user_id'], "RAMS", "GetProgressPercent", $sql, 1);
        $query = mysql_query($sql);
        if (!$query) {
            echo "Error on line " . __LINE__ . " in " . __FILE__ . ". " . mysql_error() . " Query: $sql\n";
            return -1;
        } else {
            $row = mysql_fetch_array($query);
            $this->rams_debug($_SESSION['user_id'], "RAMS", "GetProgressPercentValue", $row['Percent'], 1);
            setcookie('ProgressBarStatus', intval($row['Percent']));
        }
    }


    // Check uploaded CSV file against Regencia
    public function CheckCSVAgainstRegencia($path)
    {

        $number_of_lines = $this->CheckNumberOfLinesInCSVFil($path);

        //Open csv file with addresses
        if (!$fh = fopen($path, "r")) {
            die("Failed to open csv file!\n");
        }

        // Open temporary file for exporting addresses with cin:s not available
        if (!$fhe = fopen("./csv/temp_lastcheck.csv", "w")) {
            die("Failed to open temporary csv file!\n");
        }

        // Open temporary file for exporting addresses with invalid postal codes
        if (!$fhi = fopen("./csv/temp_lastinvalid.csv", "w")) {
            die("Failed to open temporary csv file for invalid pc:s!\n");
        }

        // Read the file line by line
        $line = 0;
        $percent = 0;

        //Keep track of failures and successes
        $incomplete = 0;
        $not_allowed = 0;
        $missing_county_or_district_id = 0;
        $query_for_county_or_district_failed = 0;
        $prevented_duplicates = 0;
        $not_exitisting_cin_to_add = 0;

        while (!feof($fh)) {
            if (!$nextline = fgets($fh)) {
                break;
            }
            $line++;
            $this->rams_debug($_SESSION['user_id'], "RAMS", "CheckCSVAgainstRegencia", $nextline, 1);
            $percent = round(100 * $line / $number_of_lines);
            jQuery::evalScript('$( "#progressbar" ).progressbar( "option", "value", ' . $percent . ' );');
            $this->rams_debug($_SESSION['user_id'], "RAMS", "ProgressPercent", $percent, 1);

            // Get nextline in the form we want by removing ":s and exchangning
            // ,:s for ;:s
            $nextline = preg_replace('/"/', "", $nextline);
            //$nextline = preg_replace('/,/',";", $nextline);

            // Get address parts by splitting the line into six parts
            $values = preg_split('/;/', $nextline);

            // If we did not get at least six parts, the address is incomplete and
            // we should skip this iteration, also fill in missing values is necessary
            $this->rams_debug($_SESSION['user_id'], "RAMS", "CheckCSVAgainstRegencia", "Count values: " . count($values), 1);
            if (count($values) < 6) {
                $this->rams_debug($_SESSION['user_id'], "RAMS", "CheckCSVAgainstRegencia", "Incomplete address: " . $nextline, 1);
                $incomplete++;
                continue;
            } elseif (count($values) < 11) {
                $diff = 11 - count($values);
                for ($i = 1; $i <= $diff; $i++) {
                    array_push($values, '');
                }
            }

            // Trim all address parts from leading or ending whitespaces
            foreach ($values as &$value) {
                $value = trim($value);
            }

            // Set countrycode if available
            $this->country_code = $this->getCountryCodeFromPostalCode($values[3]);

            // Check for signal of CIN request
            if ($this->requestCIN($values[0]) == 0) {
                if ($values[5] != "") {
                    $values[0] = $this->getCINFromWebsiteSearchBasedOnPhoneNumber($values[5], $values[1]);
                }
            }
            $this->rams_debug($_SESSION['user_id'], "RAMS", "CheckCSVAgainstRegencia", $values[0], 1);

            // Check for invalid cin
            if ($this->invalidCIN($values[0]) == 1) {
                $this->rams_debug($_SESSION['user_id'], "RAMS", "CheckCSVAgainstRegencia", "Invalid CIN: " . $nextline, 1);
                $incomplete++;
                continue;
            }

            // If valid CIN exists, perform branch request
            if ($this->invalidCIN($values[0]) == 0) {
                if ($values[10] == "?") {
                    $values[10] = $this->getBranchFromWebsiteSearch($values[0], $values[5], $values[1]);
                }
            }

            // Check address into Regencia
            $this->rams_debug($_SESSION['user_id'], "RAMS", "CheckCSVAgainstRegencia", $line . "(" . $percent . "%): " . $nextline, 1);
            $values[3] = $this->formatPostalCodeWithCountryCode($values[3]);
            $message = $this->checkAddress(
                $_SESSION['user_id'],
                utf8_encode($values[1]),
                utf8_encode($values[2]),
                utf8_encode($values[3]),
                utf8_encode($values[5]),
                '',
                '',
                '',
                utf8_encode($values[7]),
                utf8_encode($values[9]),
                utf8_encode($values[10]),
                utf8_encode($values[0]),
                utf8_encode($values[6]),
                utf8_encode($values[8]),
                '44',
                '',
                '',
                '',
                ''
            );
            $this->rams_debug($_SESSION['user_id'], "RAMS", "CheckCSVAgainstRegencia", $message, 1);
            if ($message == "SAI") {
                $not_exitisting_cin_to_add++;
                fwrite($fhe, implode(";", $values) . "\n");
            } elseif ($message == "NAP") {
                $not_allowed++;
            } elseif ($message == "MCI" or $message == "MDI") {
                $missing_county_or_district_id++;
                fwrite($fhi, $nextline);
            } elseif ($message == "QCF" or $message == "QDF") {
                $query_for_county_or_district_failed++;
            } elseif ($message == "PAD") {
                $prevented_duplicates++;
            }
        }

        if (!fclose($fh)) {
            die("Failed to close csv file!\n");
        }

        if (!fclose($fhe)) {
            die("Failed to close temporary csv file!\n");
        } else {

            // Move temporary file to final name and make a copy of the same file
            // with a name closely related to the original file
            rename("./csv/temp_lastcheck.csv", "./csv/.lastcheck.csv");
            $path2 = (preg_replace('/\.csv/', '', $path)) . "-checked.csv";
            copy("./csv/.lastcheck.csv", $path2);
        }

        if (!fclose($fhi)) {
            die("Failed to close temporary csv file for invalid pc:s!\n");
        } else {

            // Move temporary file to final name
            rename("./csv/temp_lastinvalid.csv", "./csv/.lastinvalid.csv");
        }

        // Return number of lines
        $return_message = "" . $line . "," . $incomplete . "," . $not_allowed . "," .
            $missing_county_or_district_id . "," .
            $query_for_county_or_district_failed . "," . $prevented_duplicates . "," .
            $not_exitisting_cin_to_add . "";
        $this->rams_debug(
            $_SESSION['user_id'],
            "RAMS",
            "CheckCSVAgainstRegencia",
            "Return message: " . $return_message,
            1
        );
        jQuery::evalScript('callback_checkCSV();');
        return $return_message;
    }

    // Format postalcode such that is is preceeded by country code
    private function formatPostalCodeWithCountryCode($postal_code)
    {

        $tmp = strtolower(substr($postal_code, 0, 2));
        if ($tmp == "se" or $tmp == "no") {
            return $postal_code;
        } else {
            return strtoupper($this->country_code) . "" . $postal_code;
        }
    }

    // Get country code from postal code
    private function getCountryCodeFromPostalCode($postal_code)
    {

        $tmp = strtolower(substr($postal_code, 0, 2));
        if (!is_numeric($tmp) and $tmp == "se" or $tmp == "no") {
            return tmp;
        } else
            return "se";
    }

    // Check for invalid CIN
    private function invalidCIN($cin)
    {

        // Length of string must be 11
        if (strlen($cin) != 11) {
            return 1;
        }

        if ($this->country_code == "se") {
            $part1 = substr($cin, 0, 6);
            $part2 = substr($cin, 6, 1);
            $part3 = substr($cin, 7, 4);
            if ($part2 != '-' or !is_numeric($part1) or !is_numeric($part3)) {
                return 1;
            }
        } elseif ($this->country_code == "no") {
            $part1 = substr($cin, 0, 3);
            $part2 = substr($cin, 3, 1);
            $part3 = substr($cin, 4, 3);
            $part4 = substr($cin, 7, 1);
            $part5 = substr($cin, 8, 3);
            if ($part2 != ' ' or $part4 != ' ' or !is_numeric($part1) or !is_numeric($part3) or !is_numeric($part5)) {
                return 1;
            }
        }

        return 0;
    }

    private function requestCIN($cin)
    {

        if (strcasecmp($cin, 'XXXXXX-XXXX') == 0) {
            return 0;
        } else {
            return 1;
        }
    }

    private function getCINFromWebsiteSearchBasedOnPhoneNumber($phone, $company)
    {
        $x = rand(1, 85);
        $y = rand(1, 38);

        $url = "http://www.proff.se/bransch-s%C3%B6k?q=" . $phone . "&x=" . $x . "&y=" . $y . "";
        $this->rams_debug(
            $_SESSION['user_id'],
            "RAMS",
            "getCINFromWebsiteSearchBasedOnPhoneNumber",
            "url: " . $url,
            1
        );

        $content = file_get_contents($url);

        if ($content != "") {

            //$antalStringStart = utf8_encode("<h2><span>Ditt sök på <strong>".$phone."</strong> hittade");
            //$antalStringStop = utf8_encode("träffar</span></h2>");
            $antalStringStart = utf8_encode("<span class=\"ui-wide\">-"); // Ändrade 2013-01-31
            $antalStringStop = utf8_encode(" träffar</span>"); // Ändrade 2013-01-31

            $start = strpos($content, $antalStringStart);
            $this->rams_debug(
                $_SESSION['user_id'],
                "RAMS",
                "getCINFromWebsiteSearchBasedOnPhoneNumber",
                "start: " . $start,
                1
            );
            if ($start === false) {
                return 'xxxxxx-xxxx';
            }
            $stop = strpos($content, $antalStringStop, $start + strlen($antalStringStart));
            $this->rams_debug(
                $_SESSION['user_id'],
                "RAMS",
                "getCINFromWebsiteSearchBasedOnPhoneNumber",
                "stop: " . $stop,
                1
            );
            if ($stop === false) {
                return 'xxxxxx-xxxx';
            }
            $antalHits = (int) trim(substr($content, $start + strlen($antalStringStart), $stop - 1));
            $this->rams_debug(
                $_SESSION['user_id'],
                "RAMS",
                "getCINFromWebsiteSearchBasedOnPhoneNumber",
                "antalHits: " . $antalHits,
                1
            );
            $orgNrOffset = 0;
            if ($antalHits > 1) {
                $company2 = preg_replace("/\&/i", "", $company);
                $content2 = preg_replace("/\&amp;/i", "", $content);
                $orgNrOffsetTemp = stripos($content2, utf8_encode($company2));
                if ($orgNrOffsetTemp !== false) {
                    $orgNrOffset = $orgNrOffsetTemp;
                }
            }
            $this->rams_debug(
                $_SESSION['user_id'],
                "RAMS",
                "getCINFromWebsiteSearchBasedOnPhoneNumber",
                "orgNrOffset: " . $orgNrOffset,
                1
            );

            // $start = strpos($content,"<h3 class=\"companyId\">", $orgNrOffset);
            $start = strpos($content, "<div class=\"org-number\">Org.nr.", $orgNrOffset); // Ändrade 2013-01-31
            if ($start === false) {
                return 'xxxxxx-xxxx';
            }
            //$stop = strpos($content, "</h3>", $start);
            $stop = strpos($content, "</div>", $start); // Ändrade 2013-01-31
            if ($stop === false) {
                return 'xxxxxx-xxxx';
            }

            for ($i = $start; $i <= $stop; $i++) {
                $cin = substr($content, $i, 11);
                if (!($this->invalidCIN($cin) == 1)) {
                    $this->rams_debug(
                        $_SESSION['user_id'],
                        "RAMS",
                        "getCINFromWebsiteSearchBasedOnPhoneNumber",
                        "found cin: " . $cin,
                        1
                    );
                    return $cin;
                }
            }
            return 'xxxxxx-xxxx';
        }
    }

    private function getBranchFromWebsiteSearch($orgnr, $phone, $company)
    {
        $x = rand(1, 85);
        $y = rand(1, 38);

        $url = "http://www.proff.se/bransch-s%C3%B6k?q=" . $phone . "&x=" . $x . "&y=" . $y . "";
        $url = "http://www.allabolag.se/?what=" . $phone . "&where=&s.x=" . $x . "&s.y=" . $y . "";

        $content = file_get_contents($url);

        if ($content != "") {

            $orgNrOffset = strpos($content, $orgnr) + 11;
            $start = strpos($content, "<span class=\"bold11grey6\">Verksamhet:</span>", $orgNrOffset) +
                strlen("<span class=\"bold11grey6\">Verksamhet:</span>");
            $stop = strpos($content, "<br />", $start);

            return preg_replace("/\&amp;/i", "&", trim(substr($content, $start, $stop - $start)));
        }
        return ' ';
    }

    // Imports uploaded CSV file into Regencia
    public function ImportCSVIntoRegencia($path)
    {

        $number_of_lines = $this->CheckNumberOfLinesInCSVFil($path);

        $this->rams_debug($_SESSION['user_id'], "RAMS", "ImportCSVIntoRegencia", "Number of lines in CSV file: " . $number_of_lines, 1);

        //Open csv file with addresses
        if (!$fh = fopen($path, "r")) {
            die("Failed to open csv file!\n");
        }

        // Open temporary file for exporting addresses successfully imported
        if (!$fhe = fopen("./csv/temp_lastimport.csv", "w")) {
            die("Failed to open temporary csv file!\n");
        }

        // Open temporary file for exporting addresses with invalid postalcodes
        if (!$fhi = fopen("./csv/.temp_lastinvalid.csv", "w")) {
            die("Failed to open temporary csv file for invalied pc:s!\n");
        }

        // Read the file line by line
        $line = 0;

        //Keep track of failures and successes
        $incomplete = 0;
        $not_allowed = 0;
        $missing_county_or_district_id = 0;
        $query_for_county_or_district_failed = 0;
        $prevented_duplicates = 0;
        $successfully_added = 0;
        $successfully_updated = 0;

        $this->rams_debug($_SESSION['user_id'], "RAMS", "ImportCSVIntoRegencia", "Running through file: " . $path, 1);
        while (!feof($fh)) {
            $this->rams_debug($_SESSION['user_id'], "RAMS", "ImportCSVIntoRegencia", "Next round of loop", 1);
            if (!$nextline = fgets($fh)) {
                break;
            }
            $this->rams_debug($_SESSION['user_id'], "RAMS", "ImportCSVIntoRegencia", $line . "(" . $percent . "%): " . $nextline, 1);
            $line++;
            $percent = round(100 * $line / $number_of_lines);
            $this->rams_debug($_SESSION['user_id'], "RAMS", "ProgressPercent", $percent, 1);
            //jQuery::evalScript('$( "#progressbar" ).progressbar( "option", "value", '.$percent.' );');
            //jQuery::evalScript('$("#progressbar").progressbar({ value: '.$percent.'});');


            $this->rams_debug($_SESSION['user_id'], "RAMS", "ImportCSVIntoRegencia", $line . "Reform line 1", 1);
            // Get nextline in the form we want by removing ":s and exchangning
            // ,:s for ;:s
            $nextline = preg_replace('/"/', "", $nextline);
            //$nextline = preg_replace('/,/',";", $nextline);

            $this->rams_debug($_SESSION['user_id'], "RAMS", "ImportCSVIntoRegencia", $line . "Reform line 2", 1);
            // Get address parts by splitting the line into six parts
            $values = preg_split('/;/', $nextline);

            // If we did not get at least six parts, the address is incomplete and
            // we should skip this iteration, also fill in missing values is necessary
            //$this->rams_debug($_SESSION['user_id'], "RAMS", "ImportCSVIntoRegencia",$line."Reform line 3",1);
            if (count($values) < 6) {
                $incomplete++;
                $this->rams_debug($_SESSION['user_id'], "RAMS", "ImportCSVIntoRegencia", $line . "Detected incomplete -- going to next line", 1);
                continue;
            } elseif (count($values) < 11) {
                $diff = 11 - count($values);
                for ($i = 1; $i <= $diff; $i++) {
                    array_push($values, '');
                }
            }

            $this->rams_debug($_SESSION['user_id'], "RAMS", "ImportCSVIntoRegencia", $line . "Reform line 4", 1);
            // Trim all address parts from leading or ending whitespaces
            foreach ($values as &$value) {
                $value = trim($value);
            }

            // Set countrycode if available
            $this->country_code = $this->getCountryCodeFromPostalCode($values[3]);

            $this->rams_debug($_SESSION['user_id'], "RAMS", "ImportCSVIntoRegencia", $line . "Check for invalid CIN", 1);
            // Check for invalid cin
            if ($this->invalidCIN($values[0]) == 1) {
                $incomplete++;
                $this->rams_debug($_SESSION['user_id'], "RAMS", "ImportCSVIntoRegencia", $line . "Detected invalid CIN -- going to next line", 1);
                continue;
            }

            // Insert address into Regencia
            $this->rams_debug($_SESSION['user_id'], "RAMS", "ImportCSVIntoRegencia", $line . "(" . $percent . "%), modified line: " . $nextline, 1);
            $values[3] = $this->formatPostalCodeWithCountryCode($values[3]);
            $message = $this->importAddress(
                $_SESSION['user_id'],
                utf8_encode($values[1]),
                utf8_encode($values[2]),
                utf8_encode($values[3]),
                utf8_encode($values[5]),
                '',
                '',
                '',
                utf8_encode($values[7]),
                utf8_encode($values[9]),
                utf8_encode($values[10]),
                utf8_encode($values[0]),
                utf8_encode($values[6]),
                utf8_encode($values[8]),
                '44',
                '',
                '',
                '',
                ''
            );
            $this->rams_debug($_SESSION['user_id'], "RAMS", "ImportCSVIntoRegencia", $message, 1);
            if ($message == "SAI") {
                $successfully_added++;
                fwrite($fhe, $nextline . ";Added");
            } elseif ($message == "SAU") {
                $successfully_updated++;
                fwrite($fhe, $nextline . ";Updated");
            } elseif ($message == "NAP") {
                $not_allowed++;
            } elseif ($message == "MCI" or $message == "MDI") {
                $missing_county_or_district_id++;
                fwrite($fhi, $nextline);
            } elseif ($message == "QCF" or $message == "QDF") {
                $query_for_county_or_district_failed++;
            } elseif ($message == "PAD") {
                $prevented_duplicates++;
            }
        }

        if (!fclose($fh)) {
            die("Failed to close csv file!\n");
        }

        if (!fclose($fhe)) {
            die("Failed to close temporary csv file!\n");
        } else {

            // Move temporary file to final name
            rename("./csv/temp_lastimport.csv", "./csv/.lastimport.csv");
        }

        if (!fclose($fhi)) {
            die("Failed to close temporary csv file for invalid pc:s!\n");
        } else {

            // Move temporary file to final name
            rename("./csv/temp_lastinvalid.csv", "./csv/.lastinvalid.csv");
        }

        // Return number of lines
        $return_message = "" . $line . "," . $incomplete . "," . $not_allowed . "," .
            $missing_county_or_district_id . "," .
            $query_for_county_or_district_failed . "," . $prevented_duplicates . "," .
            $successfully_added . "," . $successfully_updated . "";
        $this->rams_debug(
            $_SESSION['user_id'],
            "RAMS",
            "ImportCSVIntoRegencia",
            "Return message: " . $return_message,
            1
        );
        jQuery::evalScript('callback_importCSV();');
        return $return_message;
    }

    public function PreviouslyCheckedAddressInCSVFormat()
    {
        return "<a href=\"./csv/.lastcheck.csv\">Recently checked adresses with cin not in Regencia</a>";
    }

    public function PreviouslyImportedAddressInCSVFormat()
    {
        return "<a href=\"./csv/.lastimport.csv\">Recently successfully imported/updated adresses </a>";
    }

    public function PreviouslyCheckedAddressWithInvalidPostalCodeInCSVFormat()
    {
        return "<a href=\"./csv/.lastinvalid.csv\">Recent adresses with invalid postal codes</a>";
    }

    public function checkAddress(
        $user_id,
        $company,
        $address,
        $postalcode,
        $phone,
        $phone2,
        $email,
        $website,
        $omsattning,
        $antalAnstallda,
        $verksamhet,
        $orgnr,
        $registreringsdatum,
        $omsattningsar,
        $kalla,
        $completevalue,
        $projid = 0,
        $phone3,
        $unReOrNot
    ) {
        $this->rams_debug($_SESSION['user_id'], "RAMS", "checkAddress", "Step 1", 1);
        if ($this->checkAllowedPostals($postalcode, $user_id) == 0) {
            return "NAP";
        }

        $postalcode = $this->fix($postalcode);
        $this->connect();

        $sql = "SELECT C.county_id FROM County C WHERE C.postal_code = '" . $postalcode . "' ";
        $this->rams_debug($_SESSION['user_id'], "RAMS", "checkAddress", "Step 2: " . $sql, 1);
        $result = mysql_query($sql);
        if ($result) {
            if (mysql_num_rows($result) == 0) {
                return "MCI";
            } else {
                $county_id = utf8_decode(mysql_result($result, 0, $x));
            }
        } else {
            return "QCF";
        }

        $this->rams_debug($_SESSION['user_id'], "RAMS", "checkAddress", "Step 3", 1);
        $result = mysql_query("SELECT D.district_id FROM District D WHERE D.postal_code = '" . $postalcode . "' ");
        if ($result) {
            if (mysql_num_rows($result) == 0) {
                return "MDI";
            } else {
                $district_id = utf8_decode(mysql_result($result, 0, $x));
            }
        } else {
            return "QDF";
        }

        $this->rams_debug($_SESSION['user_id'], "RAMS", "checkAddress", "Step 4", 1);
        $ckeckit = "SELECT COUNT(address_id) FROM Regencia.Address WHERE TRIM(org_nr) = '" . utf8_encode($orgnr) . "' AND deleted = '0'";
        $res = mysql_query($ckeckit);
        $returnset = mysql_result($res, 0, $rs);
        if ($returnset > 0) {
            return "PAD";
        }

        $this->rams_debug($_SESSION['user_id'], "RAMS", "checkAddress", "Step 5", 1);
        return "SAI";
    }


    public function importAddress(
        $user_id,
        $company,
        $address,
        $postalcode,
        $phone,
        $phone2,
        $email,
        $website,
        $omsattning,
        $antalAnstallda,
        $verksamhet,
        $orgnr,
        $registreringsdatum,
        $omsattningsar,
        $kalla,
        $completevalue,
        $projid = 0,
        $phone3,
        $unReOrNot
    ) {
        $this->rams_debug($_SESSION['user_id'], "RAMS", "importAddress", "Step 1", 1);
        if ($this->checkAllowedPostals($postalcode, $user_id) == 0) {
            return "NAP";
        }
        $this->rams_debug($_SESSION['user_id'], "RAMS", "importAddress", "Step 2", 1);
        $postalcode = $this->fix($postalcode);
        $this->connect();

        $sql = "SELECT C.county_id FROM County C WHERE C.postal_code = '" . $postalcode . "' ";
        $result = mysql_query($sql);
        if ($result) {
            if (mysql_num_rows($result) == 0) {
                $this->rams_debug($_SESSION['user_id'], "RAMS", "importAddress", "MCI: " . $sql, 1);
                return "MCI";
            } else {
                $county_id = utf8_decode(mysql_result($result, 0, $x));
            }
        } else {
            return "QCF";
        }

        $this->rams_debug($_SESSION['user_id'], "RAMS", "importAddress", "Step 3", 1);
        $sql = "SELECT D.district_id FROM District D WHERE D.postal_code = '" . $postalcode . "' ";
        $result = mysql_query($sql);
        if ($result) {
            if (mysql_num_rows($result) == 0) {
                $this->rams_debug($_SESSION['user_id'], "RAMS", "importAddress", "MDI: " . $sql, 1);
                return "MDI";
            } else {
                $district_id = utf8_decode(mysql_result($result, 0, $x));
            }
        } else {
            return "QDF";
        }

        $this->rams_debug($_SESSION['user_id'], "RAMS", "importAddress", "Step 4", 1);
        $ckeckit = "SELECT COUNT(address_id) FROM Regencia.Address WHERE TRIM(org_nr) = '" . utf8_encode($orgnr) . "' AND deleted = '0'";
        $res = mysql_query($ckeckit);
        $returnset = mysql_result($res, 0, $rs);
        if ($returnset > 0) {
            /*$sql =  "SELECT A.address_id AS AddressID, A.district_id AS District FROM Regencia.Address A ".
			        "INNER JOIN Regencia.Address_change_log C ON C.address_id = A.address_id ".
			        "WHERE TRIM(A.org_nr) = '".utf8_encode($orgnr)."' AND A.deleted = '0' ".
			        "ORDER BY C.log_time ASC LIMIT 1";*/
            $sql = "SELECT A.address_id AS AddressID, A.district_id AS District, " .
                "(SELECT MAX(log_time) FROM Regencia.Address_change_log C WHERE " .
                "C.address_id = AddressID) AS LogTid " .
                "FROM Regencia.Address A " .
                "WHERE TRIM(A.org_nr) = '" . utf8_encode($orgnr) . "' AND A.deleted = '0' ORDER BY LogTid ASC LIMIT 1;";
            $this->rams_debug($_SESSION['user_id'], "RAMS", "importAddress, check for update?", $sql, 1);
            if ($query = mysql_query($sql)) {
                $row = mysql_fetch_array($query);
                if ($this->updateAddress(
                    $user_id,
                    $company,
                    $address,
                    $postalcode,
                    $phone,
                    $phone2,
                    $email,
                    $website,
                    $omsattning,
                    $antalAnstallda,
                    $verksamhet,
                    $orgnr,
                    $registreringsdatum,
                    $omsattningsar,
                    $kalla,
                    $row['AddressID'],
                    $row['District'],
                    $completevalue,
                    $phone3,
                    $unReOrNot,
                    ""
                ) == 1) {
                    return "SAU";
                } else {
                    return "PAD";
                }
            } else {
                return "PAD";
            }
        }

        $this->rams_debug($_SESSION['user_id'], "RAMS", "importAddress", "Step 5", 1);
        $query = "INSERT INTO Address
		                     (name, box_address1, box_postal_code, phone1, phone2, 
		                      email, website, county_id, district_id, org_nr,complete,mobile_phone,deleted) VALUES 
		                      ('" . utf8_decode($company) . "', '" . utf8_decode($address) . "', 
		                      '" . $postalcode . "','" . $this->fixIT($phone) . "', 
		                      '" . $this->fixIT($phone2) . "', '" . utf8_decode(str_replace('mailto:', '', $email)) . "',
		                     '" . utf8_decode($website) . "', '" . utf8_decode($county_id) . "', 
		                      '" . utf8_decode($district_id) . "', '" . utf8_decode($orgnr) . "', '" . $completevalue . "', '" . $phone3 . "','{$unReOrNot}');";
        $result = mysql_query($query);
        $address_id = mysql_insert_id();
        $this->rams_debug($user_id, "RAMS", "in insAddress", $query, $address_id);



        if (!$result) {
            return "Error! " . mysql_error() . " in insAddress INSERT INTO Address\n";
        }

        if (!$address_id) {
            return "Error! " . mysql_error() . " in insAddress mysql_insert_id\n";
        }
        $this->rams_debug($_SESSION['user_id'], "RAMS", "importAddress", "Step 6", 1);
        $query2 = "INSERT INTO Company_facts (turnover, number_of_employees, description, address_id, year_of_registration, turnover_year, org_nr) VALUES 
                              ('" . utf8_decode($omsattning) . "', '" . utf8_decode($antalAnstallda) . "', 
                              '" . utf8_decode($verksamhet) . "', '" . $address_id . "', 
                              '" . utf8_decode($registreringsdatum) . "', 
                              '" . utf8_decode($omsattningsar) . "', '" . utf8_decode($orgnr) . "');";

        $result = mysql_query($query2);
        $this->rams_debug($user_id, "RAMS", "in insAddress", $query2, $address_id);
        if (!$result) {
            return "Error! " . mysql_error() . " in insAddress INSERT INTO Company_facts<br />" . $query2;
        }

        $this->rams_debug($_SESSION['user_id'], "RAMS", "importAddress", "Step 7", 1);
        $query3 = "INSERT INTO Regencia.Categorized (address_id) VALUES ('" . $address_id . "');";
        $result = mysql_query($query3);
        $this->rams_debug($user_id, "RAMS", "in insAddress", $query3, $address_id);
        if (!$result) {
            return "Error! " . mysql_error() . " in insAddress INSERT INTO Categorized\n";
        }

        $last_num = substr($query, -1);
        $query = rtrim($query, $last_num) . " turnover = '" . utf8_decode($omsattning) . "'" . $last_num;
        $this->rams_debug($_SESSION['user_id'], "RAMS", "importAddress", "Step 8", 1);
        $query4 = "INSERT INTO Regencia.Address_change_log " .
            "( source, log_time, user_id, address_id, type_id, function, query ) " .
            "VALUES " .
            "('" . utf8_decode($kalla) . "', '" . date('Y-m-d H:i:s') . "'," .
            "'" . $user_id . "', '" . $address_id . "'," .
            "'" . $this->new_address_type . "'," .
            "'" . __FILE__ . ":" . __METHOD__ . "'," .
            "'" . preg_replace("/\'/", "\\\\'", $query) . "');";
        $result = mysql_query($query4);
        $this->rams_debug($user_id, "RAMS", "in insAddress", $query4, $address_id);
        if (!$result) {
            return "Error! " . mysql_error() . " in insAddress INSERT INTO Address_change_log\n";
        } else {
            $this->rams_debug($_SESSION['user_id'], "RAMS", "importAddress", "Step 9", 1);
            $_SESSION['kalla'] = $kalla;
            if ($projid != 0) {
                mysql_query("UPDATE Accenta.AdressListor SET RegenciaAdressId='$address_id' WHERE ProjektAdresserID='$projid'");
                return "SAI";
            } else {
                return "SAI";
            }
        }
    }




    //Function to updated already imported address
    public function updateAddress(
        $user_id,
        $company,
        $address,
        $postalcode,
        $phone,
        $phone2,
        $email,
        $website,
        $omsattning,
        $antalAnstallda,
        $verksamhet,
        $orgnr = 0,
        $registreringsdatum,
        $omsattningsar,
        $kalla,
        $address_id,
        $nykommun,
        $completevalue,
        $phone3,
        $unReOrNot,
        $cat
    ) {
        $this->rams_debug($_SESSION['user_id'], "RAMS", "in updateAddress step 0", "adressid and userid =" . $address_id . "," . $user_id, 1);
        $this->chkAddress_id($address_id, $user_id);
        $this->rams_debug($_SESSION['user_id'], "RAMS", "in updateAddress step 00", "", 1);
        $postalcode = $this->fix($postalcode);
        $this->rams_debug($_SESSION['user_id'], "RAMS", "in updateAddress step 000", "", 1);
        $this->connect();
        $result = mysql_query("SELECT C.county_id FROM Regencia.County C WHERE C.postal_code = '" . $postalcode . "' ");
        if ($result) {
            if (mysql_num_rows($result) == 0) {
                $this->rams_debug($_SESSION['user_id'], "RAMS", "in updateAddress step 0", "MCI " . "SELECT C.county_id FROM Regencia.County C WHERE C.postal_code = '" . $postalcode . "' ", 1);
                return "MCI";
            } else {
                $county_id = utf8_decode(mysql_result($result, 0, $x));
            }
        } else {
            $this->rams_debug($_SESSION['user_id'], "RAMS", "in updateAddress step 0", "MCI " . "SELECT C.county_id FROM Regencia.County C WHERE C.postal_code = '" . $postalcode . "' ", 1);
            return "MCI";
        }

        $this->rams_debug($_SESSION['user_id'], "RAMS", "in updateAddress step 1", "", 1);

        $result = mysql_query("SELECT D.district_id FROM Regencia.District D WHERE D.postal_code = '" . $postalcode . "' ");
        if ($result) {
            if (mysql_num_rows($result) == 0) {
                return "MDI";
            } else {
                $district_id = utf8_decode(mysql_result($result, 0, $x));
            }
        } else {
            return "MDI";
        }

        $this->rams_debug($_SESSION['user_id'], "RAMS", "in updateAddress step 0", "", 2);

        $query = "UPDATE Regencia.Address SET 
							   name = '" . utf8_decode($company) . "', 
							   box_address1 = '" . utf8_decode($address) . "', 
							   box_postal_code = '" . $postalcode . "', 
							   phone1 = '" . $this->fixIT($phone) . "', 
							   phone2 = '" . $this->fixIT($phone2) . "',
							   mobile_phone = '" . $this->fixIT($phone3) . "', 
							   email = '" . utf8_decode(str_replace('mailto:', '', $email)) . "', 
							   website = '" . utf8_decode($website) . "', 
							   org_nr = '" . utf8_decode($orgnr) . "',
							   visit_postal_code = '" . $postalcode . "',
							   county_id = '" . utf8_decode($county_id) . "',
							   district_id = '" . utf8_decode($nykommun) . "',
							   complete = '" . $completevalue . "',
							   deleted = '{$unReOrNot}'
							   WHERE address_id = '" . $address_id . "';";
        $result = mysql_query($query);
        $this->rams_debug($user_id, "RAMS", "in upAddress", $query, $address_id);
        if (!$result) {
            return "Error! " . mysql_error() . " in upAddress \n";
        }

        $this->rams_debug($_SESSION['user_id'], "RAMS", "in updateAddress step 0", "", 3);

        $query1 = "UPDATE Regencia.Company_facts SET 
		                       turnover = '" . utf8_decode($omsattning) . "', 
                               number_of_employees = '" . utf8_decode($antalAnstallda) . "', 
                               description = '" . utf8_decode($verksamhet) . "', 
                               year_of_registration = '" . $registreringsdatum . "', 
                               turnover_year = '" . utf8_decode($omsattningsar) . "',                
                               org_nr = '" . utf8_decode($orgnr) . "' WHERE address_id = '" . $address_id . "';";
        $result = mysql_query($query1);
        $this->rams_debug($user_id, "RAMS", "in upAddress", $query1, $address_id);
        if (!$result) {
            return "Error! " . mysql_error() . " in upAddress \n";
        }

        $this->rams_debug($_SESSION['user_id'], "RAMS", "in updateAddress step 0", "", 4);
        $last_num = substr($query, -1);
        $query = rtrim($query, $last_num) . " turnover = '" . utf8_decode($omsattning) . "'" . $last_num;
        $query2 = "INSERT INTO Regencia.Address_change_log " .
            "( source, log_time, user_id, address_id, type_id, function, query ) " .
            "VALUES " .
            "('" . utf8_decode($kalla) . "', '" . date('Y-m-d H:i:s') . "'," .
            "'" . $user_id . "', '" . $address_id . "'," .
            "'" . $this->update_address_type . "'," .
            "'" . __FILE__ . ":" . __METHOD__ . "'," .
            "'" . preg_replace("/\'/", "\\\\'", $query) . "');";
        $result = mysql_query($query2);
        $this->rams_debug($user_id, "RAMS", "in upAddress", $query2, $address_id);
        if (!$result) {
            return "Error! " . mysql_error() . " in upAddress \n";
        } else {
            $_SESSION['kalla'] = $kalla;
            return 1;
        }
    }
}
