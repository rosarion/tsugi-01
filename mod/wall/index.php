<?php
require_once("../../config.php");
$context = moduleContext();
if ( ! $context->valid ) {
   die("Basic LTI Session failure ".$_SERVER['PHP_SELF']);
}

if ( $_POST['response'] ) {
    date_default_timezone_set('EST');
    $sql = "INSERT INTO Announcements (user_id, data, datetime) VALUES (?, ?, ?)";
    $q = $db->prepare($sql);
    $success = $q->execute(Array($_SESSION['user_id'],$_POST['response'],date('M d, Y g:i a') ));
    // echo($sql);flush();
    if ( $success) $rows = $q->rowCount();
    if ( $rows > 0 ) {
        $_SESSION['success'] = 'Data inserted';
    } else { 
        $_SESSION['err'] = 'Unable to insert data ';
    }
}

if ( $_POST['MAX_FILE_SIZE'] ) {
    $target_path = $CFG->wwwroot;
    $target_path = $target_path . basename( $_FILES['file']['name']);
    
    if(move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) {
        //$_SESSION['success'] = 'Data inserted';
        echo "The file ".  basename( $_FILES['file']['name']). 
        " has been uploaded";
    } else{
        //$_SESSION['err'] = 'Unable to insert data '.$CFG->wwwroot;
        echo "There was an error uploading the file, please try again!";
    }
}

$sql = "SELECT * FROM Announcements JOIN LTI_Users ON Announcements.user_id=LTI_Users.id ORDER BY Announcements.id DESC;";
$q = $db->prepare($sql);
$q->execute();

$first = true;

flashMessages();

?>
<div id="medium-dialog-container">
<div id="medium-dialog">
<center>
<form  method="post">
<textarea name="response" rows="10" cols="84">
</textarea><br/>
<input type="submit" value="Post an Announcement">
</form>
<form enctype="multipart/form-data" method="post">
<input type="hidden" name="MAX_FILE_SIZE" value="100000" />
Choose a file to upload: <input name="file" type="file" id="file"/><br />
<input type="submit" value="Upload File" />
</form>
<p><table>
<tr><th width=600>Recent Announcements</th></tr>

<?php

while ( $q && $row = $q->fetch() ) {
    if ( $first ) {
        $first = false;
    }
    echo ("<tr><td>");
    echo ('<table class="announcement-feed-post"><tr><td width="30">');
    if ( strlen($row['image']) > 0 ) {
        echo('<img src="'.$row['image'].'" width="30" height="30" style="float:left">');
    }

    echo('</td><td width="570"><table width="570"><tr>');
    echo("<td>".$row['name']." ".$row['datetime']."</td></tr>");
    echo("<tr><td>".htmlentities($row['data'])."</td></tr>");
    echo('<tr><td><a href="wall.php?reply?='.$row[0].'">Reply</a></td></tr></table>');
    echo("</td></tr></table>");
    echo("</td></tr>\n");
}

if ( $first ) {
    echo("<tr><td>No announcements found.</td></tr></table></center>\n");
} else {
    echo("</table></center>\n");
}
?>
</div>
</div>
</body>