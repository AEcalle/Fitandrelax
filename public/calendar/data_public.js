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
                nb_participationsMassage = nb_participationsMassage + parseInt(ssessionsList[key].participation);             
                massages_of_the_day.push(ssessionsList[key]);
               
            }          
        }
 
        var num_massage = 0;
        var btnColors = ["btn-primary","btn-success","btn-warning","btn-danger","btn-info","btn-secondary"];
       
     
        for(var key in ssessionsList){          

        
                        
            if (ssessionsList[key].scheduledAt == ("0" + (calendarDate.getMonth() + 1)).slice(-2)+"/"+("0"+(day)).slice(-2)+"/"+calendarDate.getFullYear()){              
                
                
                // Concern only Massage
                if (ssessionsList[key].activity=="Massage"){
                    num_massage = num_massage + 1;
             
                    if (num_massage == 1)
                    {                        
                        var scheduledAt = ssessionsList[key].timeS; 
                                    
                    }
                    if (num_massage == nb_massages)
                    {   
                        var finishedAt = ssessionsList[key].timeF;
                        addlink('day'+i,massages_of_the_day,ssessionsList[key].activity,scheduledAt,finishedAt,
                        nb_participationsMassage,nb_massages,ssessionsList[key].timeLimit,                        
                        ssessionsList[key].location,ssessionsList[key].coach,ssessionsList[key].activityColor,
                        ssessionsList[key].subtitle,ssessionsList[key].ssessionDescription,ssessionsList[key].activityDescription
                        );
                 
                    }                    
                }

                // Concern other activity than massage
                if (ssessionsList[key].activity!="Massage"){
                    var scheduledAt2 = ssessionsList[key].timeS;
              
                    var finishedAt = ssessionsList[key].timeF;                   
                    addlink('day'+i,ssessionsList[key].id,ssessionsList[key].activity,scheduledAt2,finishedAt,
                    ssessionsList[key].participation,ssessionsList[key].participationMax,ssessionsList[key].timeLimit,
                    ssessionsList[key].location,ssessionsList[key].coach,ssessionsList[key].activityColor,
                    ssessionsList[key].subtitle,ssessionsList[key].ssessionDescription,ssessionsList[key].activityDescription
                    );
                 
                }
                
            }
        }
    }
    
}

function addlink(day,id,activity,scheduledAt,finishedAt,participation,participationMax,timeLimit,
    location,coach,color,subtitle,ssessionDescription,activityDescription){  
    
    ssessionDescription = ssessionDescription.replace("'","\\'");
    activityDescription = activityDescription.replace("'","\\'");


    if (location!=""){
        if (location !="En ligne")
            location = "<i class=\"fa fa-map-marker\" aria-hidden=\"true\"></i> "+location;
        else
            location = "<i class=\"fa fa-desktop\" aria-hidden=\"true\"></i> "+location;
    }

    var duree = 0;
    if(scheduledAt.substring(0,2)==finishedAt.substring(0,2)){
        duree = parseInt(finishedAt.substring(3))-parseInt(scheduledAt.substring(3));
    }else{
        duree = (parseInt(finishedAt.substring(0,2))-parseInt(scheduledAt.substring(0,2)))*60 - Math.abs(parseInt(finishedAt.substring(3))-parseInt(scheduledAt.substring(3)));
    }
   
        document.getElementById(day).innerHTML += "<a href=\"#\" style=\"min-width:175px;\" class=\"btn border border-"+color+" btn-icon-split\" data-toggle=\"modal\" data-target=\"#RegistrationModal\" onclick=\"fillModal('"+activity+"','"+scheduledAt+"','"+finishedAt+"','"+participationMax+"','"+ssessionDescription+"','"+activityDescription+"')\"><div class=\"bg-dark text-white\"><span class=\"icon\">"+scheduledAt+" "+finishedAt+"</span><span class=\"text\">"+location+"</span></div><div class=\"text-center bg-"+color+" text-white\"><span class=\"text font-weight-bold h6 mb-0\">"+activity.replace(/_/g,' ')+"</span></div><div class=\"text-center\">"+subtitle+" ("+duree+"')</div></a>";
    
    }


    function fillModal(activity,scheduledAt,finishedAt,participationMax,
        ssessionDescription, activityDescription){
             
        document.getElementById('activityTitle').innerHTML = activity.replace(/_/g,' ')+'<br />('+scheduledAt+' - '+finishedAt+')<br /><br /><i class="fa fa-users" aria-hidden="true"></i> '/*+participation+' /  '*/+participationMax+''; 
        document.getElementById('img_activity').setAttribute("src","img/"+activity.toLowerCase()+"_vignette.jpg"); 
       
        

        if (activityDescription!="" && activityDescription!="null"){
            document.getElementById('activityDescriptionP').innerHTML = activityDescription;
            document.getElementById('activityDescriptionT').innerHTML = "Description de l'activité";
            document.getElementById('activityDescription').setAttribute('class','my-3 mx-2 py-2 px-2 bg-relax text-white shadow');
        }else{
            document.getElementById('activityDescriptionP').innerHTML = '';
            document.getElementById('activityDescriptionT').innerHTML = "";
            document.getElementById('activityDescription').setAttribute('class','');
        }
        
        
        if (ssessionDescription!="" && ssessionDescription!="null"){
            document.getElementById('ssessionDescriptionP').innerHTML = ssessionDescription;
            document.getElementById('ssessionDescriptionT').innerHTML = "Description de la séance";
            document.getElementById('ssessionDescription').setAttribute('class','my-3 mx-2 py-2 px-2 bg-eo text-white shadow');
        }else{
            document.getElementById('ssessionDescriptionP').innerHTML = '';
            document.getElementById('ssessionDescriptionT').innerHTML = "";
            document.getElementById('ssessionDescription').setAttribute('class','');
        }     
       
    }







    