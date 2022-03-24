function fill(){ 
        
    for (var i=0; i<42 ; i++){            
        var actualYear = parseInt(document.getElementById('year').innerHTML);
        var actualMonth = document.getElementById('month').innerHTML;
        var indexMonth = frenchMonth.indexOf(actualMonth); 
        var calendarDate = new Date(actualYear,indexMonth,1);   
        var firstDay = calendarDate.getDay();
        if (firstDay!=0){
            var modif = -firstDay +1 +i; 
        }
        else
        {
            var modif = -6 + i; 
        }
        calendarDate.setDate(calendarDate.getDate()+modif);  
        var day = calendarDate.getDate();        
        
        //massages of the day
        var nb_massages = [];
        var nb_participationsMassage = [];
        var massages_of_the_day = [];      
        for(var key in ssessionsList){
            if (ssessionsList[key].scheduledAt == ("0" + (calendarDate.getMonth() + 1)).slice(-2)+"/"+("0"+(day)).slice(-2)+"/"+calendarDate.getFullYear() && ssessionsList[key].activity=="Massage"){
               if (typeof nb_massages[ssessionsList[key].structure]=="undefined"){
                nb_massages[ssessionsList[key].structure] = 1;
               }else{
                nb_massages[ssessionsList[key].structure]++;
               }
               if ((nb_participationsMassage[ssessionsList[key].structure]===undefined)){
                nb_participationsMassage[ssessionsList[key].structure] = parseInt(ssessionsList[key].participation);
               }else{
                nb_participationsMassage[ssessionsList[key].structure] = nb_participationsMassage[ssessionsList[key].structure] + parseInt(ssessionsList[key].participation);
               }
               if ((massages_of_the_day[ssessionsList[key].structure]===undefined)){
                massages_of_the_day[ssessionsList[key].structure] = [];
               }
                massages_of_the_day[ssessionsList[key].structure].push(ssessionsList[key]);
               
                
            }
        }
       
    
       
       
     
        var num_massage = []; 
        var massagesScheduledAt = [];
        var massagesFinishedAt = []; 
        for(var key in ssessionsList){
            // If it's not an event
            if (typeof ssessionsList[key].activity!="undefined"){
                
                 
                if (ssessionsList[key].scheduledAt == ("0" + (calendarDate.getMonth() + 1)).slice(-2)+"/"+("0"+(day)).slice(-2)+"/"+calendarDate.getFullYear()){              
                  
                     
                    // Concern only Massage
                    if (ssessionsList[key].activity=="Massage"){
                   
                        if (num_massage[ssessionsList[key].structure]===undefined){
                            num_massage[ssessionsList[key].structure] = 1;
                        }else
                       {
                        num_massage[ssessionsList[key].structure] = num_massage[ssessionsList[key].structure] + 1;
                       } 
                       
                        if (num_massage[ssessionsList[key].structure] == 1)
                        {
                            massagesScheduledAt[ssessionsList[key].structure] = ssessionsList[key].timeS;
                        
                        }
                    
                        if (num_massage[ssessionsList[key].structure] == nb_massages[ssessionsList[key].structure])
                        {
                            massagesFinishedAt[ssessionsList[key].structure] = ssessionsList[key].timeF;
                        
                            addlink('day'+i,massages_of_the_day[ssessionsList[key].structure],
                            ssessionsList[key].activity,massagesScheduledAt[ssessionsList[key].structure],
                            massagesFinishedAt[ssessionsList[key].structure],
                            nb_participationsMassage[ssessionsList[key].structure],
                            nb_massages[ssessionsList[key].structure],ssessionsList[key].structure,
                            ssessionsList[key].coach, ssessionsList[key].activityColor, ssessionsList[key].subtitle);
                        
                        }                    
                    }

                    // Concern other activity than massage
                    if (ssessionsList[key].activity!="Massage"){
                        var scheduledAt = ssessionsList[key].timeS;
                        var finishedAt = ssessionsList[key].timeF;                   
                        addlink('day'+i,ssessionsList[key].id,ssessionsList[key].activity.replace(/_/g,' '),
                        scheduledAt,finishedAt,ssessionsList[key].participation,ssessionsList[key].participationMax,
                        ssessionsList[key].structure,ssessionsList[key].coach, ssessionsList[key].activityColor,
                        ssessionsList[key].subtitle
                        );
                    
                    }
                }

                
            }
            else  if (ssessionsList[key].scheduledAt == ("0" + (calendarDate.getMonth() + 1)).slice(-2)+"/"+("0"+(day)).slice(-2)+"/"+calendarDate.getFullYear()){
                if(ssessionsList[key].createdBy==13){
                    var color = "blue";
                    var createdBy = "(Aurélio)";
                }
                else if(ssessionsList[key].createdBy==14){
                    var color = "green";
                    var createdBy = "(Didz)";
                }
                else{
                    var color = "red";
                    var createdBy = "(Jul)";
                }
                document.getElementById('day'+i).innerHTML +='<a href="modifyEvent/'+ssessionsList[key].id+'" style="color:'+color+';">'+ssessionsList[key].timeS+' '+ssessionsList[key].subject+' '+createdBy+'</a>';
            }
        }     
    }
}

function addlink(day,id,activity,scheduledAt,finishedAt,participation,participationMax,structure,coach,color,subtitle){

         if (activity!="Massage"){
           
                document.getElementById(day).innerHTML += '<a href="modifySsession/'+id+'" class="btn bg-'+color+' text-white btn-icon-split btn-sm"><div><span class="icon">'+scheduledAt+' '+finishedAt+'</span><span class="text font-weight-bold">'+activity+'</span><span>'+participation+'/'+participationMax+'</span></div><div class="text-center">'+subtitle+'</div><div style="text-align:center;">'+structure+' - '+coach+'</div></a>';
            
        }
        else{
        chain = "";   
        for (key in id){
            var freePlaces = id[key].participationMax - id[key].participation;           
            chain += id[key].id+","+id[key].timeS+","+id[key].timeF+","+freePlaces+"-";              
        }
        chain = chain.substring(0,chain.length-1);      
    document.getElementById(day).innerHTML += "<a href=\"#\" class=\"btn bg-"+color+" text-white btn-icon-split btn-sm\" data-toggle=\"modal\" data-target=\"#MassageModal\" onclick=\"fillModal('"+chain+"');\"><div><span class=\"icon\">"+scheduledAt+" "+finishedAt+"</span><span class=\"text\">"+activity+"</span><span>"+participation+"/"+participationMax+"</span></div><div style=\"text-align:center;\">"+structure+" - "+coach+"</div></a>"; 
    }
  
}    

function fillModal(id){
     //On retire les éventuels input massages déjà créés :
     var slots = document.getElementById('slots');
     while (slots.firstChild){
         slots.removeChild(slots.firstChild);
     }    
     
     var list = id.split('-');
 
     var length = list.length;
     for (var i=0;i<length;i++){        
        var list2 = list[i].split(',');
         var input = document.createElement('input');           
         var paragraph = document.createElement('p');            
         slots.appendChild(paragraph);
         
         if (list2[3]==0){
            var slot = document.createTextNode(list2[1]+"-"+list2[2]+" créneau réservé ");
         }        
         else{
            var slot = document.createTextNode(list2[1]+"-"+list2[2]+" libre ");
         }
        
         paragraph.appendChild(slot); 
         var modif = document.createElement('a');
         modif.setAttribute("href","modifySsession/"+list2[0]+"");
         modif.appendChild(document.createTextNode("Modifier"));
         paragraph.appendChild(modif);               
     }       
     
}