<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'].'/connects.php';


$ERROR = '';

if(isset($_POST['first'])){
    $current_numb = 0;
    session_unset();
    $_SESSION['info'] = $_POST['info'];
    $_SESSION['current_numb'] = "0";
}
else {
    $_SESSION['current_numb']  = (int)($_SESSION['current_numb']) + 1;
    $current_numb = (int)$_SESSION['current_numb'];
}



//print_r($_SESSION);


if($current_numb == count($_SESSION['info'])) {
    print_r(json_encode($_SESSION));
    session_unset();
    exit();
}
else {           

    
    $info = $_SESSION['info'][$current_numb];
      
    
    $name = $info[0];
    $access_token = $info[1];
    if($info[2] != '')
        $user_agent = $info[2];
    if($info[3] != ''){
        $proxy = explode("//", explode("@", $info[3])[0])[1];
        $login_password = explode("@", $info[3])[1];
    }
    if($info[4] != '')
        $comment = $info[4];
    if($_POST["group"] == '')
        $group = "Нет";
    else
        $group = $_POST["group"];
    
    
   
    $url = "https://graph.facebook.com/v7.0/me?fields=first_name,last_name,accounts{access_token,id,name,page_backed_instagram_accounts,category,is_published}&access_token=".$access_token;   
    
    
    include($_SERVER['DOCUMENT_ROOT']."/get.php");
    
    $_SESSION['output'][$current_numb] = $output;
    if($output->error) {
        $ERROR = $output->error->message;
        $_SESSION['error'][$current_numb] = $output->error->error_subcode;
    }
    else
        $firstoutput = $output;
    
    
    if($ERROR == '') {
        $url = "https://graph.facebook.com/v7.0/me/adaccounts?fields=adspixels,promote_pages{access_token,id,name,page_backed_instagram_accounts,category},insights.date_preset(lifetime),campaigns{name,effective_status,adsets{name,effective_status,ads.date_preset(lifetime).time_increment(lifetime).limit(500){insights.limit(500).date_preset(lifetime){results,relevance_score,inline_link_click_ctr,inline_link_clicks,ctr,cpc,cpm},creative{effective_object_story_id,effective_instagram_story_id,actor_id},adlabels,created_time,recommendations,updated_time,ad_review_feedback,bid_info,configured_status,delivery_info,status,effective_status,adcreatives.limit(500){place_page_set_id,object_story_spec,image_crops,image_url,status,thumbnail_url},result,cost_per_lead_fb,name,clicks,spent,cost_per,reach,link_ctr,impressions}}},date{lifetime},funding_source_details,business{name,link},adrules_library{name},current_unbilled_spend,adspaymentcycle,spend_cap,amount_spent,age,disable_reason,account_status,balance,all_payment_methods{pm_credit_card{account_id,credential_id,display_string,exp_month,exp_year}},currency,timezone_name,created_time,name,status,adtrust_dsl&access_token=".$access_token;
        
        
        include($_SERVER['DOCUMENT_ROOT']."/get.php");
        if(is_null($output)) 
            include($_SERVER['DOCUMENT_ROOT']."/get.php");
        
        $_SESSION['output'][$current_numb] = $output;
        if($output->error) {
            $ERROR = $output->error->message;
            $_SESSION['error'][$current_numb] = $output->error->error_subcode;
        }
    }
    
    
    
    
    if($ERROR == '') {
        
        
        $account_statuses = array(
                1 => 'ACTIVE',
                2 => 'DISABLED',
                3 => 'UNSETTLED',
                7 => 'PENDING_RISK_REVIEW',
                8 => 'PENDING_SETTLEMENT',
                9 => 'IN_GRACE_PERIOD',
                100 => 'PENDING_CLOSURE',
                101 => 'CLOSED',
                201 => 'ANY_ACTIVE',
                202 => 'ANY_CLOSED'
            );
            
        $disable_reasons = array('NONE', 'ADS_INTEGRITY_POLICY', 'ADS_IP_REVIEW', 'RISK_PAYMENT', 'GRAY_ACCOUNT_SHUT_DOWN', 'ADS_AFC_REVIEW', 'BUSINESS_INTEGRITY_RAR', 'PERMANENT_CLOSE', 'UNUSED_RESELLER_ACCOUNTR');
        
        
        
        $queryUp = $link->query("INSERT INTO Tokenbase(access_token, acc_id, name, proxy, fp, user_agent, groups, comment, state, reftime, acc_name) VALUES ('".$access_token."', '".$firstoutput->id."', '".$firstoutput->first_name." ".$firstoutput->last_name."', '".$info[3]."', '+', '".$user_agent."', '".$group."', '".$comment."', 'Active', '".time()."', '".$name."')");
        //$queryUp = $link->query("INSERT INTO Tokenbase(access_token, acc_id, name, proxy, fp, user_agent, groups, comment, state, reftime, acc_name) VALUES ('".$access_token."', '".$firstoutput->id."', '".$firstoutput->first_name." ".$firstoutput->last_name."', '".explode(":", explode("//", explode("@", $info[3])[0])[1])[1]."', '+', '".$user_agent."', '".$group."', '".$comment."', 'Active', '".time()."', '".$name."')");
    
        //fp
        foreach($firstoutput->accounts->data as $fp) {
            $name = $fp->name;
            $fp_id = $fp->id;
            $fp_token = $fp->access_token;
            $category = $fp->category;
            $instagram_actor_id = $fp->page_backed_instagram_accounts->data[0]->id;
            
            
            $queryUp = $link->query("INSERT INTO Fp_Id (name,fp_token,instagram_actor_id,access_token,category,fp_id,is_published) VALUES ('".$name."', '".$fp_token."', '".$instagram_actor_id."', '".$access_token."', '".$category."', '".$fp_id."', '".$fp->is_published."')");
      
        }
        
         
        foreach($output->data as $data) {
            //rk
            $pixel_id = $data->adspixels->data[0]->id;
            $disable_reason = $data->disable_reason;
            $account_status = $data->account_status;
            $card = $data->all_payment_methods->pm_credit_card->data[0]->display_string;
            $currency = $data->currency;
            $name = $data->name;
            $adtrust_dsl = $data->adtrust_dsl;
            $threshold_amount = (string)(((int)($data->adspaymentcycle->data[0]->threshold_amount))/100);
            $rk_id = $data->id;
            
        
            $queryUp = $link->query("INSERT INTO Rk_Id (state,disable_reason,card,currency,name,adtrust_dsl,threshold_amount,pixel_id,rk_id,access_token) VALUES ('".$account_statuses[$account_status]."', '".$disable_reasons[$disable_reason]."', '".$card."', '".$currency."', '".$name."', '".$adtrust_dsl."', '".$threshold_amount."', '".$pixel_id."', '".$rk_id."', '".$access_token."')");
            
                
            //camp
            foreach($data->campaigns->data as $campaing) {
                $name = $campaing->name;
                $camp_id = $campaing->id;
                $state = $campaing->effective_status;
                
                
                $queryUp = $link->query("INSERT INTO Camp_Id (name,state,rk_id,access_token,camp_id) VALUES ('".$name."', '".$state."', '".$rk_id."', '".$access_token."', '".$camp_id."')");
               
                
                //adset
                foreach($campaing->adsets->data as $adsets) {
                    $name = $adsets->name;
                    $adset_id = $adsets->id;
                    $state = $adsets->effective_status;
                    //print_r($adsets);
                    
                    $queryUp = $link->query("INSERT INTO Adset_Id (name,state,camp_id,access_token,adset_id) VALUES ('".$name."', '".$state."', '".$camp_id."', '".$access_token."','".$adset_id."')");
                
                
                    //ad
                    foreach($adsets->ads->data as $ad) {
                        //insights
                        $effective_status = $ad->effective_status;
                        $creative = $ad->adcreatives->data[0]->thumbnail_url;
                        $result = $ad->result;
                        $cpl = $ad->cost_per_lead_fb;
                        $name = $ad->name;
                        $clicks = $ad->clicks;
                        $spent = $ad->spent;
                        $impressions = $ad->impressions;
                        $ad_id = $ad->id;
                        $ctr = $ad->insights->data[0]->inline_link_click_ctr;
                        $cpc = $ad->insights->data[0]->cpc;
                        $cpm = $ad->insights->data[0]->cpm;
                        $result = $ad->result;
                        $clicks = $ad->insights->data[0]->inline_link_clicks;
                        $spent = (string)(((int)($ad->spent))/100);
                        $impressions = $ad->impressions;
                        
                        if($ad->adcreatives->data[0]->object_story_spec->link_data->link)   
                            $site = $ad->adcreatives->data[0]->object_story_spec->link_data->link;
                        else
                            $site = $ad->adcreatives->data[0]->object_story_spec->video_data->call_to_action->value->link;
                       
                        $queryUp = $link->query("INSERT INTO Ad_Id (ctr,cpc,cpm,impressions,spend,clicks,creative,name,cpl,results,state,ad_id,access_token,adset_id,link) VALUES ('".$ctr."','".$cpc."','".$cpm."','".$impressions."', '".$spent."', '".$clicks."', '".$creative."', '".$name."', '".$cpl."', '".$result."', '".$effective_status."', '".$ad_id."', '".$access_token."', '".$adset_id."','".$site."')");
                        
                    }
                }
            }
        }
        
        
    }
    
    $log = "ADD ACCOUNTS ".date("H:i:m d:m:Y")."\n".json_encode($_SESSION)."\n".date("H:i:m d:m:Y", $row['reftime'])."\n".json_encode($firstoutput)."\n".json_encode($output)."\n\n\n";
    file_put_contents($_SERVER['DOCUMENT_ROOT']."/logs.txt", $log, FILE_APPEND);
    
        
    header('Location: addAccounts.php');
    //header('Location: test.php');
}
?>
