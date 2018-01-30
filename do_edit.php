<?php
include_once './lib/fun.php';

if(!checkLogin()){
    msg(2,'请登录','login.php');
}

// 表单进行了提交处理
if(!empty($_POST['name'])){
	$con = mysqlInit('localhost', 'root', '', 'mall');

	if(!$goodsId = intval($_POST['id'])){
		msg(2,'参数非法');
	}

	// 根据商品id查询商品信息
	$sql = "SELECT * FROM `im_goods` WHERE `id` = {$goodsId}";
	$obj = mysqli_query($con,$sql);

	// 当根据id查询商品信息为空 跳转商品列表页
	if(!$goods = mysqli_fetch_assoc($obj)){
	    msg(2,'画品不存在','index.php');
	}



    // 画品名称
    $name = mysqli_real_escape_string($con,trim($_POST['name']));
    // 画品价格
    $price = intval($_POST['price']);
    // 画品简介
    $des = mysqli_real_escape_string($con,trim($_POST['des']));
    // 画品详情
    $content = mysqli_real_escape_string($con,trim($_POST['content']));

    $nameLength = mb_strlen($name,'utf-8');
    if($nameLength<=0 || $nameLength>30){
        msg(2,'画名应在1-30字符之内');
    }

    if($price<=0 || $price>99999999){
        msg(2,'画名名称应该小于9999999');
    }

    $desLength = mb_strlen($des, 'utf-8');
    if($desLength <=0 || $desLength>100){
        msg(2,'画名简介应在1-100字符之内');
    }

    if(empty($content)){
        msg(2,'画名不能为空');
    }

    // 更新数组
    $update = array(
    	'name'    => $name,
    	'price'   => $price,
    	'des'     => $des,
    	'content' => $content
    );

    // 仅当用户选择上传图片时候才上传
    if ($_FILES['files']['size'] > 0) {
    	$pic = imgUpload($_FILES['file']);
    	$update['pic'] = $pic;
    }

    // 只更新被更改的信息，对比数据库数据跟用户表单数据
    foreach($update as $k => $v){
    	if($goods[$k] == $v){// 对应key相等 删除要更新的字段
    		unset($update[$k]);
    	}
    }

   //对比2个数组，如果没有需要更新字段
   if(empty($update)){
   	msg(2,'操作成功','edit.php?id='.$goodsId);
   }

     // 更新sql处理
    $updateSql ='';
    foreach($update as $k=>$v){
    	$updateSql .="`{$k}` ='{$v}',";
    }
    // 去掉最后那个多余的逗号
    $updateSql = rtrim($updateSql,',');


    unset($sql,$obj,$result);
    $sql ="UPDATE `im_goods` SET {$updateSql} WHERE `id`={$goodsId}";

    // 当更新成功
    if($result = mysqli_query($con,$sql)){
    	// mysqli_affected_rows();//影响行数
    	msg(1,'操作成功','edit.php?id='.$goodsId);
    }else{
    	msg(2,'操作失败','edit.php?id='.$goodsId);
    }



    // update `im_goods` set `name`='value',`price`='value',
}else{
	msg(2,'路由非法','index.php');
}