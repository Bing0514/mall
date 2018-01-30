<?php



// 数据库连接初始化
function mysqlInit($host, $username, $password, $dbName){
	// 数据库操作
	$con = mysqli_connect($host, $username, $password);
	if(!$con){
		return false;
	}

	mysqli_select_db($con,'mall');
	// 设置字符集
	mysqli_set_charset($con,'utf8');

	return $con;

}

// 密码加密
function createPassword($password){
	if(!$password){
		return false;
	}
	return md5(md5($password).'IMOOC');
}


// 消息提示
function msg($type,$msg=null,$url=null){
	$toUrl = "Location:msg.php?type={$type}";
	// 但msg为空时 url不写入
	$toUrl.=$msg?"&msg={$msg}":'';
	// 但url为空时 tourl不写入
	$toUrl.=$url?"&url={$url}":'';
	header($toUrl);
	exit;
}

function imgUpload($file){
	// 检查上传文件是否合法
    if(!is_uploaded_file($file['tmp_name'])){
        msg(2,'请上传符合规范的图像');
    }
   
   // 图像类型验证
   $type = $file['type'];
   if(!in_array($type, array("image/png","image/gif","image/jpeg"))){
        msg(2,'请上传png,gif,jpg的图像');
   }


    // 上传目录
    $uploadPath = './static/file/';
    // 上传目录访问url
    $uploadUrl ='/static/file/';
    // 上传文件夹
    $fileDir = date('Y/md/',$_SERVER['REQUEST_TIME']);

    // 检查上传目录是否是存在的
    if(!is_dir($uploadPath.$fileDir)){
        //递归创建目录 0755读写权限
        mkdir($uploadPath.$fileDir,0755,true);
    }

    //获取扩展名
    $ext = strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));
    // 上传图像名称唯一性处理
    $img = uniqid().mt_rand(1000,9999).'.'.$ext;
    // 物理地址
    $imgPath = $uploadPath.$fileDir.$img;
    // url地址
    $imgUrl = 'http://localhost/mall'.$uploadUrl.$fileDir.$img;
    // 操作失败 查看上传目录的权限
    if(!move_uploaded_file($file['tmp_name'], $imgPath)){
        msg(2,'服务器繁忙请稍后再试');
    }

    return $imgUrl;

}

// 检查用户是否登录
function checkLogin(){
	// 开启session
	session_start();
	// 用户未登录返回false
	if(!isset($_SESSION['user']) || empty($_SESSION['user'])){
	    return false;
	}
	return true;
}


//获取当前url
function getUrl(){
	// 判断是http还是https的
	$url = '';
	$url .= $_SERVER['SERVER_PORT']==443 ? 'https://' : 'http://';
	$url .= $_SERVER['HTTP_HOST'];
	$url .= $_SERVER['REQUEST_URI'];
	return $url;
}

// 根据page生成URL
function pageUrl($page,$url=''){
	$url = empty($url) ? getUrl() : $url;
	// 查询url中是否存在?
	$pos =strpos($url, '?');
	if($pos === false){
		$url .='?page='.$page;
	}else{
		$queryString = substr($url, $pos+1);
		// 解析queryString为数组
		parse_str($queryString,$queryArr);
		if(isset($queryArr['page'])){
			unset($queryArr['page']);
		}
		$queryArr['page'] = $page;
		// 将queryArr重新拼接成queryString
		$queryStr = http_build_query($queryArr);

		$url = substr($url,0, $pos).'?'.$queryStr;

	}
	return $url;
}


/**
 * 分页显示
 * @param  int $total 数据总数
 * @param  int $currentPage 当前页
 * @param  int $pageSize 每页显示条数
 * @param  int $show 显示按钮数
 * @return string
 */
function pages($total,$currentPage,$pageSize,$show=6){
	$pageStr = '';
	// 仅仅当总数大于每页显示条数 才进行分页处理
	if($total>$pageSize){
		// 总页数
		$totalPage = ceil($total/$pageSize);//向上取整 获取总页数

		// 对当前页进行容错处理你
		 $currentPage = $currentPage > $totalPage ? $totalPage : $currentPage;

		// 分页起始页
		$from = max(1,($currentPage - intval($show/2)));
		// 分页结束页
		$to = $from + $show-1;
		
		$pageStr .= '<div class="page-nav">';
		$pageStr .= '<ul>';
		// 仅当 当前页大于1的时候 存在首页和上一页按钮
		if($currentPage>1){
			$pageStr .= "<li><a href='".pageUrl(1)."'>首页</a></li>";
            $pageStr .= "<li><a href='".pageUrl($currentPage - 1)."'>上一页</a></li>";
		}

		// 当结束页大于总页
		if($to>$totalPage){
			$to = $totalPage;
			$from = max(1,$to-$show+1);
		}

		if($from>1){
			$pageStr .= '<li>...</li>';
		}

		for($i=$from;$i<=$to;$i++){
			if($i != $currentPage){
				$pageStr .="<li><a href='".pageUrl($i)."'>{$i}</a></li>";
			}else{
				$pageStr .= "<li><span class='curr-page'>{$i}</span></li>";
			}
		}

		if($to<$totalPage){
			$pageStr .= '<li>...</li>';
		}

		if($currentPage<$totalPage){
            $pageStr .= "<li><a href='".pageUrl($currentPage + 1)."'>下一页</a></li>";
            $pageStr .= "<li><a href='".pageUrl($totalPage)."'>尾页</a></li>";
		}
		$pageStr .='</ul>';
		$pageStr .='</div>';
	}
	return $pageStr;
}