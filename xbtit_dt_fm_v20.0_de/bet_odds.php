<?php
/////////////////////////////////////////////////////////////////////////////////////
// xbtit - Bittorrent tracker/frontend
//
// Copyright (C) 2004 - 2015  Btiteam
//
//   SPORT BETTING HACK , orginal TBDEV 2009 by Soft & Bigjoos 
//   XBTIT conversion by DiemThuy , April 2010
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
      
require_once("include/functions.php");
dbconn();

global $BASEURL,$CURUSER,$btit_settings;

$HTMLOUT ="";

if ($btit_settings["min_bet"]=="false")
{
stderr("Sorry", "You have no acces to betting.");
}

$id = isset($_GET['id']) && is_valid_id($_GET['id']) ? $_GET['id'] : 0;

$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM {$TABLE_PREFIX}betoptions WHERE id =".sqlesc($id)."") or sqlerr(__FILE__, __LINE__);
$a = mysqli_fetch_assoc($res);

$sp = (int) $a['gameid'];

$res2 = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * from {$TABLE_PREFIX}betgames where id =".sqlesc($sp)."") or sqlerr(__FILE__, __LINE__);
$b = mysqli_fetch_assoc($res2);

if($b['active'] == 0){
header("location: $BASEURL/index.php?page=bet");
exit;
}
$HTMLOUT .="<br><img src='images/betting.png' alt='Softbet' title='Betting' width='400' height='125' />
<table class='main' width='200' cellspacing='0' cellpadding='5' border='0'><br>
<tr>
<td align='center' class='navigation'><a href='$BASEURL/index.php?page=bet'>Current Bets</a></td>
<td align='center' class='navigation'><a href='$BASEURL/index.php?page=betcoupon'>Wagers</a></td>
<td align='center' class='navigation'><a href='$BASEURL/index.php?page=betinfo'>Bet Info</a></td>
</tr>
</table>
<form method='post' action='$BASEURL/index.php?page=betoddstwo'>
<table cellpadding='5'>
<tr>
<td class='colhead' width='100'>Games</td>
<td class='colhead' width='100'>My Games</td>
<td class='colhead'>Odds</td>
<td class='colhead'>Amount</td>
<td class='colhead'>&nbsp;</td>
</tr>
<tr>
<td>{$b['heading']}</td>
<td>{$a['text']}</td>
<td>{$a['odds']}</td>
<td>
<input type='text' name='bonus' size='2' maxlength='4' />  <b> Points</b>
<input type='hidden' name='id' value='".htmlspecialchars($id)."' /></td>
<td><input type='submit' value='Bet' /><br />(Note that you can not undo this)</td></tr></table></form>";


$betoddstpl = new bTemplate();
$betoddstpl->set("language", $language);
$betoddstpl->set(betoffs,$HTMLOUT);
?>