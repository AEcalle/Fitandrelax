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
        var already_waiting = "no";
        var waiting_id = 0;
        var waitingNumero = 0;
        for(var key in ssessionsList){
            if (ssessionsList[key].scheduledAt == ("0" + (calendarDate.getMonth() + 1)).slice(-2)+"/"+("0"+(day)).slice(-2)+"/"+calendarDate.getFullYear() && ssessionsList[key].activity=="Massage"){
                nb_massages ++;
                nb_participationsMassage = nb_participationsMassage + parseInt(ssessionsList[key].participation);             
                massages_of_the_day.push(ssessionsList[key]);
                if(ssessionsList[key].already_waiting=="yes"){
                    already_waiting = "yes"
                    waiting_id = ssessionsList[key].id;
                    waitingNumero = ssessionsList[key].waitingNumero;
                }
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
                    if (num_massage == nb_massages)
                    {   
                        var finishedAt = ssessionsList[key].timeF;
                        addlink('day'+i,massages_of_the_day,ssessionsList[key].activity,scheduledAt,finishedAt,
                        nb_participationsMassage,nb_massages,ssessionsList[key].timeLimit,
                        ssessionsList[key].already,ssessionsList[key].enoughCredit,
                        already_waiting,ssessionsList[key].location,waiting_id,waitingNumero,
                        ssessionsList[key].activityColor, ssessionsList[key].subtitle,
                        ssessionsList[key].ssessionDescription, ssessionsList[key].activityDescription                         
                        );
                        
                    }                    
                }

                // Concern other activity than massage
                if (ssessionsList[key].activity!="Massage"){
                    var scheduledAt2 = ssessionsList[key].timeS;
              
                    var finishedAt = ssessionsList[key].timeF;                   
                    addlink('day'+i,ssessionsList[key].id,ssessionsList[key].activity,scheduledAt2,finishedAt,
                    ssessionsList[key].participation,ssessionsList[key].participationMax,ssessionsList[key].timeLimit,
                    ssessionsList[key].already,ssessionsList[key].enoughCredit,
                    ssessionsList[key].already_waiting,ssessionsList[key].location,waiting_id,
                    ssessionsList[key].waitingNumero,ssessionsList[key].activityColor, ssessionsList[key].subtitle,
                    ssessionsList[key].ssessionDescription, ssessionsList[key].activityDescription
                    );
                    
                }
                
            }
        }
    }
}

function addlink(day,id,activity,scheduledAt,finishedAt,participation,participationMax,timeLimit,already,enoughCredit,
    already_waiting,location,waiting_id,waitingNumero,color, subtitle, ssessionDescription, activityDescription){
   
        ssessionDescription = ssessionDescription.replace("'","\\'");
        activityDescription = activityDescription.replace("'","\\'");

    chain = "";
    if (id.constructor === Array){
        for (key in id){
            var freePlaces = id[key].participationMax - id[key].participation;
            chain += id[key].id+","+id[key].timeS+","+id[key].timeF+","+id[key].already+","+freePlaces+"-"; 
            if (id[key].already=="yes")
            already = "yes";       
        }
        chain = chain.substring(0,chain.length-1);      
    }
    else{
        chain = id;
    } 
    if (already=="yes"){
        var alreadyMsg = '<i class="fa fa-check" aria-hidden="true"></i>';
    }else if(already_waiting=="yes"){
        var alreadyMsg = '<i class="fa fa-spinner" aria-hidden="true"></i>'+' '+waitingNumero;
    }else{
        var alreadyMsg = '<i class="fa fa-times" aria-hidden="true"></i>';
    }

    if (location!=""){
        if (location!="En ligne")
            location = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class=\"fa fa-map-marker\" aria-hidden=\"true\"></i> &nbsp;"+location;
        else{
            location = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class=\"fa fa-desktop\" aria-hidden=\"true\"></i> &nbsp;"+location;
        }
        }
    
    var duree = 0;
    if(scheduledAt.substring(0,2)==finishedAt.substring(0,2)){
        duree = parseInt(finishedAt.substring(3))-parseInt(scheduledAt.substring(3));
    }else{
        duree = (parseInt(finishedAt.substring(0,2))-parseInt(scheduledAt.substring(0,2)))*60 - Math.abs(parseInt(finishedAt.substring(3))-parseInt(scheduledAt.substring(3)));
    }
 
    document.getElementById(day).innerHTML += "<a href=\"#\" style=\"min-width:175px;\" class=\"btn border border-"+color+" btn-icon-split\" data-toggle=\"modal\" data-target=\"#RegistrationModal\" onclick=\"fillModal('"+chain+"','"+activity+"','"+scheduledAt+"','"+finishedAt+"','"+participation+"','"+participationMax+"','"+timeLimit+"','"+already+"','"+enoughCredit+"','"+already_waiting+"','"+waiting_id+"','"+ssessionDescription+"','"+activityDescription+"')\"><div class=\"bg-dark text-white\"><span class=\"icon\"><span class=\"font-weight-bold\">"+scheduledAt+"</span> "+finishedAt+"</span><span class=\"text\">"+location+"</span></div><div class=\"text-center bg-"+color+" text-white\"><span class=\"text font-weight-bold h6 mb-0\">"+activity.replace(/_/g,' ')+"</span></div><div class=\"text-center\"><span class=\"font-italic\">"+subtitle+" ("+duree+"')</span><br />"+alreadyMsg+"</div></a>";
}

function fillModal(id,activity,scheduledAt,finishedAt,participation,participationMax,
    timeLimit,already,enoughCredit,already_waiting,waiting_id,ssessionDescription, activityDescription){
    //On retire les éventuels input massages déjà créés :
    var slots = document.getElementById('slots');
    while (slots.firstChild){
        slots.removeChild(slots.firstChild);
    }
    if (already_waiting!="yes"){
        // S'il sa'agit de massages, on créé les input
        var coma = id.indexOf(',');
        if (coma!=-1){     
            var list = id.split('-');

            var length = list.length;
            for (var i=0;i<length;i++){
                var list2 = list[i].split(',');

                //Si l'utilisateur est déjà inscrit
                if (list2[3]=="yes"){                   
                    var slot = document.createElement('p');
                    slot.setAttribute('class','alert-primary');
                    slot.appendChild(document.createTextNode("Vous êtes inscrit sur le créneau "+list2[1]+"-"+list2[2]));
                    slots.appendChild(slot);
                    already = "yes";
                    id = list2[0];               
                }

                var input = document.createElement('input');           
                var paragraph = document.createElement('p');            
                slots.appendChild(paragraph);
                input.setAttribute("type","radio");
                input.setAttribute("name","slot");
                input.setAttribute("onclick","document.getElementById('participation_ssessionId').value="+list2[0]+";document.getElementById('invitation').href='invitation/"+list2[0]+"'");
                paragraph.appendChild(input);
                if (list2[4]==0 && list2[3]!="yes" && already!="yes"){
                    var slot = document.createTextNode(list2[1]+"-"+list2[2]+" Créneau réservé. Vous pouvez vous inscrire sur liste d'attente. ");           
                }
                else if (list2[4]==0 && list2[3]!="yes" && already=="yes")
                {
                    var slot = document.createTextNode(list2[1]+"-"+list2[2]+" Créneau réservé.");
                    input.setAttribute("disabled","true");
                }
                else if(list2[4]==0 && list2[3]=="yes"){
                    var slot = document.createTextNode(list2[1]+"-"+list2[2]+" Créneau réservé.");
                    input.setAttribute("disabled","true");
                }
                else{
                    var slot = document.createTextNode(list2[1]+"-"+list2[2]);           
                }          
                paragraph.appendChild(slot);
                    
            }   
        
    }
    else{
        document.getElementById('slots').innerHTML = "";
    }
    }
    var chainDateTime = timeLimit.split(' ');
    var chainDate = chainDateTime[0].split('-');
    var chainTime = chainDateTime[1].split(':');
    var chainTimeLimit = chainDate[2]+'/'+chainDate[1]+'/'+chainDate[0]+' '+chainTime[0]+':'+chainTime[1];
    document.getElementById('timeLimit').innerHTML = '<p>Les inscriptions sont ouvertes jusqu\'au : '+chainTimeLimit+'</p>';   
    
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
    
    
    if (ssessionDescription!=""){
        document.getElementById('ssessionDescription').innerHTML = '<p class="h5 font-weight-bold">Description de la séance</p><p>'+ssessionDescription+'</p>';
        document.getElementById('ssessionDescription').setAttribute('class','my-3 mx-2 py-2 px-2 bg-eo text-white shadow');
    }else{
        document.getElementById('ssessionDescription').innerHTML = '';
        document.getElementById('ssessionDescription').setAttribute('class','');
    }
    

    if (already=="yes"){       
        document.getElementById('subscription_timeLimit').style.display = 'none';
        document.getElementById('subscription_form').style.display = 'block';
        document.getElementById('subscription_waitinglist').style.display = 'none';
        document.getElementById('subscription_credit').style.display = 'none';
        document.getElementById('participation_delete_post').style.display = 'block';
        document.getElementById('participation_submit_post').style.display = 'none';     
        document.getElementById('participation_ssessionId').value = id;
        document.getElementById('invitation').style.display = 'block';      
        document.getElementById('invitation').href = "invitation/"+id;      
    }
    else if(already_waiting=="yes"){
        document.getElementById('subscription_timeLimit').style.display = 'none';
        document.getElementById('subscription_form').style.display = 'none';
        document.getElementById('subscription_waitinglist').style.display = 'block';
        document.getElementById('subscription_credit').style.display = 'none';
        document.getElementById('waiting_list_delete_post').style.display = 'block';   
        document.getElementById('waiting_list_submit_post').style.display = 'none';             
        if (waiting_id==0)
         document.getElementById('waiting_list_ssessionId').value = id; 
        else
         document.getElementById('waiting_list_ssessionId').value = waiting_id; 
    }    
    else if(new Date(timeLimit)<new Date()){         
        document.getElementById('subscription_timeLimit').style.display = 'block';      
        document.getElementById('subscription_form').style.display = 'none';
        document.getElementById('subscription_waitinglist').style.display = 'none';
        document.getElementById('subscription_credit').style.display = 'none'; 
        document.getElementById('invitation').style.display = 'none';         
    }    
    else if (parseInt(participation)>=parseInt(participationMax)){   
        document.getElementById('subscription_timeLimit').style.display = 'none';
        document.getElementById('subscription_form').style.display = 'none';
        document.getElementById('subscription_waitinglist').style.display = 'block';
        document.getElementById('waiting_list_delete_post').style.display = 'none';
        document.getElementById('waiting_list_submit_post').style.display = 'block'; 
        document.getElementById('subscription_credit').style.display = 'none'; 
        document.getElementById('invitation').style.display = 'none';  
        document.getElementById('waiting_list_ssessionId').value = id;      
    }
    else if(enoughCredit=="no"){    
        document.getElementById('subscription_timeLimit').style.display = 'none';
        document.getElementById('subscription_waitinglist').style.display = 'none';
        document.getElementById('subscription_form').style.display = 'none';
        document.getElementById('subscription_credit').style.display = 'block';    
    }
    else{
        document.getElementById('subscription_timeLimit').style.display = 'none';
        document.getElementById('subscription_waitinglist').style.display = 'none';
        document.getElementById('subscription_credit').style.display = 'none';
        document.getElementById('subscription_form').style.display = 'block';
        document.getElementById('participation_submit_post').style.display = 'block';
        document.getElementById('participation_delete_post').style.display = 'none';
        document.getElementById('invitation').style.display = 'block';      
        if (coma==-1){
            document.getElementById('participation_ssessionId').value = id; 
            document.getElementById('invitation').href = "invitation/"+id;     
        }
        
    }    
}





    