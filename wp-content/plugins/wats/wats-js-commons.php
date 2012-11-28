<?php
?>
function wats_invert_visibility(id)
{
    var cell = document.getElementById(id);
    
	if(cell.style.display == 'block')
		cell.style.display = 'none';
    else
        cell.style.display = 'block';
}

function wats_loading(cell,msg)
{
     cell.style.display = 'inline';
	 cell.className = 'wats_loading';
	 cell.innerHTML = msg;
}

function wats_stop_loading(cell,msg)
{
    cell.style.display = 'inline';
	cell.className = 'wats_stop_loading';
	if (msg)
		cell.innerHTML = msg;
}

function wats_js_add_blank_cell(idtable,colspan,message)
{
	var table = document.getElementById(idtable);
	var tr = table.insertRow(table.rows.length);
	c = tr.insertCell(-1); 
	c.appendChild(document.createTextNode(message));
	c.style.textAlign = 'center';
	c.colSpan = colspan;
	
	return;
}

function wats_js_add_check_input(tr,checkid,checkvalue)
{
	c = tr.insertCell(-1);
	input = document.createElement('input');
	input.type = "checkbox";
	input.name = checkid;
	input.id = checkid;
	input.value = checkvalue;
	c.appendChild(input);

	return;
}

function wats_js_add_table_col_with_default(table,liste,checkid,x,checkvalue,editable,defaultgroupname)
{
	if (x == 0)
		table.deleteRow(table.rows.length-1);
	tr = table.insertRow(table.rows.length);
	if ((table.rows.length % 2) == 1)
	tr.className = 'alternate';
	for (var i = 0; i < liste.length; i++)
	{
		c = tr.insertCell(-1);
		c.appendChild(document.createTextNode(liste[i]));
		if (editable[i] == 1)
			c.className = 'wats_editable';
	}
	c = tr.insertCell(-1);
	try{
		input = document.createElement('<input type="radio" name="'+defaultgroupname+'" />');
	}
	catch(err){
		input = document.createElement('input');
	}
	input.type = "radio";
	input.name = defaultgroupname;
	input.value = checkvalue;
	c.appendChild(input);
	c = tr.insertCell(-1);
	input = document.createElement('input');
	input.type = "checkbox";
	input.name = checkid;
	input.id = checkid;
	input.value = checkvalue;
	c.appendChild(input);
		
	return;
}

function wats_js_add_table_col(table,liste,checkid,x,checkvalue,editable)
{
	if (x == 0)
		table.deleteRow(table.rows.length-1);
	tr = table.insertRow(table.rows.length);
	if ((table.rows.length % 2) == 1)
	tr.className = 'alternate';
	for (var i = 0; i < liste.length; i++)
	{
		c = tr.insertCell(-1);
		c.appendChild(document.createTextNode(liste[i]));
		if (editable[i] == 1)
			c.className = 'wats_editable';
	}
	c = tr.insertCell(-1);
	input = document.createElement('input');
	input.type = "checkbox";
	input.name = checkid;
	input.id = checkid;
	input.value = checkvalue;
	c.appendChild(input);
		
	return;
}

function wats_js_remove_checked_cols(idcheck,iddivresult,idtable,span,costtoremove)
{
	x = jQuery("input[name='"+idcheck+"'][checked]").length;
	y = jQuery("input[name='"+idcheck+"']").length;
	if (x == 0)
		wats_stop_loading(document.getElementById(iddivresult),"Erreur : aucune dépense sélectionnée");
	else
	{
		jQuery("input[name='"+idcheck+"'][checked]").each(function()
		{
			var nodetoremove = this.parentNode;
			parenttoremove = nodetoremove.parentNode;
			parenttoremove.parentNode.removeChild(parenttoremove);
		});
		if (x == y)
			wats_js_add_blank_cell(idtable,span,"Aucune dépense");
		wats_js_update_total_cost("subtotalcost",-costtoremove);
		wats_stop_loading(document.getElementById(iddivresult),"Suppression terminée avec succès!");
	}
	return;
}

function wats_js_remove_all_cols(idtable,idcheck,span)
{
	var x = 0;
	jQuery("input[name='"+idcheck+"']").each(function()
	{
		x = 1;
		var nodetoremove = this.parentNode;
		parenttoremove = nodetoremove.parentNode;
		parenttoremove.parentNode.removeChild(parenttoremove);
	});
	if (x == 1)
		wats_js_add_blank_cell(idtable,span,"Aucune dépense");

	return;
}

function wats_js_is_float(x)
{
	if (isNaN(parseFloat(x)) == false)
		return 1;
	
	return 0;
}

function wats_js_is_date(x)
{
	var validformat=/^\d{2}\/\d{2}\/\d{4}$/;
	
	if (validformat.test(x) == 1)
		return 1;
	
	return 0;
}

function wats_js_is_string(x)
{
	var validformat=/^[\w\s\.\,\#\&\;\'\"\+\-\_\:?!()@ÀÁÂÃÄÅÇČĎĚÈÉÊËÌÍÎÏŇÒÓÔÕÖŘŠŤÙÚÛÜŮÝŽکگچپژیàáâãäåçčďěèéêëìíîïňðòóôõöřšťùúûüůýÿžدجحخهعغفقثصضطكمنتاأللأبيسشظزوةىآلالآرؤءئ]+$/;

	if (validformat.test(x) == 1)
		return 1;
	
	return 0;
}

function wats_js_check_form(fieldtocheck,fieldtype)
{
	res = "";
	switch(fieldtype)
	{
		case "date" :  	if (jQuery("#"+fieldtocheck).val().length == 0)
							res = "Veuillez entrer une date.";
						else if (wats_js_is_date(jQuery("#"+fieldtocheck).val()) == 0)
							res = "La date n'est pas au bon format (jj/mm/aaaa).";
						break;
		case "string" : if (jQuery("#"+fieldtocheck).val().length == 0)
							res = "Veuillez entrer une chaîne de caractères.";
						else if (wats_js_is_string(jQuery("#"+fieldtocheck).val()) == 0)
							res = "La chaîne contient des caractères invalides.";
						break;
		case "number" : if (jQuery("#"+fieldtocheck).val().length == 0)
							res = "Veuillez entrer un nombre.";
						else if (wats_js_is_float(jQuery("#"+fieldtocheck).val()) == 0)
							res = "Le nombre contient des caractères invalides.";
						break;
		defaut : break;
	}
	jQuery("#"+fieldtocheck).parent("td").next().html(res);
	
	if (res.length == 0)
		return 0;
	else
		return 1;
}