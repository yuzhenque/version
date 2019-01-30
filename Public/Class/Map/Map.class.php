<?php
class Map {
    /**
	 * 根据地址返回坐标
	 */
    public function getPoint($address){
		if($address){
	    	$url = 'http://maps.google.cn/maps/geo?q='.$address.'&output=csv&oe=utf8&sensor';
	    	import("Http",'Public/Class/Net/');
	    	$http = new Http();
	    	$data = $http->getdata($url);
	    	if($data){
	    		$data = explode(',',$data);
	    		$points['x'] = $data[3];
	    		$points['y'] = $data[2];
	    	}
	    	header('Content-Type:application/json;charset=utf-8');
	    	echo json_encode($points);exit;
		}
    }
    /**
     * 根据坐标返回地址名称
     */
    public function getPointAddr($x,$y){
    	//http://maps.google.cn/maps/geo?output=csv&key=abcdef&q=24.4414170,118.0684000
    	$url = 'http://ditu.google.cn/maps/geo?output=csv&key=abcdef&q='.$x.','.$y;
    	import("Http",'Public/Class/Net/');
    	$http = new Http();
    	$data = $http->getdata($url);
    	if($data){
    		$data = explode(',',$data);
    		$data = $data[2];
    	}
    	return $data;
    }
    public function Google2Baidu($x,$y){
		$url = 'http://api.map.baidu.com/ag/coord/convert?from=2&x='.$x.'&y='.$y.'&to=4';
		import("Http",'Public/Class/Net/');
    	$http = new Http();
    	$data = $http->getdata($url);
    	if($data){
    		$data = json_decode($data);
    		$redata['x'] = base64_decode($data->x);
    		$redata['y'] = base64_decode($data->y);
    	}
    	return $redata;
	}
	public function Google2Baidu2($x,$y){
    	if($x&&$y){
    		$redata['x'] = $x*1.0000568461567492425578691530827;
    		$redata['y'] = $y*1.0002012762190961772159526495686;
    	}
    	return $redata;
	}
	public function getDistance($radLat1,$radLat2,$type=2) {
		$data = array();
		if($radLat1&&$radLat2){
	    	$radLat1 = explode(',',$radLat1);
	    	$radLat2 = explode(',',$radLat2);
	    	$x1 = $radLat1[0];
	    	$y1 = $radLat1[1];
	    	$x2 = $radLat2[0];
	    	$y2 = $radLat2[1];
	    	$latx1 = $this->Rad($x1);
	    	$latx2 = $this->Rad($x2);
	    	$a = $latx1-$latx2;
	    	$b = $this->Rad($y1)-$this->Rad($y2);
	    	$s = 2*asin(sqrt(pow(sin($a/2),2)+cos($latx1)*cos($latx2)*pow(sin($b/2),2)));
	    	$s = $s*6378.137;
	    	$s = round($s*10000*1000)/10000;//米
	    	if($type==1){
		    	$data['distance'] = sprintf('%d',$s);
		    	$data['unit'] = '米';
	    	}
	    	elseif($type==2){
	    		$data['distance'] = sprintf('%.1f',$s/1000);
		    	$data['unit'] = '公里';
	    	}
	    	else{
	    		if($s>=1000){
	    			$data['distance'] = sprintf('%.1f',$s/1000);
		    		$data['unit'] = '公里';
	    		}else{
	    			$data['distance'] = sprintf('%d',$s);
		    		$data['unit'] = '米';
	    		}
	    	}
		}
		return $data;
	}
	private function Rad($val){
		$PI = 3.14159265358979;
		return $val*$PI/180.0;
	}
}
?>