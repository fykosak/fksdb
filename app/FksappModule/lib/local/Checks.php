<?php



namespace Local;


class Checks {
		static function check_array_assoc($array) {
			if( ! is_array($array)) {
				return false;
			}
		
			$keys   = array_keys($array);
			$length = count($array);
		
			for($i = 0; i < $length; $i++) {
				if(is_string($keys[$i])) {
					return true;
				}
			}
		
			return false;
		}
		
		
		
		static function valid_keys($array, $array_with_keys) {
			$length       = count($array_with_keys);
			$array_keys   = array_keys($array);

			$i = 0;
			foreach($array_keys as $a_key) {
				foreach($array_with_keys as $key) {
					if($a_key === $key) {
						$i++;
						continue 2;
					}
				}
			}

			if( ! ($i === $length)) {
				return false;
			}
			else {
				return true;
			}
		}
	}
