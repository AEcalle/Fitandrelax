// Calendar Initialisation
window.onload = function(){
    var date = new Date();
    frenchMonth = new Array("Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre");
    frenchDay = new Array("Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi","Dimanche");
    if(document.body.clientWidth>768){         
        show();
    }
    else{
        showMobile();
    }
    month(date);
    year(date);    
    for (var i=0; i<42 ; i++){
        dayNumber(i); 
    }   

    fill();      
};

function month(date){      
    var mois = frenchMonth[date.getMonth()];   
    document.getElementById('month').innerHTML= mois;
}

function year(date){   
    var year = date.getFullYear();
    document.getElementById('year').innerHTML= year;
}

function modifyMonth(increment){
    var actualMonth = document.getElementById('month').innerHTML;    
    var indexMonth = frenchMonth.indexOf(actualMonth); 
        
    if (indexMonth==0 && increment == -1){
        var newMonth = "Décembre";
        modifyYear(-1);
    }
    else if (indexMonth==11 && increment == +1){
        var newMonth = "Janvier";
        modifyYear(1);
    }
    else{
        var newMonth = frenchMonth[indexMonth +increment];
    }  

    document.getElementById('month').innerHTML= newMonth;
    for (var i=0; i<42 ; i++){
        dayNumber(i); 
    }
    fill();    
}

function modifyYear(increment){
    var actualYear = document.getElementById('year').innerHTML;
    var newYear = parseInt(actualYear) + increment;
    document.getElementById('year').innerHTML= newYear;
}

function dayNumber(increment){
    var actualYear = parseInt(document.getElementById('year').innerHTML);
    var actualMonth = document.getElementById('month').innerHTML;
    var indexMonth = frenchMonth.indexOf(actualMonth); 
    var calendarDate = new Date(actualYear,indexMonth,1);   
    var firstDay = calendarDate.getDay();
    if (firstDay!=0){
        var modif = -firstDay +1 +increment; 
    }
    else
    {
        var modif = -6 + increment; 
    }
    calendarDate.setDate(calendarDate.getDate()+modif);  
    var day = calendarDate.getDate();
    if(document.body.clientWidth>768){
        document.getElementById('day'+increment).innerHTML = day;
    }
    else{
        var jour = calendarDate.getDay();        
        var jourSemaine = frenchDay[jour-1];
        if (jourSemaine==undefined)
            jourSemaine = "Dimanche";
        document.getElementById('day'+increment).innerHTML = jourSemaine+' '+ day;
    }
    if (calendarDate.getMonth()!=indexMonth){        
        document.getElementById('day'+increment).parentElement.style.opacity = 0.5;
        if(document.body.clientWidth<768)
        document.getElementById('day'+increment).parentElement.style.display = 'none';
    }  
    else{
        document.getElementById('day'+increment).parentElement.style.opacity = 1;
        if(document.body.clientWidth<768)
        document.getElementById('day'+increment).parentElement.style.display = 'block';
    }
    
    if(calendarDate.getFullYear()+''+calendarDate.getMonth()+''+calendarDate.getDate() == new Date().getFullYear()+''+new Date().getMonth()+''+new Date().getDate()){
        document.getElementById('day'+increment).parentElement.style.backgroundColor = "rgba(0, 0, 255, 0.1)";
    }
    else{
        document.getElementById('day'+increment).parentElement.style.backgroundColor = "transparent";
    }     
}

//Structure HTML
function show(){
    var calendar = document.getElementById('calendar');
    var cardShadow = document.createElement('div');
    cardShadow.setAttribute("class","card shadow mb-4");
    calendar.appendChild(cardShadow);
    var cardHeader = document.createElement('div'); 
    cardHeader.setAttribute("class","card-header py-3 text-gray-100  bg-success");
    cardShadow.appendChild(cardHeader);
    var row = document.createElement('div');
    row.setAttribute("class","row");
    cardHeader.appendChild(row);
    var colLeft = document.createElement('div');
    colLeft.setAttribute("class","col-md-2");
    row.appendChild(colLeft);
    var colRight = document.createElement('div');
    colRight.setAttribute("class","col-md-8");
    row.appendChild(colRight);
    var chevronLeft = document.createElement('i');
    chevronLeft.setAttribute("class","fa fa-chevron-left");
    chevronLeft.setAttribute("aria-hidden","true");
    chevronLeft.setAttribute("onclick","modifyMonth(-1);");
    colLeft.appendChild(chevronLeft);
    var space = document.createTextNode(" ")
    colLeft.appendChild(space);
    var chevronRight = document.createElement('i');
    chevronRight.setAttribute("class","fa fa-chevron-right");
    chevronRight.setAttribute("aria-hidden","true");
    chevronRight.setAttribute("onclick","modifyMonth(+1);");
    colLeft.appendChild(chevronRight);
    var title = document.createElement('h6');
    title.setAttribute("class","font-weight-bold text-center");
    colRight.appendChild(title);
    var spanMonth = document.createElement('span');
    spanMonth.setAttribute("id","month");
    title.appendChild(spanMonth);
    var space2 = document.createTextNode(" ")
    title.appendChild(space2);
    var spanYear = document.createElement('span');
    spanYear.setAttribute("id","year");
    title.appendChild(spanYear);
    
    var cardBody = document.createElement('div');
    cardBody.setAttribute("class","card-body");
    cardShadow.appendChild(cardBody);
    var tableResponsive = document.createElement('div');
    tableResponsive.setAttribute("class","table-responsive");
    cardBody.appendChild(tableResponsive);
    var table = document.createElement('table');
    table.setAttribute("class","table table-bordered");
    table.setAttribute("id","dataTable");
    table.setAttribute("width","100%");
    table.setAttribute("cellspacing","0");
    tableResponsive.appendChild(table);
    var thead= document.createElement('thead');
    table.appendChild(thead);
    var tbody = document.createElement('tbody');
    table.appendChild(tbody);
    var tr0 = document.createElement('tr');
    thead.appendChild(tr0);
    for (var i = 0; i < 6; i++){
        var th = document.createElement('th');
        if (i<5)
            th.textContent = frenchDay[i];
        else
            th.textContent = frenchDay[i]+" / "+frenchDay[i+1];
        tr0.appendChild(th);
    }
    for (var i = 0; i < 12; i++){
        var tr = document.createElement('tr');
        tbody.appendChild(tr);
        if (i%2==0){
            for (var j = 0; j < 6; j++){
                var td = document.createElement('td');
                if (j<5)
                    td.setAttribute("rowspan","2");    

                var increment = Math.floor((i/2))*7+j;                                          
                tr.appendChild(td);
                var cell = document.createElement('div'); 
                if (j<5)
                cell.setAttribute("class","cell");
                else
                cell.setAttribute("class","cell2");
                cell.setAttribute("id","day"+increment); 
                td.appendChild(cell);                   
            }
        }
        else{
            var td = document.createElement('td');
            var increment = Math.floor((i/2))*7+j;              
            tr.appendChild(td);
            var cell = document.createElement('div'); 
            if (j<5)
                cell.setAttribute("class","cell");
            else
                cell.setAttribute("class","cell2");
            cell.setAttribute("id","day"+increment); 
            td.appendChild(cell);              
        }
        
    }
    
}

function showMobile(){
    var calendar = document.getElementById('calendar');
    var cardShadow = document.createElement('div');
    cardShadow.setAttribute("class","card shadow mb-4");
    calendar.appendChild(cardShadow);
    var cardHeader = document.createElement('div'); 
    cardHeader.setAttribute("class","card-header py-3 text-gray-100  bg-success");
    cardShadow.appendChild(cardHeader);
    var row = document.createElement('div');
    row.setAttribute("class","row");
    cardHeader.appendChild(row);
    var colLeft = document.createElement('div');
    colLeft.setAttribute("class","col-2 text-center");
    row.appendChild(colLeft);
    var colCenter = document.createElement('div');
    colCenter.setAttribute("class","col-8 text-center");
    row.appendChild(colCenter);
    var colRight = document.createElement('div');
    colRight.setAttribute("class","col-2 text-center");
    row.appendChild(colRight);
    var chevronLeft = document.createElement('i');
    chevronLeft.setAttribute("class","fa fa-chevron-left");
    chevronLeft.setAttribute("aria-hidden","true");
    chevronLeft.setAttribute("onclick","modifyMonth(-1);");
    colLeft.appendChild(chevronLeft);
    var space = document.createTextNode(" ")
    colLeft.appendChild(space);
    var chevronRight = document.createElement('i');
    chevronRight.setAttribute("class","fa fa-chevron-right");
    chevronRight.setAttribute("aria-hidden","true");
    chevronRight.setAttribute("onclick","modifyMonth(+1);");
    colRight.appendChild(chevronRight);
    var title = document.createElement('h6');
    title.setAttribute("class","font-weight-bold text-center");
    colCenter.appendChild(title);
  
    var spanMonth = document.createElement('span');
    spanMonth.setAttribute("id","month");
    title.appendChild(spanMonth);
    var space2 = document.createTextNode(" ")
    title.appendChild(space2);
    var spanYear = document.createElement('span');
    spanYear.setAttribute("id","year");
    title.appendChild(spanYear);
    
    var cardBody = document.createElement('div');
    cardBody.setAttribute("class","card-body");
    cardShadow.appendChild(cardBody);

    for (var i = 0; i < 12; i++){       
        if (i%2==0){
            for (var j = 0; j < 7; j++){             
                var line = document.createElement('div'); 
                line.setAttribute('class','border-bottom');
               if (j==6){
                line.setAttribute('class','border-bottom border-danger'); 
               }
                cardBody.appendChild(line); 
                var increment = Math.floor((i/2))*7+j;            
                var cell = document.createElement('div');                
                cell.setAttribute("id","day"+increment);                
                line.appendChild(cell);
                                 
            } 
        }      
    }

}