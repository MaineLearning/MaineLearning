jQuery(document).ready(function($){

// BPS jQuery Tab Menus
	$('#bps-tabs').tabs();
	$('#bps-edittabs').tabs();
});

// single dialog numbered individually per Core section - figure out each + num
// and add main options variable later on to condense this code
jQuery(document).ready(function($){				
	$.fx.speeds._default = 400;
	var $info1 = $("#bps-modal-content1");     
		$info1.dialog({                            
		 'dialogClass'   : 'wp-dialog',                    
		 'modal'         : false,         
		 'autoOpen'      : false,          
		 'closeOnEscape' : true,
		 'width'		 : 400,
		 'height'	 	 : 500,
		 'show'			 : 'blind',
		 'hide'			 : 'blind',
		 'position'		 : 'center',
		 'buttons'       : {             
		 "Close": function() {                 
		 $(this).dialog('close');             
		 }         
	}     
	});
	$("#bps-open-modal1").click(function(event) {         
		event.preventDefault();         
		$info1.dialog('open');     
	});	
	var $info2 = $("#bps-modal-content2");     
		$info2.dialog({                            
		 'dialogClass'   : 'wp-dialog',                    
		 'modal'         : false,         
		 'autoOpen'      : false,          
		 'closeOnEscape' : true,
		 'width'		 : 400,
		 'height'	 	 : 500,
		 'show'			 : 'blind',
		 'hide'			 : 'blind',
		 'position'		 : 'center',
		 'buttons'       : {             
		 "Close": function() {                 
		 $(this).dialog('close');             
		 }         
	}     
	});  	
	$("#bps-open-modal2").click(function(event) {         
		event.preventDefault();         
		$info2.dialog('open');     
	});
	var $info3 = $("#bps-modal-content3");     
		$info3.dialog({                            
		 'dialogClass'   : 'wp-dialog',                    
		 'modal'         : false,         
		 'autoOpen'      : false,          
		 'closeOnEscape' : true,
		 'width'		 : 400,
		 'height'	 	 : 500,
		 'show'			 : 'blind',
		 'hide'			 : 'blind',
		 'position'		 : 'center',
		 'buttons'       : {             
		 "Close": function() {                 
		 $(this).dialog('close');             
		 }         
	}     
	});  	
	$("#bps-open-modal3").click(function(event) {         
		event.preventDefault();         
		$info3.dialog('open');     
	});
	var $info4 = $("#bps-modal-content4");     
		$info4.dialog({                            
		 'dialogClass'   : 'wp-dialog',                    
		 'modal'         : false,         
		 'autoOpen'      : false,          
		 'closeOnEscape' : true,
		 'width'		 : 400,
		 'height'	 	 : 500,
		 'show'			 : 'blind',
		 'hide'			 : 'blind',
		 'position'		 : 'center',
		 'buttons'       : {             
		 "Close": function() {                 
		 $(this).dialog('close');             
		 }         
	}     
	});
	$("#bps-open-modal4").click(function(event) {         
		event.preventDefault();         
		$info4.dialog('open');     
	});
	var $info5 = $("#bps-modal-content5");     
		$info5.dialog({                            
		 'dialogClass'   : 'wp-dialog',                    
		 'modal'         : false,         
		 'autoOpen'      : false,          
		 'closeOnEscape' : true,
		 'width'		 : 400,
		 'height'	 	 : 500,
		 'show'			 : 'blind',
		 'hide'			 : 'blind',
		 'position'		 : 'center',
		 'buttons'       : {             
		 "Close": function() {                 
		 $(this).dialog('close');             
		 }         
	}     
	});
	$("#bps-open-modal5").click(function(event) {         
		event.preventDefault();         
		$info5.dialog('open');     
	});
	var $info6 = $("#bps-modal-content6");     
		$info6.dialog({                            
		 'dialogClass'   : 'wp-dialog',                    
		 'modal'         : false,         
		 'autoOpen'      : false,          
		 'closeOnEscape' : true,
		 'width'		 : 400,
		 'height'	 	 : 500,
		 'show'			 : 'blind',
		 'hide'			 : 'blind',
		 'position'		 : 'center',
		 'buttons'       : {             
		 "Close": function() {                 
		 $(this).dialog('close');             
		 }         
	}     
	});
	$("#bps-open-modal6").click(function(event) {         
		event.preventDefault();         
		$info6.dialog('open');     
	});
	var $info7 = $("#bps-modal-content7");     
		$info7.dialog({                            
		 'dialogClass'   : 'wp-dialog',                    
		 'modal'         : false,         
		 'autoOpen'      : false,          
		 'closeOnEscape' : true,
		 'width'		 : 400,
		 'height'	 	 : 500,
		 'show'			 : 'blind',
		 'hide'			 : 'blind',
		 'position'		 : 'center',
		 'buttons'       : {             
		 "Close": function() {                 
		 $(this).dialog('close');             
		 }         
	}     
	});
	$("#bps-open-modal7").click(function(event) {         
		event.preventDefault();         
		$info7.dialog('open');     
	});
	var $info8 = $("#bps-modal-content8");     
		$info8.dialog({                            
		 'dialogClass'   : 'wp-dialog',                    
		 'modal'         : false,         
		 'autoOpen'      : false,          
		 'closeOnEscape' : true,
		 'width'		 : 400,
		 'height'	 	 : 500,
		 'show'			 : 'blind',
		 'hide'			 : 'blind',
		 'position'		 : 'center',
		 'buttons'       : {             
		 "Close": function() {                 
		 $(this).dialog('close');             
		 }         
	}     
	});
	$("#bps-open-modal8").click(function(event) {         
		event.preventDefault();         
		$info8.dialog('open');     
	});
	var $info9 = $("#bps-modal-content9");     
		$info9.dialog({                            
		 'dialogClass'   : 'wp-dialog',                    
		 'modal'         : false,         
		 'autoOpen'      : false,          
		 'closeOnEscape' : true,
		 'width'		 : 400,
		 'height'	 	 : 500,
		 'show'			 : 'blind',
		 'hide'			 : 'blind',
		 'position'		 : 'center',
		 'buttons'       : {             
		 "Close": function() {                 
		 $(this).dialog('close');             
		 }         
	}     
	});
	$("#bps-open-modal9").click(function(event) {         
		event.preventDefault();         
		$info9.dialog('open');     
	});
	var $info10 = $("#bps-modal-content10");     
		$info10.dialog({                            
		 'dialogClass'   : 'wp-dialog',                    
		 'modal'         : false,         
		 'autoOpen'      : false,          
		 'closeOnEscape' : true,
		 'width'		 : 400,
		 'height'	 	 : 500,
		 'show'			 : 'blind',
		 'hide'			 : 'blind',
		 'position'		 : 'center',
		 'buttons'       : {             
		 "Close": function() {                 
		 $(this).dialog('close');             
		 }         
	}     
	});
	$("#bps-open-modal10").click(function(event) {         
		event.preventDefault();         
		$info10.dialog('open');     
	});
	var $info11 = $("#bps-modal-content11");     
		$info11.dialog({                            
		 'dialogClass'   : 'wp-dialog',                    
		 'modal'         : false,         
		 'autoOpen'      : false,          
		 'closeOnEscape' : true,
		 'width'		 : 400,
		 'height'	 	 : 500,
		 'show'			 : 'blind',
		 'hide'			 : 'blind',
		 'position'		 : 'center',
		 'buttons'       : {             
		 "Close": function() {                 
		 $(this).dialog('close');             
		 }         
	}     
	});
	$("#bps-open-modal11").click(function(event) {         
		event.preventDefault();         
		$info11.dialog('open');     
	});
	var $info12 = $("#bps-modal-content12");     
		$info12.dialog({                            
		 'dialogClass'   : 'wp-dialog',                    
		 'modal'         : false,         
		 'autoOpen'      : false,          
		 'closeOnEscape' : true,
		 'width'		 : 400,
		 'height'	 	 : 500,
		 'show'			 : 'blind',
		 'hide'			 : 'blind',
		 'position'		 : 'center',
		 'buttons'       : {             
		 "Close": function() {                 
		 $(this).dialog('close');             
		 }         
	}     
	});
	$("#bps-open-modal12").click(function(event) {         
		event.preventDefault();         
		$info12.dialog('open');     
	});
	var $info13 = $("#bps-modal-content13");     
		$info13.dialog({                            
		 'dialogClass'   : 'wp-dialog',                    
		 'modal'         : false,         
		 'autoOpen'      : false,          
		 'closeOnEscape' : true,
		 'width'		 : 400,
		 'height'	 	 : 500,
		 'show'			 : 'blind',
		 'hide'			 : 'blind',
		 'position'		 : 'center',
		 'buttons'       : {             
		 "Close": function() {                 
		 $(this).dialog('close');             
		 }         
	}     
	});
	$("#bps-open-modal13").click(function(event) {         
		event.preventDefault();         
		$info13.dialog('open');     
	});
	var $info14 = $("#bps-modal-content14");     
		$info14.dialog({                            
		 'dialogClass'   : 'wp-dialog',                    
		 'modal'         : false,         
		 'autoOpen'      : false,          
		 'closeOnEscape' : true,
		 'width'		 : 400,
		 'height'	 	 : 500,
		 'show'			 : 'blind',
		 'hide'			 : 'blind',
		 'position'		 : 'center',
		 'buttons'       : {             
		 "Close": function() {                 
		 $(this).dialog('close');             
		 }         
	}     
	});
	$("#bps-open-modal14").click(function(event) {         
		event.preventDefault();         
		$info14.dialog('open');     
	});
	var $info15 = $("#bps-modal-content15");     
		$info15.dialog({                            
		 'dialogClass'   : 'wp-dialog',                    
		 'modal'         : false,         
		 'autoOpen'      : false,          
		 'closeOnEscape' : true,
		 'width'		 : 400,
		 'height'	 	 : 500,
		 'show'			 : 'blind',
		 'hide'			 : 'blind',
		 'position'		 : 'center',
		 'buttons'       : {             
		 "Close": function() {                 
		 $(this).dialog('close');             
		 }         
	}     
	});
	$("#bps-open-modal15").click(function(event) {         
		event.preventDefault();         
		$info15.dialog('open');     
	});
	var $info16 = $("#bps-modal-content16");     
		$info16.dialog({                            
		 'dialogClass'   : 'wp-dialog',                    
		 'modal'         : false,         
		 'autoOpen'      : false,          
		 'closeOnEscape' : true,
		 'width'		 : 400,
		 'height'	 	 : 500,
		 'show'			 : 'blind',
		 'hide'			 : 'blind',
		 'position'		 : 'center',
		 'buttons'       : {             
		 "Close": function() {                 
		 $(this).dialog('close');             
		 }         
	}     
	});
	$("#bps-open-modal16").click(function(event) {         
		event.preventDefault();         
		$info16.dialog('open');     
	});
	var $info17 = $("#bps-modal-content17");     
		$info17.dialog({                            
		 'dialogClass'   : 'wp-dialog',                    
		 'modal'         : false,         
		 'autoOpen'      : false,          
		 'closeOnEscape' : true,
		 'width'		 : 400,
		 'height'	 	 : 500,
		 'show'			 : 'blind',
		 'hide'			 : 'blind',
		 'position'		 : 'center',
		 'buttons'       : {             
		 "Close": function() {                 
		 $(this).dialog('close');             
		 }         
	}     
	});
	$("#bps-open-modal17").click(function(event) {         
		event.preventDefault();         
		$info17.dialog('open');     
	});
	var $info18 = $("#bps-modal-content18");     
		$info18.dialog({                            
		 'dialogClass'   : 'wp-dialog',                    
		 'modal'         : false,         
		 'autoOpen'      : false,          
		 'closeOnEscape' : true,
		 'width'		 : 400,
		 'height'	 	 : 500,
		 'show'			 : 'blind',
		 'hide'			 : 'blind',
		 'position'		 : 'center',
		 'buttons'       : {             
		 "Close": function() {                 
		 $(this).dialog('close');             
		 }         
	}     
	});
	$("#bps-open-modal18").click(function(event) {         
		event.preventDefault();         
		$info18.dialog('open');     
	});
});