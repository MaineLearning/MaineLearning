<?php
//*********** Example of Use *********//
// http://ezinedesigner.com/embed-images-php.html
// echo ezimg::genImageTag('bullet_go.png');
// generates:  <img src='?ezimg=bullet_go.png' alt='' width='16' height='16' />  
//*********** End of Example *********//

class ezimg {
	
	
	function genImageTag($name){
		$image = ezimg::getImgData($name);     
		$result = "<img src='?ezimg={$name}' alt='' width='{$image['width']}' height='{$image['height']}' border='0' />";
		return $result;
	}
	
	
	function showImg($name){ 
		$image = ezimg::getImgData($name); 
		header("Content-type: image/{$image['type']}");
		echo gzuncompress(base64_decode(str_replace(' ', '', $image['code'])));
		exit;	  
	}
	
	
	function getImgData($name){
		$images = array(
					'blank.gif' => array( 
						'type'=>'gif', 'width'=>'1', 'height'=>'1',
						'code'=>"eJxz93SzsExkZGBkaGCAAsWfLIwgWgdEgGQYmJhcGBmsAXIBA/w="
					),
				   'working.gif' => array( 
						'type'=>'gif', 'width'=>'43', 'height'=>'11',
						'code'=>"eJxz93SzsEzUZuBm+MjA8PPnT42u7ZMO3QCSiv+5/VxDgp0dA1yN9AyYGRkYGBT/STkXpSaWpKYo
						lGeWZCgkZiVW5OQnpuhl5qXlMyj+ZOHkAqrSAWIGkIEMTEYtfRynp9280fz5KJf0vbDPp1sfmLVF
						RC5MU6+9N2vfcp50Xc/r7af0fh3Z/NNuxwIFIx9RBmzG2B4BGqPAn5i8cVbL8TU1n2ck3vvekzJj
						xvxTC72cvQp0drBnn9249qUK3ybGVBGp/IhPtX2n/C4/mqWTFHR8PXYTQQ4zAJq4clZW9J38R/xc
						wtsrH1b2LLmyYMUuvmXch177JJt1Pj957uXPoFkz1pieMehxP/Xy00ygiSvWYTXRAerVGVN2sUvX
						bfy1M9Vnhcl8d7lArRL1o+tW/ubbaDZ769z+v5seT0xxjn37TNur7ISrsMCeBKVzAqK/mEQYGKwZ
						YAAACIaX3A=="
					), 
					'alert.png' => array( 
						'type'=>'png', 'width'=>'128', 'height'=>'128',
						'code'=>"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
						H8xjKY4/AiCHkpyabIU0JPB/5isXZQ=="
					),
					'pluginbuddy_tip.png' => array( 
						'type'=>'png', 'width'=>'15', 'height'=>'15',
						'code'=>"eJwBNQLK/YlQTkcNChoKAAAADUlIRFIAAAAPAAAADwgGAAAAO9aVSgAAAfxJREFUeNp9kT2IGkEY
						hufOa3JRi4SkNoGDlIqY4sgRgk0IaTRFbK4QIoLFoZBCsEhEEIwga6EgYmEKRRAb/wp/lwi7BJTj
						Agb1GhWFgEaF4CknN3lZUPY2Py88MMx8z8w3MySTyQjk83lSKpUEarXaY/ASvAZPwH6xWCTpdJqI
						I5VfAOYr0ul0vvd6vU4TKZfLdchv/idbWJbl5vP5jEqyXC5/8TzfhPweyr5Ufl6pVPjNZnNN/5Eb
						BBucRyKRd9BkghyLxUgikQhMp9Of28LBYECz2SyNx+MUNxB3cJVKpViVSvVgKz9EOxwVJZfLCUwm
						ExqNRul4PN6t1ev1C4vF8hbqAQmFQifVavWbWF6tVnQ4HAobBINBulgsdmutVuvSarV+gKwgfr//
						uFAoQL4VoV1sTPv9PhWH47hLk8nkhXyfeDweRSAQaNwgohqhVXREpUkmky2dTne2lYnT6fzY7XZ/
						iIvwbdTlct0SR6PRzGw25yHqwV1it9sJULvd7gbudiUuxg/sxuv1+trn83E41Q/xCMi2MsELnuKk
						Rrvd3nUg+rqZ1+vl9Xr9Z0jPgJxIsmc0Go/xGIzNZmMZhmmGw+Fzh8PxxWAwZNVq9SfUnIB7YA9I
						I0weyuVylVarNWo0GrNSqXyFuafgEVBIxD8DmSAycAccgoO/Sb8BZ/P7CEyp2F0AAAAASUVORK5C
						YII2SgQp"
					),
					'gray-grad.png' => array( 
						'type'=>'png', 'width'=>'5', 'height'=>'31',
						'code'=>"eJzrDPBz5+WS4mJgYOD19HAJAtKsQCzPwQQkFzhM8AdSzMVOniEcHBy3H/o/AHI5CzwiixkYuIVA
						mPHS3bo/QEHFEteIkuD8tJLyxKJUBt/E5KL83NSUzEQFt8yi1PL8ouxiBRM9g1dq6qVAxWIgxc5F
						qYklmfl5CiGZuakMhgb6Rib6BhaafKt/A1Voero4hlTMSa75/U2XWSjh5///9kbzOxlFF3xeuVYu
						dl7IV2Zd/5WVW8L4GazFdb5zHtrDDdTE4Onq57LOKaEJAAQFP48="
					),
				   'bullet_go.png' => array( 
					 'type'=>'png', 'width'=>'16', 'height'=>'16',
					 'code'=>"eJwBmgFl/olQTkcNChoKAAAADUlIRFIAAAAQAAAAEAgGAAAAH/P/YQAAAARnQU1BAACvyDcFiukA
							  AAAZdEVYdFNvZnR3YXJlAEFkb2JlIEltYWdlUmVhZHlxyWU8AAABLElEQVQ4y2P4//8/AyWYYZgb
							  kL3Y/GvqfMP/8XN1OckyIHm+4dfGzVH/w2do/PefKs9J0ID8pbb/cxeb/01faPw3ca7+35r1of9X
							  nZ74v2S1/3/XfvG/tt2CPHgNADr5/4Zz0/6vPTsFrHHF6Qn/J+wp+b/weNf/jKVu/03b2f/qNjPy
							  4zQA6GSw5r5d+f87d2T/b92W9r9hc+L/pq3p/2ccav4fs8Dmv2o9wx+cBkTP1vy/8tSE/0tP9P5f
							  eKzr/7yjHUBDsv5PP9T0P22px3/FWoZX0pUMBjgNCJyu+M9zovQ/537Rf9bd/P/i5lv9n3aw4X/S
							  Yrf/8rUMzyUrGbRIigXtZsav8Qud/8tXMzwBalYnORqBTv4qV838X7SSQZHslChczsA5uPMCAIeV
							  x/oO3azsAAAAAElFTkSuQmCCHZbPdQ=="
					),
					'bullet_error.png' => array( 
					 'type'=>'png', 'width'=>'16', 'height'=>'16',
					 'code'=>"eJwBxgE5/olQTkcNChoKAAAADUlIRFIAAAAQAAAAEAgGAAAAH/P/YQAAAARnQU1BAACvyDcFiukA
							  AAAZdEVYdFNvZnR3YXJlAEFkb2JlIEltYWdlUmVhZHlxyWU8AAABWElEQVQ4y2P8//8/AyWAiYFC
							  QLEBLLgkHhyID/3/71/x/3///ZRclrwiyQX39sTw/fv9u5Ffzt8cSJeR7AWgpgI+WW9NPklDhj8/
							  fydfXxdgSrQBtzaFaAE15fOKCDD8/bSJQVwvReDvz1+1l5d4sBI0AGgT05+fvypFtaKFGH6cYzi1
							  aCkDj8B3hj8/fvkAsR9BA4A2uXEJ68XwCn5j+PfzPgPD/38Mf7+cZlB0KGb88/Nn86lJlrw4Dbi4
							  0I0NaEuLgJwBw79vl4B6vzMYB6sz/Pv1jIGD4xaDuG6EJtB1hTgNANqeKqTibczF+4bh/5+3DAyM
							  zAxn190CBSnDvx+3GETV5IFqfmcfatLRwpoO/vz4CfS7P9DIBwxMfKJAkf8MZkkBcHuYOZUZlFzL
							  xW5tbmoECoRiGvDzF8e52cHfgQkH6Px/SPg/nGYA0f//SyC7gHHoZyYATLOoGeLYqQwAAAAASUVO
							  RK5CYIKy1slr"
					),
					 'pluginbuddy.png' => array( 
					 'type'=>'png', 'width'=>'16', 'height'=>'16',
					 'code'=>"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
							  rkJggr35OwY="
					),
					'button-grad.png' => array( 
					 'type'=>'png', 'width'=>'5', 'height'=>'30',
					 'code'=>"eJzrDPBz5+WS4mJgYOD19HAJAtKsQCzHwQYkn9Rd2QukWIqdPEM4gKCGI6UDyOcs8IgsZmDgFgJh
							  xkt36/4ABRVLXCNKgvPTSsoTi1IZfBOTi/JzU1MyExXcMotSy/OLsosVTPQMXqmplwIVi4EUOxel
							  JpZk5ucphGTmpjIYGuobmusbWNzKKS0EqnDzdHEMqZiTfOXNza/zGXk3GPw5LpCc8PP/f/llCoft
							  c5puhPw7nqYW9nZKlEvT5A1RLW/O/W8+7xmy5YX9Li+hGpbLv+cv8PJxZHjV5F0eXGHUDzSRwdPV
							  z2WdU0ITAKUwUss="
					),
					'white-grad.png' => array( 
					 'type'=>'png', 'width'=>'5', 'height'=>'30',
					 'code'=>"eJzrDPBz5+WS4mJgYOD19HAJAtKsQCzHwQYkn9Rd2QukWIqdPEM4gKCGI6UDyOcs8IgsZmDgFgJh
						  xkt36/4ABRVLXCNKgvPTSsoTi1IZfBOTi/JzU1MyExXcMotSy/OLsosVTPQMXqmplwIVi4EUOxel
						  JpZk5ucphGTmpjIYGuobmusbWNzKKS0EqlD1dHEMqZiT/Of////1bCYHWvQOgECTq2LZwYWLalfs
						  KP7RKsTQzGOlds/bxBGogcHT1c9lnVNCEwBxhkCT"
					),
					'loading.gif' => array(
					 'type'=>'gif', 'width'=>'16', 'height'=>'11',
					 'code'=>"eJxz93SzsEwUYOBm+MLA8P//AwYGhlu3Dly4sP3Vq3NsbKxArp6eRlNTUUJCyK5dS5SUZL28HLu6
							  KlNSIvbtW66mpsjCwuzn5/Ls2akbN/Z9+XLdwsLwzp1Dnz5d3bZtwYIFvadObXr37iIDDCj+5/Zz
							  DQl2dgxwNdIzYGYECf2Tci5KTSxJTVEozyzJUEjMSqzIyU9M0cvMS8tnUPzJwskNVKUD0g1yJAOr
							  roJCX0rmvCULuB6dYlrnINBscca04HL1vds75vOxPxFs3/LI4QSPXvG6a1yua6c5YTNBJSFRgjVh
							  3opMmfaJ+w7cOrTyzaXlvc/f7H29O0JfYw5ro7VgpwQTNo1mQKu7E3Ued6hwKknxJC9JWqRxUvr8
							  lt7XRlIHpjMfaNob/fGEt2Dj2YuXdwj7zTwUyq0lMs3vkAA2o0yBRi3hcBUMYep9bJKk4nnxlU2/
							  8PnSyufWs3iXM59osUva4P5LtP/MxGJLFqnK1de6d2nNDVLEZpIRJDyubYp45hI4eYfHpaSgUNeT
							  W2IfWcW9nXabMe1wfOALZcbeE89OsCbP81p59asUVr+ZQ4xRKYxtWbEpaVnmzdbE6RmPF23LnRXL
							  p/Dmb9P2RMEHzHrTfVX6Z5gwxesymJmdXrDvQRAHNrP0IWY9AbpnxaZ3q4pkVIO28D4wXf97u0zB
							  dO5vXUdmFbbK/IrTZTBhc45cvwRrAJFshjU8lQEAXE/9dg=="
					)
				);
		if ( isset( $images[$name] ) ) {
			return $images[$name];
		} else {
			return $images['blank.gif'];
		}
	}
}
?>