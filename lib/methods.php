<?php

function get_item_id($item){
	Global $DB;
	$item=trim($item);
	if(empty($item)) return false;
	$id=$DB->get_field('item','id',array('name'=>$item));
	if($id===false)
		$id=$DB->insert_record('item',array('name'=>$item));
	return $id;
}

function insert_transaction_multiple($date,$tcategory,$entries){
	Global $DB;
	$tcategory_parts=explode('_',$tcategory);
	if(count($tcategory_parts)!==3 or $tcategory_parts[2]!='multiple')
		throw new Exception("Категория tcategory не является multiple $tcategory\n $date");
	list($y,$m,$d)=explode('.',$date);
	$tcategory_id=$DB->get_field('transaction_category','id',array('name' => $tcategory,'deleted'=>0));
	if(!$tcategory_id or !checkdate($m,$d,$y))
		throw new Exception("Несуществующая категория или неверная дата $tcategory_id $date\n");

	$sign=$DB->get_field('transaction_category','sign',array('id'=>$tcategory_id));
	if ($sign=='+') $sign=1;
		elseif($sign=='-') $sign=-1;
	else
		throw new Exception("Не указан знак категории multiple $tcategory\n");

	$available_ids=$DB->get_fieldset_sql('select id from record where `date`="'.$date.'" and `tcategory`='.$tcategory_id);

	foreach($entries as $entry){
		$entry->sum=$sign * abs((int)$entry->sum);
		$entry->itemid=get_item_id($entry->item);
		if (!$entry->sum or !$entry->itemid)
			throw new Exception("Нет суммы или пусное описание $date : $entry->sum $entry->item");
		unset($entry->item);
		$entry->tcategory=$tcategory_id;
		$entry->date=$date;
		$entry->id=array_shift($available_ids);
		if(is_null($entry->id)){
			$DB->insert_record('record',$entry);
		}else{
			$DB->update_record('record',$entry);
		}
	}
	if (!empty($available_ids)){
		$DB->delete_record_sql('delete from record where id in ('.implode(',',$available_ids).')');
	}
}

function insert_transaction_single($date,$tcategory,$sum,$with_zero_sum=false){
	Global $DB;
	$item=$DB->get_field('transaction_category','value',array('name'=>$tcategory,'deleted'=>0));
	$tcategory_id=$DB->get_field('transaction_category','id',array('name' => $tcategory,'deleted'=>0));
	$sign=$DB->get_field('transaction_category','sign',array('id'=>$tcategory_id));
	$sum=(int) $sum;
	if ($sign=='+') $sum=abs($sum);
		elseif ($sign=='-') $sum=-abs($sum);
	$item_id=get_item_id($item);
	list($y,$m,$d)=explode('.',$date);
	if((!$with_zero_sum && $sum==0) or !$tcategory_id or !$item_id or !checkdate($m,$d,$y))
		throw new Exception("\n\nНеверная транзакция Дата:{$date} Сумма:{$sum} Имя:{$item} Категория:{$tcategory}");
	$params=array('date'=>$date,'tcategory'=>$tcategory_id,'itemid'=>$item_id);
	$recs=$DB->get_records('record',$params);
	$rec=array_shift($recs);
	if(!empty($recs)){
		throw new Exception("Найдено более одной записи в single категории ".$tcategory." дата: ".$date);
	}

	if ($rec){
		$rec->sum=$sum;
		$DB->update_record('record',$rec);
	}
	else{
		$params=array_merge($params,array('sum'=>$sum));
		$DB->insert_record('record',$params);
	}
}

function delete_transactions($date,$tcategory){
	Global $DB;
	$tcategory_id=$DB->get_field('transaction_category','id',array('name' => $tcategory,'deleted'=>0));
	$DB->delete_record_sql("delete from record where tcategory='$tcategory_id' and date='$date'");
}

function optional_param($name,$default,$type){
	if (isset($_POST[$name])) {       // POST has precedence
		$param = $_POST[$name];
	} else if (isset($_GET[$name])) {
		$param = $_GET[$name];
	} else {
		return $default;
	}

	if (is_array($param)) {
		throw new Exception('optional_param_array expected');
	}

	return clean_param($param, $type);
}

function optional_param_array($parname, $default, $type) {
	if (isset($_POST[$parname])) {       // POST has precedence
		$param = $_POST[$parname];
	} else if (isset($_GET[$parname])) {
		$param = $_GET[$parname];
	} else {
		return $default;
	}
	if (!is_array($param)) {
		throw new Exception('optional_param_array() expects array parameters only: '.$parname);
	}

	$result = array();
	foreach($param as $key=>$value) {
		if (!preg_match('/^[a-z0-9_-]+$/i', $key)) {
			throw new Exception('Invalid key name in optional_param_array() detected: '.$key.', parameter: '.$parname);
			continue;
		}
		$result[$key] = clean_param($value, $type);
	}

	return $result;
}


const PARAM_RAW='raw';
const PARAM_RAW_TRIMMED='trimmed';
const PARAM_INT='int';
const PARAM_FLOAT='float';
const PARAM_NUMBER='float';
const PARAM_ALPHA='alpha';
const PARAM_ALPHATEXT='alphatext';
const PARAM_ALPHANUM='alphanum';
const PARAM_ALPHANUMEXT='alphanumtext';
const PARAM_NUMSEQUENCE='numsequence';
const PARAM_BOOL='bool';
const PARAM_NOTAGS='notags';
const PARAM_PATH='path';
const PARAM_HOST='host';
const PARAM_BASE64='base64';

function clean_param($param, $type) {
	if (is_array($param)) {
		throw new Exception('clean_param() can not process arrays, please use clean_param_array() instead.');
	} else if (is_object($param)) {
		if (method_exists($param, '__toString')) {
			$param = $param->__toString();
		} else {
			throw new coding_exception('clean_param() can not process objects, please use clean_param_array() instead.');
		}
	}
	switch ($type) {
		case PARAM_RAW:          // no cleaning at all
			return $param;
		case PARAM_RAW_TRIMMED:         // no cleaning, but strip leading and trailing whitespace.
			return trim($param);
		case PARAM_INT:
			return (int)$param;  // Convert to integer
		case PARAM_FLOAT:
		case PARAM_NUMBER:
			return (float)$param;  // Convert to float

		case PARAM_ALPHA:        // Remove everything not a-z
			return preg_replace('/[^a-zA-Z]/i', '', $param);

		case PARAM_ALPHATEXT:     // Remove everything not a-zA-Z_- (originally allowed "/" too)
			return preg_replace('/[^a-zA-Z_-]/i', '', $param);

		case PARAM_ALPHANUM:     // Remove everything not a-zA-Z0-9
			return preg_replace('/[^A-Za-z0-9]/i', '', $param);

		case PARAM_ALPHANUMEXT:     // Remove everything not a-zA-Z0-9_-
			return preg_replace('/[^A-Za-z0-9_-]/i', '', $param);

		case PARAM_NUMSEQUENCE:     // Remove everything not 0-9,
			return preg_replace('/[^0-9,]/i', '', $param);

		case PARAM_BOOL:         // Convert to 1 or 0
			$tempstr = strtolower($param);
			if ($tempstr === 'on' or $tempstr === 'yes' or $tempstr === 'true') {
				$param = 1;
			} else if ($tempstr === 'off' or $tempstr === 'no'  or $tempstr === 'false') {
				$param = 0;
			} else {
				$param = empty($param) ? 0 : 1;
			}
			return $param;

		case PARAM_NOTAGS:       // Strip all tags
			return strip_tags($param);

		case PARAM_PATH:         // Strip all suspicious characters from file path
			$param = str_replace('\\', '/', $param);
			$param = preg_replace('~[[:cntrl:]]|[&<>"`\|\':]~u', '', $param);
			$param = preg_replace('~\.\.+~', '', $param);
			$param = preg_replace('~//+~', '/', $param);
			return preg_replace('~/(\./)+~', '/', $param);

		case PARAM_HOST:         // allow FQDN or IPv4 dotted quad
			$param = preg_replace('/[^\.\d\w-]/','', $param ); // only allowed chars
			// match ipv4 dotted quad
			if (preg_match('/(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})/',$param, $match)){
				// confirm values are ok
				if ( $match[0] > 255
					|| $match[1] > 255
					|| $match[3] > 255
					|| $match[4] > 255 ) {
					// hmmm, what kind of dotted quad is this?
					$param = '';
				}
			} elseif ( preg_match('/^[\w\d\.-]+$/', $param) // dots, hyphens, numbers
				&& !preg_match('/^[\.-]/',  $param) // no leading dots/hyphens
				&& !preg_match('/[\.-]$/',  $param) // no trailing dots/hyphens
			) {
				// all is ok - $param is respected
			} else {
				// all is not ok...
				$param='';
			}
			return $param;

		case PARAM_BASE64:
			if (!empty($param)) {
				// PEM formatted strings may contain letters/numbers and the symbols
				// forward slash: /
				// plus sign:     +
				// equal sign:    =
				if (0 >= preg_match('/^([\s\w\/\+=]+)$/', trim($param))) {
					return '';
				}
				$lines = preg_split('/[\s]+/', $param, -1, PREG_SPLIT_NO_EMPTY);
				// Each line of base64 encoded data must be 64 characters in
				// length, except for the last line which may be less than (or
				// equal to) 64 characters long.
				for ($i=0, $j=count($lines); $i < $j; $i++) {
					if ($i + 1 == $j) {
						if (64 < strlen($lines[$i])) {
							return '';
						}
						continue;
					}

					if (64 != strlen($lines[$i])) {
						return '';
					}
				}
				return implode("\n",$lines);
			} else {
				return '';
			}

		default:                 // throw error, switched parameters in optional_param or another serious problem
			throw new Exception("unknownparamtype ".$type);
	}
}