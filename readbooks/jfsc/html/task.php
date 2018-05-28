<?php
    header ( "Content-type: text/html; charset=utf-8" );
    require_once '../../include/common_new.inc.php';
    require_once '../../include/wx_jsdk_class.php';
    require_once '../../auth_comm_jfsc.php';
        
    $appid = $site_conf ['appid'];        
    
    $db = new dbstuff();
    $db->connect ( $dbhost, $dbuser, $dbpw, $dbname, $pconnectz, true, "utf8" );
    if (!$db)
    {
        echo json_encode ( array ( 'status' => - 1, 'msg' => '数据库链接失败') );
        exit();
    }

    if (!empty($sopenid))
    {
        $openid 		= $userinfo ["openid"];
        $unionid 		= $userinfo ["unionid"];
        $requestdomain 	= $site_conf ['maindomain'];
        $appid          = $site_conf ['appid'];
        $reqUrl 		= $requestdomain . "/jfsc/jfsc_function.php?method=getShareUserInfo&sopenid=$sopenid&openid=$openid&group=$group&unionid=$unionid&appid=$appid";

        $userData 		= httpRequest_php ( $reqUrl );                
    }

    //活动信息
    $rs = $db->fetch_first("select * from {$tablepre}subject where appid ='$appid' and ishidden=0 limit 1");
    //查询剩余奖品数目
    $subject_id = $rs['id'];
    $orderrs = $db->fetch_first("select count(1) onum from {$tablepre}order where subject_id = $subject_id");
    $shengyu = $rs['stock']-$orderrs['onum'];
    //查询用户是否完成任务
    $unionid = $userinfo ["unionid"];
    $rsu = $db->fetch_first("select tid from {$tablepre}openid where unionid='$unionid' and appid='$appid'");
    //$sql = "select tid from {$tablepre}openid where unionid='$unionid' and appid='$appid'";    
    $uid = $rsu['tid'];
    $rsy = $db->fetch_first("select count(1) ynum from {$tablepre}openid where rel_id = $uid");
    $ynum = $rsy['ynum'];
    //查询用户是否已兑奖
    $rsjp = $db->fetch_first("select * from {$tablepre}order where subject_id = $subject_id and unionid='$unionid'");
?>
<!doctype html>
<html>
    <head>
        <meta charset="utf-8">        
        <meta name="viewport" content="width=device-width, initial-scale=1.0 user-scalable=no">
        <meta name="format-detection" content="telephone=no" />        
                
        <script src="/assets/js/jquery.min-2.2.1.js"></script>        
        <script src="/assets/js/bootstrap-3.3.4.js"></script>
                     
        <link href="/assets/css/weui.css" rel="stylesheet">
        <link href="/assets/css/bootstrap-3.3.4.css" rel="stylesheet">        
        <link rel="stylesheet" type="text/css" href="/assets/css/detail.css">
        <title><? echo $rs["title"] ?></title>
    </head>
    <body>
        <!-- 详情页开始 -->
        <!-- 商品图片 -->
        <div class="detail-img">
            <img src="<? echo $rs['head_url']?>" alt="shop-img">
        </div>
        <!-- 商品图片结束 -->
        <!-- 商品简介 -->
        <section class="detail-summary">
            <div class="detail-title">
                <h3><? echo $rs["title"] ?></h3>
            </div>           
            <div class="detail-extmoney">
                <p>兑换支付:<span>0元</span></p>              
                <p>所需运费:<span>0元</span></p>
            </div>            
            <div class="detail-num">
                <p>剩余数量:<span><? echo $shengyu?>个</span></p>
            </div>
        </section>
        <!-- 商品简介结束 -->        

        <!-- 收件人信息 -->  
        <section class="addressee">
            <h3>收件人信息</h3>
            <p><label for="addres-person">收件人:</label><input id='address_realname' type="text" class="addres-person" value="<? echo $rsjp['name'] ?>"></p>
            <p><label for="addres-phone">手机号:</label><input id='address_mobile' type="text" class="addres-phone" value="<? echo $rsjp['mobile']?>"></p>
            <div class="selectAddress">
                <label>收货地址</label>
                <div class="line-wrap">
                    <div class="line">
                        <select id="sel-provance" onchange="selectCity();" class="select">
                            <option value="" selected="true">所在省份</option>
                        </select>
                    </div>
                    <div class="line">
                        <select id="sel-city" onchange="selectcounty()" class="select">
                            <option value="" selected="true">所在城市</option>
                        </select>
                    </div>
                    <div class="line">
                        <select id="sel-area" class="select">
                            <option value="" selected="true">所在地区</option>
                        </select>
                    </div>
                </div>
            </div>
            <p><label for="addres-site"></label><textarea id='address_address' class="addres-site" rows="2" ><? echo $rsjp['address']?></textarea></p>
            <p><label class="addres-nobg" for="addres-other">备注:</label><textarea id="remarks" class="addres-other" rows="2"><? echo $rsjp['remarks']?></textarea></p>
        </section>
        <!-- 收件人信息结束 -->

        <!-- 商品详情 -->
        <section class="detail-infor">
            <? echo $rs['details']?>
        </section>
        <!-- 商品详情结束 -->
        <!-- fixed标签 -->
        <div class=btn-wrap>
            <?
                if($rsjp){
                    //已经领取了奖品
                    ?>
                    <a class="weui_btn weui_btn_disabled weui_btn_warn" href="javascript:void(0)">奖品已经领取</a>
                    <?
                }else{
                    if($shengyu>0){
                        if($ynum<$rs['tasks']){
                    ?>
                        <a class="weui_btn weui_btn_disabled weui_btn_warn" href="javascript:void(0)">任务还未完成</a>
                    <?
                        }else{
                    ?>
                        <a class="weui_btn weui_btn_plain_primary" href="javascript:void(0)" onclick="saveAddress()">领取任务奖励</a>
                    <?
                        }
                    }else{
                        ?>
                        <a class="weui_btn weui_btn_disabled weui_btn_warn" href="javascript:void(0)">奖品数量不足</a>
                        <?
                    }
                }
            ?>
        </div>
        <!-- fixed标签结束 -->
        <!-- 弹出项开始 -->
        <div id="toast" style="display: none;">
            <div class="weui_toast_content"></div>
        </div>        
        <!-- 弹出项结束 -->
        <script type="text/javascript" src="/assets/js/cascade.js"></script>
        <script type="text/javascript" src="/assets/js/sweetalert.min.js"></script>
        <script>
            // 省市级联
            $(function(){
                <? 
                    if($rsjp){
                ?>
                    cascdeInit("<? echo $rsjp['provance']?>","<? echo $rsjp['city']?>","<? echo $rsjp['area']?>");
                <?
                    }else{
                ?>
                    cascdeInit();
                <?
                    }
                ?>                
            })

            function saveAddress(){
                var name = $("#address_realname").val().trim();
                if(name == ''){
                    $("#toast div").text("请填写收件人姓名");
                    $("#toast").fadeIn(500).fadeOut(2000);
                    //$("#address_realname").focus();
                    return;
                }
                var mobile = $("#address_mobile").val().trim();
                if(mobile == ''){
                    $("#toast div").text("请填写收件人电话");
                    $("#toast").fadeIn(500).fadeOut(2000);
                    //$("#address_mobile").focus();
                    return;
                }

                var provance = $("#sel-provance").val();
                if(provance == ''){
                    $("#toast div").text("请选择邮寄的省份");
                    $("#toast").fadeIn(500).fadeOut(2000);
                    //$("#sel-provance").focus();
                    return;
                }
                var city = $("#sel-city").val();
                if(city == ''){
                    $("#toast div").text("请选择邮寄的城市");
                    $("#toast").fadeIn(500).fadeOut(2000);
                    //$("#sel-city").focus();
                    return;
                }
                var area = $("#sel-area").val();
                if(area == ''){
                    $("#toast div").text("请选择邮寄的地区");
                    $("#toast").fadeIn(500).fadeOut(2000);
                    //$("#sel-area").focus();
                    return;
                }
                var address = $("#address_address").val().trim();
                if(address == ''){
                    $("#toast div").text("请填写邮寄的详情地址");
                    $("#toast").fadeIn(500).fadeOut(2000);
                    //$("#address_address").focus();
                    return;
                }
                var remarks = $("#remarks").val().trim();
                               
                $.ajax({
                    type:'GET',
                    url:'../orderInfo.php',
                    data:{
                        method:'addOrder',
                        subject_id:<? echo $subject_id?>,
                        uid: <? echo $uid?>,			//用户id（必填）
						unionid: '<? echo $unionid?>',
                        name: name,						//接收人姓名 （必填）
						mobile: mobile,					//接收人电话 （必填）
						provance: provance,
						city: city,
						area: area,
                        address: address,				//接收人地址 （必填）
                        remarks: remarks,                        
                        appid: '<? echo $appid?>'		//对应的公众号id（必填）
                    },
                    dataType:"json",
                    success:function(data){                        
                        $("#toast").hide();                        
                        // code：0 成功 
                        // -1:任务未完成
                        // -2：缺少参数
                        // -3:失败                        
                        // -4：没有库存
                        if(data.code == 0)
                        {
                            var sdata = {'title': '', 'text': '兑换成功' ,'icon':'success'};
                            swal(sdata).then((value) => {
                                location.reload();
                            });                            
                        }else if(data.code == -1)
                        {
                            var sdata = {'title': '', 'text': '任务未完成' ,'icon':'error'};
                            swal(sdata).then((value) => {
                                
                            });                        
                        }else if(data.code == -2)
                        {
                            var sdata = {'title': '', 'text': '缺少必填参数' ,'icon':'error'};
                            swal(sdata).then((value) => {
                                
                            });                           
                        }else if(data.code == -3)
                        {
                            var sdata = {'title': '', 'text': '兑换失败' ,'icon':'error'};
                            swal(sdata).then((value) => {
                                
                            });                          
                        }else if(data.code == -4)
                        {
                            var sdata = {'title': '', 'text': '没有库存' ,'icon':'error'};
                            swal(sdata).then((value) => {
                                
                            });                           
                        }else if(data.code == -5)
                        {
                            var sdata = {'title': '', 'text': '请不要重复提交' ,'icon':'error'};
                            swal(sdata).then((value) => {
                                
                            });                           
                        }
                    }
                })
            }
        </script>
    </body>
</html>