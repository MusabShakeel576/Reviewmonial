<?php
/**
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://gum.co/reviewmonial
 * @since             1.0
 * @package           Reviewmonial
 *
 * Plugin Name:     Reviewmonial
 * Plugin URI:      https://
 * Description:     Get reviews and showcase testimonials on any page or widget, a Plugin for WordPress.
 * Version:         0.1
 * Author:          MusabShakeel
 * Author URI:      https://musab.kedruga.com
 * Text Domain:     reviewmonial
 * Domain Path:     /languages
 */
// Reviewmonial - Get reviews and showcase testimonials on any page or widget, a Plugin for WordPress.
// Copyright (C) 2020  Musab Shakeel

// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <https://www.gnu.org/licenses/>.

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

//Bootstrap
function add_bootstrap() 
{       
	wp_enqueue_style('include_bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css');
	wp_enqueue_style('include_font_awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
}
add_action('wp_enqueue_scripts','add_bootstrap');

define('FACEBOOK_SDK_V5_SRC_DIR', __DIR__.'Facebook/');
require_once(plugin_dir_path( __FILE__ ) . 'Facebook/autoload.php');

//START DB Table create
register_activation_hook( __FILE__, 'jal_install' );
global $jal_db_version;
$jal_db_version = '1.0';

function jal_install() {
	global $wpdb;
	global $jal_db_version;

	$table_name_1 = $wpdb->prefix . 'reviewmonial_review';
	$table_name_2 = $wpdb->prefix . 'reviewmonial_fb';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql_1 = "CREATE TABLE $table_name_1 (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		name varchar(255) NOT NULL,
		email varchar(255) NOT NULL,
		comment varchar(255) NOT NULL,
		image varchar(255) NOT NULL,
		rating int(3) NOT NULL,
		edit int(3) NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	$sql_2 = "CREATE TABLE $table_name_2 (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		fbappid varchar(255) NOT NULL,
		fbappsecret varchar(255) NOT NULL,
		fbaccesstoken varchar(255) NOT NULL,
		fbpageid varchar(255) NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql_1 );
	dbDelta( $sql_2 );

	add_option( 'jal_db_version', $jal_db_version );
}
//END DB Table create

function hook_css() {
    ?>
        <style>
.container-show-review {
    border: 2px solid white;
    background-color: #ebad60;
    border-radius: 5px;
    padding: 16px;
	margin: 16px 0;
	border-radius: 30px;
	color: white;
  }
  
  .container-show-review::after {
    content: "";
    clear: both;
    display: table;
  }
  
  .container-show-review img {
    float: left;
    margin-right: 20px;
    border-radius: 50%;
  }
  
  .container-show-review span {
    font-size: 20px;
    margin-right: 15px;
  }
  .tooltip {
  position: relative;
  display: inline-block;
  border-bottom: 1px dotted black;
}

.tooltip .tooltiptext {
  visibility: hidden;
  width: 120px;
  background-color: black;
  color: #fff;
  text-align: center;
  border-radius: 6px;
  padding: 5px 0;
  
  /* Position the tooltip */
  position: absolute;
  z-index: 1;
  top: -5px;
  left: 105%;
}

.tooltip:hover .tooltiptext {
  visibility: visible;
}
  
  @media (max-width: 500px) {
    .container-show-review {
        text-align: center;
    }
    .container-show-review img {
        margin: auto;
        float: none;
        display: block;
    }
  }
        </style>
    <?php
}
add_action('wp_head', 'hook_css');

//START Admin Dashboard
add_action("admin_menu", "addMenu");
function addMenu()
{
  add_menu_page("Reviewnomial", "Reviewnomial", 4, "reviewnomial", "reviewnomialFunction" );
  add_submenu_page("reviewnomial", "Edit", "Edit", 4, "reviewnomial-edit", "editFunction" );
  add_submenu_page("reviewnomial", "Approve", "Approve", 4, "reviewnomial-approve", "approveFunction" );
  add_submenu_page("reviewnomial", "Setting", "Setting", 4, "reviewnomial-setting", "settingFunction" );
}

//START Delete
$action = isset($_GET['action']) ? trim($_GET['action']) : "";
$id = isset($_GET['id']) ? intval($_GET['id']) : "";
global $wpdb;
$table_name = $wpdb->prefix . 'reviewmonial_review';
if(!empty($action) && $action == "delete"){
	$row_exists = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT * from $table_name WHERE id = %d", $id 
		)
	);
		$wpdb->delete("$table_name", array("id" =>$id)); 
	?>
	<script>
	location.href = "<?php echo site_url() ?>/wp-admin/admin.php?page=reviewnomial";
	</script>
	<?php
}
//END Delete

function reviewnomialFunction()
{
	echo '<div class="container-show-review">';
	global $wpdb;
	$table_name = $wpdb->prefix . 'reviewmonial_review';
	$show_review_data = $wpdb->get_results("SELECT * FROM $table_name");  
	$show_review_data= json_decode( json_encode($show_review_data), true);
	if (is_array($show_review_data) || is_object($show_review_data)){
		foreach($show_review_data as $show_review_d){
		echo '<div class="container-show-review">';
		echo '<p><span><b>'.$show_review_d['name'].'</b>'.' - '.$show_review_d['email'].' - '.$show_review_d['time'].' ';
		if($show_review_d['rating'] == 5){
		echo '- 5 stars';
		}elseif($show_review_d['rating'] == 4){
		echo '- 4 stars';
		}elseif($show_review_d['rating'] == 3){
		echo '- 3 stars';
		}elseif($show_review_d['rating'] == 2){
		echo '- 2 stars';
		}elseif($show_review_d['rating'] == 1){
		echo '- 1 stars';
		}
		echo ' '.'<a href="admin.php?page=reviewnomial&id='.$show_review_d['id'].'&action=delete"> <button style="float:right; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;  margin: 4px 2px; transition-duration: 0.4s; cursor: pointer; background-color: white; color: black; border: 2px solid #f44336; border-radius: 5px;" name="delete_button_submit">Delete</button></a>';
		echo ' '.'<a href="admin.php?page=reviewnomial-edit&action=edit&id='.$show_review_d['id'].'"> <button style="float:right; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;  margin: 4px 2px; transition-duration: 0.4s; cursor: pointer; background-color: white; color: black; border: 2px solid #3acece; border-radius: 5px;" name="edit_button_submit">Edit</button></a>';
		if($show_review_d['edit'] == 0 && $show_review_d['rating'] <= 3){
		echo ' '.'<a href="admin.php?page=reviewnomial-approve&action=approve&id='.$show_review_d['id'].'"> <button style="float:right; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;  margin: 4px 2px; transition-duration: 0.4s; cursor: pointer; background-color: white; color: black; border: 2px solid #6cd242; border-radius: 5px;" name="approve_button_submit">Approve</button></a>';
		}
		echo '</span></p>';
		echo '<p><i>'.$show_review_d['comment']; echo '</i></p>';
		?>
		<?php $image_string = $show_review_d['image'];
				$image_string_explode = explode(" , ", $image_string);
		?>
		<?php if(isset($image_string_explode[2])){?>
		<img src="<?php echo $image_string_explode[0]; ?>" alt="image" width="150px" height="150px" style="border-radius:0 !important;">
		<img src="<?php echo $image_string_explode[1]; ?>" alt="image" width="150px" height="150px" style="border-radius:0 !important;">
		<img src="<?php echo $image_string_explode[2]; ?>" alt="image" width="150px" height="150px" style="border-radius:0 !important;">
		<br><a id="download" href="<?php echo $image_string_explode[0]; ?>" download>Download Image</a>
		<a id="download" href="<?php echo $image_string_explode[1]; ?>" download>Download Image</a>
		<a id="download" href="<?php echo $image_string_explode[2]; ?>" download>Download Image</a>
		<?php }elseif(isset($image_string_explode[1])){ ?>
		<img src="<?php echo $image_string_explode[0]; ?>" alt="image" width="150px" height="150px" style="border-radius:0 !important;">
		<img src="<?php echo $image_string_explode[1]; ?>" alt="image" width="150px" height="150px" style="border-radius:0 !important;">
		<br><a id="download" href="<?php echo $image_string_explode[0]; ?>" download>Download Image</a>
		<a id="download" href="<?php echo $image_string_explode[1]; ?>" download>Download Image</a>
		<?php }elseif(isset($image_string_explode[0])){?>
		<img src="<?php echo $image_string_explode[0]; ?>" alt="image" width="150px" height="150px" style="border-radius:0 !important;">
		<br><a id="download" href="<?php echo $image_string_explode[0]; ?>" download>Download Image</a>
		<?php } ?>
		<?php
		echo '</div>';
	  }
	}
	echo '</div>';
}

function editFunction()
{
global $wpdb;
$msg = '';

$action = isset($_GET['action']) ? trim($_GET['action']) : "";
$id = isset($_GET['id']) ? intval($_GET['id']) : "";
$table_name = $wpdb->prefix . 'reviewmonial_review';
$row_details = $wpdb->get_row(
        $wpdb->prepare(
                "SELECT * from $table_name WHERE id = %d", $id 
        ), ARRAY_A
);

if (isset($_POST['btn-submit'])) {

    $action = isset($_GET['action']) ? trim($_GET['action']) : "";
	$id = isset($_GET['id']) ? intval($_GET['id']) : "";

	//Start Image Upload
	$upload_dir = wp_upload_dir();
	$target_dir = $_SERVER['DOCUMENT_ROOT'] .'/wp-content/uploads';
	$countfiles = count($_FILES['txtimage']['name']);
	for($i=0;$i<$countfiles;$i++){
	if($countfiles == 1){
	$fileName_1 = $_FILES["txtimage"]["name"][0];
	$target_file = $target_dir .'/'. $fileName_1;
	$uploadOk = 1;
	$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
	
	// Check if image file is a actual image or fake image
	if(isset($_POST["submit"])) {
	  $check = getimagesize($_FILES["txtimage"]["tmp_name"][0]);
	  if($check !== false) {
		echo "File is an image - " . $check["mime"] . ".";
		$uploadOk = 1;
	  } else {
		echo "File is not an image.";
		$uploadOk = 0;
	  }
	}
	
	// Check if file already exists
	if (file_exists($target_file)) {
	  echo "Sorry, file already exists.";
	  $uploadOk = 0;
	}
	
	// Check file size
	if ($_FILES["txtimage"]["size"][0] > 500000) {
	  echo "Sorry, your file is too large.";
	  $uploadOk = 0;
	}
	
	// Allow certain file formats
	if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
	&& $imageFileType != "gif" ) {
	  echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
	  $uploadOk = 0;
	}
	
	// Check if $uploadOk is set to 0 by an error
	if ($uploadOk == 0) {
	  echo "Sorry, your file was might not uploaded.";
	// if everything is ok, try to upload file
	} else {
	  if (move_uploaded_file($_FILES["txtimage"]["tmp_name"][0], $target_file)) {
		echo "The file ". htmlspecialchars($fileName_1). " has been uploaded.";
	  } else {
		echo "Sorry, there was an error uploading your file.";
	  }
	}
	$filename_upload_1 = htmlspecialchars($fileName_1);
	$filename_upload_link_1 = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 
	"https" : "http") . "://" . $_SERVER['HTTP_HOST'] . '/wp-content/uploads/' . $filename_upload_1; 
	}elseif($countfiles == 2){
	$fileName_1 = basename($_FILES["txtimage"]["name"][0]);
	$fileName_2 = basename($_FILES["txtimage"]["name"][1]);
	$target_file_1 = $target_dir .'/'. $fileName_1;
	$target_file_2 = $target_dir .'/'. $fileName_2;
	$uploadOk = 1;
	$imageFileType_1 = strtolower(pathinfo($target_file_1,PATHINFO_EXTENSION));
	$imageFileType_2 = strtolower(pathinfo($target_file_2,PATHINFO_EXTENSION));
	
	// Check if image file is a actual image or fake image
	if(isset($_POST["submit"])) {
	  $check = getimagesize($_FILES["txtimage"]["tmp_name"][0]);
	  if($check !== false) {
		echo "File is an image - " . $check["mime"] . ".";
		$uploadOk = 1;
	  } else {
		echo "File is not an image.";
		$uploadOk = 0;
	  }
	}
	
	// Check if file already exists
	if (file_exists($target_file_1)) {
	  echo "Sorry, file already exists.";
	  $uploadOk = 0;
	}
	
	// Check file size
	if ($_FILES["txtimage"]["size"][0] > 500000) {
	  echo "Sorry, your file is too large.";
	  $uploadOk = 0;
	}
	
	// Allow certain file formats
	if($imageFileType_1 != "jpg" && $imageFileType_1 != "png" && $imageFileType_1 != "jpeg"
	&& $imageFileType_1 != "gif" ) {
	  echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
	  $uploadOk = 0;
	}
	
	// Check if $uploadOk is set to 0 by an error
	if ($uploadOk == 0) {
	  echo "Sorry, your file was might not uploaded.";
	// if everything is ok, try to upload file
	} else {
	  if (move_uploaded_file($_FILES["txtimage"]["tmp_name"][0], $target_file_1) && move_uploaded_file($_FILES["txtimage"]["tmp_name"][1], $target_file_2)) {
		echo "The file ". htmlspecialchars($fileName_1). "and" .htmlspecialchars($fileName_2). " has been uploaded.";
	  }else {
		echo "Sorry, there was an error uploading your file.";
	  }
	}
	$filename_upload_1 = htmlspecialchars($fileName_1);
	$filename_upload_link_1 = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 
	"https" : "http") . "://" . $_SERVER['HTTP_HOST'] . '/wp-content/uploads/' . $filename_upload_1;
	$filename_upload_2 = htmlspecialchars($fileName_2);
	$filename_upload_link_2 = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 
	"https" : "http") . "://" . $_SERVER['HTTP_HOST'] . '/wp-content/uploads/' . $filename_upload_2;
	}elseif($countfiles == 3){
		$fileName_1 = basename($_FILES["txtimage"]["name"][0]);
		$fileName_2 = basename($_FILES["txtimage"]["name"][1]);
		$fileName_3 = basename($_FILES["txtimage"]["name"][2]);
		$target_file_1 = $target_dir .'/'. $fileName_1;
		$target_file_2 = $target_dir .'/'. $fileName_2;
		$target_file_3 = $target_dir .'/'. $fileName_3;
		$uploadOk = 1;
		$imageFileType_1 = strtolower(pathinfo($target_file_1,PATHINFO_EXTENSION));
		$imageFileType_2 = strtolower(pathinfo($target_file_2,PATHINFO_EXTENSION));
		$imageFileType_3 = strtolower(pathinfo($target_file_3,PATHINFO_EXTENSION));
		
		// Check if image file is a actual image or fake image
		if(isset($_POST["submit"])) {
		  $check = getimagesize($_FILES["txtimage"]["tmp_name"][0]);
		  if($check !== false) {
			echo "File is an image - " . $check["mime"] . ".";
			$uploadOk = 1;
		  } else {
			echo "File is not an image.";
			$uploadOk = 0;
		  }
		}
		
		// Check if file already exists
		if (file_exists($target_file_1)) {
		  echo "Sorry, file already exists.";
		  $uploadOk = 0;
		}
		
		// Check file size
		if ($_FILES["txtimage"]["size"][0] > 500000) {
		  echo "Sorry, your file is too large.";
		  $uploadOk = 0;
		}
		
		// Allow certain file formats
		if($imageFileType_1 != "jpg" && $imageFileType_1 != "png" && $imageFileType_1 != "jpeg"
		&& $imageFileType_1 != "gif" ) {
		  echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
		  $uploadOk = 0;
		}
		
		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk == 0) {
		  echo "Sorry, your file was might not uploaded.";
		// if everything is ok, try to upload file
		} else {
		  if (move_uploaded_file($_FILES["txtimage"]["tmp_name"][0], $target_file_1) && move_uploaded_file($_FILES["txtimage"]["tmp_name"][1], $target_file_2) && move_uploaded_file($_FILES["txtimage"]["tmp_name"][2], $target_file_3)) {
			echo "The file ". htmlspecialchars($fileName_1). "," .htmlspecialchars($fileName_2). "and" .htmlspecialchars($fileName_3)." has been uploaded.";
		  }else {
			echo "Sorry, there was an error uploading your file.";
		  }
		}
		$filename_upload_1 = htmlspecialchars($fileName_1);
		$filename_upload_link_1 = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 
		"https" : "http") . "://" . $_SERVER['HTTP_HOST'] . '/wp-content/uploads/' . $filename_upload_1;
		$filename_upload_2 = htmlspecialchars($fileName_2);
		$filename_upload_link_2 = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 
		"https" : "http") . "://" . $_SERVER['HTTP_HOST'] . '/wp-content/uploads/' . $filename_upload_2;
		$filename_upload_3 = htmlspecialchars($fileName_3);
		$filename_upload_link_3 = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 
		"https" : "http") . "://" . $_SERVER['HTTP_HOST'] . '/wp-content/uploads/' . $filename_upload_3;
	}else{
		echo 'You can upload upto 3 images only';
	}
}
    if (!empty($action) && $action == "edit") {
	if($countfiles == 1){
        $wpdb->update("$table_name", array( 
			'time' => date("Y-m-d h:i:s"),
            "name" => $_POST['txtname'],
			"email" => $_POST['txtemail'],
			"comment" => $_POST['txtcomment'],
			"image" => $filename_upload_link_1, 
            "rating" => $_POST['txtrating'],
            "edit" => 1,
                ), array(
            "id" => $id
        ));
		$msg = "Form data updated successfully";
	}elseif($countfiles == 2){
        $wpdb->update("$table_name", array( 
			'time' => date("Y-m-d h:i:s"),
            "name" => $_POST['txtname'],
			"email" => $_POST['txtemail'],
			"comment" => $_POST['txtcomment'],
			"image" => $filename_upload_link_1.' , '.$filename_upload_link_2, 
            "rating" => $_POST['txtrating'],
            "edit" => 1,
                ), array(
            "id" => $id
        ));
		$msg = "Form data updated successfully";
	}elseif($countfiles == 3){
        $wpdb->update("$table_name", array( 
			'time' => date("Y-m-d h:i:s"),
            "name" => $_POST['txtname'],
			"email" => $_POST['txtemail'],
			"comment" => $_POST['txtcomment'],
			"image" => $filename_upload_link_1.' , '.$filename_upload_link_2.' , '.$filename_upload_link_3, 
            "rating" => $_POST['txtrating'],
            "edit" => 1,
                ), array(
            "id" => $id
        ));
		$msg = "Form data updated successfully";
	}
    } else {
		if($countfiles == 1){
			$wpdb->insert("$table_name", array( 
				'time' => date("Y-m-d h:i:s"),
				"name" => $_POST['txtname'],
				"email" => $_POST['txtemail'],
				"comment" => $_POST['txtcomment'],
				"image" => $filename_upload_link_1, 
				"rating" => $_POST['txtrating'],
				));
		}elseif($countfiles == 2){
			$wpdb->insert("$table_name", array( 
				'time' => date("Y-m-d h:i:s"),
				"name" => $_POST['txtname'],
				"email" => $_POST['txtemail'],
				"comment" => $_POST['txtcomment'],
				"image" => $filename_upload_link_1.' , '.$filename_upload_link_2, 
				"rating" => $_POST['txtrating'],
				));
		}elseif($countfiles == 3){
			$wpdb->insert("$table_name", array( 
				'time' => date("Y-m-d h:i:s"),
				"name" => $_POST['txtname'],
				"email" => $_POST['txtemail'],
				"comment" => $_POST['txtcomment'],
				"image" => $filename_upload_link_1.' , '.$filename_upload_link_2.' , '.$filename_upload_link_3, 
				"rating" => $_POST['txtrating'],
				));
		}
        if ($wpdb->insert_id > 0) {
            $msg = "Form data saved successfully";
        } else {
            $msg = "Failed to save data";
        }
	}
	//Post property to Facebook
	global $wpdb;
	$table_name_fb = $wpdb->prefix . 'reviewmonial_fb';
	$show_review_data = $wpdb->get_results("SELECT * FROM $table_name_fb order by id desc limit 1");  
	$show_review_data= json_decode( json_encode($show_review_data), true);
	if (is_array($show_review_data) || is_object($show_review_data)){
		foreach($show_review_data as $show_review_d){
	$fb = new \Facebook\Facebook([
		'app_id' => $show_review_d['fbappid'], 
		'secret_id' => $show_review_d['fbappsecret'], 
		'default_graph_version' => 'v2.10',
	]);
	$accessToken = $show_review_d['fbaccesstoken']; 
	
	if($countfiles == 1){
		try {
			// Returns a `FacebookFacebookResponse` object
			$response = $fb->post(
				'/'.$show_review_d['fbpageid'].'/photos', //CHANGE IT
				array (
				'message' => $_POST['txtname'] . ' - ' . $_POST['txtcomment'] . ' - ' . $_POST['txtrating'] . 'stars',
				'source' => $fb->fileToUpload("$filename_upload_link_1")
				), $accessToken
			);
			} catch(FacebookExceptionsFacebookResponseException $e) {
			echo 'Graph returned an error: ' . $e->getMessage();
			exit;
			} catch(FacebookExceptionsFacebookSDKException $e) {
			echo 'Facebook SDK returned an error: ' . $e->getMessage();
			exit;
			}
			$graphNode = $response->getGraphNode();
			$graphNode = $response->getGraphNode();
		}elseif($countfiles == 2){
			try{
				$uploadimage1 = $fb->post('/me/photos', ['published' => 'false', 'source' => $fb->fileToUpload($filename_upload_link_1)], $accessToken);
				$uploadimage2 = $fb->post('/me/photos', ['published' => 'false', 'source' => $fb->fileToUpload($filename_upload_link_2)], $accessToken);
	
				$uploadimage1 = $uploadimage1->getGraphNode()->asArray();
				$uploadimage2 = $uploadimage2->getGraphNode()->asArray();
	
				$image1 = $uploadimage1['id'];
				$image2 = $uploadimage2['id'];
	
				$response = $fb->post('/me/feed', [
					'attached_media[0]' => '{media_fbid:"'.$image1.'"}',
					'attached_media[1]' => '{media_fbid:"'.$image2.'"}',
					'message' => $_POST['txtname'] . ' - ' . $_POST['txtcomment'] . ' - ' . $_POST['txtrating'] . 'stars',
				], $accessToken);
	
			}catch(FacebookExceptionsFacebookResponseException $e) {
				echo 'Graph returned an error: ' . $e->getMessage();
				exit;
			  } catch(FacebookExceptionsFacebookSDKException $e) {
				echo 'Facebook SDK returned an error: ' . $e->getMessage();
				exit;
			  }
			  $graphNode = $response->getGraphNode();
			  $graphNode = $response->getGraphNode();
		}elseif($countfiles == 3){
			try{
				$uploadimage1 = $fb->post('/me/photos', ['published' => 'false', 'source' => $fb->fileToUpload($filename_upload_link_1)], $accessToken);
				$uploadimage2 = $fb->post('/me/photos', ['published' => 'false', 'source' => $fb->fileToUpload($filename_upload_link_2)], $accessToken);
				$uploadimage3 = $fb->post('/me/photos', ['published' => 'false', 'source' => $fb->fileToUpload($filename_upload_link_3)], $accessToken);
				
				$uploadimage1 = $uploadimage1->getGraphNode()->asArray();
				$uploadimage2 = $uploadimage2->getGraphNode()->asArray();
				$uploadimage3 = $uploadimage3->getGraphNode()->asArray();
				
				$image1 = $uploadimage1['id'];
				$image2 = $uploadimage2['id'];
				$image3 = $uploadimage3['id'];
				
				$response = $fb->post('/me/feed', [
					'attached_media[0]' => '{media_fbid:"'.$image1.'"}',
					'attached_media[1]' => '{media_fbid:"'.$image2.'"}',
					'attached_media[2]' => '{media_fbid:"'.$image3.'"}',
					'message' => $_POST['txtname'] . ' - ' . $_POST['txtcomment'] . ' - ' . $_POST['txtrating'] . 'stars',
				], $accessToken);
	
			}catch(FacebookExceptionsFacebookResponseException $e) {
				echo 'Graph returned an error: ' . $e->getMessage();
				exit;
			  } catch(FacebookExceptionsFacebookSDKException $e) {
				echo 'Facebook SDK returned an error: ' . $e->getMessage();
				exit;
			  }
			  $graphNode = $response->getGraphNode();
			  $graphNode = $response->getGraphNode();
		}else{
			echo'You can upload upto 3 images only';
		}
	//Success Alert Message
	echo '<div class="alert alert-success">
		<strong>Success!</strong> Your review has been submitted.
	</div>';
}}}
	?>
	<p><?php echo $msg; ?></p>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?page=reviewnomial-edit<?php
	if (!empty($action)) {
		echo '&action=edit&id=' . $id;
	}
	?>" method="post" enctype="multipart/form-data">
		<p>
		<?php echo $row_details['name']; ?>
			<label>
				Name
			</label>
			<input type="text" name="txtname" value="<?php echo isset($row_details['name']) ? $row_details['name'] : ""; ?>" placeholder="Enter name" required/>
		</p>
		<p>
			<label>
				Email
			</label>
			<input type="email" name="txtemail" value="<?php echo isset($row_details['email']) ? $row_details['email'] : ""; ?>" placeholder="Enter email" required/>
		</p>
		<p>
			<label>
				Comment
			</label>
			<input type="text" name="txtcomment" value="<?php echo isset($row_details['comment']) ? $row_details['comment'] : ""; ?>" placeholder="Enter comment" required/>
		</p>
		<p>
			<label>
				Image
			</label>
			<input type="file" id="txtimage" name="txtimage[]" accept="image/*" multiple required/>
		</p>
		<p>
			<label>
				Rating
			</label>
			<input type="text" name="txtrating" value="<?php echo isset($row_details['rating']) ? $row_details['rating'] : ""; ?>" placeholder="Enter rating" required/>
		</p>
		<p>
			<button type="submit" name="btn-submit">Submit</button>
		</p>
	</form>
	<?php
}

function approveFunction()
{
global $wpdb;
$msg = '';

$action = isset($_GET['action']) ? trim($_GET['action']) : "";
$id = isset($_GET['id']) ? intval($_GET['id']) : "";
$table_name = $wpdb->prefix . 'reviewmonial_review';
$row_details = $wpdb->get_row(
        $wpdb->prepare(
                "SELECT * from $table_name WHERE id = %d", $id 
        ), ARRAY_A
);

if (isset($_POST['btn-submit'])) {

    $action = isset($_GET['action']) ? trim($_GET['action']) : "";
	$id = isset($_GET['id']) ? intval($_GET['id']) : "";
    if (!empty($action) && $action == "approve") {
			$wpdb->update("$table_name", array( 
				'time' => date("Y-m-d h:i:s"),
				"name" => $_POST['txtname'],
				"email" => $_POST['txtemail'],
				"comment" => $_POST['txtcomment'],
				"image" => $_POST['txtimage'],
				"rating" => $_POST['txtrating'],
				"edit" => 1,
					), array(
				"id" => $id
			));
	$msg = "Form data updated successfully";

	$show_review_data = $wpdb->get_results("SELECT * FROM $table_name");  
	$show_review_data= json_decode( json_encode($show_review_data), true);
	if (is_array($show_review_data) || is_object($show_review_data)){
		foreach($show_review_data as $show_review_d){
			$image_string = $show_review_d['image'];
			$image_string_explode = explode(" , ", $image_string);
			$image_string_explode_1 = $image_string_explode[0];
		}
	}
	//Post property to Facebook
	global $wpdb;
	$table_name_fb = $wpdb->prefix . 'reviewmonial_fb';
	$show_review_data = $wpdb->get_results("SELECT * FROM $table_name_fb order by id desc limit 1");  
	$show_review_data= json_decode( json_encode($show_review_data), true);
	if (is_array($show_review_data) || is_object($show_review_data)){
		foreach($show_review_data as $show_review_d){
	$fb = new \Facebook\Facebook([
		'app_id' => $show_review_d['fbappid'], 
		'secret_id' => $show_review_d['fbappsecret'], 
		'default_graph_version' => 'v2.10',
	]);
	$accessToken = $show_review_d['fbaccesstoken']; 
	if($countfiles == 1){
		try {
			// Returns a `FacebookFacebookResponse` object
			$response = $fb->post(
				'/'.$show_review_d['fbpageid'].'/photos', //CHANGE IT
				array (
				'message' => $_POST['txtname'] . ' - ' . $_POST['txtcomment'] . ' - ' . $_POST['txtrating'] . 'stars',
				'source' => $fb->fileToUpload("$filename_upload_link_1")
				), $accessToken
			);
			} catch(FacebookExceptionsFacebookResponseException $e) {
			echo 'Graph returned an error: ' . $e->getMessage();
			exit;
			} catch(FacebookExceptionsFacebookSDKException $e) {
			echo 'Facebook SDK returned an error: ' . $e->getMessage();
			exit;
			}
			$graphNode = $response->getGraphNode();
			$graphNode = $response->getGraphNode();
		}elseif($countfiles == 2){
			try{
				$uploadimage1 = $fb->post('/me/photos', ['published' => 'false', 'source' => $fb->fileToUpload($filename_upload_link_1)], $accessToken);
				$uploadimage2 = $fb->post('/me/photos', ['published' => 'false', 'source' => $fb->fileToUpload($filename_upload_link_2)], $accessToken);
	
				$uploadimage1 = $uploadimage1->getGraphNode()->asArray();
				$uploadimage2 = $uploadimage2->getGraphNode()->asArray();
	
				$image1 = $uploadimage1['id'];
				$image2 = $uploadimage2['id'];
	
				$response = $fb->post('/me/feed', [
					'attached_media[0]' => '{media_fbid:"'.$image1.'"}',
					'attached_media[1]' => '{media_fbid:"'.$image2.'"}',
					'message' => $_POST['txtname'] . ' - ' . $_POST['txtcomment'] . ' - ' . $_POST['txtrating'] . 'stars',
				], $accessToken);
	
			}catch(FacebookExceptionsFacebookResponseException $e) {
				echo 'Graph returned an error: ' . $e->getMessage();
				exit;
			  } catch(FacebookExceptionsFacebookSDKException $e) {
				echo 'Facebook SDK returned an error: ' . $e->getMessage();
				exit;
			  }
			  $graphNode = $response->getGraphNode();
			  $graphNode = $response->getGraphNode();
		}elseif($countfiles == 3){
			try{
				$uploadimage1 = $fb->post('/me/photos', ['published' => 'false', 'source' => $fb->fileToUpload($filename_upload_link_1)], $accessToken);
				$uploadimage2 = $fb->post('/me/photos', ['published' => 'false', 'source' => $fb->fileToUpload($filename_upload_link_2)], $accessToken);
				$uploadimage3 = $fb->post('/me/photos', ['published' => 'false', 'source' => $fb->fileToUpload($filename_upload_link_3)], $accessToken);
				
				$uploadimage1 = $uploadimage1->getGraphNode()->asArray();
				$uploadimage2 = $uploadimage2->getGraphNode()->asArray();
				$uploadimage3 = $uploadimage3->getGraphNode()->asArray();
				
				$image1 = $uploadimage1['id'];
				$image2 = $uploadimage2['id'];
				$image3 = $uploadimage3['id'];
				
				$response = $fb->post('/me/feed', [
					'attached_media[0]' => '{media_fbid:"'.$image1.'"}',
					'attached_media[1]' => '{media_fbid:"'.$image2.'"}',
					'attached_media[2]' => '{media_fbid:"'.$image3.'"}',
					'message' => $_POST['txtname'] . ' - ' . $_POST['txtcomment'] . ' - ' . $_POST['txtrating'] . 'stars',
				], $accessToken);
	
			}catch(FacebookExceptionsFacebookResponseException $e) {
				echo 'Graph returned an error: ' . $e->getMessage();
				exit;
			  } catch(FacebookExceptionsFacebookSDKException $e) {
				echo 'Facebook SDK returned an error: ' . $e->getMessage();
				exit;
			  }
			  $graphNode = $response->getGraphNode();
			  $graphNode = $response->getGraphNode();
		}else{
			echo'You can upload upto 3 images only';
		}
		//Success Alert Message
		echo '<div class="alert alert-success">
			<strong>Success!</strong> Your review has been submitted.
		</div>';
		}
	}
	}else{
		echo 'Nothing to approve';
	}
}
	?>
	<p><?php echo $msg; ?></p>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?page=reviewnomial-approve<?php
	if (!empty($action)) {
		echo '&action=approve&id=' . $id;
	}
	?>" method="post" enctype="multipart/form-data">
		<p>
		<?php echo $row_details['name']; ?>
			<label>
				Name
			</label>
			<input type="text" name="txtname" value="<?php echo isset($row_details['name']) ? $row_details['name'] : ""; ?>" placeholder="Enter name" required/>
		</p>
		<p>
			<label>
				Email
			</label>
			<input type="email" name="txtemail" value="<?php echo isset($row_details['email']) ? $row_details['email'] : ""; ?>" placeholder="Enter email" required/>
		</p>
		<p>
			<label>
				Comment
			</label>
			<input type="text" name="txtcomment" value="<?php echo isset($row_details['comment']) ? $row_details['comment'] : ""; ?>" placeholder="Enter comment" required/>
		</p>
		<p>
			<label>
				Image
			</label>
			<input type="text" name="txtimage" value="<?php echo isset($row_details['image']) ? $row_details['image'] : ""; ?>" placeholder="Enter image" required/>
		</p>
		<p>
			<label>
				Rating
			</label>
			<input type="text" name="txtrating" value="<?php echo isset($row_details['rating']) ? $row_details['rating'] : ""; ?>" placeholder="Enter rating" required/>
		</p>
		<p>
			<button type="submit" name="btn-submit">Submit</button>
		</p>
	</form>
	<?php
}
function settingFunction()
{
global $wpdb;
$msg = '';
$table_name_2 = $wpdb->prefix . 'reviewmonial_fb';

if (isset($_POST['btn-fb-submit'])) {
			$wpdb->insert("$table_name_2", array( 
				'time' => date("Y-m-d h:i:s"),
				"fbappid" => $_POST['txtfbappid'],
				"fbappsecret" => $_POST['txtfbappsecret'],
				"fbaccesstoken" => $_POST['txtfbaccesstoken'],
				"fbpageid" => $_POST['txtfbpageid'],
				));
	$msg = "Form data updated successfully"; }
?>
	<p><?php echo $msg; ?></p>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?page=reviewnomial-setting" method="post" enctype="multipart/form-data">
		<p>
		<?php echo $row_details['name']; ?>
			<label>
				Facebook App ID
			</label>
			<input type="text" name="txtfbappid" value="<?php echo isset($row_details['txtfbappid']) ? $row_details['txtfbappid'] : ""; ?>" required/>
		</p>
		<p>
			<label>
				Facebook App Secret
			</label>
			<input type="text" name="txtfbappsecret" value="<?php echo isset($row_details['txtfbappsecret']) ? $row_details['txtfbappsecret'] : ""; ?>" required/>
		</p>
		<p>
			<label>
				Facebook Access Token
			</label>
			<input type="text" name="txtfbaccesstoken" value="<?php echo isset($row_details['txtfbaccesstoken']) ? $row_details['txtfbaccesstoken'] : ""; ?>" required/>
		</p>
		<p>
			<label>
				Facebook Page ID
			</label>
			<input type="text" name="txtfbpageid" value="<?php echo isset($row_details['txtfbpageid']) ? $row_details['txtfbpageid'] : ""; ?>" required/>
		</p>
		<p>
			<button type="submit" name="btn-fb-submit">Submit</button>
		</p>
	</form>
	<?php
}

//END Admin Dashboard

function html_form_code() {
	echo '<form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post" enctype="multipart/form-data">';
	echo '<p>';
	echo 'Your Name (required) <br/>';
	echo '<input type="text" name="cf-name" class="form-control" pattern="[a-zA-Z0-9 ]+" value="' . ( isset( $_POST["cf-name"] ) ? esc_attr( $_POST["cf-name"] ) : '' ) . '" size="40" required/>';
	echo '</p>';
	echo '<p>';
	echo 'Your Email (required) <br/>';
	echo '<input type="email" name="cf-email" class="form-control" value="' . ( isset( $_POST["cf-email"] ) ? esc_attr( $_POST["cf-email"] ) : '' ) . '" size="40" required/>';
	echo '</p>';
	echo '<p>';
	echo 'Your Review (required) <br/>';
	echo '<textarea rows="10" cols="35" name="cf-message" class="form-control" required>' . ( isset( $_POST["cf-message"] ) ? esc_attr( $_POST["cf-message"] ) : '' ) . '</textarea>';
	echo '</p>';
	echo '<p>';
	echo 'Select Image <a href="#" data-toggle="tooltip" data-placement="right" title="You can upload upto 3 images by holding CTRL or CMD!">(required)</a><br/>';
	echo '<input type="file" id="ct-image" name="ct-image[]" accept="image/*" multiple required>';
	echo '</p>';
    echo '<p>';
	echo 'Your Rating (required) <br/>';
    echo '<select name="cf-rating" id="cf-rating">
			<option value="1">1</option>
			<option value="2">2</option>
			<option value="3">3</option>
			<option value="4">4</option>
			<option value="5">5</option>' . ( isset( $_POST["cf-rating"] ) ? esc_attr( $_POST["cf-rating"] ) : '' ) . '</select>';
	echo '</p>';
	echo '<p><input type="submit" name="cf-submitted" class="btn btn-primary" value="Submit"></p>';
	echo '</form>';
	?>
	<script>
	$(document).ready(function(){
		$('[data-toggle="tooltip"]').tooltip();   
	  });
	</script>
	<?php
}

function deliver_mail() {
	// if the submit button is clicked, send the email
	global $wpdb;
	$table_name = $wpdb->prefix . 'reviewmonial_review';
	if ( isset( $_POST['cf-submitted'] ) ) {
		if ($_POST['cf-rating'] >= 4){
		global $wpdb;
		//Start Image Upload
		$upload_dir = wp_upload_dir();
		$target_dir = $_SERVER['DOCUMENT_ROOT'] .'/wp-content/uploads';
		$countfiles = count($_FILES['ct-image']['name']);
		for($i=0;$i<$countfiles;$i++){
		if($countfiles == 1){
		$fileName_1 = $_FILES["ct-image"]["name"][0];
		$target_file = $target_dir .'/'. $fileName_1;
		$uploadOk = 1;
		$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
		
		// Check if image file is a actual image or fake image
		if(isset($_POST["submit"])) {
		  $check = getimagesize($_FILES["ct-image"]["tmp_name"][0]);
		  if($check !== false) {
			echo "File is an image - " . $check["mime"] . ".";
			$uploadOk = 1;
		  } else {
			echo "File is not an image.";
			$uploadOk = 0;
		  }
		}
		
		// Check if file already exists
		if (file_exists($target_file)) {
		  echo "Sorry, file already exists.";
		  $uploadOk = 0;
		}
		
		// Check file size
		if ($_FILES["ct-image"]["size"][0] > 500000) {
		  echo "Sorry, your file is too large.";
		  $uploadOk = 0;
		}
		
		// Allow certain file formats
		if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
		&& $imageFileType != "gif" ) {
		  echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
		  $uploadOk = 0;
		}
		
		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk == 0) {
		  echo "Sorry, your file was might not uploaded.";
		// if everything is ok, try to upload file
		} else {
		  if (move_uploaded_file($_FILES["ct-image"]["tmp_name"][0], $target_file)) {
			echo "The file ". htmlspecialchars($fileName_1). " has been uploaded.";
		  } else {
			echo "Sorry, there was an error uploading your file.";
		  }
		}
		$filename_upload_1 = htmlspecialchars($fileName_1);
		$filename_upload_link_1 = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 
		"https" : "http") . "://" . $_SERVER['HTTP_HOST'] . '/wp-content/uploads/' . $filename_upload_1;
		$data = array(
			'time' => date("Y-m-d h:i:s"),
            'name' => $_POST['cf-name'],
            'email' => $_POST['cf-email'], 
			'comment' => $_POST['cf-message'],
			'image' => $filename_upload_link_1, 
            'rating' => $_POST['cf-rating'],
		);
		}elseif($countfiles == 2){
		$fileName_1 = basename($_FILES["ct-image"]["name"][0]);
		$fileName_2 = basename($_FILES["ct-image"]["name"][1]);
		$target_file_1 = $target_dir .'/'. $fileName_1;
		$target_file_2 = $target_dir .'/'. $fileName_2;
		$uploadOk = 1;
		$imageFileType_1 = strtolower(pathinfo($target_file_1,PATHINFO_EXTENSION));
		$imageFileType_2 = strtolower(pathinfo($target_file_2,PATHINFO_EXTENSION));
		
		// Check if image file is a actual image or fake image
		if(isset($_POST["submit"])) {
		  $check = getimagesize($_FILES["ct-image"]["tmp_name"][0]);
		  if($check !== false) {
			echo "File is an image - " . $check["mime"] . ".";
			$uploadOk = 1;
		  } else {
			echo "File is not an image.";
			$uploadOk = 0;
		  }
		}
		
		// Check if file already exists
		if (file_exists($target_file_1)) {
		  echo "Sorry, file already exists.";
		  $uploadOk = 0;
		}
		
		// Check file size
		if ($_FILES["ct-image"]["size"][0] > 500000) {
		  echo "Sorry, your file is too large.";
		  $uploadOk = 0;
		}
		
		// Allow certain file formats
		if($imageFileType_1 != "jpg" && $imageFileType_1 != "png" && $imageFileType_1 != "jpeg"
		&& $imageFileType_1 != "gif" ) {
		  echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
		  $uploadOk = 0;
		}
		
		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk == 0) {
		  echo "Sorry, your file was might not uploaded.";
		// if everything is ok, try to upload file
		} else {
		  if (move_uploaded_file($_FILES["ct-image"]["tmp_name"][0], $target_file_1) && move_uploaded_file($_FILES["ct-image"]["tmp_name"][1], $target_file_2)) {
			echo "The file ". htmlspecialchars($fileName_1). "and" .htmlspecialchars($fileName_2). " has been uploaded.";
		  }else {
			echo "Sorry, there was an error uploading your file.";
		  }
		}
		$filename_upload_1 = htmlspecialchars($fileName_1);
		$filename_upload_link_1 = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 
		"https" : "http") . "://" . $_SERVER['HTTP_HOST'] . '/wp-content/uploads/' . $filename_upload_1;
		$filename_upload_2 = htmlspecialchars($fileName_2);
		$filename_upload_link_2 = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 
		"https" : "http") . "://" . $_SERVER['HTTP_HOST'] . '/wp-content/uploads/' . $filename_upload_2;
		$data = array(
			'time' => date("Y-m-d h:i:s"),
			'name' => $_POST['cf-name'],
			'email' => $_POST['cf-email'], 
			'comment' => $_POST['cf-message'],
			'image' => $filename_upload_link_1.' , '.$filename_upload_link_2, 
			'rating' => $_POST['cf-rating'],
		);
		}elseif($countfiles == 3){
			$fileName_1 = basename($_FILES["ct-image"]["name"][0]);
			$fileName_2 = basename($_FILES["ct-image"]["name"][1]);
			$fileName_3 = basename($_FILES["ct-image"]["name"][2]);
			$target_file_1 = $target_dir .'/'. $fileName_1;
			$target_file_2 = $target_dir .'/'. $fileName_2;
			$target_file_3 = $target_dir .'/'. $fileName_3;
			$uploadOk = 1;
			$imageFileType_1 = strtolower(pathinfo($target_file_1,PATHINFO_EXTENSION));
			$imageFileType_2 = strtolower(pathinfo($target_file_2,PATHINFO_EXTENSION));
			$imageFileType_3 = strtolower(pathinfo($target_file_3,PATHINFO_EXTENSION));
			
			// Check if image file is a actual image or fake image
			if(isset($_POST["submit"])) {
			  $check = getimagesize($_FILES["ct-image"]["tmp_name"][0]);
			  if($check !== false) {
				echo "File is an image - " . $check["mime"] . ".";
				$uploadOk = 1;
			  } else {
				echo "File is not an image.";
				$uploadOk = 0;
			  }
			}
			
			// Check if file already exists
			if (file_exists($target_file_1)) {
			  echo "Sorry, file already exists.";
			  $uploadOk = 0;
			}
			
			// Check file size
			if ($_FILES["ct-image"]["size"][0] > 500000) {
			  echo "Sorry, your file is too large.";
			  $uploadOk = 0;
			}
			
			// Allow certain file formats
			if($imageFileType_1 != "jpg" && $imageFileType_1 != "png" && $imageFileType_1 != "jpeg"
			&& $imageFileType_1 != "gif" ) {
			  echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
			  $uploadOk = 0;
			}
			
			// Check if $uploadOk is set to 0 by an error
			if ($uploadOk == 0) {
			  echo "Sorry, your file was might not uploaded.";
			// if everything is ok, try to upload file
			} else {
			  if (move_uploaded_file($_FILES["ct-image"]["tmp_name"][0], $target_file_1) && move_uploaded_file($_FILES["ct-image"]["tmp_name"][1], $target_file_2) && move_uploaded_file($_FILES["ct-image"]["tmp_name"][2], $target_file_3)) {
				echo "The file ". htmlspecialchars($fileName_1). "," .htmlspecialchars($fileName_2). "and" .htmlspecialchars($fileName_3)." has been uploaded.";
			  }else {
				echo "Sorry, there was an error uploading your file.";
			  }
			}
			$filename_upload_1 = htmlspecialchars($fileName_1);
			$filename_upload_link_1 = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 
			"https" : "http") . "://" . $_SERVER['HTTP_HOST'] . '/wp-content/uploads/' . $filename_upload_1;
			$filename_upload_2 = htmlspecialchars($fileName_2);
			$filename_upload_link_2 = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 
			"https" : "http") . "://" . $_SERVER['HTTP_HOST'] . '/wp-content/uploads/' . $filename_upload_2;
			$filename_upload_3 = htmlspecialchars($fileName_3);
			$filename_upload_link_3 = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 
			"https" : "http") . "://" . $_SERVER['HTTP_HOST'] . '/wp-content/uploads/' . $filename_upload_3;
			$data = array(
				'time' => date("Y-m-d h:i:s"),
				'name' => $_POST['cf-name'],
				'email' => $_POST['cf-email'], 
				'comment' => $_POST['cf-message'],
				'image' => $filename_upload_link_1.' , '.$filename_upload_link_2.' , '.$filename_upload_link_3, 
				'rating' => $_POST['cf-rating'],
			);
		}else{
			echo 'You can upload upto 3 images only';
		}
	}
		//End Image Upload
		//Enter into DB
		$result = $wpdb->insert($table_name, $data);
		//Post property to Facebook
		global $wpdb;
		$table_name_fb = $wpdb->prefix . 'reviewmonial_fb';
		$show_review_data = $wpdb->get_results("SELECT * FROM $table_name_fb order by id desc limit 1");  
		$show_review_data= json_decode( json_encode($show_review_data), true);
		if (is_array($show_review_data) || is_object($show_review_data)){
			foreach($show_review_data as $show_review_d){
		$fb = new \Facebook\Facebook([
			'app_id' => $show_review_d['fbappid'], 
			'secret_id' => $show_review_d['fbappsecret'], 
			'default_graph_version' => 'v2.10',
		]);
		$accessToken = $show_review_d['fbaccesstoken']; 
if($countfiles <= 3){
		if($countfiles == 1){
			try {
				// Returns a `FacebookFacebookResponse` object
				$response = $fb->post(
					'/'.$show_review_d['fbpageid'].'/photos', //CHANGE IT
					array (
					'message' => $_POST['txtname'] . ' - ' . $_POST['txtcomment'] . ' - ' . $_POST['txtrating'] . 'stars',
					'source' => $fb->fileToUpload("$filename_upload_link_1")
					), $accessToken
				);
				} catch(FacebookExceptionsFacebookResponseException $e) {
				echo 'Graph returned an error: ' . $e->getMessage();
				exit;
				} catch(FacebookExceptionsFacebookSDKException $e) {
				echo 'Facebook SDK returned an error: ' . $e->getMessage();
				exit;
				}
				$graphNode = $response->getGraphNode();
				$graphNode = $response->getGraphNode();
			}elseif($countfiles == 2){
				try{
					$uploadimage1 = $fb->post('/me/photos', ['published' => 'false', 'source' => $fb->fileToUpload($filename_upload_link_1)], $accessToken);
					$uploadimage2 = $fb->post('/me/photos', ['published' => 'false', 'source' => $fb->fileToUpload($filename_upload_link_2)], $accessToken);
		
					$uploadimage1 = $uploadimage1->getGraphNode()->asArray();
					$uploadimage2 = $uploadimage2->getGraphNode()->asArray();
		
					$image1 = $uploadimage1['id'];
					$image2 = $uploadimage2['id'];
		
					$response = $fb->post('/me/feed', [
						'attached_media[0]' => '{media_fbid:"'.$image1.'"}',
						'attached_media[1]' => '{media_fbid:"'.$image2.'"}',
						'message' => $_POST['txtname'] . ' - ' . $_POST['txtcomment'] . ' - ' . $_POST['txtrating'] . 'stars',
					], $accessToken);
		
				}catch(FacebookExceptionsFacebookResponseException $e) {
					echo 'Graph returned an error: ' . $e->getMessage();
					exit;
				  } catch(FacebookExceptionsFacebookSDKException $e) {
					echo 'Facebook SDK returned an error: ' . $e->getMessage();
					exit;
				  }
				  $graphNode = $response->getGraphNode();
				  $graphNode = $response->getGraphNode();
			}elseif($countfiles == 3){
				try{
					$uploadimage1 = $fb->post('/me/photos', ['published' => 'false', 'source' => $fb->fileToUpload($filename_upload_link_1)], $accessToken);
					$uploadimage2 = $fb->post('/me/photos', ['published' => 'false', 'source' => $fb->fileToUpload($filename_upload_link_2)], $accessToken);
					$uploadimage3 = $fb->post('/me/photos', ['published' => 'false', 'source' => $fb->fileToUpload($filename_upload_link_3)], $accessToken);
					
					$uploadimage1 = $uploadimage1->getGraphNode()->asArray();
					$uploadimage2 = $uploadimage2->getGraphNode()->asArray();
					$uploadimage3 = $uploadimage3->getGraphNode()->asArray();
					
					$image1 = $uploadimage1['id'];
					$image2 = $uploadimage2['id'];
					$image3 = $uploadimage3['id'];
					
					$response = $fb->post('/me/feed', [
						'attached_media[0]' => '{media_fbid:"'.$image1.'"}',
						'attached_media[1]' => '{media_fbid:"'.$image2.'"}',
						'attached_media[2]' => '{media_fbid:"'.$image3.'"}',
						'message' => $_POST['txtname'] . ' - ' . $_POST['txtcomment'] . ' - ' . $_POST['txtrating'] . 'stars',
					], $accessToken);
		
				}catch(FacebookExceptionsFacebookResponseException $e) {
					echo 'Graph returned an error: ' . $e->getMessage();
					exit;
				  } catch(FacebookExceptionsFacebookSDKException $e) {
					echo 'Facebook SDK returned an error: ' . $e->getMessage();
					exit;
				  }
				  $graphNode = $response->getGraphNode();
				  $graphNode = $response->getGraphNode();
			}else{
				echo'You can upload upto 3 images only';
			}
		//Success Alert Message
		echo '<div class="alert alert-success">
			<strong>Success!</strong> Your review has been submitted.
		</div>';
		}else{
			echo '<div class="alert alert-danger">
			<strong>Warning!</strong> Your review has not been submitted.
		</div>';
		}
		}}}else{
			global $wpdb;
			//Start Image Upload
			$upload_dir = wp_upload_dir();
			$target_dir = $_SERVER['DOCUMENT_ROOT'] .'/wp-content/uploads';
			$countfiles = count($_FILES['ct-image']['name']);
			for($i=0;$i<$countfiles;$i++){
			if($countfiles == 1){
			$fileName_1 = $_FILES["ct-image"]["name"][0];
			$target_file = $target_dir .'/'. $fileName_1;
			$uploadOk = 1;
			$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
			
			// Check if image file is a actual image or fake image
			if(isset($_POST["submit"])) {
			  $check = getimagesize($_FILES["ct-image"]["tmp_name"][0]);
			  if($check !== false) {
				echo "File is an image - " . $check["mime"] . ".";
				$uploadOk = 1;
			  } else {
				echo "File is not an image.";
				$uploadOk = 0;
			  }
			}
			
			// Check if file already exists
			if (file_exists($target_file)) {
			  echo "Sorry, file already exists.";
			  $uploadOk = 0;
			}
			
			// Check file size
			if ($_FILES["ct-image"]["size"][0] > 500000) {
			  echo "Sorry, your file is too large.";
			  $uploadOk = 0;
			}
			
			// Allow certain file formats
			if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
			&& $imageFileType != "gif" ) {
			  echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
			  $uploadOk = 0;
			}
			
			// Check if $uploadOk is set to 0 by an error
			if ($uploadOk == 0) {
			  echo "Sorry, your file was might not uploaded.";
			// if everything is ok, try to upload file
			} else {
			  if (move_uploaded_file($_FILES["ct-image"]["tmp_name"][0], $target_file)) {
				echo "The file ". htmlspecialchars($fileName_1). " has been uploaded.";
			  } else {
				echo "Sorry, there was an error uploading your file.";
			  }
			}
			$filename_upload_1 = htmlspecialchars($fileName_1);
			$filename_upload_link_1 = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 
			"https" : "http") . "://" . $_SERVER['HTTP_HOST'] . '/wp-content/uploads/' . $filename_upload_1;
			$data = array(
				'time' => date("Y-m-d h:i:s"),
				'name' => $_POST['cf-name'],
				'email' => $_POST['cf-email'], 
				'comment' => $_POST['cf-message'],
				'image' => $filename_upload_link_1, 
				'rating' => $_POST['cf-rating'],
			);
			}elseif($countfiles == 2){
			$fileName_1 = basename($_FILES["ct-image"]["name"][0]);
			$fileName_2 = basename($_FILES["ct-image"]["name"][1]);
			$target_file_1 = $target_dir .'/'. $fileName_1;
			$target_file_2 = $target_dir .'/'. $fileName_2;
			$uploadOk = 1;
			$imageFileType_1 = strtolower(pathinfo($target_file_1,PATHINFO_EXTENSION));
			$imageFileType_2 = strtolower(pathinfo($target_file_2,PATHINFO_EXTENSION));
			
			// Check if image file is a actual image or fake image
			if(isset($_POST["submit"])) {
			  $check = getimagesize($_FILES["ct-image"]["tmp_name"][0]);
			  if($check !== false) {
				echo "File is an image - " . $check["mime"] . ".";
				$uploadOk = 1;
			  } else {
				echo "File is not an image.";
				$uploadOk = 0;
			  }
			}
			
			// Check if file already exists
			if (file_exists($target_file_1)) {
			  echo "Sorry, file already exists.";
			  $uploadOk = 0;
			}
			
			// Check file size
			if ($_FILES["ct-image"]["size"][0] > 500000) {
			  echo "Sorry, your file is too large.";
			  $uploadOk = 0;
			}
			
			// Allow certain file formats
			if($imageFileType_1 != "jpg" && $imageFileType_1 != "png" && $imageFileType_1 != "jpeg"
			&& $imageFileType_1 != "gif" ) {
			  echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
			  $uploadOk = 0;
			}
			
			// Check if $uploadOk is set to 0 by an error
			if ($uploadOk == 0) {
			  echo "Sorry, your file was might not uploaded.";
			// if everything is ok, try to upload file
			} else {
			  if (move_uploaded_file($_FILES["ct-image"]["tmp_name"][0], $target_file_1) && move_uploaded_file($_FILES["ct-image"]["tmp_name"][1], $target_file_2)) {
				echo "The file ". htmlspecialchars($fileName_1). "and" .htmlspecialchars($fileName_2). " has been uploaded.";
			  }else {
				echo "Sorry, there was an error uploading your file.";
			  }
			}
			$filename_upload_1 = htmlspecialchars($fileName_1);
			$filename_upload_link_1 = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 
			"https" : "http") . "://" . $_SERVER['HTTP_HOST'] . '/wp-content/uploads/' . $filename_upload_1;
			$filename_upload_2 = htmlspecialchars($fileName_2);
			$filename_upload_link_2 = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 
			"https" : "http") . "://" . $_SERVER['HTTP_HOST'] . '/wp-content/uploads/' . $filename_upload_2;
			$data = array(
				'time' => date("Y-m-d h:i:s"),
				'name' => $_POST['cf-name'],
				'email' => $_POST['cf-email'], 
				'comment' => $_POST['cf-message'],
				'image' => $filename_upload_link_1.' , '.$filename_upload_link_2, 
				'rating' => $_POST['cf-rating'],
			);
			}elseif($countfiles == 3){
				$fileName_1 = basename($_FILES["ct-image"]["name"][0]);
				$fileName_2 = basename($_FILES["ct-image"]["name"][1]);
				$fileName_3 = basename($_FILES["ct-image"]["name"][2]);
				$target_file_1 = $target_dir .'/'. $fileName_1;
				$target_file_2 = $target_dir .'/'. $fileName_2;
				$target_file_3 = $target_dir .'/'. $fileName_3;
				$uploadOk = 1;
				$imageFileType_1 = strtolower(pathinfo($target_file_1,PATHINFO_EXTENSION));
				$imageFileType_2 = strtolower(pathinfo($target_file_2,PATHINFO_EXTENSION));
				$imageFileType_3 = strtolower(pathinfo($target_file_3,PATHINFO_EXTENSION));
				
				// Check if image file is a actual image or fake image
				if(isset($_POST["submit"])) {
				  $check = getimagesize($_FILES["ct-image"]["tmp_name"][0]);
				  if($check !== false) {
					echo "File is an image - " . $check["mime"] . ".";
					$uploadOk = 1;
				  } else {
					echo "File is not an image.";
					$uploadOk = 0;
				  }
				}
				
				// Check if file already exists
				if (file_exists($target_file_1)) {
				  echo "Sorry, file already exists.";
				  $uploadOk = 0;
				}
				
				// Check file size
				if ($_FILES["ct-image"]["size"][0] > 500000) {
				  echo "Sorry, your file is too large.";
				  $uploadOk = 0;
				}
				
				// Allow certain file formats
				if($imageFileType_1 != "jpg" && $imageFileType_1 != "png" && $imageFileType_1 != "jpeg"
				&& $imageFileType_1 != "gif" ) {
				  echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
				  $uploadOk = 0;
				}
				
				// Check if $uploadOk is set to 0 by an error
				if ($uploadOk == 0) {
				  echo "Sorry, your file was might not uploaded.";
				// if everything is ok, try to upload file
				} else {
				  if (move_uploaded_file($_FILES["ct-image"]["tmp_name"][0], $target_file_1) && move_uploaded_file($_FILES["ct-image"]["tmp_name"][1], $target_file_2) && move_uploaded_file($_FILES["ct-image"]["tmp_name"][2], $target_file_3)) {
					echo "The file ". htmlspecialchars($fileName_1). "," .htmlspecialchars($fileName_2). "and" .htmlspecialchars($fileName_3)." has been uploaded.";
				  }else {
					echo "Sorry, there was an error uploading your file.";
				  }
				}
				$filename_upload_1 = htmlspecialchars($fileName_1);
				$filename_upload_link_1 = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 
				"https" : "http") . "://" . $_SERVER['HTTP_HOST'] . '/wp-content/uploads/' . $filename_upload_1;
				$filename_upload_2 = htmlspecialchars($fileName_2);
				$filename_upload_link_2 = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 
				"https" : "http") . "://" . $_SERVER['HTTP_HOST'] . '/wp-content/uploads/' . $filename_upload_2;
				$filename_upload_3 = htmlspecialchars($fileName_3);
				$filename_upload_link_3 = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 
				"https" : "http") . "://" . $_SERVER['HTTP_HOST'] . '/wp-content/uploads/' . $filename_upload_3;
				$data = array(
					'time' => date("Y-m-d h:i:s"),
					'name' => $_POST['cf-name'],
					'email' => $_POST['cf-email'], 
					'comment' => $_POST['cf-message'],
					'image' => $filename_upload_link_1.' , '.$filename_upload_link_2.' , '.$filename_upload_link_3, 
					'rating' => $_POST['cf-rating'],
				);
			}else{
				echo 'You can upload upto 3 images only';
			}
		}
		if($countfiles <= 3){
		$result = $wpdb->insert($table_name, $data);
		//Email to Admin

		$subject = sanitize_text_field( $_POST["cf-subject"] );
		$message = esc_textarea( $_POST["cf-message"] );
		$to = get_option( 'admin_email' );
		wp_mail($to, $subject, $message );

		//Success Alert Message
		echo '<div class="alert alert-success">
				<strong>Success!</strong> Your review has been submitted.
			</div>';
		}else{
			echo '<div class="alert alert-danger">
			<strong>Warning!</strong> Your review has not been submitted.
		</div>';
		}
		}
    }
}

function cf_shortcode() {
	ob_start();
	deliver_mail();
	html_form_code();

	return ob_get_clean();
}

add_shortcode( 'reviewmonial', 'cf_shortcode' );

function showreview_function() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'reviewmonial_review';
	$show_review_data = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");  
	$show_review_data= json_decode( json_encode($show_review_data), true);
	if (is_array($show_review_data) || is_object($show_review_data)){
		foreach($show_review_data as $show_review_d){
			if($show_review_d['edit'] == 1 || $show_review_d['rating'] >= 4){?>
		<div class="container-show-review">
		<p><span><?php echo $show_review_d['name']; ?>
		<?php if($show_review_d['rating'] == 5){?>
		<img src="https://i.ibb.co/Y7qmcWX/5-stars.png" alt="5 star" width="150px" height="150px" style="border-radius:0 !important; float:right">
		<?php }elseif($show_review_d['rating'] == 4){?>
		<img src="https://i.ibb.co/WW5crMx/4-stars.png" alt="4 star" width="150px" height="150px" style="border-radius:0 !important; float:right">
		<?php }elseif($show_review_d['rating'] == 3){?>
		<img src="https://i.ibb.co/7nwtkZZ/3-stars.png" alt="3 star" width="150px" height="150px" style="border-radius:0 !important; float:right">
		<?php }elseif($show_review_d['rating'] == 2){?>
		<img src="https://i.ibb.co/G34X1pH/2-stars.png" alt="2 star" width="150px" height="150px" style="border-radius:0 !important; float:right">
		<?php }elseif($show_review_d['rating'] == 1){?>
		<img src="https://i.ibb.co/ysvHH9R/1-stars.png" alt="1 star" width="150px" height="150px" style="border-radius:0 !important; float:right">
		<?php }?>
		</span></p>
		<p><i><?php echo $show_review_d['comment']; ?></i></p>
		<?php $image_string = $show_review_d['image'];
				$image_string_explode = explode(" , ", $image_string);
		?>
		<?php if(isset($image_string_explode[2])){?>
		<img src="<?php echo $image_string_explode[0]; ?>" alt="image" width="150px" height="150px" style="border-radius:0 !important;">
		<img src="<?php echo $image_string_explode[1]; ?>" alt="image" width="150px" height="150px" style="border-radius:0 !important;">
		<img src="<?php echo $image_string_explode[2]; ?>" alt="image" width="150px" height="150px" style="border-radius:0 !important;">
		<?php }elseif(isset($image_string_explode[1])){ ?>
		<img src="<?php echo $image_string_explode[0]; ?>" alt="image" width="150px" height="150px" style="border-radius:0 !important;">
		<img src="<?php echo $image_string_explode[1]; ?>" alt="image" width="150px" height="150px" style="border-radius:0 !important;">
		<?php }elseif(isset($image_string_explode[0])){?>
		<img src="<?php echo $image_string_explode[0]; ?>" alt="image" width="150px" height="150px" style="border-radius:0 !important;">
		<?php } ?>
	  	</div>
	  <?php } }
	}
}

function showreview_query() {
	//function code
}

function showreview_shortcode() {
	ob_start();
	showreview_query();
	showreview_function();

	return ob_get_clean();
}

add_shortcode( 'review_testimonial', 'showreview_shortcode' );
?>