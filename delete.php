<?php
include_once './lib/fun.php';
if(!checkLogin()){
	msg(2,'请登录','login.php');
}

$goodsId = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : '';

// 如果id不存在 跳转到商品列表
if(!$goodsId){
	msg(2,'参数非法','index.php');
}

// 根据商品id查询商品信息
$con = mysqlInit('localhost', 'root', '', 'mall');

//当根据id查询商品信息为空 跳转商品列表页
$sql = "SELECT `id` FROM `im_goods` WHERE `id` = {$goodsId}";
$obj = mysqli_query($con,$sql);

// 当根据id查询商品信息为空 跳转商品列表页
if(!$goods = mysqli_fetch_assoc($obj)){
    echo $goods;die();
    msg(2,'画品不存在','index.php');
}

// 删除处理
$sql = "DELETE FROM `im_goods` WHERE `id` ={$goodsId} LIMIT 1";
if($result = mysqli_query($con,$sql)){
	msg(1,'操作成功','index.php');
}else{
	msg(2,'操作失败','index.php');
}

// 注意
// 1.项目中 不会真正的删除商品 而是更新商品表中的status 1:正常操作 -1：删除操作
// 2.增加商品编辑时候的时间 update_time