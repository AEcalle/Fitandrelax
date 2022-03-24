function markNotifAsView(){
    if (document.getElementById('badge-counter')!= null){
        document.getElementById('badge-counter').style.display = 'none'; 
    }  
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.open("GET", "markNotifAsView");
    xmlhttp.send();
}