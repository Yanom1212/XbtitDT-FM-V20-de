<?php
/////////////////////////////////////////////////////////////////////////////////////
// xbtit - Bittorrent tracker/frontend
//
// Copyright (C) 2004 - 2015  Btiteam
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

if (!defined("IN_ACP"))
      die("non direct access!");


if (!$CURUSER || $CURUSER["admin_access"]!="yes")
   {
       err_msg(ERROR,NOT_ADMIN_CP_ACCESS);
       stdfoot();
       exit;
}
else
{
   $teamsres=do_sqlquery("SELECT COUNT(*) from {$TABLE_PREFIX}users where team>0 ORDER BY id ASC $limit");
    $teamnum=mysqli_fetch_row($teamsres);
    $num2=$teamnum[0];
    $perpage=(max(0,$CURUSER["torrentsperpage"])>0?$CURUSER["torrentsperpage"]:10);
    list($pagertop, $pagerbottom, $limit) = pager($perpage, $num2, "index.php?page=admin&amp;user=".$CURUSER["uid"]."&amp;code=".$CURUSER["random"]."&amp;do=team_users&amp;");
    
    
    $admintpl->set("pagertop",$pagertop);
    $admintpl->set("pagerbottom",$pagerbottom);


$teamres=do_sqlquery("SELECT u.id AS id, u.username, prefixcolor, suffixcolor, u.team AS userteam, t.id AS teamsid, t.name AS teamname, t.image AS teamimage
FROM {$TABLE_PREFIX}teams t
LEFT JOIN {$TABLE_PREFIX}users u ON u.team = t.id 
LEFT JOIN {$TABLE_PREFIX}users_level ul ON u.id_level=ul.id
WHERE team >0
ORDER BY u.id ASC $limit");

    $teams=array();
    $i=0;

while ($row = mysqli_fetch_array($teamres)) {
	
        $teams[$i][id] = (int)$row['id'];
	$teams[$i][username] = stripslashes($row[prefixcolor]) .$row['username'].stripslashes($row[suffixcolor]);
	$teams[$i][teamimage] = htmlspecialchars($row['teamimage']);
	$teams[$i][teamname] =  htmlspecialchars($row['teamname']);
	


$i++;

}
$admintpl->set("teams",$teams);

    

    unset($row);
    @((mysqli_free_result($teamres) || (is_object($teamres) && (get_class($teamres) == "mysqli_result"))) ? true : false);
    unset($teams);

}
?>