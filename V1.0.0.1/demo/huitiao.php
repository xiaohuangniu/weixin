<?php
class Huitiao{
	public function WxNotify($pid){
		error_log(json_encode($pid), 3, 'pid.txt');
		return true;
	}
}