console.log("Lancement du programme 'ajax formulaire.js'");
document.getElementById("formulaire_reservation").addEventListener("change", ajax_formulaire);

function ajax_formulaire(){
    
    $.ajax({
        type: 'POST',           //La méthode cible (POST ou GET)
        url : 'formulaire_ajax.php', //Script Cible
        asynch : false,          //Ici on force l'appel de manière synchrone
        cache    : false,
        timeout: 6000,
        dataType : "html",
        data:{"date":document.getElementById('date').value,"datef":document.getElementById('datef').value,"place":document.getElementById('place').value,"contact":document.getElementById('contact').value,"nom":document.getElementById('nom').value},
        success : function(response){
            console.log( response );
            $("#resultat_formulaire").html(response);
        }
    });

};