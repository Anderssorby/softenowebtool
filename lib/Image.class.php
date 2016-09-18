<?php
class Image {
	protected $id = "";
	protected $temp;
	protected $mime = "";
	protected $height,$width;

	function __construct($id) {
		$this->id = $id;
		$this->load();
		if ($this->height&&$this->width) {
			$this->crop($this->width, $this->height);
		} else {
			list($this->width, $this->height) = getimagesize($this->temp);
		}
	}

	protected function load() {
		$query = new Query();
		$result = $query->select('images')->where("id", $this->id)->query()->assoc();
// 		$query = 'select * from `images` where `id` = '.$this->id;
// 		$resource = mysql_query($query);
// 		if (!$resource) {
// 			echo 'kunne ikke gjøre query:'.mysql_error();
// 		}
// 		$result = mysql_fetch_array($resource);
		if ($result) {
			$this->height = $result[0]['height'];
			$this->width = $result[0]['width'];
			$this->mime = $result[0]['mime'];
			$this->temp = tempnam(sys_get_temp_dir(), 'bilde');
			file_put_contents($this->temp, $result[0]['bilde']);
		} else {
			$pic = imagecreatetruecolor(100, 100);
			$black = imagecolorallocate($pic, 0, 0, 0);
			$white = imagecolorallocate($pic, 255, 255, 255);
			imagefilledrectangle($pic, 0, 0, 99, 99, $white);
			$this->height = 100;
			$this->width = 100;
			$this->mime = 'image/jpeg';
			$this->temp = tempnam(sys_get_temp_dir(), 'bilde');
			imagejpeg($pic, $this->temp);
		}
	}

	function crop($nwidth = 50, $nheight = 50, $scale = 0.7) {
		$dest = imagecreatetruecolor($nwidth, $nheight);
		$data = imagecreatefromstring(file_get_contents($this->temp, "r"));
		$so_x = ($this->width-$this->width*$scale)/2;
		$so_y = ($this->height-$this->height*$scale)/2;
		$width = $this->width;
		$height = $this->height;
		if (!($width < $nwidth) || !($height < $nheight)) {
			$re = imagecopyresized($dest, $data, 0, 0, $so_x, $so_y, $nwidth, $nheight, $width*$scale, $height*$scale);
			imagejpeg($dest, $this->temp);
		}
	}

	function __toString() {
		header('Content-type: ' . $this->mime);
		return file_get_contents($this->temp);
	}

	function __get($name) {
		$ar = (array) $this;
		return $ar[$name];
	}
}
