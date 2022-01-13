<?php
/////////////////////////////////////////////////////////////////////////////////////
// xbtit - Bittorrent tracker/frontend
//
// Copyright (C) 2004 - 2014  Btiteam
//
//    This file is part of xbtit DT FM.
//
// Redistribution and use in source and binary forms, with or without modification,
// are permitted provided that the following conditions are met:
//
//   1. Redistributions of source code must retain the above copyright notice,
//      this list of conditions and the following disclaimer.
//   2. Redistributions in binary form must reproduce the above copyright notice,
//      this list of conditions and the following disclaimer in the documentation
//      and/or other materials provided with the distribution.
//   3. The name of the author may not be used to endorse or promote products
//      derived from this software without specific prior written permission.
//
// THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR IMPLIED
// WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
// MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
// IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
// SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED
// TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
// PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
// LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
// NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
// EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
//
////////////////////////////////////////////////////////////////////////////////////

if (!defined("IN_BTIT"))
      die("non direct access!");


switch ($action)
{
    case 'post':
           $idlangue=(isset($_POST["language"])?intval(0+$_POST["language"]):0);
           $idstyle=(isset($_POST["style"])?intval(0+$_POST["style"]):0);
           $email=AddSlashes($_POST["email"]);
           $avatar=str_replace(array('\t','%25','%00'), array('','',''), htmlspecialchars(AddSlashes($_POST["avatar"])));
           $idflag=intval(0+$_POST["flag"]);
           $timezone=intval($_POST["timezone"]);
           $emailnot =   $_POST["emailnot"]?"yes":"no";
      
           $invisible =   $_POST["invisible"]?"yes":"no";
      
           $showporn =   $_POST["showporn"]?"yes":"no";

           $dobdate=$_POST["datepicker"];
           $parts = explode('-', $dobdate);

           $dobday=$parts[0];
           $dobmonth=$parts[1];
           $dobyear=$parts[2];

           $realdate=checkdate($dobmonth,$dobday,$dobyear);
           $gender=intval(0+$_POST["gender"]);
          
           if($realdate)
           {
               $dob=$dobyear."-".$dobmonth."-".$dobday;

               $age=userage($dobyear, $dobmonth, $dobday);
               $dobtime=mktime(0,0,0,$dobmonth,$dobday,$dobyear);
               
               if($dobtime>time())
               {
                   err_msg($language["ERROR"], $language["ERR_BORN_IN_FUTURE"]);
                   stdfoot();
                   exit();                
               }
               elseif($age<$btit_settings["birthday_lower_limit"])
               {
                   err_msg($language["ERROR"], $language["ERR_DOB_1"] . $age . $language["ERR_DOB_2"]);
                   stdfoot();
                   exit();
               }
               elseif($age>$btit_settings["birthday_upper_limit"])
               {
                   err_msg($language["ERROR"], $language["ERR_DOB_1"] . $age . $language["ERR_DOB_2"]);
                   stdfoot();
                   exit();
               }
           }
           else
           {
               err_msg($language["ERROR"], $language["INVALID_DOB_1"].$dobday."/".$dobmonth."/".$dobyear.$language["INVALID_DOB_2"]);
               stdfoot();
               exit();
           }

           // Password confirmation required to update user record
           (isset($_POST["passconf"])) ? $passcheck=hash_generate(array("salt" => $CURUSER["salt"]), $_POST["passconf"], $CURUSER["username"]) : $passcheck=array();
           if(isset($passcheck[$btit_settings["secsui_pass_type"]]) && is_array($passcheck[$btit_settings["secsui_pass_type"]]))
               $password=$passcheck[$btit_settings["secsui_pass_type"]]["hash"];
           else
               $password="";           

           if($password=="" || $CURUSER["password"]!=$password)
           {
               stderr($language["ERROR"], $language["ERR_PASS_WRONG"]);
               stdfoot();
               exit();
           }
           // Password confirmation required to update user record

           // check avatar is a valid image and one of the supported file types
           if($avatar && $avatar!="")
           {
               $imagearr=@getimagesize($avatar);
               if(!is_array($imagearr) || !in_array($imagearr["mime"], array("image/bmp", "image/jpeg", "image/pjpeg", "image/gif", "image/x-png", "image/png")))
                   stderr($language["ERROR"], $language["ERR_AVATAR_EXT"]);
           }

           if ($email=="")
          {
            err_msg($language["ERROR"],$language["ERR_NO_EMAIL"]);
            stdfoot();
            exit;
          }
           else
               {
               // Reverify Mail Hack by Petr1fied - Start --->
               if ($VALIDATION=="user") {
                   // Send a verification e-mail to the e-mail address they want to change it to
                   if (($email!="")&&($email!=$CURUSER["email"])) {
                       $id=$CURUSER["uid"];
                       // Generate a random number between 10000 and 99999
                       $floor = 100000;
                       $ceiling = 999999;
                       srand((double)microtime()*1000000);
                       $random = rand($floor, $ceiling);

                       // Update the members record with the random number and store the email they want to change to
                       do_sqlquery("UPDATE {$TABLE_PREFIX}users SET random='".$random."', temp_email='".$email."' WHERE id='".$id."'",true);

                       // Send the verification email
                       @ini_set("sendmail_from","");
                       if (((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false))==0)
                          send_mail($email,$language["EMAIL_VERIFY"],$language["EMAIL_VERIFY_MSG"]."\n\n".$BASEURL."/index.php?page=usercp&do=verify&action=changemail&newmail=".$email."&uid=".$id."&random=".$random."","From: ".$SITENAME." <".$SITEEMAIL.">") OR stderr($language["ERROR"],$language["EMAIL_FAILED"]);
                       }
               }
               $set=array();

               if ($VALIDATION!="user") {
                   if ($email!="")
                   {
                       $set[]="email='$email'";
                       if(substr($GLOBALS["FORUMLINK"],0,3)=="smf")
                       {
                           do_sqlquery("UPDATE `{$db_prefix}members` SET `email".(($GLOBALS["FORUMLINK"]=="smf")?"A":"_a")."ddress`='".$email."' WHERE ".(($GLOBALS["FORUMLINK"]=="smf")?"`ID_MEMBER`":"`id_member`")."=".$CURUSER["smf_fid"]);
                       }
                       elseif($GLOBALS["FORUMLINK"]=="ipb")
                       {
                           if(!defined('IPS_ENFORCE_ACCESS'))
                               define('IPS_ENFORCE_ACCESS', true);
                           if(!defined('IPB_THIS_SCRIPT'))
                               define( 'IPB_THIS_SCRIPT', 'public' );
                           require_once($THIS_BASEPATH. '/ipb/initdata.php' );
                           require_once( IPS_ROOT_PATH . 'sources/base/ipsRegistry.php' );
                           require_once( IPS_ROOT_PATH . 'sources/base/ipsController.php' );
                           $registry = ipsRegistry::instance(); 
                           $registry->init();
                           IPSMember::save($CURUSER["ipb_fid"], array("members" => array("email" => "$email")));
                       }
                   }
                }
                // <--- Reverify Mail Hack by Petr1fied - End
                
//Profile Status by Yupy Start
    if (isset($_POST['status']) && ($status = $_POST['status']) && !empty($status)) {
        do_sqlquery("INSERT INTO {$TABLE_PREFIX}profile_status (userid, last_status, last_update) VALUES (".sqlesc($CURUSER['uid']).", ".sqlesc($status).", ".time().") ON DUPLICATE KEY UPDATE last_status = values(last_status), last_update = values(last_update)") or sqlerr(__FILE__, __LINE__);
    }
//Profile Status by Yupy End
               if ($idlangue>0)
                  $set[]="language=$idlangue";
               if ($idstyle>0)
                  $set[]="style=$idstyle";
               if ($idflag>0)
                  $set[]="flag=$idflag";

               $set[]="time_offset='$timezone'";
               $set[]="avatar='$avatar'";
               $set[]="topicsperpage=".intval(0+$_POST["topicsperpage"]);
               $set[]="postsperpage=".intval(0+$_POST["postsperpage"]);
               $set[]="torrentsperpage=".intval(0+$_POST["torrentsperpage"]);
               $commentpm = isset($_POST["commentpm"])?"true":"false";
               $set[]="commentpm='".$commentpm."'";
               
	           $set[]="gender=".intval(0+$_POST["gen"]);
               $set[]="emailnot='$emailnot'";
      
               // Userbar
               $set[]="userbar=".intval(0+$_POST["userbar"]);
               // Userbar
               $set[]="invisible='$invisible'";
      
               $set[]="showporn='$showporn'";
      
			   $set[]="profileview=".intval(0+$_POST["profileview"]);               
			   
			   if(isset($dob))
               $set[]="dob='$dob'";

               $updateset=implode(",",$set);

               // Reverify Mail Hack by Petr1fied - Start --->
               // If they've tried to change their e-mail, give them a message telling them as much
               if (($email!="")&&($VALIDATION=="user")&&($email!=$CURUSER["email"]))
                  {
                  success_msg($language["EMAIL_VERIFY_BLOCK"], "".$language["EMAIL_VERIFY_SENT1"]." ".$email." ".$language["EMAIL_VERIFY_SENT2"]."<a href=\"".$BASEURL."\">".$language["MNU_INDEX"]."</a>");
                  stdfoot(true,false);
                  exit;
                  }

elseif ($updateset=implode(",",$set));
               // <--- Reverify Mail Hack by Petr1fied - End
               
                $park=$_POST['park'];
                if(!is_numeric($park)) {        err_msg(ERROR,"Something went wrong");
                       stdfoot();
                       exit;
                }
               if ($updateset!="")
                  {
                  mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE {$TABLE_PREFIX}users SET $updateset WHERE id=$uid") or die(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
                  if($park==0) { 
                        
                        $r=mysqli_query($GLOBALS["___mysqli_ston"], "SELECT parked from {$TABLE_PREFIX}users where id = $uid");
                        $p=mysqli_result($r,0,"parked");
                        if ($p!=0) {
                            mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE {$TABLE_PREFIX}users SET id_level=$p WHERE id=$uid") or die(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))); 
                            mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE {$TABLE_PREFIX}users SET parked='0' WHERE id=$uid") or die(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))); 
                        }
                  } else {
                        $r=mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id_level from {$TABLE_PREFIX}users where id = $uid");
                        $cc=mysqli_result($r,0,"id_level");
                        $r=mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE {$TABLE_PREFIX}users SET parked = $cc where id = $uid");
                        $r=mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE {$TABLE_PREFIX}users SET id_level = 13 where id = $uid");
                  }
                  
                    if($idlangue>0)
                      $_SESSION['CURUSER']['style_url']=idlangue;
                    if($idstyle>0)
                      $_SESSION['CURUSER']['language_path']=$idstyle;
                      
                  success_msg($language["SUCCESS"], $language["INF_CHANGED"]."<br /><a href=\"index.php?page=usercp&amp;uid=".$uid."\">".$language["BCK_USERCP"]."</a>");
                  stdfoot(true,false);
                  exit;
                  }
              }
    break;

    case '':
    case 'change':
    default:
      $usercptpl->set("AVATAR",false,true);
      $usercptpl->set("USER_VALIDATION",false,true);
      $usercptpl->set("INTERNAL_FORUM",false,true);
      $profiletpl=array();      $row=mysqli_fetch_assoc(mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `dob` FROM `{$TABLE_PREFIX}users` WHERE `id`=".$uid));
      $usercptpl->set("DOBEDIT",($row["dob"]=="0000-00-00")?true:false,true);
      $dob=explode("-",$row["dob"]);
      $profiletpl["dobday"]=$dob[2];
      $profiletpl["dobmonth"]=$dob[1];
      $profiletpl["dobyear"]=$dob[0];
      $profiletpl["frm_action"]="index.php?page=usercp&amp;do=user&amp;action=post&amp;uid=".$uid."";
      $profiletpl["delete_uid"]=$uid;
      $profiletpl["username"]=$CURUSER["username"];
      $usercptpl->set("show_profile", (($CURUSER["profileview"] == 0)?true:false), true);
      $usercptpl->set("hide_profile", (($CURUSER["profileview"] == 1)?true:false), true);
 
global  $btit_settings;    
	  if ($btit_settings["delsw"]==true )     
      $profiletpl["deluser"]='<a href="index.php?page=usercp&uid=<tag:profile.delete_uid />&do=deleteme&uid=<tag:profile.delete_uid />"> [Delete your account]</a>';
      else
       $profiletpl["deluser"]='';
       
//avatar
      if ($CURUSER["avatar"] && $CURUSER["avatar"]!="")
        {
          $usercptpl->set("AVATAR",true,true);
          $profiletpl["avatar"]="<img border=\"0\" onload=\"resize_avatar(this);\" src=\"".htmlspecialchars(unesc($CURUSER["avatar"]))."\" alt=\"\" />";
        }

      $profiletpl["avatar_field"]=unesc($CURUSER["avatar"]);
      $profiletpl["email"]=unesc($CURUSER["email"]);
      
$uid=$CURUSER["uid"];
		
		 $r=mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * from {$TABLE_PREFIX}users WHERE id = $uid"); 
		 $x=mysqli_result($r,0,"gender"); 

		 
		 if($x==0) {
			$profiletpl["gender"].="<input name=\"gen\" type=\"radio\" value=\"0\" checked=\"checked\" />
			  ".$language["MALE"]."   
			  <input name=\"gen\" type=\"radio\" value=\"1\" />
			".$language["FEMALE"]."  </label>";
			} else {
			$profiletpl["gender"].="<input name=\"gen\" type=\"radio\" value=\"0\" />
			  ".$language["MALE"]."   </label>
			  <input name=\"gen\" type=\"radio\" value=\"1\" checked=\"checked\"/>
			".$language["FEMALE"]." </label>";
         $profiletpl["gender"].=($option);
		 }      

      //Reverify Mail Hack by Petr1fied - Start
      if ($VALIDATION=="user")
        {
          //Display a message informing users that they will have
          //to verify their e-mail address if they attempt to change it
          $usercptpl->set("USER_VALIDATION",true,true);
        }
      //Reverify Mail Hack by Petr1fied - End

      if($btit_settings["hide_language_visible"]!="visible")
      {
          //language list
          $lres=language_list();
          $langtpl=array();
          foreach($lres as $langue)
          {
              $langtpl["language_combo"].="\n<option ";
              if ($langue["id"]==$CURUSER["language"])
                  $langtpl["language_combo"].="selected=\"selected\" ";
              $langtpl["language_combo"].="value=\"".$langue["id"]."\">".unesc($langue["language"])."</option>";
              $langtpl["language_combo"].=($option);
          }
          unset($lres);
          $usercptpl->set("lang",$langtpl);
      }
     if($btit_settings["hide_style_visible"]!="visible")
      {
          //style list
          $sres=style_list();
          $styletpl=array();
          foreach($sres as $style)
          {
              $styletpl["style_combo"].="\n<option ";
              if ($style["id"]==$CURUSER["style"])
                  $styletpl["style_combo"].="selected=\"selected\" ";
              $styletpl["style_combo"].="value=\"".$style["id"]."\">".unesc($style["style"])."</option>";
              $styletpl["style_combo"].=($option);
          }
          unset($sres);
          $usercptpl->set("style",$styletpl);
      }

      //flag list
      $fres=flag_list();
      $flagtpl=array();
        foreach($fres as $flag)
          {
        $flagtpl["flag_combo"].="\n<option ";
          if ($flag["id"]==$CURUSER["flag"])
        $flagtpl["flag_combo"].="selected=\"selected\" ";
        $flagtpl["flag_combo"].="value=\"".$flag["id"]."\">".unesc($flag["name"])."</option>";
        $flagtpl["flag_combo"].=($option);
          }
        unset($fres);
      $usercptpl->set("flag",$flagtpl);
      $usercptpl->set("emailnot_checked", ($CURUSER["emailnot"]=="yes"?"checked=\"checked\"":""));
      
      $usercptpl->set("invisible_checked", ($CURUSER["invisible"]=="yes"?"checked=\"checked\"":""));
      
      $usercptpl->set("showporn_checked", ($CURUSER["showporn"]=="yes"?"checked=\"checked\"":""));
      

      //timezone list
      $tres=timezone_list();
      $tztpl=array();
        foreach($tres as $timezone)
          {
        $tztpl["tz_combo"].="\n<option ";
          if ($timezone["difference"]==$CURUSER["time_offset"])
        $tztpl["tz_combo"].="selected=\"selected\" ";
        $tztpl["tz_combo"].="value=\"".$timezone["difference"]."\">".unesc($timezone["timezone"])."</option>";
        $tztpl["tz_combo"].=($option);
          }
        unset($tres);
      $usercptpl->set("tz",$tztpl);

      if ($FORUMLINK=="" || $FORUMLINK=="internal")
        {
          $usercptpl->set("INTERNAL_FORUM",true,true);
          $profiletpl["topicsperpage"]=$CURUSER["topicsperpage"];
          $profiletpl["postsperpage"]=$CURUSER["postsperpage"];
        }

      $profiletpl["torrentsperpage"]=$CURUSER["torrentsperpage"];
      $profiletpl["commentpm"]=($CURUSER["commentpm"]=="true"?"checked=\"checked\"":"");
      $profiletpl["frm_cancel"]="index.php?page=usercp&amp;uid=".$uid."";
      
              $uid=$CURUSER['uid'];
                        $r=mysqli_query($GLOBALS["___mysqli_ston"], "SELECT parked from {$TABLE_PREFIX}users where id = $uid");
                        $p=mysqli_result($r,0,"parked");
            
                      if($p!=0) {
                                  $profiletpl["parked"].="<input name=\"park\" id=\"park\" type=\"radio\" value=\"0\" />
            ".$language["NO"]."" ;      
                      $profiletpl["parked"].="<input name=\"park\" id=\"park\" type=\"radio\" value=\"1\" checked=\"checked\"  />
              ".$language["YES"].""  ;
                  
              } else { 
                      $profiletpl["parked"].="<input name=\"park\" id=\"park\" type=\"radio\" value=\"0\" checked=\"checked\"  />
              ".$language["NO"].""  ;
              $profiletpl["parked"].="<input name=\"park\" id=\"park\" type=\"radio\" value=\"1\" />
            ".$language["YES"]."" ; 
            $profiletpl["parked"].=($option);
                            }

			// Userbar
			$res = do_sqlquery("SELECT * FROM {$TABLE_PREFIX}userbars",true);
			while ($row = mysqli_fetch_array($res))
				$bars[$row['id']] = array('desc'=>$row['description'],'img'=>$row['img']);

      $js_userbar = '';
			foreach ($bars as $value=>$key)
				$js_userbar.= "case '".$value."': img = 'images/userbar/".$key['img']."'; break;\n";
			$usercptpl->set("js_userbar",$js_userbar);

		  $profiletpl['newuserbar'] = '<select name="userbar" onchange="updateuserbar(this);">';
		  foreach ($bars as $value=>$key)
		  	$profiletpl['newuserbar'] .= '<option value="'.$value.'" '.($CURUSER['userbar']==$value?'selected':'').'>'.$key['desc'].'</option>';
		  $profiletpl['newuserbar'] .= '</select><br><img id="userbar" src="images/userbar/'.$bars[$CURUSER['userbar']]['img'].'">';
			// Userbar

      $usercptpl->set("profile",$profiletpl);
      $usercptpl->set("hide_language_visible", (($btit_settings["hide_language"]=="hidden")?false:true), true);
      $usercptpl->set("hide_style_visible", (($btit_settings["hide_style"]=="hidden")?false:true), true);

    break;
}
?>