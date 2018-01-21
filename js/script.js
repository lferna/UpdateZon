function showForm(key){

	
   var text= document.getElementById("show"+key).firstChild;
   if (text.data == "Show"){
	text.data = "Hide";
	document.getElementById('oculto'+key).style.display='block';
   }else{
	text.data = "Show";
	document.getElementById('oculto'+key).style.display='none';
        return;
   }
   

var length = document.getElementsByName('oculto').length;
	for (i=0;i<length;i++){
		document.getElementsByName('oculto')[i].style.display = 'none';		
		document.getElementById('oculto'+key).style.display='block';
		var text= document.getElementsByName("show")[i].firstChild;		
		text.data = "Show";
		text= document.getElementById("show"+key).firstChild;			
		text.data = "Hide";
}

	
        
}