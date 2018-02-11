<?php
require(dirname(__FILE__).'/config.php');
require(dirname(__FILE__).'/vendor/autoload.php');
function getGTK($skey){
   $hash = 5381;
   for($i=0;$i<strlen($skey);++$i){
      $hash += ($hash << 5) + utf8_unicode($skey[$i]);
   }
   return $hash & 0x7fffffff;
}
function utf8_unicode($c) {                 
    switch(strlen($c)) {                 
        case 1:                 
        return ord($c);                 
        case 2:                 
        $n = (ord($c[0]) & 0x3f) << 6;                 
        $n += ord($c[1]) & 0x3f;                 
        return $n;                 
        case 3:                 
        $n = (ord($c[0]) & 0x1f) << 12;                 
        $n += (ord($c[1]) & 0x3f) << 6;                 
        $n += ord($c[2]) & 0x3f;                 
        return $n;                 
        case 4:                 
        $n = (ord($c[0]) & 0x0f) << 18;                 
        $n += (ord($c[1]) & 0x3f) << 12;                 
        $n += (ord($c[2]) & 0x3f) << 6;                 
        $n += ord($c[3]) & 0x3f;                 
        return $n;                 
    }                 
}
function getpuin($qqnum){
	for($i=strlen($qqnum);$i<10;$i++){
		$qqnum='0'.$qqnum;
	}
	return 'o'.$qqnum;
}
$food_count=0;
$gtk=getGTK(SKEY);
$p_uin=getpuin(QQNUM);
$cookies='skey='.SKEY.'; p_uin='.$p_uin.'; p_skey='.PSKEY;


$client = new \GuzzleHttp\Client(['base_uri'=>'https://h5.qzone.qq.com','headers'=>['Cookie'=>$cookies]]);
$r=$client->request('GET',
'/proxy/domain/mqzmall.qzone.qq.com/fcg-bin/fcg_qzpet_user_rank?t=1&g_tk='.$gtk.'&qua=V1_AND_SQ_7.3.5_776_YYB_D&inCharset=utf-8&outCharset=utf-8&format=json&type=userLvTitle&start=0&count=1500&uin='.QQNUM);
if($r->getStatusCode()=='403'){
	exit('登录失败'.PHP_EOL);
}
$all_user=json_decode($r->getBody());
if($all_user->code!=0){
	exit('登录失败'.PHP_EOL);
}

//遍历用户列表
foreach($all_user->data->rank as $user){
	//得到资源
	if($user->resource_state=='normal'){
		$r=$client->request('POST','/proxy/domain/mqzmall.qzone.qq.com/fcg-bin/fcg_qzpet_collect_resource?t=0.5680071718097595&g_tk='.$gtk,
			['body'=>'hostUin='.$user->uin.'&format=json&uin='.QQNUM]
		);
		if(json_decode($r->getBody())->data->code==0){
			echo '成功取得'.$user->nick.'的糖果'.PHP_EOL;
		}
	}
	
	//清理baba+喂食
	$r=$client->request('GET','/proxy/domain/mqzmall.qzone.qq.com/fcg-bin/fcg_qzpet_get?t=1&g_tk='.$gtk.'&hostUin='.$user->uin.'&format=json&uin='.QQNUM);
	$pets=json_decode($r->getBody())->data->host->pet_list->list;
	foreach($pets as $pet){
		$petid=$pet->id;
		$petname=$pet->name;
		$r=$client->request('GET','/proxy/domain/mqzmall.qzone.qq.com/fcg-bin/fcg_qzpet_get?t=1&g_tk='.$gtk.'&hostUin='.$user->uin.'&format=json&hostPetId='.$petid.'&uin='.QQNUM);
		$babacount=json_decode($r->getBody())->data->host->pet->baba->count;
		$foodamount=json_decode($r->getBody())->data->host->pet->food_amount;
		for($i=0;$i<$babacount;$i++){//清理baba
			$r=$client->request('POST','/proxy/domain/mqzmall.qzone.qq.com/fcg-bin/fcg_qzpet_clean_baba?t=1&g_tk='.$gtk,[
			'body'=>'format=json&hostUin='.$user->uin.'&hostPetId='.$petid.'&userPetId=0&uin='.QQNUM
			]);
			if(json_decode($r->getBody())->data->code==0){
				echo '成功清理'.$user->nick.'之宠'.$petname.'的1个baba'.PHP_EOL;
			}
		}
		$foodamount=$foodamount/20;//喂食
		for($i=$foodamount;$i<3;$i++){
			if($food_count>=FOOD_LIMIT){
				echo '喂食次数已达到设定的LIMIT'.PHP_EOL;
				break;
			}
			$r=$client->request('POST','/proxy/domain/mqzmall.qzone.qq.com/fcg-bin/fcg_qzpet_feed?t=1&g_tk='.$gtk,[
			'body'=>'hostUin='.$user->uin.'&hostPetId='.$petid.'&foodId=12189&format=json&uin='.QQNUM
			]);
			if(json_decode($r->getBody())->data->code==0){
				echo '成功给'.$user->nick.'之宠'.$petname.'喂食1次'.PHP_EOL;
				$food_count++;
			}else{
				echo '给'.$user->nick.'之宠'.$petname.'喂食失败'.PHP_EOL;
			}
		}
	}
}
