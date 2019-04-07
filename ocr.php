<?php
$action 	= isset($_GET['action'])?$_GET['action']:'';
$base_url	= 'http://localhost/ocr_test/';

switch($action)
{
	case 'checkfile':
		$filename = isset($_GET['filename'])?$_GET['filename']:'';
		check_file($filename);
		break;
	case 'search':
		$filename = isset($_GET['filename'])?$_GET['filename']:'';
		search_keywords($filename);	
		break;
	case 'ocr':
		$filename = isset($_GET['filename'])?$_GET['filename']:'';
		do_ocr($filename);	
		break;
	default:
		load_images();
		break;
}

function check_file($filename='')
{
	$output_dir	= '/xampp/htdocs/ocr_test/output/';
	$return_txt = 'no';
	if(file_exists($output_dir.$filename.'_psm6_jpg.txt'))
	{
		$return_txt = 'yes';
	}
	
	echo $return_txt;
}

function do_ocr($filename='')
{
	$output_dir	= '/xampp/htdocs/ocr_test/output/';
	$input_dir	= '/xampp/htdocs/ocr_test/input/';
	$return_txt = 'no';
	$cmd = "tesseract ".$input_dir . $filename.".jpg ".$output_dir.$filename."_psm6_jpg -l eng -psm 6";
	//tesseract /xampp/htdocs/ocr_test/input/zen_engineers.jpg /xampp/htdocs/ocr_test/output/zen_engineers_psm6_jpg -l eng -psm 6
	exec($cmd);   
	
	$return_txt = check_file($filename);
	echo $return_txt;
}

function load_images()
{
	$arr_images =  array();
	$imagesDir 	= '/xampp/htdocs/ocr_test/input/';
	$images 	= glob($imagesDir . '*.{jpg,jpeg,png}', GLOB_BRACE);
	foreach($images as $image)
	{
		$arr_images[] = str_replace($imagesDir,'', $image);
	}
	echo json_encode($arr_images);
}

function search_keywords($filename='')
{
	$arr_result			= array();
	$base_dir			= '/xampp/htdocs/ocr_test/';
	$arr_number_keywords= array('tax_rate','total_amount');
	$url_file 			= $filename.'_psm6_jpg.txt';
	//$arr_keywords_files	= array('invoice_no' => 'invoice_no_keywords.txt');
	$arr_keywords_files	= array(
							'invoice_no' 	=> 'invoice_no_keywords.txt',
							'invoice_date' 	=> 'invoice_date_keywords.txt',
							'total_amount'  => 'total_amount_keywords.txt',
							'tax_rate' 		=> 'tax_rate_keywords.txt'
							);
	
	$get_contents = @file_get_contents('http://localhost/ocr_test/output/'.$url_file);
	
	if($get_contents)
	{
		$contents 		= strtolower($get_contents);
		$arr_contents 	= explode("\n",$contents);
		//echo"<pre>";print_r($arr_contents);
		/*echo '<pre>';
		echo trim($contents);
		echo '</pre>';
		
		echo '<h/r>';*/
		
		foreach($arr_keywords_files as $keyword=>$keyword_file)
		{
			$arr_out_string 	= array();
			$arr_keywords 		= file($base_dir . $keyword_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			
			if(is_array($arr_keywords) && count($arr_keywords))
			{
				foreach($arr_keywords as $searchfor_key=>$searchfor)
				{
					// escape special characters in the query
					$pattern = preg_quote($searchfor, '/');
					//echo 'pattern '.$pattern;echo"</br>";
					
					// finalise the regular expression, matching the whole line
					$pattern = "/^.*$searchfor.*\$/m";
					//echo 'pattern '.$pattern;echo"</br>";die;
					// search, and store all matching occurences in $matches
					
					if(preg_match_all($pattern, $contents, $matches))
					{
					   	//echo "<br/>Found matches for keyword(<font color='green'><strong>".$searchfor."</strong></font>) :\n";
					   	//echo"<pre>matches : ";print_r($matches);
					   	
					   	//Replace the search keyword found with blank
					   	$required_string 	= str_replace($searchfor,"", $matches[0]);
					   	$out_string 		= implode(" ", $required_string);   	
					   	$arr_out_string 	= explode(' ',$out_string);

					   	/* Get next line content to handle vertical pattern */
					   	foreach ($matches[0] as $match_key => $match_value) 
					   	{
					   		$content_key 				= array_search($match_value, $arr_contents)+1;						   		
						   	$arr_next_line_out_string 	= explode(' ',$arr_contents[$content_key]);
						   	//echo"<pre>Next Line : ".$arr_contents[$content_key];
						   	foreach ($arr_next_line_out_string as $next_line_key => $next_line_value) 
						   	{
						   		//echo'Next line value : '. $next_line_value;echo"<br>";
						   		array_push($arr_out_string, $next_line_value);
						   	}
					   	}
					   	if(is_array($arr_out_string) && count($arr_out_string)>0)
						{
							//echo '<pre>arr_out_string : '.$searchfor.'  ';print_r($arr_out_string);echo '</pre>';
							foreach($arr_out_string as $out_string)
						   	{
						   		if(in_array($keyword, $arr_number_keywords))
						   		{
						   			$out_string = rtrim($out_string,"%");
									$out_string = ltrim($out_string,"@");
						   		}
						   		
						   		if(!is_word($out_string)) 
							   	{
									//echo '<br/>Output: '.$out_string;
									$function_name 	= 'find_'.$keyword; 
									$out_string 	= call_user_func($function_name, $out_string);
									
									if($out_string!="")
									{
										//echo '<br/>Valid Output: '.$out_string;
										$arr_result[$keyword][] = $out_string; 
									}								
							   	}						   
						  	}
						}
					}
					else
					{
						//echo "<br/>No matches found for keyword(<font color='red'><strong>".$searchfor."</strong></font>)";
					}
					
					//echo '<hr/>';
				}
			}
		}
	}
	else
	{
		echo "<br/>Invalid File";
	}
	
	foreach ($arr_result as $key => $value) 
	{
		$arr_final_result[$key] = array_unique($arr_result[$key]);
	}
	// Pick the max value from the array of values
	if (array_key_exists("total_amount",$arr_final_result))
	{
		if(count($arr_final_result['total_amount'])>1)
		{
			$arr_final_result['total_amount'] = max($arr_final_result['total_amount']);
		}
	}
	
	//echo "<pre>RESULT : ";print_r($arr_result);echo"<br>";
	//echo "<pre>FINAL RESULT : ";print_r($arr_final_result);

	echo 'Invoice Number: ';
	echo"<br>";
	echo "<ul>";
	foreach ($arr_final_result['invoice_no'] as $key => $value) 
	{
		echo"<li>".$value."</li>";
	}
	echo "</ul>";
	echo"<br>";
	

	echo 'Invoice Date: ';
	echo "<ul>";
	foreach ($arr_final_result['invoice_date'] as $key => $value) 
	{
		echo"<li>".$value."</li>";
	}
	echo "</ul>";
	echo"<br>";


	echo 'Total Amount: ';
	echo "<ul>";
	if(is_array($arr_final_result['total_amount']))
	{
		foreach ($arr_final_result['total_amount'] as $key => $value) 
		{
			echo"<li>".$value."</li>";
		}
	}
	else
	{
		echo"<li>".$arr_final_result['total_amount']."</li>";
	}
	echo"</ul>";
	echo"<br>";



	echo 'Tax Rate: ';
	echo "<ul>";
	foreach ($arr_final_result['tax_rate'] as $key => $value) 
	{
		echo"<li>".$value."</li>";
	}
	echo"</ul>";
	die;
}

function find_invoice_no($raw_string="")
{
	//echo '<br/><font color="orange">Calling... find_invoice_no function '. $raw_string .'</font>';
	// Custom check for special characters
	$raw_string = check_for_special_characters($raw_string);

	if(strlen($raw_string) > 16)
	{
		$raw_string = '';
	}
	
	return $raw_string;	
}
function check_for_special_characters($raw_string='')
{
	if(preg_match('/[\'^£$%&*()°}{@#~?><>,|= _+¬]/', $raw_string))
	{
	    $raw_string = '';
	}
	return $raw_string;
}
function find_total_amount($raw_string="")
{
	//echo '<br/><font color="orange">Calling... find_total_amount function '. $raw_string .'</font>';
	$valid_amount = str_replace(",","", $raw_string);
	if(!is_numeric($valid_amount))
	{
		$valid_amount = '';
	}
	return $valid_amount;
}
function find_invoice_date($raw_string="")
{
	//echo '<br/><font color="orange">Calling... find_invoice_date function for '.$raw_string.'</font>';
	
	$raw_string = preg_replace("/[\'^£$%&*()°:}{@#~?><>,|= _+¬]/", "", $raw_string);
	$valid_string = str_to_date($raw_string);
	//var_dump($valid_string);
	return $valid_string;
}

function find_tax_rate($raw_string="")
{
	//echo '<br/><font color="orange">Calling... find_tax_rate function '. $raw_string .'</font>';
	$valid_tax_value 	= '';

	//echo 'raw string : '.$raw_string;echo"<br>";
	if(is_numeric($raw_string))
	{
		$arr_tax_master 	= array('5','6','9','12','18','28');
		
		if(in_array($raw_string, $arr_tax_master))
		{
			$valid_tax_value = $raw_string;
		}
	}

	return $valid_tax_value;	
}
function is_word($word='')
{
	//echo '<br/><font color="orange">Calling... is_word function '. $word .'</font>';

	$ret_falg 				= FALSE;

	if(!is_numeric($word))
	{
		$myFile 	= "/xampp/htdocs/ocr_test/words.txt";
		//$cmd 		= 'grep '.escapeshellarg($word).' '.$myFile;
		$cmd 		= 'findstr '.escapeshellarg($word).' '.$myFile;
		if(exec($cmd)) 
		{
			$ret_falg = TRUE;
		}
	}
	
	return $ret_falg;
}
function is_word_v2($word='')
{
	$ret_falg 	= FALSE;
	
	if(@file_get_contents('https://googledictionaryapi.eu-gb.mybluemix.net/?define='.$word.'&lang=en')) 
	{
		$ret_falg = TRUE;
	}
	return $ret_falg;
}

function str_to_date($str_date='', $time = false)
{
	$return_date 	= "";
	$str_date		= str_replace(' ','',trim($str_date));
	$str_date		= str_replace('/','-',trim($str_date));
	
	if($str_date!='' && strlen($str_date)>=8 ) 
	{
		$format = 'd-m-Y';
		if($time !== false) 
		{
			$format = 'd-m-Y '. $time;
		}
		
		$return_date = date($format,strtotime($str_date));
		if($return_date=="01-01-1970")
		{
			$return_date = "";
		}
	}
	
	return $return_date;
}