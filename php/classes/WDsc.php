<?php
/**
 * WDsc - Wellclubs discounts
 *
 * @author AT
 */
class WDsc
{
 const TABLE_CAMPAIGN = 'dsc_cmp';
 const TABLE_CAMPAIGN_CLIENT = 'dsc_cmp_clnt';
 const TABLE_CAMPAIGN_MESSAGES = 'dsc_cmp_msgs';
 const SIGNUP_EVENT = 'Sign up';
 const PROMO_CODE_EVENT = 'Promo code';
 const PRO_CODES_FIELDS = 'cmp_msgs_id,promo_code,evnt_dsc_amnt,evnt_dsc_prcnt,min_vle,max_othr_dsc_vle';

 public static function sqlActCmp($alias = 'sqlActCmp')
 {
  return
  "(select * from dsc_cmp
   where active='Y' and start_date<=curdate() and (expiry_date is null or expiry_date>=curdate())
   and domain_id=" . WDomain::id() . ")" .
   $alias;
 }

 public static function sqlActiveCampaign($alias = 'sqlActiveCampaign')
 {
  return
  "(select * from
    (select *
      ,(select ifnull(sum(evnt_dsc_amnt*max_rdmptns),0) from dsc_cmp_clnt where cmp_id=sqlActCmp.id
        and (rdmptn_durtn=0 or date_add(created,interval rdmptn_durtn day)>=now())
        )expected_redemption_amount
      from " . self::sqlActCmp() . "
     )expected_budget
    where expected_redemption_amount+evnt_dsc_amnt<=bdgt_lmt
  )" .
  $alias;
 }

 public static function sqlActiveCmpMsgs($alias = 'sqlActiveCmpMsgs')
 {
  return
  "(select sqlActiveCampaign.domain_id,sqlActiveCampaign.max_rdmptns,m.*
   from dsc_cmp_msgs m inner join " . self::sqlActiveCampaign() . " on m.cmp_id=sqlActiveCampaign.id
   where (m.msgng_start_date is null or m.msgng_start_date<=curdate())
   and (m.msgng_expiry_date is null or m.msgng_expiry_date>=curdate())
  )" .
  $alias;
 }

 public static function lastCmpMsg($channel, $fields = null)
 {
  return DB::getDB()->queryPairs(self::sqlActiveCmpMsgs(), $fields, 'channel=' . DB::str($channel), 'id desc');
 }

 public static function testCmp($cmp)
 {
  return DB::getDB()->queryField(self::sqlActiveCampaign(), 'id', 'ref=' . DB::str($cmp));
 }

 public static function signinCltCmp($cltId, $cmpId = null)
 {
  $result = null;
  $where = 'event=' . DB::str(self::SIGNUP_EVENT);
  $id = DB::getDB()->queryField(self::sqlActiveCampaign(), 'id', $where);
  if ($id)
   $result = self::createCmpClt($cltId, $id);
  if ($cmpId)
  {
   $values = self::createCmpClt($cltId, $cmpId);
   if (!$result)
    $result = $values;
  }
  return $result;
 }

 public static function createCmpClt($cltId, $cmpId, $cmpMsgId = null, $prc = null)
 {
  $db = DB::getAdminDB();
  if (!$cmpId && $cmpMsgId)
  {
   $cmpCltData = $db->queryPairs(self::TABLE_CAMPAIGN_MESSAGES, 'cmp_id,promo_code', 'id=' . $cmpMsgId);
   $cmpId = $cmpCltData['cmp_id'];
  }
  if (!$cmpId)
   return null;
  $fields = 'id,event,start_date,start_time,expiry_date,expiry_time,evnt_dsc_amnt,evnt_dsc_prcnt,' .
    'art_pay_dsc_dscrptn,min_vle,max_othr_dsc_vle,max_rdmptns,rdmptn_durtn';
  $where = 'id=' . $cmpId;
  $row = $db->queryPairs(self::sqlActiveCampaign(), $fields, $where);
  if (!$row)
   return null;
  if ($prc)
    self::clcDscAmnt($row, $prc);
  $values = array
  (
   'cmp_id' => DB::intn($row['id']),
   'domain_id' => WDomain::id(),
   'client_id' => DB::intn($cltId),
   'event' => DB::strn($row['event']),
   'creator_id' => DB::intn($cltId),
   'start_date' => DB::strn($row['start_date']),
   'start_time' => DB::intn($row['start_time']),
   'expiry_date' => DB::strn($row['expiry_date']),
   'expiry_time' => DB::intn($row['expiry_time']),
   'evnt_dsc_amnt' => DB::moneyn($row['evnt_dsc_amnt']),
   'art_pay_dsc_dscrptn' => DB::strn($row['art_pay_dsc_dscrptn']),
   'min_vle' => DB::moneyn($row['min_vle']),
   'max_rdmptns' => DB::intn($row['max_rdmptns']),
   'rdmptn_durtn' => DB::intn($row['rdmptn_durtn']),
   'max_othr_dsc_vle' => DB::intn($row['max_othr_dsc_vle']),
   'cmp_msgs_id' => DB::intn($cmpMsgId),
   'promo_code' => DB::strn($cmpCltData['promo_code'])
  );
  if (!$db->insertValues(self::TABLE_CAMPAIGN_CLIENT, $values))
   return null;
  $values['cmp_clnt_id'] = $db->insert_id;
  return $values;
 }

 public static function getCmpDesc($cmpId)
 {
  return DB::getDB()->queryField(self::sqlActiveCampaign(), 'art_pay_dsc_dscrptn', 'id=' . $cmpId);
 }

 public static function clauseCalcRedemptions($alias = 'redemptions')
 {
  return
  "ifnull((select count(1) from " . WPurchase::TABLE_BOOK . " where cmp_clnt_id=dsc_cmp_clnt.id and status='a'),0)" .
  $alias;
 }

 public static function sqlCltCmp($alias = 'sqlCltCmp')
 {
  return
  "(select *," . self::clauseCalcRedemptions() . "
    from dsc_cmp_clnt
    where active='Y' and start_date<=curdate() and (expiry_date is null or expiry_date>=curdate())
     and (rdmptn_durtn=0 or date_add(created,interval rdmptn_durtn day)>=current_timestamp)
     and client_id=" . WClient::id() . "
   )" .
  $alias;
 }

 public static function sqlActiveCltCmp($alias = 'sqlActiveCltCmp')
 {
  return "(select * from " . self::sqlCltCmp() . " where redemptions<max_rdmptns)" . $alias;
 }

 public static function sqlCltCmpMsg($alias = 'sqlCltCmpMsg')
 {
  return
  "(select client_id,cmp_msgs_id,sum(redemptions)redemptions from(
   select client_id,cmp_msgs_id," . self::clauseCalcRedemptions() . "
   from dsc_cmp_clnt
   inner join dsc_cmp_msgs on dsc_cmp_clnt.cmp_id = dsc_cmp_msgs.cmp_id and ifnull(dsc_cmp_clnt.cmp_msgs_id,-1) = ifnull(dsc_cmp_msgs.id,-1)
   where client_id=" . WClient::id() . "
   )clt_cmp_msg_rdm_clc
   group by client_id,cmp_msgs_id
  )" .
  $alias;
 }

 //promo code
 public static function sqlProCodes($alias = 'sqlProCodes')
 {
  if (WClient::id())
   $cmpMsgSql = self::sqlCltActiveCmpMsgs('dsc_cmp_msgs');
  else
   $cmpMsgSql = self::sqlActiveCmpMsgs('dsc_cmp_msgs');

  return
  "(select distinct dsc_cmp_msgs.id cmp_msgs_id,dsc_cmp_msgs.promo_code,dsc_cmp_msgs.cmp_id,com_menu_tip.centre_id,com_menu_tip.srv_id
   ,com_menu_tip.id tip_id,evnt_dsc_amnt,min_vle,max_othr_dsc_vle,evnt_dsc_prcnt,max_vle
   from " . $cmpMsgSql . "
   inner join dsc_cmp on dsc_cmp_msgs.cmp_id = dsc_cmp.id
   inner join (
   select dsc_cmp.id cmp_id,com_menu_tip.* from dsc_cmp inner join com_menu_tip on dsc_cmp.tip_id = com_menu_tip.id
   union
   select dsc_cmp.id cmp_id,com_menu_tip.* from dsc_cmp inner join com_menu_tip on dsc_cmp.srv_id = com_menu_tip.srv_id
   union
   select dsc_cmp.id cmp_id,com_menu_tip.* from dsc_cmp inner join com_menu_tip on dsc_cmp.centre_id = com_menu_tip.centre_id
   union
   select dsc_cmp_ctrs.cmp_id,com_menu_tip.* from dsc_cmp_ctrs inner join com_menu_tip on dsc_cmp_ctrs.centre_id = com_menu_tip.centre_id
   union
   select dsc_cmp_tips.cmp_id,com_menu_tip.* from dsc_cmp_tips inner join com_menu_tip on dsc_cmp_tips.tip_id = com_menu_tip.id
   union
   select dsc_cmp_bnds.cmp_id,com_menu_tip.* from dsc_cmp_bnds
   inner join com_centre on dsc_cmp_bnds.brand_id = com_centre.brand_id
   inner join com_menu_tip on com_centre.id = com_menu_tip.centre_id
   union
   select dsc_cmp_srvs.cmp_id,com_menu_tip.* from dsc_cmp_srvs inner join com_menu_tip on dsc_cmp_srvs.srv_id = com_menu_tip.srv_id
   )com_menu_tip on com_menu_tip.cmp_id=dsc_cmp.id
   where dsc_cmp.event = '" . self::PROMO_CODE_EVENT  . "'
  )" .
  $alias;
 }

 public static function sqlClCmpMsgs($alias = 'sqlClCmpMsgs')
 {
  return
  "(select clt_msg.id cmp_msgs_id,ifnull(ifnull(sqlCltCmpMsg.redemptions,sqlCltCmp.redemptions),0)redemptions,clt_msg.* from(
    select biz_client.id client_id,sqlActiveCmpMsgs.* from
    biz_client cross join " . self::sqlActiveCmpMsgs() . "
    where biz_client.id=" . WClient::id() . "
   )clt_msg
   left outer join " . self::sqlCltCmpMsg() . "
    on clt_msg.client_id=sqlCltCmpMsg.client_id and clt_msg.id=sqlCltCmpMsg.cmp_msgs_id
   left outer join " . self::sqlCltCmp() . "
    on clt_msg.client_id=sqlCltCmp.client_id and clt_msg.cmp_id=sqlCltCmp.cmp_id and sqlCltCmp.cmp_msgs_id is null
  )" .
  $alias;
 }

 public static function sqlCltActiveCmpMsgs($alias = 'sqlCltActiveCmpMsgs')
 {
  return "(select * from" . self::sqlClCmpMsgs() . " where redemptions<max_rdmptns)" . $alias;
 }

 public static function tipProCodes($tipId, $result)
 {
  $db=DB::getDB();
  $result=$db->queryArrays(self::sqlProCodes(), self::PRO_CODES_FIELDS, 'tip_id=' . $tipId . ' and (min_vle is null or min_vle<' . $result['total'] . ')');
  //echo htmlspecialchars(DB::lastQuery()) . '</br></br></br>';
  return $result;
 }

 public static function addDscData(&$refData, &$result, &$db)
 {
  // sign-in discount
  $table = $refData ? self::TABLE_CAMPAIGN_CLIENT : self::sqlActiveCltCmp();
  $fields = 'id,evnt_dsc_amnt,art_pay_dsc_dscrptn,min_vle,max_othr_dsc_vle';
  $where = $refData ?
    'id=' . $refData['cmp_clnt_id']:
    'client_id=' . WClient::id();
  $where .= " and event=" . DB::str(self::SIGNUP_EVENT);
  $order = $refData ? null : 'expiry_date,id';
  $signInDscData = $db->queryPairs($table, $fields, $where, $order);

  if ($signInDscData)
  {
   if ($refData ||
       ($result['total'] > $signInDscData['min_vle']) &&
       ($signInDscData['max_othr_dsc_vle'] >= $result['totalDiscount']))
   {
    $result['cmp_clnt_id'] = $signInDscData['id'];
    $result['signInDscDscr'] = $signInDscData['art_pay_dsc_dscrptn'];
    $result['signInDscMinVle'] = $signInDscData['min_vle'];
    $result['signInDscAmnt'] = $signInDscData['evnt_dsc_amnt'];
    if (!$refData)
     $result['total'] -= $result['signInDscAmnt'];
   }
  }

  // promo codes
  if ($refData)
  {
   if ($refData['cmp_clnt_id'])
   {
    $fields = 'cmp_msgs_id,promo_code,evnt_dsc_amnt,min_vle,max_othr_dsc_vle';
    $where = 'id=' . $refData['cmp_clnt_id'] . " and event='" . self::PROMO_CODE_EVENT . "'" . " and (min_vle is null or min_vle<" . $result['total'] . ")";
    $cmpCltData = $db->queryPairs(self::TABLE_CAMPAIGN_CLIENT, $fields, $where);
   }
   if ($cmpCltData)
   {
    $result['msgId'] = $cmpCltData['cmp_msgs_id'];
    $result['proCode'] = $cmpCltData['promo_code'];
    $result['proCodes'] = $cmpCltData;
   }
  }
  else
  {
   $tipId = $result['tip'];
   if (!$tipId)
    $tipId = $db->queryField(WService::TABLE_TIP, 'id', 'srv_id=' . $result['srv']);
   $proCodeData = self::tipProCodes($tipId, $result);
   foreach($proCodeData as &$proCode)
    self::clcDscAmnt($proCode, $result['price']);
   if ($proCodeData)
    $result['proCodes'] = $proCodeData;
  }
//  echo htmlspecialchars(DB::lastQuery()) . '</br></br></br>';
  return;
 }

 public static function clcDscAmnt(&$proCode, $prc)
 {
  if (!!$proCode && !!$prc && $proCode['evnt_dsc_amnt']==0)
   $proCode['evnt_dsc_amnt'] = round($prc * $proCode['evnt_dsc_prcnt']/100);
  return;
 }
}
?>