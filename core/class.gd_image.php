<?php
/*
	Sample Usage:
	$im = new GD_Image('foo.jpg');
	$im->scale_crop(800, 600); // this method will resize and crop from center, use scale() to just resize to max width and height
	$im->save_as('foo_new.jpg');

	TODO Proper Error Handling
*/

class Gd_Image
{
	private $im     = null;
	private $width  = null;
	private $height = null;
	private $type   = null;
	private $image_types = array('jpg' => 'jp(e)?g', 'png' => 'png', 'gif' => 'gif');

	public function __construct($data = null)
	{
		if (is_resource($data) && get_resource_type($data) == 'gd')
			return $this->load_resource($data);
		elseif (file_exists($data) && is_readable($data) && is_file($data))
			return $this->load_file($data);
		else
			return false;
	}

	public function __destruct()
	{
		if (is_resource($this->im) && get_resource_type($this->im) == 'gd')
		{
			imagedestroy($this->im);
		}
	}

    // Return image data as a string.
    // Is there a way to do this without using output buffering?
    public function __tostring($type = 'jpg', $quality = 75)
    {
        ob_start();

        if ($type == 'jpg' && (imagetypesypes() & IMG_JPG))
            imagejpeg($this->im, null, $quality);
        elseif ($type == 'png' && (imagetypes() & IMG_PNG))
            imagepng($this->im);
        elseif ($type == 'gif' && (imagetypes() & IMG_GIF))
            imagegif ($this->im);

        return ob_get_clean();
    }

	private function load_resource($im)
	{	
		if (!is_resource($im) || !get_resource_type($im) == 'gd')
			return false;

		$this->im     = $im;
		$this->width  = imagesx($im);
		$this->height = imagesy($im);

		return true;
	}

	private function load_file($filename)
	{
		if (!file_exists($filename) || !is_readable($filename))
			return false;

		$info = @getimagesize($filename);
		if (!$info)
			return false;

		$this->width  = $info[0];
		$this->height = $info[1];
		$this->type   = image_type_to_extension($info[2], false);
	
		if ($this->type == 'jpeg' && (imagetypes() & IMG_JPG))
		{
			$im = imagecreatefromjpeg($filename);
		}
		elseif ($this->type == 'png' && (imagetypes() & IMG_PNG))
		{
			$im = imagecreatefrompng($filename);
		}
		elseif ($this->type == 'gif' && (imagetypes() & IMG_GIF))
		{
			$im = imagecreatefromgif ($filename);
		}
		else
		{
			return false;
		}

		return $this->load_resource($im);
	}

	public function save_as($filename, $type = null, $quality = 90)
	{
		// If type isn't set, lets try and find the proper image type by file extension.
		if (is_null($type))
		{
			$type = $this->find_image_type($filename);
		}

		if ($type == 'jpg' && (imagetypes() & IMG_JPG))
		{
			return imagejpeg($this->im, $filename, $quality);
		}
		elseif ($type == 'png' && (imagetypes() & IMG_PNG))
		{
			return imagepng($this->im, $filename);
		}
		elseif ($type == 'gif' && (imagetypes() & IMG_GIF))
		{
			return imagegif ($this->im, $filename);
		}
		else
		{
			return false;
		}
	}

	public function output($type = 'jpg', $quality = 90)
	{
		if ($type == 'jpg' && (imagetypes() & IMG_JPG))
		{
			header('Content-Type: image/jpeg');
			imagejpeg($this->im, null, $quality);
			return true;
		}
		elseif ($type == 'png' && (imagetypes() & IMG_PNG))
		{
			header('Content-Type: image/png');
			imagepng($this->im);
			return true;
		}
		elseif ($type == 'gif' && (imagetypes() & IMG_GIF))
		{
			header('Content-Type: image/gif');
			imagegif ($this->im);
			return true;
		}
		else
		{
			return false;
		}
	}

	public function scale($new_width = null, $new_height = null)
	{
		if ($new_width <= 0 || $new_height <= 0)
		{
			return false;				
		}

		$ratio = $this->width / $this->height;
		if (($new_width / $new_height) > $ratio)
		{
			$new_width = $new_height * $ratio;
		}
		else
		{
			$new_height = $new_width / $ratio;
		}

		return $this->resize($new_width, $new_height);
	}
	
	public function scale_safe($new_width = null, $new_height = null)
	{	
		if(!is_null($new_width) && $this->width > $new_width)
			$this->scale($new_width,$new_height);
		elseif(!is_null($new_height) && $this->height > $new_height)
			$this->scale($new_width,$new_height);

		return true;
	}

	public function scale_crop($new_width = null, $new_height = null)
	{
		if ($new_width <= 0 || $new_height <= 0)
			return false;

		$scale = min($this->width / $new_width, $this->height / $new_height);

		$dst_w = $this->width / $scale;
		$dst_h = $this->height / $scale;

		$dst_x = ($new_width - $dst_w) / 2;
		$dst_y = ($new_height - $dst_h) / 2;

		return $this->resize($new_width, $new_height, $dst_x, $dst_y, $dst_w, $dst_h);
	}

	private function resize($new_width, $new_height, $dst_x = 0, $dst_y = 0, $dst_w = 0, $dst_h = 0)
	{
		$dest = imagecreatetruecolor($new_width, $new_height);
	
		if ($dst_w <= 0 || $dst_h <= 0)
		{
			$dst_w = $new_width;
			$dst_y = $new_height;
		}

		// for transparancy
		imagealphablending($dest, false);  
		imagesavealpha($dest, true);

		if (imagecopyresampled($dest, $this->im, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $this->width, $this->height))
		{
			$this->im = $dest;
			$this->width = imagesx($this->im);
			$this->height = imagesy($this->im);
			return true;
		}

		return false;
	}
	
	private function find_image_type($filename)
	{
		foreach ($this->image_types as $image_type => $regex)
		{
			if (preg_match('#.+\.' . $regex . '$#i', $filename))
			{
				return $image_type;
			}
		}

		return false;
	}

}

?>