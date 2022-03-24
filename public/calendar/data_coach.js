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
        var nb_massages = 0;
        var nb_participationsMassage = 0; 
        var massages_of_the_day = [];      
        for(var key in ssessionsList){
            if (ssessionsList[key].scheduledAt == ("0" + (calendarDate.getMonth() + 1)).slice(-2)+"/"+("0"+(day)).slice(-2)+"/"+calendarDate.getFullYear() && ssessionsList[key].activity=="Massage"){
                nb_massages ++;
                nb_participationsMassage = nb_participationsMassage + parseInt(ssessionsList[key].nbParticipations);
                massages_of_the_day.push(ssessionsList[key]);
            }
        }
        var num_massage = 0;
       
       
       
        for(var key in ssessionsList){         
            
               
                            
                if (ssessionsList[key].scheduledAt == ("0" + (calendarDate.getMonth() + 1)).slice(-2)+"/"+("0"+(day)).slice(-2)+"/"+calendarDate.getFullYear()){              
                    
                    // Concern only Massage
                    if (ssessionsList[key].activity=="Massage"){
                        num_massage = num_massage + 1;
                        if (num_massage == 1)
                        {
                            var scheduledAt = ssessionsList[key].timeS;
                        }
                        else if (num_massage == nb_massages)
                        {
                            var finishedAt = ssessionsList[key].timeF;
                            addlink('day'+i,massages_of_the_day,ssessionsList[key].activity,scheduledAt,finishedAt,nb_participationsMassage,nb_massages,ssessionsList[key].structure,ssessionsList[key].activityColor, ssessionsList[key].subtitle);
                        
                        }                    
                    }

                    // Concern other activity than massage
                    if (ssessionsList[key].activity!="Massage"){
                        var scheduledAt = ssessionsList[key].timeS;
                        var finishedAt = ssessionsList[key].timeF;                   
                        addlink('day'+i,ssessionsList[key].id,ssessionsList[key].activity.replace(/_/g,' '),scheduledAt,finishedAt,ssessionsList[key].nbParticipations,ssessionsList[key].participationMax,ssessionsList[key].structure,ssessionsList[key].activityColor, ssessionsList[key].subtitle);
                    
                    }
                }       
            
        }     
    }
}

function addlink(day,id,activity,scheduledAt,finishedAt,nbParticipations,participationMax,structure,color,subtitle){
    
    if (activity=="Massage"){
        chain = "";   
        for (key in id){
                   
            chain = id[key].id;              
        }
        id = chain;      
   
    }
    document.getElementById(day).innerHTML += "<a href=\"participations/"+id+"\" class=\"btn bg-"+color+" text-white btn-icon-split btn-sm\"><div><span class=\"icon\">"+scheduledAt+" "+finishedAt+"</span><span class=\"text\">"+activity+"</span><span>"+nbParticipations+"/"+participationMax+"</span></div><div class=\"text-center\">"+subtitle+"</div><div style=\"text-align:center;\">"+structure+"</div></a>"; 
}    
