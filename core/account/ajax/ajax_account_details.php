<?php
	include_once(dirname(__FILE__)."/../../../lib/session_manager.php");
	include_once(dirname(__FILE__)."/../../../database/config.php");
	include_once(dirname(__FILE__)."/../../../database/mcat_db.php");
	include_once(dirname(__FILE__)."/../../../lib/user.php");
	include_once(dirname(__FILE__)."/../../../lib/user_manager.php");
    include_once(dirname(__FILE__)."/../../../lib/site_config.php") ;
	include_once(dirname(__FILE__)."/../../../lib/utils.php") ;

	$user_id = CSessionManager::Get(CSessionManager::STR_USER_ID);
	
	$objDB = new CMcatDB();
	$user_email = $objDB->GetUserEmail($user_id);
	
	if(array_search($user_email, CConfig::$reserved_emails) !== FALSE)
	{
		// Don't save edited details for reserved email ids.
		CUtils::Redirect("../account.php");
		exit();
	}
	
	$parsAry = parse_url(CUtils::curPageURL());
	$qry = split("[=&]", $parsAry["query"]);
	
	//Function to sanitize values received from the form. Prevents SQL injection
	function clean($str) 
	{
		/*if(!get_magic_quotes_gpc()) 
		{
			$str = trim(mysql_real_escape_string($str));
		}
		else */
		{
			$str = trim($str);
		}

		return $str;
	}
	
	$sRedirectURL = "";
	$objUM = new CUserManager();
	if($qry[0] == "sec")
	{
		switch(intval($qry[1]))
		{
			case 1:
				{
					//Sanitize the POST values
					$fname		= clean($_POST['FNAME']);
					$lname		= clean($_POST['LNAME']);
					$gender		= clean($_POST['GENDER']);
					$contact	= clean($_POST['PHONE']); //change by me
					$address	= clean($_POST['ADDRESS']);  //change by me
					$city		= clean($_POST['CITY']);
					$state		= clean($_POST['STATE']);
					$country	= clean($_POST['COUNTRY']);
					
					$dob	= sprintf("%s-%s-%s", clean($_POST['BIRTHYEAR']), clean($_POST['MONTH']), clean($_POST['DAY']));
					
					$user_data = array(CUser::FIELD_FIRST_NAME=>$fname,
									   CUser::FIELD_LAST_NAME=>$lname,
									   CUser::FIELD_GENDER=>$gender,
									   CUser::FIELD_CONTACT_NO=>$contact,
							           CUser::FIELD_ADDRESS=>$address,
									   CUser::FIELD_CITY=>$city,
									   CUser::FIELD_STATE=>$state,
									   CUser::FIELD_COUNTRY=>$country,
									   CUser::FIELD_DOB=>$dob);
					
					$objUM->UpdateUser($user_id, $user_data);
					
					$sRedirectURL = "../personal-details.php";
				}
				break;
			case 2:
				{
					$password	= $_POST['PASSWORD'];
					$question	= clean($_POST['QUESTION']);	
					$security_answer = clean($_POST['ANSWER']);
					
					$user_data = array(CUser::FIELD_PASSWORD =>$password,
									   CUser::FIELD_SECURITY_QUES=>$question,
									   CUser::FIELD_SECURITY_ANS=>$security_answer);
					
					$objUM->UpdateUser($user_id, $user_data);
					
					$sRedirectURL = "../security.php";
				}
				break;
			case 3:
				{
					$org_id			= clean($_POST['organization_id']);
					$org_address 	= clean($_POST['organization_address']);
					$org_phone		= clean($_POST['organization_phone']);
					$org_email		= clean($_POST['organization_email']);
					$org_city 		= clean($_POST['organization_city']);
					$org_state		= clean($_POST['organization_state']);
					$org_country	= clean($_POST['organization_country']);
					$org_zip		= clean($_POST['zip_code']);
					
					$org_name 		= clean($_POST['organization_name']);
					$org_url 		= clean($_POST['organization_url']);
					$youtube_videos = clean($_POST['youtube_videos']);
					$org_desc		= clean($_POST['description']);
					$logo_name  	= "";
					$punch_line 	= "";
					$org_size		= 0;
					
					$aryCourses = array();
					foreach($_POST['course_name'] as $key => $sCourseName)
					{
						$aryCourses[$sCourseName] = $_POST['course_desc'][$key];
					}
					$courses = json_encode($aryCourses);
					
					/*echo "<pre>";
					print_r($aryCourses);
					echo "</pre>";
					exit();*/
					
					$logo_image	= null;
					if($_FILES['organization_img']['size'] > 0)
					{
						$handle = fopen($_FILES['organization_img']['tmp_name'], "rb");
						$logo_image  = fread($handle, $_FILES['organization_img']['size']);
						fclose($handle);
					}
					
					$org_data = array(CUser::FIELD_ORGANIZATION_ADDRESS => $org_address,
									  CUser::FIELD_ORGANIZATION_PHONE => $org_phone,
									  CUser::FIELD_ORGANIZATION_EMAIL => $org_email,
									  CUser::FIELD_ORGANIZATION_CITY => $org_city,
									  CUser::FIELD_ORGANIZATION_STATE => $org_state,
									  CUser::FIELD_ORGANIZATION_COUNTRY => $org_country,
									  CUser::FIELD_ORGANIZATION_ZIPCODE => $org_zip,
									  CUser::FIELD_ORGANIZATION_NAME => $org_name,
									  CUser::FIELD_ORGANIZATION_SIZE => $org_size,
									  CUser::FIELD_ORGANIZATION_URL => $org_url,
									  CUser::FIELD_ORGANIZATION_YOUTUBE => $youtube_videos,
									  CUser::FIELD_ORGANIZATION_DESC => $org_desc,
									  CUser::FIELD_ORGANIZATION_COURSES => $courses,
									  CUser::FIELD_LOGO_IMAGE => $logo_image,
									  CUser::FIELD_LOGO_NAME => $logo_name,
									  CUser::FIELD_PUNCH_LINE => $punch_line);
					
					$objUM->UpdateOrg($user_id, $org_id, $org_data);
					
					$sRedirectURL = "../about.php";
				}
				break;
			case 4:
				{
					$qualification	= $_POST['qualification'];
					$area			= $_POST['area'];
					$stream			= $_POST['stream'];
					$percent		= $_POST['percent'];
					$institute		= $_POST['institute'];
					$board			= $_POST['board'];
					$passing_year	= $_POST['passing_year'];
					$qual_count 	= $_POST['qual_count'];
					
					/*echo "<pre>";
					print_r($_POST);
					echo "</pre>";*/
					
					$objUM->PurgeUserCV($user_id);
					
					for ($aryIndex = 0; $aryIndex <$qual_count; $aryIndex++)
					{
						$objUM->InsertIntoUserCV($user_id, $qualification[$aryIndex], $area[$aryIndex],
												 $stream[$aryIndex], $percent[$aryIndex],
												 $institute[$aryIndex], $board[$aryIndex], $passing_year[$aryIndex]);
					}
					
					$sRedirectURL = "../about.php";
				}
				break;
			case 5:
				{
					
				}
				break;
		}
	}
	else 
	{
		CUtils::Redirect(dirname(__FILE__)."/../../401.shtml");
	}
	
	CUtils::Redirect($sRedirectURL);
?>