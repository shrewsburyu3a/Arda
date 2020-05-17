<?php

require_once('u3a_db_object.php');
require_once('project.php');

class U3A_File_Utilities
{

	public static $audio_extensions = ["wav", "flac", "mp3", "mpeg3", "ape", "ogg", "alc", ".aicc"];
	public static $video_extensions = [".mp4", ".mkv", ".avi"];
	public static $image_extensions = [".jpg", ".jpeg", ".png", ".gif", ".tiff", ".bmp"];

	public static function get_extension($path, $include_dot = false)
	{
		$ret = null;
		$bname = basename($path);
		$lastdot = strrpos($bname, '.');
		if ($lastdot !== FALSE)
		{
			$ret = substr($bname, $lastdot + ($include_dot ? 0 : 1));
		}
		return $ret;
	}

	public static function remove_extension($path)
	{
		$bname = basename($path);
		$lastslash = strrpos($bname, DIRECTORY_SEPARATOR);
		$ret = $lastslash === FALSE ? "" : substr($path, $lastslash + 1);
		$lastdot = strrpos($bname, '.');
		if ($lastdot !== FALSE)
		{
			$ret .= substr($bname, 0, $lastdot);
		}
		return $ret;
	}

	public static function has_extension($path, $ext, $case_independent = false)
	{
		$ret = false;
		if (is_string($ext))
		{
			$dotext = str_replace('..', '.', '.' . $ext);
			if ($case_independent)
			{
				$ret = OJ_Utilities::ends_with(strtolower($path), strtolower($dotext));
			}
			else
			{
				$ret = OJ_Utilities::ends_with($path, $dotext);
			}
		}
		else if (is_array($ext))
		{
			for ($n = 0; ($n < count($ext)) && !$ret; $n++)
			{
				$ret = self::has_extension($path, $ext[$n], $case_independent);
			}
		}
		return $ret;
	}

	private static function get_extensions($extensions)
	{
		$ret = null;
		if (is_array($extensions))
		{
			$ret = $extensions;
		}
		else if (is_string($extensions))
		{
			$ext = $extensions . "_extensions";
			$ret = self::$$ext;
		}
		return $ret;
	}

	public static function is_file_of_type($path, $extensions)
	{
		return self::has_extension($path, self::get_extensions($extensions), true);
	}

	public static function is_audio_file($path)
	{
		return self::is_file_of_type($path, self::$audio_extensions);
	}

	public static function is_video_file($path)
	{
		return self::is_file_of_type($path, self::$video_extensions);
	}

	public static function is_image_file($path)
	{
		return self::is_file_of_type($path, self::$image_extensions);
	}

	/**
	 * returns directory name ending in separator
	 * @param type $dirpath
	 */
	public static function check_dirname($dirpath)
	{
		$dpath = realpath($dirpath);
		return OJ_Utilities::ends_with($dpath, DIRECTORY_SEPARATOR) ? $dpath : ($dpath . DIRECTORY_SEPARATOR);
	}

	// $restricted by of form [label=>[extensions]...]
	// an empty extensions array means accept everything
	public static function dir_info($dirpath, $recursive = false, $restricted_by = null)
	{
		if ($restricted_by === null)
		{
			$restricted_by = ["files" => []];
		}
		$dpath = self::check_dirname($dirpath);
		$files = array_diff(scandir($dpath), array('.', '..'));
		$ret = ["dir" => $dpath, "subdirs" => []];
		foreach ($restricted_by as $label => $exts)
		{
			$ret[$label] = [];
		}
		foreach ($files as $f)
		{
			if (!OJ_Utilities::starts_with($f, "."))
			{
				$file = $dpath . $f;
				if (is_dir($file))
				{
					if ($recursive)
					{
						array_push($ret["subdirs"], self::dir_info($file, $recursive, $restricted_by));
					}
					else
					{
						array_push($ret["subdirs"], $file . DIRECTORY_SEPARATOR);
					}
				}
				else
				{
					foreach ($restricted_by as $label => $extensions)
					{
						if ((count($extensions) == 0) || self::is_file_of_type($path, $extensions))
						{
							array_push($ret[$label], $file);
						}
					}
				}
			}
		}
		return $ret;
	}

	public static function contains_file_of_type($dirpath, $extensions1, $recurse = true)
	{
		$extensions = self::get_extensions($extensions1);
		$dirinfo = self::dir_info($dirpath);
		$ret = false;
		for ($n = 0; ($n < count($dirinfo["files"])) && !$ret; $n++)
		{
			$path = $dirinfo["files"][$n];
			$ret = self::is_file_of_type($path, $extensions);
		}
		if (!$ret && $recurse)
		{
			for ($n = 0; ($n < count($dirinfo["subdirs"])) && !$ret; $n++)
			{
				$path = $dirinfo["subdirs"][$n];
				$ret = self::contains_file_of_type($path, $extensions, $recurse);
			}
		}
		return $ret;
	}

	public static function contains_audio_file($dirpath, $recurse = true)
	{
		return self::contains_file_of_type($dirpath, self::$audio_extensions, $recurse);
	}

	public static function contains_video_file($dirpath, $recurse = true)
	{
		return self::contains_file_of_type($dirpath, self::$video_extensions, $recurse);
	}

	public static function contains_image_file($dirpath, $recurse = true)
	{
		return self::contains_file_of_type($dirpath, self::$image_extensions, $recurse);
	}

	public static function contains_file($dir, $filename)
	{
		return file_exists(self::check_dirname($dir) . $filename);
	}

	public static function already_imported($dir, $extensions)
	{
		$ret = self::contains_file($dir, ".oj");
		if (!$ret)
		{
			$info = self::dir_info($dir);
			if (count($info["subdirs"]) > 0)
			{
				$ret1 = true;
				$cnt = 0;
				for ($n = 0; ($n < count($info["subdirs"])) && $ret1; $n++)
				{
					$dirpath = $info["subdirs"][$n];
					if (self::contains_file_of_type($dirpath, $extensions, true))
					{
						$cnt++;
						$ret1 = self::already_imported($dirpath, $extensions);
						print "check " . $dirpath . " " . $ret1 . "\n";
					}
					//				print "check ".$dirpath." ".$ret."\n";
				}
				$ret = ($cnt > 0) && $ret1;
			}
		}
		return $ret;
	}

	public static function to_logical_path($path, $logicals, $use_alternative = false)
	{
		$vora = $use_alternative ? "alternative" : "value";
		$ret = ["len" => 0, "lpath" => null];
		foreach ($logicals as $lname => $logical)
		{
			if (strpos($path, $logical->$vora) === 0)
			{
				$l = strlen($logical->$vora);
				if ($l > $ret["len"])
				{
					$ret["len"] = $l;
					$ret["lpath"] = '${' . $lname . '}' . substr($path, $l + 1);
				}
			}
		}
		return $ret["lpath"];
	}

	public static function find_in_file($filename, $needle, $case_sensitive = true)
	{
		return OJ_Utilities::find_in_array(file($filename, FILE_IGNORE_NEW_LINES), $needle, $case_sensitive);
	}

	/**
	 * Add files and sub-directories in a folder to zip file.
	 * @param string $folder
	 * @param ZipArchive $zipFile
	 * @param int $exclusiveLength Number of text to be exclusived from the file path.
	 */
	private static function folder_to_zip($folder, &$zipFile, $exclusiveLength)
	{
		$handle = opendir($folder);
		while (false !== $f = readdir($handle))
		{
			if ($f != '.' && $f != '..')
			{
				$filePath = "$folder/$f";
				// Remove prefix from file path before add to zip.
				$localPath = substr($filePath, $exclusiveLength);
				if (is_file($filePath))
				{
					$zipFile->addFile($filePath, $localPath);
				}
				elseif (is_dir($filePath))
				{
					// Add sub-directory.
					$zipFile->addEmptyDir($localPath);
					self::folder_to_zip($filePath, $zipFile, $exclusiveLength);
				}
			}
		}
		closedir($handle);
	}

	/**
	 * Zip a folder (include itself).
	 * Usage:
	 *   HZip::zipDir('/path/to/sourceDir', '/path/to/out.zip');
	 *
	 * @param string $sourcePath Path of directory to be zip.
	 * @param string $outZipPath Path of output zip file.
	 */
	public static function zip_dir($sourcePath, $outZipPath)
	{
		$pathInfo = pathInfo($sourcePath);
		$parentPath = $pathInfo['dirname'];
		$dirName = $pathInfo['basename'];

		$z = new ZipArchive();
		$z->open($outZipPath, ZIPARCHIVE::CREATE);
		$z->addEmptyDir($dirName);
		self::folder_to_zip($sourcePath, $z, strlen("$parentPath/"));
		$z->close();
	}

	/**
	 * Reads the requested portion of a file and sends its contents to the client with the appropriate headers.
	 *
	 * This HTTP_RANGE compatible read file function is necessary for allowing streaming media to be skipped around in.
	 *
	 * @param string $location
	 * @param string $filename
	 * @param string $mimeType
	 * @return void
	 *
	 * @link https://groups.google.com/d/msg/jplayer/nSM2UmnSKKA/Hu76jDZS4xcJ
	 * @link http://php.net/manual/en/function.readfile.php#86244
	 */
	public static function smartReadFile($location, $filename, $mimeType = 'application/octet-stream', $xheaders = [])
	{
		if (!file_exists($location))
		{
			header("HTTP/1.1 404 Not Found");
			return;
		}

		$size = filesize($location);
		$time = date('r', filemtime($location));

		$fm = @fopen($location, 'rb');
		if (!$fm)
		{
			header("HTTP/1.1 505 Internal server error");
			return;
		}

		$begin = 0;
		$end = $size - 1;

		if (isset($_SERVER['HTTP_RANGE']))
		{
			if (preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $_SERVER['HTTP_RANGE'], $matches))
			{
				$begin = intval($matches[1]);
				if (!empty($matches[2]))
				{
					$end = intval($matches[2]);
				}
			}
		}

		if (isset($_SERVER['HTTP_RANGE']))
		{
			header('HTTP/1.1 206 Partial Content');
		}
		else
		{
			header('HTTP/1.1 200 OK');
		}

		header("Content-Type: $mimeType");
		header('Cache-Control: public, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Accept-Ranges: bytes');
		header('Content-Length:' . (($end - $begin) + 1));
		if (isset($_SERVER['HTTP_RANGE']))
		{
			header("Content-Range: bytes $begin-$end/$size");
		}
		header("Content-Disposition: inline; filename=$filename");
		header("Content-Transfer-Encoding: binary");
		header("Last-Modified: $time");
		foreach ($xheaders as $xh)
		{
			header($xh);
		}

		$cur = $begin;
		fseek($fm, $begin, 0);

		while (!feof($fm) && $cur <= $end && (connection_status() == 0))
		{
			print fread($fm, min(1024 * 16, ($end - $cur) + 1));
			$cur += 1024 * 16;
		}
	}

}

class U3A_Utilities
{

	private static $_system_parameters = [];
	public static $first_few_ordinals = [
		"1st",
		"2nd",
		"3rd",
		"4th",
		"last"
	];
	public static $vowels = [
		"a",
		"A",
		"e",
		"E",
		"i",
		"I",
		"o",
		"O",
		"u",
		"U"
	];

	public static function set_system_parameter($name, $value = true)
	{
		self::$_system_parameters[$name] = $value;
	}

	public static function get_system_parameter($name, $default_value = null)
	{
		return array_key_exists($name, self::$_system_parameters) ? self::$_system_parameters[$name] : $default_value;
	}

	public static function unset_system_parameter($name)
	{
		if (array_key_exists($name, self::$_system_parameters))
		{
			unset($_system_parameters[$name]);
		}
	}

	public static function get_post($key, $default = null)
	{
		$ret = $default;
		if (isset($_POST[$key]))
		{
			$ret = $_POST[$key];
		}
		elseif (isset($_GET[$key]))
		{
			$ret = htmlspecialchars($_GET[$key]);
		}
		return $ret;
	}

	public static function load_json_file($file)
	{
		$json = file_get_contents($file);
		return json_decode($json);
	}

	public static function var_dump_pre($mixed = null)
	{
		echo '<pre>';
		var_dump($mixed);
		echo '</pre>';
		return null;
	}

	public static function number_to_string($ord)
	{
		if ($ord == 0)
		{
			$ret = "zero";
		}
		elseif ($ord == 1)
		{
			$ret = "one";
		}
		elseif ($ord == 2)
		{
			$ret = "two";
		}
		elseif ($ord == 3)
		{
			$ret = "three";
		}
		elseif ($ord == 4)
		{
			$ret = "four";
		}
		elseif ($ord == 5)
		{
			$ret = "five";
		}
		else
		{
			$ret = "many";
		}
		return $ret;
	}

	public static function ordinal_to_string($ord)
	{
		if ($ord == 1)
		{
			$ret = "1st";
		}
		elseif ($ord == 2)
		{
			$ret = "2nd";
		}
		elseif ($ord == 3)
		{
			$ret = "3rd";
		}
		elseif ($ord == 4)
		{
			$ret = "4th";
		}
		else
		{
			$ret = "last";
		}
		return $ret;
	}

	public static function number_to_adverb($ord)
	{
		if ($ord == 1)
		{
			$ret = "once";
		}
		elseif ($ord == 2)
		{
			$ret = "twice";
		}
		elseif ($ord == 3)
		{
			$ret = "three times";
		}
		elseif ($ord == 4)
		{
			$ret = "four times";
		}
		else
		{
			$ret = "many times";
		}
		return $ret;
	}

	private static function remove_seconds($tim)
	{
		$ret = $tim;
		$colon1 = strpos($tim, ':');
		if ($colon1 !== FALSE)
		{
			$colon2 = strpos($tim, ':', $colon1 + 1);
			if ($colon2 > $colon1)
			{
				$ret = substr($tim, 0, $colon2);
			}
		}
		return $ret;
	}

	public static function day_to_string($day1)
	{
		if (is_object($day1))
		{
			$day = get_object_vars($day1);
		}
		elseif (is_array($day1))
		{
			$day = $day1;
		}
		return ($day["ord"] >= 1 ? "alternate " : "") . $day["day"] . " from " . self::remove_seconds($day["from"]) . " to " . self::remove_seconds($day["to"]);
	}

	public static function ordinal_day_to_string($ordday1)
	{
		if (is_object($ordday1))
		{
			$ordday = get_object_vars($ordday1);
		}
		elseif (is_array($ordday1))
		{
			$ordday = $ordday1;
		}
		return self::ordinal_to_string($ordday["ord"]) . " " . $ordday["day"] . " from " . self::remove_seconds($ordday["from"]) . " to " . self::remove_seconds($ordday["to"]);
	}

	public static function days_to_string($days, $count = 0)
	{
		$day = [];
		$ntimes = $count ? $count : count($days);
		for ($n = 0; $n < $ntimes; $n++)
		{
			$day[] = self::day_to_string($days[$n]);
		}
		return implode(" and ", $day);
	}

	public static function ordinal_days_to_string($orddays, $count = 0)
	{
		$ordday = [];
		$ntimes = $count ? $count : count($orddays);
		for ($n = 0; $n < $ntimes; $n++)
		{
			$ordday[] = self::ordinal_day_to_string($orddays[$n]);
		}
		return implode(" and ", $ordday);
	}

	public static function starts_with($haystack, $needle)
	{
		// search backwards starting from haystack length characters from the end
		return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
	}

	public static function starts_with_vowel($str)
	{
		return $str && array_search($str[0], self::$vowels) !== FALSE;
	}

	public static function add_indefinite_article($str)
	{
		$indef = self::starts_with_vowel($str) ? "an " : "a ";
		return $indef . $str;
	}

	public static function add_definite_article($str)
	{
		return "the " . $str;
	}

	public static function add_indefinite_article_uc1($str)
	{
		$indef = self::starts_with_vowel($str) ? "An " : "A ";
		return $indef . $str;
	}

	public static function add_definite_article_uc1($str)
	{
		return "The " . $str;
	}

	public static function ends_with($haystack, $needle)
	{
		// search forward starting from end minus needle length characters
		return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
	}

	public static function has_string_keys($array)
	{
		return count(array_filter(array_keys($array), 'is_string')) > 0;
	}

	public static function multiexplode($delimiters, $string)
	{
		$ready = str_replace($delimiters, $delimiters[0], $string);
		$launch = explode($delimiters[0], $ready);
		return $launch;
	}

	public static function starts_with_number($str)
	{
		$length = strlen($str);
		$num = "";
		$gap = "";
		$base = "";
		$phase = 0;
		$numbase = 10;
		for ($i = 0, $int = ''; $i < $length; $i++)
		{
			if ($phase === 0)
			{
				if (ctype_xdigit($str[$i]))
				{
					if (!ctype_digit($str[$i]))
					{
						$numbase = 16;
					}
					$num .= $str[$i];
				}
				else
				{
					if (ctype_alnum($str[$i]))
					{
						$base = $num;
						$num = "";
						$phase = 2;
					}
					else
					{
						$phase++;
					}
				}
			}
			if ($phase === 1)
			{
				if (!ctype_alnum($str[$i]))
				{
					$gap .= $str[$i];
				}
				else
				{
					$phase++;
				}
			}
			if ($phase === 2)
			{
				$base .= $str[$i];
			}
//			print $phase." ".$str[$i]." ".$numbase."\n";
		}
		$nlen = strlen($num);
		$swn = $nlen !== 0;
		if ((($numbase == 16) && ($nlen > 2)) || (($numbase == 10) && ($nlen > 3)))
		{
			$base = $num . $gap . $base;
			$num = "";
			$gap = "";
			$swn = false;
		}
		$num = $swn ? intval($num, $numbase) : 0;
		$ret = ["starts_with_number" => $swn, "number" => $num, "gap" => $gap, "rest" => $base];
		return $ret;
	}

	public static function normalize($string)
	{
		$table = array(
			'Š' => 'S', 'š' => 's', 'Đ' => 'Dj', 'đ' => 'dj', 'Ž' => 'Z', 'ž' => 'z', 'Č' => 'C', 'č' => 'c', 'Ć' => 'C', 'ć' => 'c',
			'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
			'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O',
			'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss',
			'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e',
			'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o',
			'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'ý' => 'y', 'þ' => 'b',
			'ÿ' => 'y', 'Ŕ' => 'R', 'ŕ' => 'r'
		);

		return strtr($string, $table);
	}

	public static function current_time_millis()
	{
		return round(microtime(true) * 1000);
	}

	public static function compare_comparison_values($cval1, $cval2)
	{
		$ret = 0;
		$ct = $cval1["type"];
		if (($ct == $cval2["type"]) && ($ct < 2))
		{
			$cv1 = $cval1["value"];
			$cv2 = $cval2["value"];
			if ($ct == 0)
			{
				$ret = $cv1 < $cv2 ? -1 : ($cv1 > $cv2 ? 1 : 0);
			}
			else
			{
				$ret = strcasecmp($cv1, $cv2);
			}
		}
		return $ret;
	}

	public static function is_email($str)
	{
		return filter_var($str, FILTER_VALIDATE_EMAIL);
	}

	public static function reverse_name($nm)
	{
		$lastsp = strrpos($nm, ' ');
		if ($lastsp === FALSE)
		{
			$lastsp = strrpos($nm, '.');
			if ($lastsp === FALSE)
			{
				$ret = $nm;
			}
			else
			{
				$ret = substr($nm, $lastsp + 1) . ", " . trim(substr($nm, 0, $lastsp));
			}
		}
		else
		{
			$ret = substr($nm, $lastsp + 1) . ", " . trim(substr($nm, 0, $lastsp));
		}
		return $ret;
	}

	public static function as_string($item)
	{
		if (
		  (!is_array($item) ) &&
		  ( (!is_object($item) && settype($item, 'string') !== false ) ||
		  ( is_object($item) && method_exists($item, '__toString') ) )
		)
		{
			$ret = (string) $item;
		}
		else
		{
			$ret = json_encode($item);
		}
		return $ret;
	}

	public static function get_where_clause($hash, $connective = 'AND')
	{
		if (is_string($hash))
		{
			$ret = $hash;
		}
		else
		{
			$ret = " ";
			$first1 = true;
			$pc = "";
			foreach ($hash as $key => $val)
			{
				$eq = "=";
				$match = false;
				if (U3A_Utilities::ends_with($key, "<>"))
				{
					$key = substr($key, 0, -2);
					$eq = "<>";
				}
				elseif (U3A_Utilities::ends_with($key, ">"))
				{
					$key = substr($key, 0, -1);
					$eq = ">";
				}
				elseif (U3A_Utilities::ends_with($key, "<"))
				{
					$key = substr($key, 0, -1);
					$eq = "<";
				}
				elseif (U3A_Utilities::ends_with($key, ">="))
				{
					$key = substr($key, 0, -2);
					$eq = ">=";
				}
				elseif (U3A_Utilities::ends_with($key, "<="))
				{
					$key = substr($key, 0, -2);
					$eq = "<=";
				}
				elseif (U3A_Utilities::ends_with($key, "%~%"))
				{
					$key = substr($key, 0, -3);
					$eq = " LIKE ";
					$pc = "LR";
				}
				elseif (U3A_Utilities::ends_with($key, "%~"))
				{
					$key = substr($key, 0, -2);
					$eq = " LIKE ";
					$pc = "L";
				}
				elseif (U3A_Utilities::ends_with($key, "~%"))
				{
					$key = substr($key, 0, -2);
					$eq = " LIKE ";
					$pc = "R";
				}
				elseif (U3A_Utilities::ends_with($key, "~~"))
				{
					$key = substr($key, 0, -2);
					$match = true;
				}
				elseif (U3A_Utilities::ends_with($key, "~"))
				{
					$key = substr($key, 0, -1);
					$eq = " LIKE ";
					$pc = "";
				}
				if (!$first1)
				{
					$ret .= " " . $connective . " ";
				}
				else
				{
					$first1 = false;
				}
				if (is_array($val) && (count($val) == 1))
				{
					$val = $val[0];
				}
				if ($val === null)
				{
					if ($eq === "<>")
					{
						$ret .= " " . $key . " IS NOT NULL";
					}
					else
					{
						$ret .= " " . $key . " IS NULL";
					}
				}
				elseif (is_array($val) && (count($val) > 0))
				{
					$ret1 = "";
					$first2 = true;
					foreach ($val as $v)
					{
						if (!$first2)
						{
							$ret1 .= " OR ";
						}
						else
						{
							$first2 = false;
						}
						if ($v === null)
						{
							if ($eq === '<>')
							{
								$ret1 .= " " . $key . " IS NOT NULL";
							}
							else
							{
								$ret1 .= " " . $key . " IS NULL";
							}
						}
						elseif (($key == 'id') || self::ends_with($key, '_id') || is_numeric($v) || self::ends_with($v, "()"))
						{
							$ret1 .= " " . $key . $eq . $v;
						}
						elseif ($match)
						{
							$ret .= " match(" . $key . ") against(\"" . $val . "\")";
						}
						else
						{
							switch ($pc) {
								case "LR":
									$v = '%' . $v . '%';
									break;
								case "L":
									$v = '%' . $v;
									break;
								case "R":
									$v = $v . '%';
									break;
								default:
									break;
							}
							$ret1.= ' (UPPER(' . $key . ")" . $eq . 'UPPER("' . $v . '") OR UPPER(' . $key . ")" . $eq . 'UPPER("' . addslashes($v) . '") OR UPPER(' . $key . ")" . $eq . 'UPPER("' . addslashes(addslashes($v)) . '"))';
						}
					}
					$ret .= " (" . $ret1 . ") ";
				}
				else
				{
					if (($key == 'id') || self::ends_with($key, '_id') || is_numeric($val) || self::ends_with($val, "()"))
					{
						$ret .= " " . $key . $eq . $val;
					}
					elseif ($match)
					{
						$ret .= " match(" . $key . ") against(\"" . $val . "\")";
					}
					else
					{
						switch ($pc) {
							case "LR":
								$val = '%' . $val . '%';
								break;
							case "L":
								$val = '%' . $val;
								break;
							case "R":
								$val = $val . '%';
								break;
							default:
								break;
						}
						$ret.= ' (UPPER(' . $key . ")" . $eq . 'UPPER("' . $val . '") OR UPPER(' . $key . ")" . $eq . 'UPPER("' . addslashes($val) . '") OR UPPER(' . $key . ")" . $eq . 'UPPER("' . addslashes(addslashes($val)) . '"))';
					}
				}
			}
		}
		return $ret;
	}

	public static function matches_whole_word_in($needle, $haystack)
	{
		$ret = false;
		if (is_string($needle))
		{
			$ndl = [$needle];
		}
		elseif (is_array($needle))
		{
			$ndl = $needle;
		}
		else
		{
			$ndl = [];
		}
		$len = count($ndl);
		for ($n = 0; $n < $len && !$ret; $n++)
		{
			$ret = preg_match("/\b$ndl[$n]\b/i", $haystack);
		}
		return $ret;
	}

	public static function get_input_name_from_column_name($table_name, $column_name)
	{
		return "oj-" . str_replace('_', '-', $table_name . '-' . $column_name);
	}

	public static function get_hash_values_as_array($hash)
	{
		$ret = array();
		foreach ($hash as $k => $v)
		{
			$ret[] = $v;
		}
		return $ret;
	}

	public static function get_array_values_as_hash($array, $fname)
	{
		$ret = array();
		foreach ($array as $obj)
		{
			$key = $obj->$fname;
			$ret[$key] = $obj;
		}
		return $ret;
	}

	public static function hash_join($hash1, $hash2)
	{
		$ret = array();
		foreach ($hash1 as $k => $v1)
		{
			if (array_key_exists($k, $hash2))
			{
				$ret[$k] = array($v1, $hash2[$k]);
			}
		}
		return $ret;
	}

	public static function array_object_join($array1, $array2, $fname1 = 'id', $fname2 = null)
	{
		$ret = array();
		if ($fname2 == null)
		{
			$fname2 = $fname1;
		}
		$hash1 = self::get_array_values_as_hash($array1, $fname1);
		$hash2 = self::get_array_values_as_hash($array2, $fname2);
		return self::hash_join($hash1, $hash2);
	}

	/**
	 * hashes are of the same type using the same key as field
	 */
	public static function hash_intersection($hash1, $hash2)
	{
		$ret = array();
		foreach ($hash1 as $k => $v1)
		{
			if (array_key_exists($k, $hash2))
			{
				$ret[$k] = $v1;
			}
		}
		return $ret;
	}

	/**
	 * arrays are of the same type
	 */
	public static function array_object_intersection($array1, $array2, $fname1 = 'id', $fname2 = null)
	{
		$ret = array();
		if ($fname2 == null)
		{
			$fname2 = $fname1;
		}
		$hash1 = self::get_array_values_as_hash($array1, $fname1);
		$hash2 = self::get_array_values_as_hash($array2, $fname2);
		$hash = self::hash_intersection($hash1, $hash2);
		return self::get_hash_values_as_array($hash);
	}

	public static function mangle_name_for_css($name)
	{
		$ret1 = str_replace(' - ', '-', strtolower($name));
		$ret2 = str_replace(' ', '-', $ret1);
		$ret3 = str_replace('_', '-', $ret2);
		$ret4 = str_replace('--', '-', $ret3);
		return $ret4;
//		return preg_replace('/[^a-z0-9]+/i', '-', $ret3);
	}

	public static function decode_array(&$array)
	{
		if (isset($array['usebase64']))
		{
			$keys_to_decode = explode(',', $array['usebase64']);
			foreach ($keys_to_decode as $key)
			{
				$array[$key] = base64_decode($array[$key]);
			}
		}
	}

	public static function find_in_array($haystack, $needle, $case_sensitive = true)
	{
		$ret = -1;
		$ndl = $case_sensitive ? $needle : strtolower($needle);
		$ln = 0;
		foreach ($haystack as $line)
		{
			$l = $case_sensitive ? $line : strtolower($line);
			$n = strpos($l, $ndl);
			if ($n !== FALSE)
			{
				$ret = $ln;
				break;
			}
			$ln++;
		}
		return $ret;
	}

	public static function get_cmp_using_array($lines, $case_sensitive = true)
	{
		return function($cmpa, $cmpb) use ($lines, $case_sensitive)
		{
			$ca = U3A_Utilities::find_in_array($lines, $cmpa, $case_sensitive);
			$cb = U3A_Utilities::find_in_array($lines, $cmpb, $case_sensitive);
			return ($ca > $cb ? -1 : ($ca == $cb ? 0 : 1));
		};
	}

	/**
	 * Insert the method's description here. Creation date: (07/02/2000 12:46:41)
	 *
	 * @return java.lang.String
	 * @param st
	 *           java.lang.String
	 */
	public static function decrypt($st)
	{
		$ret = $st;
		if (($st != null) && (strlen($st) > 0))
		{
			try
			{
				if (substr($st, 0, 4) === "CHK1")
				{
					$lens = [];
					try
					{
						$start = 4;
						$end = 7;
						$len = substr($st, $start, $start - $end);
						while ($len != 999)
						{
							$lens[] = $len;
							$start += 3;
							$end += 3;
							$len = substr($st, $start, $start - $end);
						}
						$st = substr($st, end);
					}
					catch (Exception $ignored)
					{

					}
//               Integer[] lens = v.toArray(new Integer[v.size()]);
					$ret = "";
					$start = 0;
					for ($n = 0; $n < count($lens); $n++)
					{
						$ret .= decrypt($st, $start, $lens[$n]);
						$start += $lens[$n];
					}
//               ret = buff.toString();
				}
				else
				{
					$b = unpack('C*', $st);
//				var_dump($b);
					$al1 = unpack('C*', "a");
					$au1 = unpack('C*', "A");
					$al = $al1[1];
					$au = $au1[1];
					$len = count($b);
					$newLen = $len / 3;
					$out = [];
					$out1 = [];
					for ($n = 1; $n <= $len; $n += 3)
					{
						$out1[$b[$n] - $au + 1] = $b[$n + 2] + 2 - $b[$n + 1] + $al;
					}
					for ($n = 1; $n <= count($out1); $n++)
					{
						$out[] = $out1[$n];
					}
//				var_dump($out);
					$ret = call_user_func_array("pack", array_merge(array("C*"), $out));
				}
			}
			catch (Exception $e)
			{
				// err.println("decryption failure for " + st);
			}
		}
		return $ret;
	}

	/**
	 * Insert the method's description here. Creation date: (07/02/2000 12:46:41)
	 *
	 * @return java.lang.String
	 * @param st
	 *           java.lang.String
	 */
	public static function encrypt($st)
	{
		$ret = $st;
		if (($st != null) && (strlen($st) > 0))
		{
			if (strlen($st) > 32)
			{
//            StringBuilder buff = new StringBuilder("CHK1");
				$ret = "";
				$lens = [];
				while (strlen($st) > 32)
				{
					$est = encrypt(substr($st, 0, 32));
					$elen = strlen($est);
					if ($elen < 10)
					{
						$ret .= "00";
					}
					else if (elen < 100)
					{
						$ret .= '0';
					}
					$ret .= $elen;
					$lens[] = $est;
					$st = substr($st, 32);
				}
				if (strlen($st) > 0)
				{
					$est = encrypt($st);
					$elen = strlen($est);
					if ($elen < 10)
					{
						$ret .= "00";
					}
					else if (elen < 100)
					{
						$ret .= '0';
					}
					$ret .= $elen;
					$lens[] = $est;
					;
				}
				$ret .= "999";
				for ($n = 0; $n < count($lens); $n++)
				{
					$ret .= $lens[$n];
				}
//            ret = buff.toString();
			}
			else
			{
				$b = unpack('C*', $st);
//			var_dump($b);
//            long seed = (new java.util.Date()).getTime();
//            $gen = new java.util.Random(seed);
				$len = count($b);
				$addons = [];
				$positions = [];
				$al1 = unpack('C*', "a");
				$au1 = unpack('C*', "A");
				$al = $al1[1];
				$au = $au1[1];
				for ($n = 0; $n < $len; $n++)
				{
					$addons[$n] = rand(0, 3);
					$positions[$n] = $n;
				}
				$ntimes = rand(0, 99);
				for ($n = 0; $n < $ntimes; $n++)
				{
					$n1 = rand(0, $len - 1);
					$n2 = rand(0, $len - 1);
					$tmp = $positions[$n1];
					$positions[$n1] = $positions[$n2];
					$positions[$n2] = $tmp;
				}
				$out = [];
				for ($n = 0; $n < $len; $n++)
				{
					$out[$n * 3 + 1] = $au + $positions[$n];
					$out[$n * 3 + 2] = $al + $addons[$n];
					$out[$n * 3 + 3] = $b[$positions[$n] + 1] + $addons[$n] - 2;
				}
//			var_dump($out);
//            $ret = pack('C*', $out);
				$ret = call_user_func_array("pack", array_merge(array("C*"), $out));
			}
		}
		return $ret;
	}

	public static function get_current_url()
	{
//		var_dump($_SERVER);exit;
		$pageURL = 'http';
		if (isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] == "on"))
		{
			$pageURL .= "s";
		}
		$pageURL .= "://";
		if (isset($_SERVER["SERVER_PORT"]) && ($_SERVER["SERVER_PORT"] != "80"))
		{
			$pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["SCRIPT_NAME"];
		}
		else
		{
			$pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["SCRIPT_NAME"];
		}
		return $pageURL;
	}

	public static function usage($app, $req, $optn, $nvalues = 0)
	{
		$usg = "usage: $app ";
		foreach ($req as $rq)
		{
			if (U3A_Utilities::ends_with($rq, ":"))
			{
				$r = substr($rq, 0, strlen($rq) - 1);
				$usg .= "--$r $r" . "_value ";
			}
			else
			{
				$usg .= "--$rq ";
			}
		}
		foreach ($optn as $op)
		{
			if (U3A_Utilities::ends_with($op, ":"))
			{
				$o = substr($op, 0, strlen($op) - 1);
				$usg .= "[--$o $o" . "_value] ";
			}
			else
			{
				$usg .= "[--$op] ";
			}
		}
		if ($nvalues > 0)
		{
			for ($n = 1; $n <= $nvalues; $n++)
			{
				$usg .= "value$n ";
			}
		}
		elseif ($nvalues < 0)
		{
			$usg .= "values...";
		}
		return $usg;
	}

	public static function u3a_check_menu_item($item)
	{
		$ret = false;
		$parent_id = $item->menu_item_parent;
		if ($parent_id)
		{
//			write_log("parent id $parent_id");
			$parent_post = get_post($parent_id);
			if ($parent_post)
			{
//				write_log("parent post");
//				write_log($parent_post);
				$ptitle = $parent_post->post_title;
//				write_log("parent $ptitle");
				if ($ptitle == "{first_name} {last_name}")
				{
					$ret = true;
					$current_wp_user = wp_get_current_user();
					if (get_user_meta($current_wp_user->ID, "profile_photo", true))
					{
						$ret = false;
					}
				}
				elseif ($ptitle == "{user_avatar_small}")
				{
					$ret = false;
					$current_wp_user = wp_get_current_user();
					if (get_user_meta($current_wp_user->ID, "profile_photo", true))
					{
						$ret = true;
					}
				}
			}
		}
		return $ret;
	}

}

class U3A_Timestamp_Utilities
{

	const MINUTE1 = 60;
	const HOUR1 = 3600;
	const DAY1 = 86400;
	const WEEK1 = 604800;

	public static $days_of_week = [
		"monday",
		"tuesday",
		"wednesday",
		"thursday",
		"friday",
		"saturday",
		"sunday"
	];
	public static $ordinal_endings = [
		"st",
		"nd",
		"rd",
		"th"
	];

	public static function get_day_of_week($day)
	{
		$ret = null;
		$daylc = strtolower($day);
		foreach (self::$days_of_week as $dow)
		{
			if (U3A_Utilities::starts_with($dow, $daylc))
			{
				$ret = $dow;
				break;
			}
		}
		return $ret;
	}

	public static function ordinal_to_number($ord)
	{
		$ret = 0;
		if (is_numeric($ord))
		{
			$ret = intval($ord);
		}
		else
		{
			foreach (self::$ordinal_endings as $oe)
			{
				if (U3A_Utilities::ends_with($ord, $oe))
				{
					$ret = intval($ord . substr($ord, 0, 2));
					break;
				}
			}
		}
		return $ret;
	}

	public static function seconds($tm)
	{
		return $tm % self::MINUTE1;
	}

	public static function minutes($tm)
	{
		$min = (int) ($tm / self::MINUTE1);
		return $min % 60;
	}

	public static function hours($tm)
	{
		return date("H", $tm);
	}

	public static function day_of_week($tm)
	{
		return date('w', $tm);
	}

	public static function day_of_month($tm)
	{
		return date('j', $tm);
	}

	public static function day_of_month_from_0($tm)
	{
		return self::day_of_month($tm) - 1;
	}

	public static function day_of_year($tm)
	{
		return date('z', $tm);
	}

	public static function week_of_month($tm)
	{
		return (int) (self::day_of_month_from_0($tm) / 7);
	}

	public static function week_of_year($tm)
	{
		return (int) (self::day_of_year($tm) / 7);
	}

	public static function month($tm)
	{
		return date('z', $tm);
	}

	public static function month_from_0($tm)
	{
		return self::month($tm) - 1;
	}

	public static function year($tm = null)
	{
		return $tm ? date('Y', $tm) : date('Y');
	}

	public static function start_of_day($tm)
	{
//        $str = date("d/m/Y", $tm)
//        echo "hours ".self::hours($tm)," mins ".self::minutes($tm)." secs ".self::seconds($tm)."<br/>";
		return $tm - self::seconds($tm) - (self::minutes($tm) * self::MINUTE1) - (self::hours($tm) * self::HOUR1);
	}

	public static function end_of_day($tm)
	{
		return self::start_of_day($tm + self::DAY1) - 1;
	}

	public static function start_of_week($tm)
	{
		return self::start_of_day($tm) - ((self::day_of_week($tm) - 1) * self::DAY1);
	}

	public static function end_of_week($tm)
	{
		return self::start_of_week($tm) + (7 * self::DAY1) - 1;
	}

	public static function start_of_month($tm)
	{
		return self::start_of_day($tm) - (self::day_of_month_from_0($tm) * self::DAY1);
	}

	public static function number_of_days_in_month($tm)
	{
		return date('t', $tm);
	}

	public static function number_of_seconds_in_months($start_tm, $nmonths = 1)
	{
		$tm = $start_tm;
		$ret = 0;
		if ($nmonths)
		{
			if ($nmonths > 0)
			{
				for ($n = 0; $n < $nmonths; $n++)
				{
					$tm += self::number_of_days_in_month($tm) * self::DAY1;
				}
				$ret = $tm - $start_tm;
			}
			else
			{
				// firmly in last month
				$tm = self::start_of_month($tm) - self::WEEK1;
				for ($n = 0; $n > $nmonths; $n--)
				{
					$ret += self::number_of_days_in_month($tm) * self::DAY1;
					$tm = self::start_of_month($tm) - self::WEEK1;
				}
			}
		}
		return $ret;
	}

	public static function end_of_month($tm)
	{
		return self::start_of_month($tm) + (self::number_of_days_in_month($tm) * self::DAY1) - 1;
	}

	public static function start_of_year($tm)
	{
		$yr = self::year($tm);
		return strtotime("$yr-01-01 00:00:01");
	}

	public static function end_of_year($tm)
	{
		$yr = self::year($tm);
		return strtotime("$yr-12-31 11:59:59");
	}

	public static function same_day($tm1, $tm2)
	{
		return self::year($tm1) == self::year($tm2) && self::day_of_year($tm1) == self::day_of_year($tm2);
	}

	public static function same_time($tm1, $tm2, $include_seconds = false)
	{
		return self::hours($tm1) == self::hours($tm2) && self::minutes($tm1) == self::minutes($tm2) &&
		  (!include_seconds || (self::seconds($tm1) == self::seconds($tm2)));
	}

	public static function before_time($tm1, $tm2, $include_seconds = false)
	{
		return self::hours($tm1) < self::hours($tm2) ||
		  (self::hours($tm1) == self::hours($tm2) && (self::minutes($tm1) < self::minutes($tm2)) ||
		  (include_seconds && self::minutes($tm1) == self::minutes($tm2) && self::seconds($tm1) < self::seconds($tm2)));
	}

	public static function after_time($tm1, $tm2, $include_seconds = false)
	{
		return self::before_time($tm2, $tm1, $include_seconds);
	}

	public static function sql_datetime($tm)
	{
		$ts = is_numeric($tm) ? $tm : strtotime($tm);
		return date('Y-m-d H:i:s', $ts);
	}

	public static function sql_date($tm)
	{
		return date('Y-m-d', $tm);
	}

	public static function sql_time($tm)
	{
		return date('H:i:s', $tm);
	}

}

class U3AProcess
{

	private $pid;
	private $command;

	public function __construct($cl = false)
	{
		if ($cl != false)
		{
			$this->command = $cl;
			$this->runCom();
		}
	}

	private function runCom()
	{
		$command = 'nohup ' . $this->command . ' > /dev/null 2>&1 & echo $!';
//		print $command."\n";
		exec($command, $op);
		$this->pid = (int) $op[0];
	}

	public function setPid($pid)
	{
		$this->pid = $pid;
	}

	public function getPid()
	{
		return $this->pid;
	}

	public function status()
	{
		$command = 'ps -p ' . $this->pid;
		exec($command, $op);
		return isset($op[1]);
	}

	public function start()
	{
//		print "start ".$this->command."\n";
		if ($this->command != '')
		{
			$this->runCom();
		}
		else
		{
			return true;
		}
	}

	public function stop()
	{
		$command = 'kill -9 ' . $this->pid;
//		print $command."\n";
		exec($command);
		return $this->status() == false;
	}

}

?>