<?php
/////////////////////////////////////////////////////////////////////////////////////
// xbtit - Bittorrent tracker/frontend
//
// Copyright (C) 2004 - 2015  Btiteam
//
//    This file is part of xbtit DT FM.
//
// Expected & To Offer Torrents by DiemThuy oct 2010 based on Jboy,s BTI version
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

$expectid = (int)$_GET[expectid];

$res2 = mysqli_query($GLOBALS["___mysqli_ston"], "select count(addedexpected.id) from {$TABLE_PREFIX}addedexpected addedexpected inner join {$TABLE_PREFIX}users users on addedexpected.userid = users.id inner join {$TABLE_PREFIX}expected expected  on addedexpected.expectid = expected.id WHERE addedexpected.expectid =$expectid") or die(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
$row = mysqli_fetch_array($res2);
$count = $row[0];

$home = 'index.php?page=votesexpectedview';
$perpage = 20;

 list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, $home ."&" );

$res = mysqli_query($GLOBALS["___mysqli_ston"], "select users.id as userid,users.username, users.downloaded,users.uploaded, expected.id as expectid, expected.expect from {$TABLE_PREFIX}addedexpected addedexpected inner join {$TABLE_PREFIX}users users on addedexpected.userid = users.id inner join {$TABLE_PREFIX}expected expected on addedexpected.expectid = expected.id WHERE addedexpected.expectid =$expectid $limit") or sqlerr();

$res2 = mysqli_query($GLOBALS["___mysqli_ston"], "select expect from {$TABLE_PREFIX}expected where id=$expectid");
      $exp=array();
      $i=0;
$arr2 = mysqli_fetch_assoc($res2);

$votesexpectedviewtpl = new bTemplate();
$votesexpectedviewtpl->set("language",$language);

$votesexpectedviewtpl->set("vv1","<p align=center>" . $language["VOTE_EXPECTED"] . "<a href=index.php?page=addexpected&id=$expectid><b>" . $language["OFFER_A"] . "</b></a></p>");

if (mysqli_num_rows($res) == 0)
$votesexpectedviewtpl->set("vv2","<p align=center><b>" . $language["OFFER_N"] . "</b></p>\n");
else
{
$votesexpectedviewtpl->set("vv3","<center><table width=99% class=lista align=center cellpadding=3>\n");
$votesexpectedviewtpl->set("vv4","<tr><td class=header><center>".$language['OF_USER']."</td><td class=header><center>".$language['UPLOADED']."</td><td class=header><center>".$language['DOWNLOADED']."</center></td>".
   "<td class=header><center>".$language['RATIO']."</center></td>\n");

 while ($arr = mysqli_fetch_assoc($res))
 {

  Global $XBTT_USE;

if ($XBTT_USE)
   {
    $udownloaded="users.downloaded+IFNULL(x.downloaded,0)";
    $uuploaded="users.uploaded+IFNULL(x.uploaded,0)";
    $utables="{$TABLE_PREFIX}users users LEFT JOIN xbt_users x ON x.uid=users.id";
   }
else
    {
    $udownloaded="users.downloaded";
    $uuploaded="users.uploaded";
    $utables="{$TABLE_PREFIX}users users";
}

  $dt=$arr["userid"];
$ress = mysqli_query($GLOBALS["___mysqli_ston"], "select $udownloaded as downloaded,$uuploaded as uploaded from $utables WHERE users.id =$dt") or sqlerr();
$roww = mysqli_fetch_array($ress);
if ($roww["downloaded"] > 0)
{
       $ratio = number_format($roww["uploaded"] / $roww["downloaded"], 3);

    }
    else
       if ($roww["uploaded"] > 0)
         $ratio = "Inf.";
 else
  $ratio = "---";
$uploaded = makesize($arr["uploaded"]);
$joindate = "$arr[added] (" . get_elapsed_time(sql_timestamp_to_unix_timestamp($arr["added"])) . " ago)";
$downloaded = makesize($arr["downloaded"]);


  $exp[$i]["vv5"]=("<tr><td class=lista><center><a href=index.php?page=userdetails&id=$arr[userid]><b>$arr[username]</b></a></td><td align=left class=lista><center>$uploaded</td><td align=left class=lista><center>$downloaded</td><td align=left class=lista><center>$ratio</td></tr>\n");
  $i++;
 }
$votesexpectedviewtpl->set("exp",$exp);
$votesexpectedviewtpl->set("vv6","</table></center><BR><BR>\n");
}

?>